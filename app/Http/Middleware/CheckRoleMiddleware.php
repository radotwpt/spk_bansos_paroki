<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk fitur ini.',
                'errors' => [
                    'role' => ['Role pengguna tidak diizinkan.'],
                ],
            ], 403);
        }

        return $next($request);
    }
}
