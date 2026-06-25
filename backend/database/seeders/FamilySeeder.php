<?php

namespace Database\Seeders;

use App\Models\Clan;
use App\Models\Document;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Marriage;
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
                $this->seedGenealogyTree($family, $data, $index, $admin, $historian, $member);
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

    private function seedGenealogyTree(
        Family $family,
        array $data,
        int $index,
        User $admin,
        ?User $historian,
        ?User $member
    ): void {
        $lastName = str_replace('Famille ', '', $data['name']);
        $total = $data['members'];

        $patriarch = FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $index === 0 ? $member?->id : null,
            'first_name' => fake()->firstName('male'),
            'last_name' => $lastName,
            'gender' => 'male',
            'birth_date' => fake()->dateTimeBetween('-85 years', '-75 years'),
            'biography' => 'Patriarche fondateur de la lignée '.$lastName.'.',
        ]);

        $matriarch = FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $index === 0 ? $historian?->id : null,
            'first_name' => fake()->firstName('female'),
            'last_name' => $lastName,
            'gender' => 'female',
            'birth_date' => fake()->dateTimeBetween('-80 years', '-70 years'),
            'biography' => 'Matriarche de la famille '.$lastName.'.',
        ]);

        Marriage::create([
            'family_id' => $family->id,
            'spouse_one_id' => $patriarch->id,
            'spouse_two_id' => $matriarch->id,
            'marriage_date' => fake()->dateTimeBetween('-55 years', '-50 years'),
        ]);

        $childrenCount = min(4, max(2, (int) floor($total * 0.35)));
        $children = [];

        for ($c = 0; $c < $childrenCount; $c++) {
            $gender = fake()->randomElement(['male', 'female']);
            $children[] = FamilyMember::create([
                'family_id' => $family->id,
                'first_name' => fake()->firstName($gender),
                'last_name' => $lastName,
                'gender' => $gender,
                'birth_date' => fake()->dateTimeBetween('-55 years', '-40 years'),
                'father_id' => $patriarch->id,
                'mother_id' => $matriarch->id,
            ]);
        }

        $remaining = $total - 2 - $childrenCount;
        $grandchildIndex = 0;

        while ($remaining > 0 && $grandchildIndex < count($children)) {
            $parent = $children[$grandchildIndex % count($children)];
            $gender = fake()->randomElement(['male', 'female']);

            FamilyMember::create([
                'family_id' => $family->id,
                'first_name' => fake()->firstName($gender),
                'last_name' => $lastName,
                'gender' => $gender,
                'birth_date' => fake()->dateTimeBetween('-35 years', '-5 years'),
                'father_id' => $parent->gender === 'male' ? $parent->id : $patriarch->id,
                'mother_id' => $parent->gender === 'female' ? $parent->id : $matriarch->id,
            ]);

            $remaining--;
            $grandchildIndex++;
        }

        while ($remaining > 0) {
            $gender = fake()->randomElement(['male', 'female']);
            FamilyMember::create([
                'family_id' => $family->id,
                'first_name' => fake()->firstName($gender),
                'last_name' => $lastName,
                'gender' => $gender,
                'birth_date' => fake()->dateTimeBetween('-30 years', '-2 years'),
                'father_id' => $children[0]->id ?? $patriarch->id,
                'mother_id' => $matriarch->id,
            ]);
            $remaining--;
        }
    }
}
