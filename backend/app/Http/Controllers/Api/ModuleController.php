<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\Location;
use App\Models\Notification;
use App\Models\OralMemory;
use App\Models\Post;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    // ── Publications ──────────────────────────────────────────

    public function publications(Request $request): JsonResponse
    {
        $query = Post::with(['family', 'user'])->latest();

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->integer('family_id'));
        }
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->string('visibility'));
        }
        if ($request->filled('q')) {
            $query->where('content', 'like', '%'.$request->string('q').'%');
        }

        return response()->json($query->paginate(15));
    }

    public function storePublication(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => 'required|exists:families,id',
            'content' => 'required|string|max:5000',
            'visibility' => 'in:family,public',
        ]);

        $post = Post::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'visibility' => $validated['visibility'] ?? 'family',
        ]);

        ActivityLogger::log($request->user(), 'created', 'publication', $post->id, 'Publication créée');

        return response()->json(['publication' => $post->load(['family', 'user'])], 201);
    }

    public function updatePublication(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'sometimes|string|max:5000',
            'visibility' => 'in:family,public',
        ]);
        $post->update($validated);
        ActivityLogger::log($request->user(), 'updated', 'publication', $post->id, 'Publication modifiée');

        return response()->json(['publication' => $post->fresh(['family', 'user'])]);
    }

    public function destroyPublication(Request $request, Post $post): JsonResponse
    {
        $post->delete();
        ActivityLogger::log($request->user(), 'deleted', 'publication', null, 'Publication supprimée');

        return response()->json(['message' => 'Publication supprimée.']);
    }

    // ── Documents ─────────────────────────────────────────────

    public function documents(Request $request): JsonResponse
    {
        $query = Document::with(['family', 'uploader'])->latest();

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->integer('family_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }

        return response()->json($query->paginate(15));
    }

    public function storeDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => 'required|exists:families,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:pdf,photo,act,testimony,other',
            'description' => 'nullable|string|max:2000',
            'file_path' => 'nullable|string|max:500',
            'mime_type' => 'nullable|string|max:100',
            'file_size' => 'nullable|integer',
        ]);

        $doc = Document::create([
            ...$validated,
            'uploaded_by' => $request->user()->id,
            'file_path' => $validated['file_path'] ?? 'documents/pending/'.$validated['title'],
        ]);

        ActivityLogger::log($request->user(), 'created', 'document', $doc->id, 'Document ajouté', $doc->title);

        return response()->json(['document' => $doc->load(['family', 'uploader'])], 201);
    }

    public function updateDocument(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:pdf,photo,act,testimony,other',
            'description' => 'nullable|string|max:2000',
        ]);
        $document->update($validated);
        ActivityLogger::log($request->user(), 'updated', 'document', $document->id, 'Document modifié', $document->title);

        return response()->json(['document' => $document->fresh(['family', 'uploader'])]);
    }

    public function destroyDocument(Request $request, Document $document): JsonResponse
    {
        $document->delete();
        ActivityLogger::log($request->user(), 'deleted', 'document', null, 'Document supprimé');

        return response()->json(['message' => 'Document supprimé.']);
    }

    // ── Events ────────────────────────────────────────────────

    public function events(Request $request): JsonResponse
    {
        $query = Event::with(['family', 'creator'])->orderBy('event_date');

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->integer('family_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }

        return response()->json($query->paginate(15));
    }

    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => 'required|exists:families,id',
            'title' => 'required|string|max:255',
            'type' => 'in:birthday,death,marriage,reunion,ceremony,other',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'notify_members' => 'boolean',
        ]);

        $event = Event::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'notify_members' => $validated['notify_members'] ?? true,
        ]);

        ActivityLogger::log($request->user(), 'created', 'event', $event->id, 'Événement créé', $event->title);

        return response()->json(['event' => $event->load(['family', 'creator'])], 201);
    }

    public function updateEvent(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:birthday,death,marriage,reunion,ceremony,other',
            'event_date' => 'sometimes|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'notify_members' => 'boolean',
        ]);
        $event->update($validated);
        ActivityLogger::log($request->user(), 'updated', 'event', $event->id, 'Événement modifié', $event->title);

        return response()->json(['event' => $event->fresh(['family', 'creator'])]);
    }

    public function destroyEvent(Request $request, Event $event): JsonResponse
    {
        $event->delete();
        ActivityLogger::log($request->user(), 'deleted', 'event', null, 'Événement supprimé');

        return response()->json(['message' => 'Événement supprimé.']);
    }

    // ── Oral memories ─────────────────────────────────────────

    public function oralMemories(Request $request): JsonResponse
    {
        $query = OralMemory::with(['family', 'member'])->latest();

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->integer('family_id'));
        }
        if ($request->filled('media_type')) {
            $query->where('media_type', $request->string('media_type'));
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }

        return response()->json($query->paginate(15));
    }

    public function storeOralMemory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => 'required|exists:families,id',
            'family_member_id' => 'nullable|exists:family_members,id',
            'title' => 'required|string|max:255',
            'narrator' => 'nullable|string|max:255',
            'media_type' => 'in:audio,video',
            'file_path' => 'nullable|string|max:500',
            'transcription' => 'nullable|string|max:10000',
            'duration_seconds' => 'nullable|integer',
            'language' => 'nullable|string|max:10',
        ]);

        $memory = OralMemory::create([
            ...$validated,
            'recorded_by' => $request->user()->id,
            'media_type' => $validated['media_type'] ?? 'audio',
        ]);

        ActivityLogger::log($request->user(), 'created', 'oral_memory', $memory->id, 'Mémoire orale ajoutée', $memory->title);

        return response()->json(['oral_memory' => $memory->load(['family', 'member'])], 201);
    }

    public function updateOralMemory(Request $request, OralMemory $oralMemory): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'narrator' => 'nullable|string|max:255',
            'transcription' => 'nullable|string|max:10000',
            'duration_seconds' => 'nullable|integer',
            'language' => 'nullable|string|max:10',
        ]);
        $oralMemory->update($validated);
        ActivityLogger::log($request->user(), 'updated', 'oral_memory', $oralMemory->id, 'Mémoire orale modifiée');

        return response()->json(['oral_memory' => $oralMemory->fresh(['family', 'member'])]);
    }

    public function destroyOralMemory(Request $request, OralMemory $oralMemory): JsonResponse
    {
        $oralMemory->delete();
        ActivityLogger::log($request->user(), 'deleted', 'oral_memory', null, 'Mémoire orale supprimée');

        return response()->json(['message' => 'Mémoire orale supprimée.']);
    }

    // ── Locations ─────────────────────────────────────────────

    public function locations(Request $request): JsonResponse
    {
        $query = Location::with(['family', 'member']);

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->integer('family_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return response()->json(['locations' => $query->get()]);
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => 'required|exists:families,id',
            'family_member_id' => 'nullable|exists:family_members,id',
            'name' => 'required|string|max:255',
            'type' => 'in:origin,birth,residence,migration,death,other',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'year' => 'nullable|integer|min:1800|max:2100',
            'description' => 'nullable|string|max:2000',
        ]);

        $location = Location::create($validated);
        ActivityLogger::log($request->user(), 'created', 'location', $location->id, 'Lieu ajouté', $location->name);

        return response()->json(['location' => $location->load(['family', 'member'])], 201);
    }

    public function updateLocation(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:origin,birth,residence,migration,death,other',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'year' => 'nullable|integer|min:1800|max:2100',
            'description' => 'nullable|string|max:2000',
        ]);
        $location->update($validated);
        ActivityLogger::log($request->user(), 'updated', 'location', $location->id, 'Lieu modifié', $location->name);

        return response()->json(['location' => $location->fresh(['family', 'member'])]);
    }

    public function destroyLocation(Request $request, Location $location): JsonResponse
    {
        $location->delete();
        ActivityLogger::log($request->user(), 'deleted', 'location', null, 'Lieu supprimé');

        return response()->json(['message' => 'Lieu supprimé.']);
    }

    // ── Notifications ───────────────────────────────────────────

    public function notifications(Request $request): JsonResponse
    {
        $query = Notification::where('user_id', $request->user()->id)->latest();

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        return response()->json($query->paginate(20));
    }

    public function notificationStats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'total' => Notification::where('user_id', $userId)->count(),
            'unread' => Notification::where('user_id', $userId)->whereNull('read_at')->count(),
        ]);
    }

    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues.']);
    }

    public function destroyNotification(Request $request, int $id): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->where('id', $id)->delete();

        return response()->json(['message' => 'Notification supprimée.']);
    }

    // ── Invitations ───────────────────────────────────────────

    public function invitations(Request $request): JsonResponse
    {
        $query = Invitation::with(['family', 'inviter'])->latest();

        if ($request->filled('family_id')) {
            $query->where('family_id', $request->integer('family_id'));
        }

        return response()->json($query->paginate(15));
    }

    public function storeInvitation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => 'required|exists:families,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'role' => 'in:family_admin,member,guest',
        ]);

        $invitation = Invitation::create([
            'family_id' => $validated['family_id'],
            'invited_by' => $request->user()->id,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? 'member',
            'token' => Str::random(48),
            'expires_at' => now()->addDays(7),
        ]);

        ActivityLogger::log($request->user(), 'created', 'invitation', $invitation->id, 'Invitation envoyée');

        return response()->json([
            'invitation' => $invitation->load(['family', 'inviter']),
            'invite_link' => url('/invitation/'.$invitation->token),
        ], 201);
    }

    public function destroyInvitation(Request $request, Invitation $invitation): JsonResponse
    {
        $invitation->delete();
        ActivityLogger::log($request->user(), 'deleted', 'invitation', null, 'Invitation révoquée');

        return response()->json(['message' => 'Invitation révoquée.']);
    }

    // ── Profile ───────────────────────────────────────────────

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'locale' => 'nullable|in:fr,ln,kg,sw',
        ]);

        $request->user()->update($validated);

        return response()->json(['user' => $request->user()->fresh()]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! \Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect.'], 422);
        }

        $request->user()->update(['password' => \Hash::make($validated['password'])]);

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }
}
