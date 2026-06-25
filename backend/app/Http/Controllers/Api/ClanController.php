<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clan;
use Illuminate\Http\JsonResponse;

class ClanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['clans' => Clan::orderBy('name')->get()]);
    }
}
