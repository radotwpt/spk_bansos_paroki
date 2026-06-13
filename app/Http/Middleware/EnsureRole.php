<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()?->hasRole(...$roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk aksi ini.',
            ], 403);
        }

        return $next($request);
    }
}
