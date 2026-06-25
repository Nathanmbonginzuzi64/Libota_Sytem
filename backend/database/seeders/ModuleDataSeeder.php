<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Family;
use App\Models\Invitation;
use App\Models\Location;
use App\Models\Notification;
use App\Models\OralMemory;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Seeder;

class ModuleDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@libota-connexion.cd')->firstOrFail();
        $families = Family::with('members')->get();

        foreach ($families as $family) {
            $member = $family->members->first();

            Event::updateOrCreate(
                ['family_id' => $family->id, 'title' => 'Réunion familiale — '.$family->name],
                [
                    'created_by' => $admin->id,
                    'type' => 'reunion',
                    'event_date' => now()->addMonths(2),
                    'location' => $family->origin_province,
                    'description' => 'Réunion annuelle de la '.$family->name,
                ]
            );

            Location::updateOrCreate(
                ['family_id' => $family->id, 'name' => 'Origine — '.$family->name],
                [
                    'family_member_id' => $member?->id,
                    'type' => 'origin',
                    'province' => $family->origin_province,
                    'country' => 'RDC',
                    'latitude' => -4.3 + fake()->randomFloat(2, -2, 2),
                    'longitude' => 15.3 + fake()->randomFloat(2, -3, 3),
                    'year' => 1950,
                ]
            );

            OralMemory::updateOrCreate(
                ['family_id' => $family->id, 'title' => 'Récit des ancêtres — '.$family->name],
                [
                    'family_member_id' => $member?->id,
                    'recorded_by' => $admin->id,
                    'narrator' => $member ? $member->first_name.' '.$member->last_name : 'Ancien',
                    'media_type' => 'audio',
                    'duration_seconds' => 320,
                    'language' => 'ln',
                    'transcription' => 'Histoire de nos ancêtres et de notre clan...',
                ]
            );

            Invitation::updateOrCreate(
                ['family_id' => $family->id, 'email' => 'invite.'.strtolower(str_replace(' ', '', $family->name)).'@example.cd'],
                [
                    'invited_by' => $admin->id,
                    'role' => 'member',
                    'token' => 'demo_token_'.$family->id,
                    'expires_at' => now()->addDays(14),
                ]
            );
        }

        Notification::updateOrCreate(
            ['user_id' => $admin->id, 'title' => 'Bienvenue sur LIBOTA CONNEXION'],
            [
                'type' => 'system',
                'message' => 'Votre espace d\'administration est prêt.',
            ]
        );

        Notification::updateOrCreate(
            ['user_id' => $admin->id, 'title' => 'Nouveau membre ajouté'],
            [
                'type' => 'member',
                'message' => 'Un nouveau membre a rejoint une famille.',
            ]
        );

        ActivityLogger::log($admin, 'created', 'system', null, 'Plateforme initialisée', 'Données de démonstration chargées');
        ActivityLogger::log($admin, 'created', 'family', $families->first()?->id, 'Famille Kabasele ajoutée', 'Via seeder');
    }
}
