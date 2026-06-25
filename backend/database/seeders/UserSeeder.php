<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Admin LC',
                'email' => 'admin@libota-connexion.cd',
                'phone' => '+243 900 000 001',
                'role' => 'admin',
            ],
            [
                'name' => 'Historien Familial',
                'email' => 'historien@libota-connexion.cd',
                'phone' => '+243 900 000 002',
                'role' => 'historian',
            ],
            [
                'name' => 'Membre Kabasele',
                'email' => 'membre@libota-connexion.cd',
                'phone' => '+243 900 000 003',
                'role' => 'member',
            ],
            [
                'name' => 'Invité Diaspora',
                'email' => 'invite@libota-connexion.cd',
                'phone' => '+243 900 000 004',
                'role' => 'guest',
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'phone' => $account['phone'],
                    'role' => $account['role'],
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'email_verified_at' => now(),
                ]
            );
        }

        if (User::where('role', 'member')->count() <= 4) {
            User::factory()->count(15)->member()->create();
        }

        if (User::where('role', 'historian')->count() <= 4) {
            User::factory()->count(3)->historian()->create();
        }

        if (User::where('role', 'guest')->count() <= 4) {
            User::factory()->count(5)->guest()->create();
        }

        $this->command?->info('Comptes de test créés (mot de passe : '.self::DEFAULT_PASSWORD.')');
        $this->command?->table(
            ['Rôle', 'Email', 'Mot de passe'],
            collect($accounts)->map(fn (array $account) => [
                $account['role'],
                $account['email'],
                self::DEFAULT_PASSWORD,
            ])->all()
        );
    }
}
