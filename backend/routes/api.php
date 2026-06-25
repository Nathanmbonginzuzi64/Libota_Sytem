<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\FamilyTreeController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => 'LIBOTA CONNEXION API',
        'version' => '1.0.0',
    ]));

    // Public
    Route::get('/public/home', [PublicController::class, 'home']);
    Route::get('/search', [SearchController::class, 'index']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Familles & arbre
        Route::get('/families', [FamilyController::class, 'index']);
        Route::post('/families', [FamilyController::class, 'store']);
        Route::get('/families/{family}', [FamilyController::class, 'show']);
        Route::get('/families/{family}/tree', [FamilyTreeController::class, 'show']);

        // Modules
        Route::get('/publications', [ModuleController::class, 'publications']);
        Route::post('/publications', [ModuleController::class, 'storePublication']);
        Route::get('/documents', [ModuleController::class, 'documents']);
        Route::get('/events', [ModuleController::class, 'events']);
        Route::get('/oral-memories', [ModuleController::class, 'oralMemories']);
        Route::get('/locations', [ModuleController::class, 'locations']);
        Route::get('/notifications', [ModuleController::class, 'notifications']);
        Route::patch('/notifications/{id}/read', [ModuleController::class, 'markNotificationRead']);
        Route::get('/invitations', [ModuleController::class, 'invitations']);
        Route::post('/invitations', [ModuleController::class, 'storeInvitation']);
        Route::patch('/profile', [ModuleController::class, 'updateProfile']);
    });
});
