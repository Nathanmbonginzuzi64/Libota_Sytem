<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim();
        if ($q->length() < 2) {
            return response()->json(['results' => [], 'message' => 'Minimum 2 caractères.']);
        }

        $term = '%'.$q.'%';

        $families = Family::with('clan')
            ->where('name', 'like', $term)
            ->orWhere('origin_province', 'like', $term)
            ->limit(10)
            ->get()
            ->map(fn ($f) => [
                'type' => 'family',
                'id' => $f->id,
                'title' => $f->name,
                'subtitle' => $f->clan?->name.' · '.$f->origin_province,
            ]);

        $members = FamilyMember::with('family')
            ->where('first_name', 'like', $term)
            ->orWhere('last_name', 'like', $term)
            ->limit(15)
            ->get()
            ->map(fn ($m) => [
                'type' => 'member',
                'id' => $m->id,
                'title' => $m->first_name.' '.$m->last_name,
                'subtitle' => $m->family?->name,
            ]);

        $documents = Document::with('family')
            ->where('title', 'like', $term)
            ->limit(10)
            ->get()
            ->map(fn ($d) => [
                'type' => 'document',
                'id' => $d->id,
                'title' => $d->title,
                'subtitle' => $d->family?->name.' · '.$d->type,
            ]);

        $publications = Post::with('family')
            ->where('content', 'like', $term)
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'type' => 'publication',
                'id' => $p->id,
                'title' => \Str::limit($p->content, 80),
                'subtitle' => $p->family?->name,
            ]);

        return response()->json([
            'query' => $q,
            'results' => $families->concat($members)->concat($documents)->concat($publications)->values(),
            'count' => $families->count() + $members->count() + $documents->count() + $publications->count(),
        ]);
    }
}
