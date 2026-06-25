<?php

namespace Database\Seeders;

use App\Models\Clan;
use Illuminate\Database\Seeder;

class ClanSeeder extends Seeder
{
    public function run(): void
    {
        $clans = [
            ['name' => 'Luba', 'region' => 'Kasaï', 'description' => 'Clan Luba — grande ethnie du centre-sud de la RDC.'],
            ['name' => 'Yaka', 'region' => 'Kwango', 'description' => 'Clan Yaka — peuple du sud-ouest congolais.'],
            ['name' => 'Kongo', 'region' => 'Kongo Central', 'description' => 'Clan Kongo — royaume historique du Bas-Congo.'],
            ['name' => 'Suku', 'region' => 'Kwilu', 'description' => 'Clan Suku — ethnie du Bandundu.'],
        ];

        foreach ($clans as $clan) {
            Clan::updateOrCreate(['name' => $clan['name']], $clan);
        }
    }
}
