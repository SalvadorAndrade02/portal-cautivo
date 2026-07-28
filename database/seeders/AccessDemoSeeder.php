<?php

namespace Database\Seeders;

use App\Models\AccessAttempt;
use App\Models\AccessSession;
use App\Models\Device;
use App\Models\PortalUser;
use Illuminate\Database\Seeder;

class AccessDemoSeeder extends Seeder
{
    public function run(): void
    {
        $portalUser = PortalUser::query()
            ->with('business')
            ->firstOrFail();

        $device = Device::query()
            ->where('business_id', $portalUser->business_id)
            ->first();

        $mac = $device?->mac_address
            ?? 'AA:BB:CC:DD:EE:FF';

        AccessAttempt::updateOrCreate(
            [
                'username' => $portalUser->username,
                'source' => 'demo',
                'reason' => 'credentials_valid',
            ],
            [
                'portal_user_id' => $portalUser->id,
                'business_id' => $portalUser->business_id,
                'device_id' => $device?->id,
                'ip_address' => '10.50.0.105',
                'mac_address' => $mac,
                'result' => 'accepted',
                'attempted_at' => now()->subMinutes(90),
            ]
        );

        AccessAttempt::updateOrCreate(
            [
                'username' => $portalUser->username,
                'source' => 'demo',
                'reason' => 'invalid_credentials',
            ],
            [
                'portal_user_id' => $portalUser->id,
                'business_id' => $portalUser->business_id,
                'device_id' => $device?->id,
                'ip_address' => '10.50.0.105',
                'mac_address' => $mac,
                'result' => 'rejected',
                'attempted_at' => now()->subMinutes(95),
            ]
        );

        AccessSession::updateOrCreate(
            [
                'radius_session_id' => 'DEMO-SESSION-001',
            ],
            [
                'portal_user_id' => $portalUser->id,
                'business_id' => $portalUser->business_id,
                'device_id' => $device?->id,
                'username' => $portalUser->username,
                'ip_address' => '10.50.0.105',
                'mac_address' => $mac,
                'nas_ip_address' => '192.168.56.2',
                'nas_identifier' => 'OPNSENSE-LAB',
                'started_at' => now()->subMinutes(90),
                'last_update_at' => now(),
                'ended_at' => null,
                'duration_seconds' => 5400,
                'input_bytes' => 125829120,
                'output_bytes' => 31457280,
                'termination_reason' => null,
                'status' => 'active',
            ]
        );
    }
}
