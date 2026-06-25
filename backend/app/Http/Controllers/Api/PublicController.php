<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    public function home(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'families' => Family::count(),
                'members' => FamilyMember::count(),
                'documents' => Document::count(),
                'publications' => Post::where('visibility', 'public')->count(),
                'users' => User::count(),
            ],
            'mission' => [
                'title' => 'LIBOTA CONNEXION',
                'tagline' => 'Préservons nos racines, transmettons notre histoire.',
                'description' => 'Plateforme de généalogie africaine dédiée aux familles de la RDC et de la diaspora.',
                'values' => [
                    'Préservation du patrimoine familial',
                    'Transmission intergénérationnelle',
                    'Connexion de la diaspora africaine',
                    'Respect des traditions et des clans',
                ],
            ],
            'languages' => [
                ['code' => 'fr', 'name' => 'Français'],
                ['code' => 'ln', 'name' => 'Lingala'],
                ['code' => 'kg', 'name' => 'Kikongo'],
                ['code' => 'sw', 'name' => 'Swahili'],
            ],
            'recent_publications' => Post::with(['family', 'user'])
                ->where('visibility', 'public')
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (Post $post) => [
                    'id' => $post->id,
                    'content' => \Str::limit($post->content, 150),
                    'family' => $post->family?->name,
                    'author' => $post->user?->name,
                    'date' => $post->created_at?->translatedFormat('d M Y'),
                ]),
        ]);
    }
}
