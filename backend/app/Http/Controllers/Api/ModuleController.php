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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function publications(): JsonResponse
    {
        $posts = Post::with(['family', 'user'])->latest()->paginate(20);

        return response()->json($posts);
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

        return response()->json(['publication' => $post->load(['family', 'user'])], 201);
    }

    public function documents(): JsonResponse
    {
        $docs = Document::with(['family', 'uploader'])->latest()->paginate(20);

        return response()->json($docs);
    }

    public function events(): JsonResponse
    {
        $events = Event::with(['family', 'creator'])->orderBy('event_date')->paginate(20);

        return response()->json($events);
    }

    public function oralMemories(): JsonResponse
    {
        $memories = OralMemory::with(['family', 'member'])->latest()->paginate(20);

        return response()->json($memories);
    }

    public function locations(): JsonResponse
    {
        $locations = Location::with(['family', 'member'])->get();

        return response()->json(['locations' => $locations]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(30);

        return response()->json($notifications);
    }

    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    public function invitations(): JsonResponse
    {
        $invitations = Invitation::with(['family', 'inviter'])->latest()->paginate(20);

        return response()->json($invitations);
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

        return response()->json([
            'invitation' => $invitation,
            'invite_link' => url('/invitation/'.$invitation->token),
        ], 201);
    }

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
}
