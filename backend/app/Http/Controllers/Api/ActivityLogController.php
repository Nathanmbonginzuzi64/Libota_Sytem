<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        return response()->json($query->paginate(25));
    }
}
