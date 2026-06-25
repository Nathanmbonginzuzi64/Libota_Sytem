<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Marriage;
use Illuminate\Http\JsonResponse;

class FamilyTreeController extends Controller
{
    public function show(Family $family): JsonResponse
    {
        $members = FamilyMember::where('family_id', $family->id)
            ->with(['father', 'mother'])
            ->get();

        $marriages = Marriage::where('family_id', $family->id)->get();

        $nodes = $members->map(fn (FamilyMember $m) => [
            'id' => $m->id,
            'first_name' => $m->first_name,
            'last_name' => $m->last_name,
            'gender' => $m->gender,
            'birth_date' => $m->birth_date?->format('Y-m-d'),
            'death_date' => $m->death_date?->format('Y-m-d'),
            'father_id' => $m->father_id,
            'mother_id' => $m->mother_id,
            'is_adopted' => $m->is_adopted,
        ]);

        return response()->json([
            'family' => $family->only(['id', 'name']),
            'nodes' => $nodes,
            'marriages' => $marriages,
            'stats' => [
                'total_members' => $members->count(),
                'generations' => $this->estimateGenerations($members),
            ],
        ]);
    }

    private function estimateGenerations($members): int
    {
        if ($members->isEmpty()) {
            return 0;
        }

        $depths = [];
        foreach ($members as $member) {
            $depth = 0;
            $current = $member;
            $visited = [];
            while ($current && $current->father_id && ! in_array($current->father_id, $visited)) {
                $visited[] = $current->father_id;
                $current = $members->firstWhere('id', $current->father_id);
                $depth++;
            }
            $depths[] = $depth;
        }

        return max($depths) + 1;
    }
}
