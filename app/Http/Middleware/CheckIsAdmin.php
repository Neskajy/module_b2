<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsAdmin
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $user = $request->user("sanctum");

        if (!$user) {
            return response()->json([
                "message" => "Login failed"
            ], 401);
        }

        if ($user->role !== "admin") {
            return response()->json([
                "message" => "No rights? :("
            ]);
        }

        return $next($request);
    }
}
