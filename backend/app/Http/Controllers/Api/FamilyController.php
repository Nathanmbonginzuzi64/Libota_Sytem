<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Family::with('clan')->withCount('members')->orderByDesc('members_count');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('origin_province', 'like', $term);
            });
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->string('visibility'));
        }

        if ($request->filled('clan_id')) {
            $query->where('clan_id', $request->integer('clan_id'));
        }

        return response()->json($query->paginate(15));
    }

    public function show(Family $family): JsonResponse
    {
        $family->load(['clan', 'members', 'documents', 'posts', 'locations']);

        return response()->json(['family' => $family]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'clan_id' => 'nullable|exists:clans,id',
            'description' => 'nullable|string',
            'origin_province' => 'nullable|string|max:100',
            'visibility' => 'in:public,private',
        ]);

        $family = Family::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'visibility' => $validated['visibility'] ?? 'private',
        ]);

        $family->users()->attach($request->user()->id, [
            'role' => 'family_admin',
            'status' => 'active',
        ]);

        ActivityLogger::log(
            $request->user(),
            'created',
            'family',
            $family->id,
            'Famille créée',
            $family->name
        );

        return response()->json(['family' => $family->load('clan')], 201);
    }

    public function update(Request $request, Family $family): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'clan_id' => 'nullable|exists:clans,id',
            'description' => 'nullable|string',
            'origin_province' => 'nullable|string|max:100',
            'visibility' => 'in:public,private',
        ]);

        $family->update($validated);

        ActivityLogger::log(
            $request->user(),
            'updated',
            'family',
            $family->id,
            'Famille modifiée',
            $family->name
        );

        return response()->json(['family' => $family->fresh('clan')]);
    }

    public function destroy(Request $request, Family $family): JsonResponse
    {
        $name = $family->name;
        $family->delete();

        ActivityLogger::log(
            $request->user(),
            'deleted',
            'family',
            null,
            'Famille supprimée',
            $name
        );

        return response()->json(['message' => 'Famille supprimée.']);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => Family::count(),
            'public' => Family::where('visibility', 'public')->count(),
            'private' => Family::where('visibility', 'private')->count(),
        ]);
    }
}
