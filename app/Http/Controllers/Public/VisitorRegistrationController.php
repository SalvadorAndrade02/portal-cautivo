<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorRegistrationRequest;
use App\Models\Device;
use App\Models\InterestArea;
use App\Models\Visitor;
use App\Models\VisitorAccessToken;
use App\Services\TurnstileVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use JsonException;

class VisitorRegistrationController extends Controller
{
    public function create(
        Request $request
    ): View {
        /*
     * Si todavía estamos dentro de la ventana
     * CaptivePortalLogin de Android, mostramos
     * únicamente una pantalla para abrir Chrome.
     */
        if (!$request->boolean('browser')) {
            $browserUrl =
                $request->fullUrlWithQuery([
                    'browser' => 1,
                ]);

            return view(
                'wifi.launcher',
                compact('browserUrl')
            );
        }

        /*
     * Desde aquí ya estamos en Chrome.
     */
        $interestAreas =
            InterestArea::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $allowedOrigins = collect(
            config(
                'captive_portal.allowed_origins',
                []
            )
        )->map(
            fn(string $origin): string =>
            rtrim(
                trim($origin),
                '/'
            )
        );

        $requestedOrigin =
            rtrim(
                trim(
                    (string) $request->query(
                        'portal_origin',
                        config(
                            'captive_portal.default_origin'
                        )
                    )
                ),
                '/'
            );

        $portalOrigin =
            $allowedOrigins->contains(
                $requestedOrigin
            )
            ? $requestedOrigin
            : (string) config(
                'captive_portal.default_origin'
            );

        $redirectUrl =
            $this->resolveRedirectUrl(
                $request->query(
                    'redirect_url'
                )
            );

        return view(
            'wifi.register',
            compact(
                'interestAreas',
                'portalOrigin',
                'redirectUrl'
            )
        );
    }

    public function store(
        StoreVisitorRegistrationRequest $request,
        TurnstileVerifier $turnstileVerifier
    ): RedirectResponse {
        $data = $request->validated();

        $turnstileIsValid = $turnstileVerifier->verify(
            token: $data['cf-turnstile-response'],
            remoteIp: $request->ip()
        );

        if (!$turnstileIsValid) {
            throw ValidationException::withMessages([
                'turnstile' =>
                'La verificación de seguridad no fue válida. Intenta nuevamente.',
            ]);
        }

        $registration = DB::transaction(
            function () use ($data, $request): array {
                $visitor = Visitor::query()
                    ->where('email', $data['email'])
                    ->first();

                if (!$visitor) {
                    $visitor = Visitor::query()
                        ->where('phone', $data['phone'])
                        ->first();
                }

                if (
                    $visitor
                    && in_array(
                        $visitor->status,
                        ['blocked', 'disabled'],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'email' =>
                        'Este registro no puede utilizar actualmente la red.',
                    ]);
                }

                if (!$visitor) {
                    $visitor = new Visitor();
                    $visitor->registered_at = now();
                    $visitor->status = 'active';
                }

                $visitor->fill([
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                ]);

                $visitor->save();

                $visitor->interestAreas()->sync(
                    $data['interest_area_ids']
                );

                $interestRedirectUrl = InterestArea::query()
                    ->whereIn(
                        'id',
                        $data['interest_area_ids']
                    )
                    ->where('active', true)
                    ->whereNotNull('redirect_url')
                    ->where('redirect_url', '!=', '')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->value('redirect_url');

                $visitor->consents()->create([
                    'privacy_notice_version' => config(
                        'captive_portal.privacy_notice_version'
                    ),

                    'terms_version' => config(
                        'captive_portal.terms_version'
                    ),

                    'privacy_accepted_at' => now(),
                    'terms_accepted_at' => now(),

                    'marketing_consent' =>
                    $data['marketing_consent'],

                    'marketing_consent_at' =>
                    $data['marketing_consent']
                        ? now()
                        : null,

                    'ip_address' => $request->ip(),

                    'mac_address' =>
                    $data['mac_address'] ?? null,

                    'user_agent' => Str::limit(
                        (string) $request->userAgent(),
                        500,
                        ''
                    ),

                    'source' => 'captive_portal',
                ]);

                $device = $this->resolveDevice(
                    visitor: $visitor,
                    macAddress: $data['mac_address'] ?? null
                );

                VisitorAccessToken::query()
                    ->where('visitor_id', $visitor->id)
                    ->where('status', 'active')
                    ->when(
                        $device,
                        fn($query) => $query->where(
                            'device_id',
                            $device->id
                        )
                    )
                    ->update([
                        'status' => 'revoked',
                        'revoked_at' => now(),
                    ]);

                $plainToken = Str::random(48);

                $accessToken = VisitorAccessToken::create([
                    'visitor_id' => $visitor->id,
                    'device_id' => $device?->id,

                    'access_username' =>
                    $this->generateAccessUsername(),

                    'token_hash' => Hash::make($plainToken),

                    'expires_at' => now()->addHours(
                        config(
                            'captive_portal.visitor_access_ttl_hours'
                        )
                    ),

                    'status' => 'active',

                    'metadata' => [
                        'registration_ip' => $request->ip(),
                        'source' => 'visitor_registration',
                    ],
                ]);


                return [
                    'visitor_name' => $visitor->full_name,
                    'username' => $accessToken->access_username,
                    'password' => $plainToken,
                    'expires_at' => $accessToken
                        ->expires_at
                        ->toIso8601String(),

                    'portal_origin' => $data['portal_origin'],

                    'redirect_url' => $interestRedirectUrl
                        ?? config(
                            'captive_portal.post_login_redirect_url'
                        ),
                ];
            }
        );

        return to_route('wifi.register.success')
            ->with('visitor_access', $registration);
    }

    public function success(): View|RedirectResponse
    {
        $visitorAccess =
            session('visitor_access');

        if (!is_array($visitorAccess)) {
            return to_route(
                'wifi.register.create'
            );
        }

        $payload = [
            'visitor_name' =>
            $visitorAccess['visitor_name']
                ?? 'Visitante',

            'username' =>
            $visitorAccess['username']
                ?? '',

            'password' =>
            $visitorAccess['password']
                ?? '',

            'portal_origin' =>
            $visitorAccess['portal_origin']
                ?? '',

            'redirect_url' =>
            $this->resolveRedirectUrl(
                $visitorAccess['redirect_url'] ?? null
            ),

            /*
         * El handoff solo puede utilizarse
         * durante los próximos 3 minutos.
         */
            'expires_at' =>
            now()
                ->addMinutes(3)
                ->timestamp,
        ];

        $encryptedHandoff =
            Crypt::encryptString(
                json_encode(
                    $payload,
                    JSON_THROW_ON_ERROR
                )
            );

        /*
     * Generamos una ruta RELATIVA.
     *
     * Esto evita volver a tener el problema
     * de localhost en el celular.
     */

        return view(
            'wifi.success',
            compact(
                'visitorAccess',
            )
        );
    }

    private function resolveDevice(
        Visitor $visitor,
        ?string $macAddress
    ): ?Device {
        if (!$macAddress) {
            return null;
        }

        $device = Device::query()
            ->where('mac_address', $macAddress)
            ->first();

        if (
            $device
            && $device->visitor_id
            && $device->visitor_id !== $visitor->id
        ) {
            throw ValidationException::withMessages([
                'mac_address' =>
                'El dispositivo ya está relacionado con otro visitante.',
            ]);
        }

        /*
         * No reasignamos automáticamente dispositivos de locales.
         */
        if (
            $device
            && (
                $device->business_id
                || $device->portal_user_id
            )
        ) {
            return null;
        }

        if ($device) {
            $device->update([
                'visitor_id' => $visitor->id,
                'authorized' => true,
                'blocked' => false,
                'last_seen_at' => now(),
                'first_seen_at' => $device->first_seen_at
                    ?? now(),
            ]);

            return $device;
        }

        $suffix = substr(
            str_replace(':', '', $macAddress),
            -4
        );

        return Device::create([
            'business_id' => null,
            'portal_user_id' => null,
            'visitor_id' => $visitor->id,

            'name' => 'Dispositivo visitante ' . $suffix,
            'device_type' => 'other',
            'mac_address' => $macAddress,

            'authorized' => true,
            'blocked' => false,
            'bypass_portal' => false,

            'first_seen_at' => now(),
            'last_seen_at' => now(),

            'notes' =>
            'Registrado desde el formulario público del portal cautivo.',
        ]);
    }

    private function generateAccessUsername(): string
    {
        do {
            $username = 'visitor_'
                . strtoupper(Str::random(12));
        } while (
            VisitorAccessToken::query()
            ->where('access_username', $username)
            ->exists()
        );

        return $username;
    }

    private function resolveRedirectUrl(
        mixed $value
    ): string {
        $fallback = (string) config(
            'captive_portal.post_login_redirect_url',
            'http://neverssl.com'
        );

        if (!is_string($value) || trim($value) === '') {
            return $fallback;
        }

        $url = trim($value);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $fallback;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (!in_array($scheme, ['http', 'https'], true)) {
            return $fallback;
        }

        return $url;
    }
}
