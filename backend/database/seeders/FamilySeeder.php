<?php

namespace Database\Seeders;

use App\Models\Clan;
use App\Models\Document;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class FamilySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@libota-connexion.cd')->firstOrFail();
        $historian = User::where('email', 'historien@libota-connexion.cd')->first();
        $member = User::where('email', 'membre@libota-connexion.cd')->first();

        $familiesData = [
            ['name' => 'Famille Kabasele', 'clan' => 'Luba', 'province' => 'Kinshasa', 'members' => 12],
            ['name' => 'Famille Mbongi', 'clan' => 'Yaka', 'province' => 'Kwango', 'members' => 10],
            ['name' => 'Famille Tshibangu', 'clan' => 'Kongo', 'province' => 'Kongo Central', 'members' => 8],
            ['name' => 'Famille Ngoma', 'clan' => 'Suku', 'province' => 'Kwilu', 'members' => 6],
            ['name' => 'Famille Lubaki', 'clan' => 'Luba', 'province' => 'Katanga', 'members' => 5],
        ];

        foreach ($familiesData as $index => $data) {
            $clan = Clan::where('name', $data['clan'])->first();

            $family = Family::updateOrCreate(
                ['name' => $data['name']],
                [
                    'clan_id' => $clan?->id,
                    'created_by' => $admin->id,
                    'visibility' => 'private',
                    'origin_province' => $data['province'],
                ]
            );

            if ($family->members()->count() === 0) {
                for ($i = 0; $i < $data['members']; $i++) {
                    $linkedUser = match (true) {
                        $index === 0 && $i === 0 && $member !== null => $member->id,
                        $index === 0 && $i === 1 && $historian !== null => $historian->id,
                        default => null,
                    };

                    FamilyMember::create([
                        'family_id' => $family->id,
                        'user_id' => $linkedUser,
                        'first_name' => fake()->firstName(),
                        'last_name' => str_replace('Famille ', '', $data['name']),
                        'gender' => fake()->randomElement(['male', 'female']),
                        'birth_date' => fake()->dateTimeBetween('-80 years', '-5 years'),
                    ]);
                }
            }

            Document::updateOrCreate(
                ['family_id' => $family->id, 'title' => 'Acte de naissance — '.$data['name']],
                [
                    'uploaded_by' => $admin->id,
                    'type' => 'pdf',
                    'file_path' => 'documents/sample.pdf',
                ]
            );

            Post::updateOrCreate(
                ['family_id' => $family->id, 'user_id' => $admin->id],
                ['content' => 'Bienvenue sur l\'espace familial de '.$data['name'].'.']
            );
        }
    }
}
