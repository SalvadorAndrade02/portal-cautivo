<?php

namespace Database\Seeders;

use App\Models\InterestArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterestAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Motocicletas',
                'description' =>
                'Motocicletas, modelos y novedades.',
            ],
            [
                'name' => 'Vehículos nuevos',
                'description' =>
                'Automóviles y vehículos nuevos.',
            ],
            [
                'name' => 'Vehículos seminuevos',
                'description' =>
                'Automóviles y vehículos seminuevos.',
            ],
            [
                'name' => 'Accesorios',
                'description' =>
                'Accesorios para vehículos y motocicletas.',
            ],
            [
                'name' => 'Restaurantes',
                'description' =>
                'Oferta gastronómica de la plaza.',
            ],
            [
                'name' => 'Entretenimiento',
                'description' =>
                'Actividades y espacios de entretenimiento.',
            ],
            [
                'name' => 'Promociones',
                'description' =>
                'Promociones y novedades de la plaza.',
            ],
        ];

        foreach ($areas as $index => $area) {
            InterestArea::updateOrCreate(
                [
                    'slug' => Str::slug($area['name']),
                ],
                [
                    'name' => $area['name'],
                    'description' => $area['description'],
                    'active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
