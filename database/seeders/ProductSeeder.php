<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('role', 'admin')->orderBy('id')->value('id');

        $products = [
            ['name' => 'Premium', 'sku' => 'SF-00001', 'location' => 'Rak A1 – A5', 'capacity' => 450000],
            ['name' => 'Medium', 'sku' => 'SF-00002', 'location' => 'Rak B1 – B5', 'capacity' => 400000],
            ['name' => 'Organik', 'sku' => 'SF-00003', 'location' => 'Rak C1 – C3', 'capacity' => 200000],
            ['name' => 'Ketan', 'sku' => 'SF-00004', 'location' => 'Rak D1 – D2', 'capacity' => 100000],
            ['name' => 'Beras Merah', 'sku' => 'SF-00005', 'location' => 'Rak E1 – E2', 'capacity' => 100000],
            ['name' => 'Beras Hitam', 'sku' => 'SF-00006', 'location' => 'Rak F1 – F2', 'capacity' => 80000],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['sku' => $p['sku']],
                [
                    'owner_id' => $ownerId,
                    'name' => $p['name'],
                    'location' => $p['location'],
                    'capacity' => $p['capacity'],
                    'temperature' => '20–25°C',
                    'humidity' => '60–70%',
                ]
            );
        }
    }
}
