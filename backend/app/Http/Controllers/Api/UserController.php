<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderBy('name');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        return response()->json($query->paginate(20));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|in:admin,historian,member,guest',
            'locale' => 'nullable|in:fr,ln,kg,sw',
        ]);

        $user->update($validated);

        ActivityLogger::log(
            $request->user(),
            'updated',
            'user',
            $user->id,
            'Utilisateur modifié',
            $user->name
        );

        return response()->json(['user' => $user->fresh()]);
    }

    public function stats(): JsonResponse
    {
        $counts = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return response()->json([
            'total' => User::count(),
            'by_role' => $counts,
        ]);
    }
}
