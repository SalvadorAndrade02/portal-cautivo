<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadiusAccountingRequest;
use App\Models\AccessSession;
use App\Models\Device;
use App\Models\PortalUser;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RadiusAccountingController extends Controller
{
    public function __invoke(
        RadiusAccountingRequest $request
    ): Response {
        $data = $request->validated();

        $portalUser = PortalUser::query()
            ->with('business')
            ->where('username', $data['username'])
            ->first();

        $device = $this->findDevice(
            $data['mac_address'] ?? null
        );

        $business = $portalUser?->business
            ?? $device?->business;

        $eventAt = now();

        $durationSeconds = (int) (
            $data['session_time'] ?? 0
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

            if (!$session) {
                $session = new AccessSession();

                $session->radius_session_id =
                    $data['session_id'];

                /*
                 * Si recibimos un Interim-Update o Stop sin Start,
                 * calculamos una hora aproximada de inicio.
                 */
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
            ], function (mixed $value): bool {
                return $value !== null && $value !== '';
            });

            $session->fill([
                'portal_user_id' => $portalUser?->id
                    ?? $session->portal_user_id,

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
             * Evitamos reducir contadores si llega un paquete atrasado
             * o duplicado.
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
                /*
                 * Start e Interim-Update mantienen activa
                 * la sesión.
                 */
                $session->status = 'active';
            }

            $session->save();

            $this->updateDevice(
                device: $device,
                portalUser: $portalUser,
                ipAddress: $data['ip_address'] ?? null,
                eventAt: $eventAt
            );
        });

        /*
         * Accounting necesita una respuesta exitosa sin contenido.
         * Esto evita que FreeRADIUS reintente el mismo evento.
         */
        return response()->noContent();
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

    private function updateDevice(
        ?Device $device,
        ?PortalUser $portalUser,
        ?string $ipAddress,
        mixed $eventAt
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
            !$device->portal_user_id
            && $portalUser
        ) {
            $updates['portal_user_id'] = $portalUser->id;
        }

        $device->update($updates);
    }

    private function calculateBytes(
        mixed $octets,
        mixed $gigawords
    ): int {
        $octetsValue = max(0, (int) ($octets ?? 0));
        $gigawordsValue = max(0, (int) ($gigawords ?? 0));

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
