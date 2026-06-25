<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\ClanController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\FamilyMemberController;
use App\Http\Controllers\Api\FamilyTreeController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => 'LIBOTA CONNEXION API',
        'version' => '1.0.0',
    ]));

    Route::get('/public/home', [PublicController::class, 'home']);
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/clans', [ClanController::class, 'index']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Familles
        Route::get('/families/stats', [FamilyController::class, 'stats']);
        Route::get('/families', [FamilyController::class, 'index']);
        Route::post('/families', [FamilyController::class, 'store']);
        Route::get('/families/{family}', [FamilyController::class, 'show']);
        Route::patch('/families/{family}', [FamilyController::class, 'update']);
        Route::delete('/families/{family}', [FamilyController::class, 'destroy']);
        Route::get('/families/{family}/tree', [FamilyTreeController::class, 'show']);

        // Membres d'arbre
        Route::post('/families/{family}/members', [FamilyMemberController::class, 'store']);
        Route::patch('/families/{family}/members/{member}', [FamilyMemberController::class, 'update']);
        Route::delete('/families/{family}/members/{member}', [FamilyMemberController::class, 'destroy']);

        // Utilisateurs
        Route::get('/users/stats', [UserController::class, 'stats']);
        Route::get('/users', [UserController::class, 'index']);
        Route::patch('/users/{user}', [UserController::class, 'update']);

        // Publications
        Route::get('/publications', [ModuleController::class, 'publications']);
        Route::post('/publications', [ModuleController::class, 'storePublication']);
        Route::patch('/publications/{post}', [ModuleController::class, 'updatePublication']);
        Route::delete('/publications/{post}', [ModuleController::class, 'destroyPublication']);

        // Documents
        Route::get('/documents', [ModuleController::class, 'documents']);
        Route::post('/documents', [ModuleController::class, 'storeDocument']);
        Route::patch('/documents/{document}', [ModuleController::class, 'updateDocument']);
        Route::delete('/documents/{document}', [ModuleController::class, 'destroyDocument']);

        // Événements
        Route::get('/events', [ModuleController::class, 'events']);
        Route::post('/events', [ModuleController::class, 'storeEvent']);
        Route::patch('/events/{event}', [ModuleController::class, 'updateEvent']);
        Route::delete('/events/{event}', [ModuleController::class, 'destroyEvent']);

        // Mémoires orales
        Route::get('/oral-memories', [ModuleController::class, 'oralMemories']);
        Route::post('/oral-memories', [ModuleController::class, 'storeOralMemory']);
        Route::patch('/oral-memories/{oralMemory}', [ModuleController::class, 'updateOralMemory']);
        Route::delete('/oral-memories/{oralMemory}', [ModuleController::class, 'destroyOralMemory']);

        // Lieux
        Route::get('/locations', [ModuleController::class, 'locations']);
        Route::post('/locations', [ModuleController::class, 'storeLocation']);
        Route::patch('/locations/{location}', [ModuleController::class, 'updateLocation']);
        Route::delete('/locations/{location}', [ModuleController::class, 'destroyLocation']);

        // Notifications
        Route::get('/notifications/stats', [ModuleController::class, 'notificationStats']);
        Route::get('/notifications', [ModuleController::class, 'notifications']);
        Route::patch('/notifications/read-all', [ModuleController::class, 'markAllNotificationsRead']);
        Route::patch('/notifications/{id}/read', [ModuleController::class, 'markNotificationRead']);
        Route::delete('/notifications/{id}', [ModuleController::class, 'destroyNotification']);

        // Invitations
        Route::get('/invitations', [ModuleController::class, 'invitations']);
        Route::post('/invitations', [ModuleController::class, 'storeInvitation']);
        Route::delete('/invitations/{invitation}', [ModuleController::class, 'destroyInvitation']);

        // Profil
        Route::patch('/profile', [ModuleController::class, 'updateProfile']);
        Route::patch('/profile/password', [ModuleController::class, 'updatePassword']);

        // Journal & sauvegardes
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/backups', [BackupController::class, 'index']);
        Route::post('/backups', [BackupController::class, 'store']);
        Route::delete('/backups/{backup}', [BackupController::class, 'destroy']);
    });
});
