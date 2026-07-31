<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\RadiusAuthenticateRequest;
use App\Models\AccessAttempt;
use App\Models\Business;
use App\Models\Device;
use App\Models\PortalUser;
use App\Models\Visitor;
use App\Models\VisitorAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class RadiusAuthenticationController extends Controller
{
    public function __invoke(
        RadiusAuthenticateRequest $request
    ): JsonResponse|Response {
        $data = $request->validated();

        /*
         * Primero buscamos usuarios permanentes de los locales.
         */
        $portalUser = PortalUser::query()
            ->with('business.plan')
            ->where('username', $data['username'])
            ->first();

        if ($portalUser) {
            return $this->authenticateBusinessUser(
                data: $data,
                portalUser: $portalUser
            );
        }

        /*
         * Si no es un usuario de local, buscamos una credencial
         * temporal generada por el registro de visitantes.
         */
        $visitorAccessToken = VisitorAccessToken::query()
            ->with([
                'visitor',
                'device',
            ])
            ->where('access_username', $data['username'])
            ->first();

        if ($visitorAccessToken) {
            return $this->authenticateVisitor(
                data: $data,
                accessToken: $visitorAccessToken
            );
        }

        return $this->reject(
            data: $data,
            reason: 'unknown_user',
            accessType: 'unknown',
            portalUser: null,
            visitor: null,
            business: null,
            device: $this->findDevice(
                $data['mac_address'] ?? null
            ),
            visitorAccessToken: null
        );
    }

    private function authenticateBusinessUser(
        array $data,
        PortalUser $portalUser
    ): JsonResponse|Response {
        $device = $this->findDevice(
            $data['mac_address'] ?? null
        );

        $business = $portalUser->business;

        if (
            !Hash::check(
                $data['password'],
                $portalUser->password
            )
        ) {
            return $this->reject(
                data: $data,
                reason: 'invalid_credentials',
                accessType: 'business_user',
                portalUser: $portalUser,
                visitor: null,
                business: $business,
                device: $device,
                visitorAccessToken: null
            );
        }

        if ($portalUser->status !== 'active') {
            return $this->reject(
                data: $data,
                reason: 'user_' . $portalUser->status,
                accessType: 'business_user',
                portalUser: $portalUser,
                visitor: null,
                business: $business,
                device: $device,
                visitorAccessToken: null
            );
        }

        if (!$business) {
            return $this->reject(
                data: $data,
                reason: 'business_not_found',
                accessType: 'business_user',
                portalUser: $portalUser,
                visitor: null,
                business: null,
                device: $device,
                visitorAccessToken: null
            );
        }

        if ($business->status !== 'active') {
            return $this->reject(
                data: $data,
                reason: 'business_' . $business->status,
                accessType: 'business_user',
                portalUser: $portalUser,
                visitor: null,
                business: $business,
                device: $device,
                visitorAccessToken: null
            );
        }

        $plan = $business->plan;

        if (!$plan) {
            return $this->reject(
                data: $data,
                reason: 'business_without_plan',
                accessType: 'business_user',
                portalUser: $portalUser,
                visitor: null,
                business: $business,
                device: $device,
                visitorAccessToken: null
            );
        }

        if (!$plan->active) {
            return $this->reject(
                data: $data,
                reason: 'plan_inactive',
                accessType: 'business_user',
                portalUser: $portalUser,
                visitor: null,
                business: $business,
                device: $device,
                visitorAccessToken: null
            );
        }

        if ($device) {
            if ($device->visitor_id) {
                return $this->reject(
                    data: $data,
                    reason: 'device_assigned_to_visitor',
                    accessType: 'business_user',
                    portalUser: $portalUser,
                    visitor: null,
                    business: $business,
                    device: $device,
                    visitorAccessToken: null
                );
            }

            if ($device->business_id !== $business->id) {
                return $this->reject(
                    data: $data,
                    reason: 'device_assigned_to_other_business',
                    accessType: 'business_user',
                    portalUser: $portalUser,
                    visitor: null,
                    business: $business,
                    device: $device,
                    visitorAccessToken: null
                );
            }

            if ($device->blocked) {
                return $this->reject(
                    data: $data,
                    reason: 'device_blocked',
                    accessType: 'business_user',
                    portalUser: $portalUser,
                    visitor: null,
                    business: $business,
                    device: $device,
                    visitorAccessToken: null
                );
            }

            if (!$device->authorized) {
                return $this->reject(
                    data: $data,
                    reason: 'device_not_authorized',
                    accessType: 'business_user',
                    portalUser: $portalUser,
                    visitor: null,
                    business: $business,
                    device: $device,
                    visitorAccessToken: null
                );
            }
        }

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
                    accessType: 'business_user',
                    portalUser: $portalUser,
                    visitor: null,
                    business: $business,
                    device: null,
                    visitorAccessToken: null
                );
            }

            $device = $this->createBusinessDevice(
                data: $data,
                portalUser: $portalUser,
                business: $business
            );
        }

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
            accessType: 'business_user',
            portalUser: $portalUser,
            visitor: null,
            business: $business,
            device: $device,
            visitorAccessToken: null
        );

        return response()->noContent();
    }

    private function authenticateVisitor(
        array $data,
        VisitorAccessToken $accessToken
    ): JsonResponse|Response {
        $visitor = $accessToken->visitor;

        if (
            !Hash::check(
                $data['password'],
                $accessToken->token_hash
            )
        ) {
            return $this->reject(
                data: $data,
                reason: 'invalid_credentials',
                accessType: 'visitor_registration',
                portalUser: null,
                visitor: $visitor,
                business: null,
                device: $accessToken->device,
                visitorAccessToken: $accessToken
            );
        }

        if (!$visitor) {
            return $this->reject(
                data: $data,
                reason: 'visitor_not_found',
                accessType: 'visitor_registration',
                portalUser: null,
                visitor: null,
                business: null,
                device: $accessToken->device,
                visitorAccessToken: $accessToken
            );
        }

        if ($visitor->status !== 'active') {
            return $this->reject(
                data: $data,
                reason: 'visitor_' . $visitor->status,
                accessType: 'visitor_registration',
                portalUser: null,
                visitor: $visitor,
                business: null,
                device: $accessToken->device,
                visitorAccessToken: $accessToken
            );
        }

        if (
            $accessToken->status !== 'active'
            || $accessToken->revoked_at
        ) {
            return $this->reject(
                data: $data,
                reason: 'visitor_token_' . $accessToken->status,
                accessType: 'visitor_registration',
                portalUser: null,
                visitor: $visitor,
                business: null,
                device: $accessToken->device,
                visitorAccessToken: $accessToken
            );
        }

        if ($accessToken->expires_at->isPast()) {
            $accessToken->update([
                'status' => 'expired',
            ]);

            return $this->reject(
                data: $data,
                reason: 'visitor_token_expired',
                accessType: 'visitor_registration',
                portalUser: null,
                visitor: $visitor,
                business: null,
                device: $accessToken->device,
                visitorAccessToken: $accessToken
            );
        }

        $incomingDevice = $this->findDevice(
            $data['mac_address'] ?? null
        );

        $tokenDevice = $accessToken->device;

        if (
            $incomingDevice
            && $tokenDevice
            && $incomingDevice->id !== $tokenDevice->id
        ) {
            return $this->reject(
                data: $data,
                reason: 'visitor_token_device_mismatch',
                accessType: 'visitor_registration',
                portalUser: null,
                visitor: $visitor,
                business: null,
                device: $incomingDevice,
                visitorAccessToken: $accessToken
            );
        }

        $device = $incomingDevice ?? $tokenDevice;

        if ($device) {
            if (
                $device->business_id
                || $device->portal_user_id
            ) {
                return $this->reject(
                    data: $data,
                    reason: 'device_assigned_to_business',
                    accessType: 'visitor_registration',
                    portalUser: null,
                    visitor: $visitor,
                    business: $device->business,
                    device: $device,
                    visitorAccessToken: $accessToken
                );
            }

            if (
                $device->visitor_id
                && $device->visitor_id !== $visitor->id
            ) {
                return $this->reject(
                    data: $data,
                    reason: 'device_assigned_to_other_visitor',
                    accessType: 'visitor_registration',
                    portalUser: null,
                    visitor: $visitor,
                    business: null,
                    device: $device,
                    visitorAccessToken: $accessToken
                );
            }

            if ($device->blocked) {
                return $this->reject(
                    data: $data,
                    reason: 'device_blocked',
                    accessType: 'visitor_registration',
                    portalUser: null,
                    visitor: $visitor,
                    business: null,
                    device: $device,
                    visitorAccessToken: $accessToken
                );
            }

            if (
                $device->visitor_id === $visitor->id
                && !$device->authorized
            ) {
                return $this->reject(
                    data: $data,
                    reason: 'device_not_authorized',
                    accessType: 'visitor_registration',
                    portalUser: null,
                    visitor: $visitor,
                    business: null,
                    device: $device,
                    visitorAccessToken: $accessToken
                );
            }

            if (!$device->visitor_id) {
                $device->update([
                    'visitor_id' => $visitor->id,
                    'authorized' => true,
                    'blocked' => false,
                ]);
            }
        }

        if (
            !$device
            && !empty($data['mac_address'])
        ) {
            $device = $this->createVisitorDevice(
                data: $data,
                visitor: $visitor
            );
        }

        if ($device) {
            $device->update([
                'visitor_id' => $visitor->id,
                'last_ip_address' => $data['ip_address']
                    ?? $device->last_ip_address,
                'last_seen_at' => now(),
                'first_seen_at' => $device->first_seen_at
                    ?? now(),
            ]);
        }

        $accessToken->update([
            'device_id' => $accessToken->device_id
                ?? $device?->id,

            'used_at' => $accessToken->used_at
                ?? now(),

            'last_used_at' => now(),
        ]);

        $visitor->update([
            'last_access_at' => now(),
        ]);

        $this->recordAttempt(
            data: $data,
            result: 'accepted',
            reason: 'visitor_credentials_valid',
            accessType: 'visitor_registration',
            portalUser: null,
            visitor: $visitor,
            business: null,
            device: $device,
            visitorAccessToken: $accessToken
        );

        return response()->noContent();
    }

    private function reject(
        array $data,
        string $reason,
        string $accessType,
        ?PortalUser $portalUser,
        ?Visitor $visitor,
        ?Business $business,
        ?Device $device,
        ?VisitorAccessToken $visitorAccessToken
    ): JsonResponse {
        $this->recordAttempt(
            data: $data,
            result: 'rejected',
            reason: $reason,
            accessType: $accessType,
            portalUser: $portalUser,
            visitor: $visitor,
            business: $business,
            device: $device,
            visitorAccessToken: $visitorAccessToken
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
        string $accessType,
        ?PortalUser $portalUser,
        ?Visitor $visitor,
        ?Business $business,
        ?Device $device,
        ?VisitorAccessToken $visitorAccessToken
    ): void {
        AccessAttempt::create([
            'portal_user_id' => $portalUser?->id,
            'visitor_id' => $visitor?->id,
            'business_id' => $business?->id,
            'device_id' => $device?->id,

            'access_type' => $accessType,
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

                'visitor_access_token_id' =>
                $visitorAccessToken?->id,
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
            ->with('business')
            ->where('mac_address', $macAddress)
            ->first();
    }

    private function createBusinessDevice(
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
            'visitor_id' => null,

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

    private function createVisitorDevice(
        array $data,
        Visitor $visitor
    ): Device {
        $suffix = substr(
            str_replace(':', '', $data['mac_address']),
            -4
        );

        return Device::create([
            'business_id' => null,
            'portal_user_id' => null,
            'visitor_id' => $visitor->id,

            'name' => 'Dispositivo visitante ' . $suffix,
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
            'Registrado durante una autenticación temporal de visitante.',
        ]);
    }
}
