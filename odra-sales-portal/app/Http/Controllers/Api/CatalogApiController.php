<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogApiController extends Controller
{
    public function __construct(protected CatalogService $catalogService)
    {
    }

    /**
     * Return the full catalog payload for Quote Builder initialization.
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $this->catalogService->getCatalogPayload();

        // Include current user info
        $user = $request->user();
        $payload['current_user'] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rep_code' => $user->rep_code,
            'territory' => $user->territory,
            'is_admin' => $user->isAdmin(),
        ];

        return response()->json($payload);
    }
}
