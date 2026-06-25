<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\Document;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Post;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index(): JsonResponse
    {
        $backups = Backup::with('creator')->latest()->paginate(20);

        return response()->json($backups);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'in:full,family,gedcom',
        ]);

        $type = $validated['type'] ?? 'full';
        $name = $validated['name'] ?? 'Sauvegarde '.now()->format('d/m/Y H:i');

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'type' => $type,
            'stats' => [
                'families' => Family::count(),
                'members' => FamilyMember::count(),
                'documents' => Document::count(),
                'publications' => Post::count(),
                'users' => User::count(),
            ],
            'families' => Family::with('clan')->get(),
        ];

        $filename = 'backup_'.now()->format('Y_m_d_His').'.json';
        $path = 'backups/'.$filename;
        $content = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Storage::disk('local')->put($path, $content);

        $backup = Backup::create([
            'created_by' => $request->user()->id,
            'name' => $name,
            'type' => $type,
            'file_path' => $path,
            'file_size' => strlen($content),
            'status' => 'completed',
        ]);

        ActivityLogger::log(
            $request->user(),
            'created',
            'backup',
            $backup->id,
            'Sauvegarde créée',
            $name
        );

        return response()->json(['backup' => $backup->load('creator')], 201);
    }

    public function destroy(Request $request, Backup $backup): JsonResponse
    {
        if ($backup->file_path) {
            Storage::disk('local')->delete($backup->file_path);
        }
        $backup->delete();

        ActivityLogger::log(
            $request->user(),
            'deleted',
            'backup',
            null,
            'Sauvegarde supprimée',
            $backup->name
        );

        return response()->json(['message' => 'Sauvegarde supprimée.']);
    }
}
