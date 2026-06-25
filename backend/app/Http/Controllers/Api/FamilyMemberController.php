<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyMemberController extends Controller
{
    public function store(Request $request, Family $family): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'father_id' => 'nullable|exists:family_members,id',
            'mother_id' => 'nullable|exists:family_members,id',
            'is_adopted' => 'boolean',
            'biography' => 'nullable|string|max:5000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $member = $family->members()->create($validated);

        ActivityLogger::log(
            $request->user(),
            'created',
            'family_member',
            $member->id,
            'Membre ajouté',
            $member->first_name.' '.$member->last_name,
            ['family_id' => $family->id]
        );

        return response()->json(['member' => $member->load(['father', 'mother'])], 201);
    }

    public function update(Request $request, Family $family, FamilyMember $member): JsonResponse
    {
        abort_if($member->family_id !== $family->id, 404);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'father_id' => 'nullable|exists:family_members,id',
            'mother_id' => 'nullable|exists:family_members,id',
            'is_adopted' => 'boolean',
            'biography' => 'nullable|string|max:5000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $member->update($validated);

        ActivityLogger::log(
            $request->user(),
            'updated',
            'family_member',
            $member->id,
            'Membre modifié',
            $member->first_name.' '.$member->last_name
        );

        return response()->json(['member' => $member->fresh(['father', 'mother'])]);
    }

    public function destroy(Request $request, Family $family, FamilyMember $member): JsonResponse
    {
        abort_if($member->family_id !== $family->id, 404);

        $name = $member->first_name.' '.$member->last_name;
        $member->delete();

        ActivityLogger::log(
            $request->user(),
            'deleted',
            'family_member',
            null,
            'Membre supprimé',
            $name
        );

        return response()->json(['message' => 'Membre supprimé.']);
    }
}
