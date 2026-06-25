<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $roleCounts = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $topFamilies = Family::query()
            ->with('clan')
            ->withCount('members')
            ->orderByDesc('members_count')
            ->limit(5)
            ->get()
            ->map(fn (Family $family, int $index) => [
                'rank' => $index + 1,
                'name' => $family->name,
                'clan' => $family->clan?->name,
                'members' => $family->members_count,
            ]);

        $recentDocuments = Document::query()
            ->with('family')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Document $doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => strtoupper($doc->type === 'photo' ? 'JPG' : $doc->type),
                'family' => $doc->family?->name,
                'date' => $doc->created_at?->translatedFormat('d M Y'),
            ]);

        return response()->json([
            'kpis' => [
                'families' => Family::count(),
                'members' => FamilyMember::count(),
                'documents' => Document::count(),
                'publications' => Post::count(),
                'users' => User::count(),
            ],
            'roles' => $roleCounts,
            'top_families' => $topFamilies,
            'recent_documents' => $recentDocuments,
        ]);
    }
}
