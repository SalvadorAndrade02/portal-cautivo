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

class VisitorRegistrationController extends Controller
{
    public function create(): View
    {
        $interestAreas = InterestArea::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'wifi.register',
            compact('interestAreas')
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
                ];
            }
        );

        return to_route('wifi.register.success')
            ->with('visitor_access', $registration);
    }

    public function success(): View|RedirectResponse
    {
        $visitorAccess = session('visitor_access');

        if (!is_array($visitorAccess)) {
            return to_route('wifi.register.create');
        }

        return view(
            'wifi.success',
            compact('visitorAccess')
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
}
