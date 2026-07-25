<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Plan;
use App\Models\PortalUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::updateOrCreate(
            [
                'name' => 'Plan Demo 50 Mbps',
            ],
            [
                'description' => 'Plan utilizado para las pruebas del portal cautivo.',
                'download_speed_mbps' => 50,
                'upload_speed_mbps' => 20,
                'session_timeout_minutes' => 480,
                'idle_timeout_minutes' => 15,
                'max_devices' => 3,
                'active' => true,
            ]
        );

        $business = Business::updateOrCreate(
            [
                'local_number' => 'A-01',
            ],
            [
                'plan_id' => $plan->id,
                'name' => 'Cafetería Demo',
                'responsible_name' => 'Responsable de prueba',
                'email' => 'cafeteria@example.com',
                'phone' => '0000000000',
                'address' => 'Local A-01',
                'status' => 'active',
                'max_devices' => null,
            ]
        );

        PortalUser::updateOrCreate(
            [
                'username' => 'cafeteria01',
            ],
            [
                'business_id' => $business->id,
                'password' => Hash::make('Prueba2026!'),
                'full_name' => 'Usuario de prueba',
                'status' => 'active',
            ]
        );
    }
}
