<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadiusAuthenticateRequest;
use App\Models\AccessAttempt;
use App\Models\Business;
use App\Models\Device;
use App\Models\PortalUser;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RadiusAuthenticationController extends Controller
{
    public function __invoke(
        RadiusAuthenticateRequest $request
    ): JsonResponse|Response {
        $data = $request->validated();

        $portalUser = PortalUser::query()
            ->with([
                'business.plan',
            ])
            ->where('username', $data['username'])
            ->first();

        $device = $this->findDevice(
            $data['mac_address'] ?? null
        );

        /*
         * 1. Comprobar existencia del usuario.
         */
        if (!$portalUser) {
            return $this->reject(
                data: $data,
                reason: 'unknown_user',
                portalUser: null,
                business: null,
                device: $device
            );
        }

        $business = $portalUser->business;

        /*
         * 2. Comprobar la contraseña.
         */
        if (
            !Hash::check(
                $data['password'],
                $portalUser->password
            )
        ) {
            return $this->reject(
                data: $data,
                reason: 'invalid_credentials',
                portalUser: $portalUser,
                business: $business,
                device: $device
            );
        }

        /*
         * 3. Comprobar el estado del usuario.
         */
        if ($portalUser->status !== 'active') {
            return $this->reject(
                data: $data,
                reason: 'user_' . $portalUser->status,
                portalUser: $portalUser,
                business: $business,
                device: $device
            );
        }

        /*
         * 4. Comprobar que el usuario tenga local.
         */
        if (!$business) {
            return $this->reject(
                data: $data,
                reason: 'business_not_found',
                portalUser: $portalUser,
                business: null,
                device: $device
            );
        }

        /*
         * 5. Comprobar el estado del local.
         */
        if ($business->status !== 'active') {
            return $this->reject(
                data: $data,
                reason: 'business_' . $business->status,
                portalUser: $portalUser,
                business: $business,
                device: $device
            );
        }

        $plan = $business->plan;

        /*
         * 6. Comprobar que exista un plan.
         */
        if (!$plan) {
            return $this->reject(
                data: $data,
                reason: 'business_without_plan',
                portalUser: $portalUser,
                business: $business,
                device: $device
            );
        }

        /*
         * 7. Comprobar que el plan esté activo.
         */
        if (!$plan->active) {
            return $this->reject(
                data: $data,
                reason: 'plan_inactive',
                portalUser: $portalUser,
                business: $business,
                device: $device
            );
        }

        /*
         * 8. Validaciones relacionadas con la MAC.
         */
        if ($device) {
            if ($device->business_id !== $business->id) {
                return $this->reject(
                    data: $data,
                    reason: 'device_assigned_to_other_business',
                    portalUser: $portalUser,
                    business: $business,
                    device: $device
                );
            }

            if ($device->blocked) {
                return $this->reject(
                    data: $data,
                    reason: 'device_blocked',
                    portalUser: $portalUser,
                    business: $business,
                    device: $device
                );
            }

            if (!$device->authorized) {
                return $this->reject(
                    data: $data,
                    reason: 'device_not_authorized',
                    portalUser: $portalUser,
                    business: $business,
                    device: $device
                );
            }
        }

        /*
         * 9. Registrar automáticamente una MAC nueva.
         */
        if (
            !$device
            && !empty($data['mac_address'])
        ) {
            $registeredDevices = $business
                ->devices()
                ->where('blocked', false)
                ->count();

            if (
                $registeredDevices
                >= $business->effective_max_devices
            ) {
                return $this->reject(
                    data: $data,
                    reason: 'max_devices_reached',
                    portalUser: $portalUser,
                    business: $business,
                    device: null
                );
            }

            $device = $this->createDevice(
                data: $data,
                portalUser: $portalUser,
                business: $business
            );
        }

        /*
         * 10. Actualizar última conexión.
         */
        if ($device) {
            $device->update([
                'portal_user_id' => $device->portal_user_id
                    ?? $portalUser->id,

                'last_ip_address' => $data['ip_address']
                    ?? $device->last_ip_address,

                'last_seen_at' => now(),

                'first_seen_at' => $device->first_seen_at
                    ?? now(),
            ]);
        }

        $portalUser->update([
            'last_login_at' => now(),
        ]);

        $this->recordAttempt(
            data: $data,
            result: 'accepted',
            reason: 'credentials_valid',
            portalUser: $portalUser,
            business: $business,
            device: $device
        );

        /*
         * Laravel usa minutos, pero RADIUS utiliza segundos.
         */
        return response()->noContent();
    }

    private function reject(
        array $data,
        string $reason,
        ?PortalUser $portalUser,
        ?Business $business,
        ?Device $device
    ): JsonResponse {
        $this->recordAttempt(
            data: $data,
            result: 'rejected',
            reason: $reason,
            portalUser: $portalUser,
            business: $business,
            device: $device
        );

        return response()->json([
            'authorized' => false,
            'reason' => $reason,
        ], 401);
    }

    private function recordAttempt(
        array $data,
        string $result,
        string $reason,
        ?PortalUser $portalUser,
        ?Business $business,
        ?Device $device
    ): void {
        AccessAttempt::create([
            'portal_user_id' => $portalUser?->id,
            'business_id' => $business?->id,
            'device_id' => $device?->id,

            'username' => $data['username'],

            'ip_address' => $data['ip_address']
                ?? null,

            'mac_address' => $data['mac_address']
                ?? null,

            'result' => $result,
            'reason' => $reason,
            'source' => 'radius_api',

            'metadata' => [
                'nas_ip_address' =>
                $data['nas_ip_address'] ?? null,

                'nas_identifier' =>
                $data['nas_identifier'] ?? null,
            ],

            'attempted_at' => now(),
        ]);
    }

    private function findDevice(
        ?string $macAddress
    ): ?Device {
        if (!$macAddress) {
            return null;
        }

        return Device::query()
            ->where('mac_address', $macAddress)
            ->first();
    }

    private function createDevice(
        array $data,
        PortalUser $portalUser,
        Business $business
    ): Device {
        $suffix = substr(
            str_replace(':', '', $data['mac_address']),
            -4
        );

        return Device::create([
            'business_id' => $business->id,
            'portal_user_id' => $portalUser->id,

            'name' => 'Dispositivo detectado ' . $suffix,
            'device_type' => 'other',

            'mac_address' => $data['mac_address'],
            'last_ip_address' => $data['ip_address']
                ?? null,

            'authorized' => true,
            'blocked' => false,
            'bypass_portal' => false,

            'first_seen_at' => now(),
            'last_seen_at' => now(),

            'notes' =>
            'Registrado automáticamente durante una autenticación RADIUS.',
        ]);
    }
}
