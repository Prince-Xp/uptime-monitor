<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Client::query()->orderBy('email')->get(['id', 'email'])
        );
    }

    public function websites(Client $client): JsonResponse
    {
        return response()->json(
            $client->websites()->orderBy('url')->get(['id', 'url', 'is_up'])
        );
    }
}   