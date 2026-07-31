<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadiusAccountingRequest;
use App\Models\AccessSession;
use App\Models\Device;
use App\Models\PortalUser;
use App\Models\Visitor;
use App\Models\VisitorAccessToken;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RadiusAccountingController extends Controller
{
    public function __invoke(
        RadiusAccountingRequest $request
    ): Response {
        $data = $request->validated();

        /*
         * Identificar si la sesión pertenece a un usuario
         * permanente o a un visitante.
         */
        $portalUser = PortalUser::query()
            ->with('business')
            ->where('username', $data['username'])
            ->first();

        $visitorAccessToken = null;
        $visitor = null;
        $accessType = 'unknown';

        if ($portalUser) {
            $accessType = 'business_user';
        } else {
            $visitorAccessToken = VisitorAccessToken::query()
                ->with([
                    'visitor',
                    'device',
                ])
                ->where(
                    'access_username',
                    $data['username']
                )
                ->first();

            if ($visitorAccessToken) {
                $visitor = $visitorAccessToken->visitor;
                $accessType = 'visitor_registration';
            }
        }

        $device = $this->resolveDevice(
            macAddress: $data['mac_address'] ?? null,
            visitor: $visitor,
            accessToken: $visitorAccessToken
        );

        /*
         * Los visitantes no pertenecen a un local.
         */
        $business = $accessType === 'business_user'
            ? ($portalUser?->business ?? $device?->business)
            : null;

        $eventAt = now();

        $durationSeconds = max(
            0,
            (int) ($data['session_time'] ?? 0)
        );

        $inputBytes = $this->calculateBytes(
            octets: $data['input_octets'] ?? null,
            gigawords: $data['input_gigawords'] ?? null
        );

        $outputBytes = $this->calculateBytes(
            octets: $data['output_octets'] ?? null,
            gigawords: $data['output_gigawords'] ?? null
        );

        DB::transaction(function () use (
            $data,
            $portalUser,
            $visitor,
            $visitorAccessToken,
            $accessType,
            $business,
            $device,
            $eventAt,
            $durationSeconds,
            $inputBytes,
            $outputBytes
        ): void {
            $session = AccessSession::query()
                ->where(
                    'radius_session_id',
                    $data['session_id']
                )
                ->lockForUpdate()
                ->first();

            /*
             * Puede recibirse un Interim-Update o Stop sin que
             * Laravel haya recibido previamente el Start.
             */
            if (!$session) {
                $session = new AccessSession();

                $session->radius_session_id =
                    $data['session_id'];

                $session->started_at =
                    $data['status_type'] === 'start'
                    ? $eventAt
                    : $eventAt
                    ->copy()
                    ->subSeconds($durationSeconds);

                $session->status = 'active';
            }

            $metadata = array_filter([
                'called_station_id' =>
                $data['called_station_id'] ?? null,

                'nas_port' =>
                $data['nas_port'] ?? null,

                'visitor_access_token_id' =>
                $visitorAccessToken?->id,

                'last_accounting_event' =>
                $data['status_type'],
            ], function (mixed $value): bool {
                return $value !== null && $value !== '';
            });

            $session->fill([
                'portal_user_id' => $portalUser?->id
                    ?? $session->portal_user_id,

                'visitor_id' => $visitor?->id
                    ?? $session->visitor_id,

                'access_type' => $accessType !== 'unknown'
                    ? $accessType
                    : (
                        $session->access_type
                        ?: 'unknown'
                    ),

                'business_id' => $business?->id
                    ?? $session->business_id,

                'device_id' => $device?->id
                    ?? $session->device_id,

                'username' => $data['username'],

                'ip_address' => $data['ip_address']
                    ?? $session->ip_address,

                'mac_address' => $data['mac_address']
                    ?? $session->mac_address,

                'nas_ip_address' =>
                $data['nas_ip_address']
                    ?? $session->nas_ip_address,

                'nas_identifier' =>
                $data['nas_identifier']
                    ?? $session->nas_identifier,

                'last_update_at' => $eventAt,
            ]);

            /*
             * Los paquetes UDP pueden llegar duplicados o en un
             * orden diferente. Nunca reducimos los contadores.
             */
            $session->duration_seconds = max(
                (int) ($session->duration_seconds ?? 0),
                $durationSeconds
            );

            $session->input_bytes = max(
                (int) ($session->input_bytes ?? 0),
                $inputBytes
            );

            $session->output_bytes = max(
                (int) ($session->output_bytes ?? 0),
                $outputBytes
            );

            $session->metadata = array_merge(
                $session->metadata ?? [],
                $metadata
            );

            if ($data['status_type'] === 'stop') {
                $session->ended_at = $eventAt;

                $session->termination_reason =
                    $data['termination_reason']
                    ?? 'unknown';

                $session->status = $this->resolveClosedStatus(
                    $data['termination_reason'] ?? null
                );
            } elseif (!$session->ended_at) {
                $session->status = 'active';
            }

            $session->save();

            $this->updateDevice(
                device: $device,
                portalUser: $portalUser,
                visitor: $visitor,
                ipAddress: $data['ip_address'] ?? null,
                eventAt: $eventAt
            );

            if ($visitor) {
                $visitor->update([
                    'last_access_at' => $eventAt,
                ]);
            }

            if ($visitorAccessToken) {
                $visitorAccessToken->update([
                    'device_id' =>
                    $visitorAccessToken->device_id
                        ?? $device?->id,

                    'last_used_at' => $eventAt,
                ]);
            }
        });

        return response()->noContent();
    }

    private function resolveDevice(
        ?string $macAddress,
        ?Visitor $visitor,
        ?VisitorAccessToken $accessToken
    ): ?Device {
        $device = $this->findDevice($macAddress)
            ?? $accessToken?->device;

        /*
         * Para usuarios permanentes no hacemos ninguna
         * adecuación adicional.
         */
        if (!$visitor) {
            return $device;
        }

        /*
         * No relacionar con un visitante un dispositivo que ya
         * pertenece a un local o a otro visitante.
         */
        if (
            $device
            && (
                $device->business_id
                || $device->portal_user_id
                || (
                    $device->visitor_id
                    && $device->visitor_id !== $visitor->id
                )
            )
        ) {
            return null;
        }

        if (
            !$device
            && $macAddress
        ) {
            $device = $this->createVisitorDevice(
                visitor: $visitor,
                macAddress: $macAddress
            );
        }

        if (
            $device
            && !$device->visitor_id
        ) {
            $device->update([
                'visitor_id' => $visitor->id,
                'authorized' => true,
                'blocked' => false,
            ]);
        }

        if (
            $accessToken
            && !$accessToken->device_id
            && $device
        ) {
            $accessToken->update([
                'device_id' => $device->id,
            ]);
        }

        return $device;
    }

    private function findDevice(
        ?string $macAddress
    ): ?Device {
        if (!$macAddress) {
            return null;
        }

        return Device::query()
            ->with('business')
            ->where('mac_address', $macAddress)
            ->first();
    }

    private function createVisitorDevice(
        Visitor $visitor,
        string $macAddress
    ): Device {
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
            'Registrado automáticamente mediante RADIUS Accounting.',
        ]);
    }

    private function updateDevice(
        ?Device $device,
        ?PortalUser $portalUser,
        ?Visitor $visitor,
        ?string $ipAddress,
        Carbon $eventAt
    ): void {
        if (!$device) {
            return;
        }

        $updates = [
            'last_seen_at' => $eventAt,

            'first_seen_at' => $device->first_seen_at
                ?? $eventAt,
        ];

        if ($ipAddress) {
            $updates['last_ip_address'] = $ipAddress;
        }

        if (
            $portalUser
            && !$device->portal_user_id
            && !$device->visitor_id
        ) {
            $updates['portal_user_id'] =
                $portalUser->id;
        }

        if (
            $visitor
            && !$device->visitor_id
            && !$device->business_id
            && !$device->portal_user_id
        ) {
            $updates['visitor_id'] = $visitor->id;
        }

        $device->update($updates);
    }

    private function calculateBytes(
        mixed $octets,
        mixed $gigawords
    ): int {
        $octetsValue = max(
            0,
            (int) ($octets ?? 0)
        );

        $gigawordsValue = max(
            0,
            (int) ($gigawords ?? 0)
        );

        return $octetsValue
            + ($gigawordsValue * 4294967296);
    }

    private function resolveClosedStatus(
        ?string $terminationReason
    ): string {
        $reason = strtolower(
            trim((string) $terminationReason)
        );

        return match ($reason) {
            'session-timeout',
            'idle-timeout' => 'expired',

            'admin-reset',
            'nas-request',
            'nas-reboot',
            'lost-carrier',
            'port-error' => 'disconnected',

            default => 'closed',
        };
    }
}
