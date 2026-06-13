<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request, AuditService $audit)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()->with(['role', 'paroki', 'stasi', 'lingkungan'])->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Email, password, atau status akun tidak valid.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'api-token')->plainTextToken;
        $audit->record('auth.login', $user, metadata: ['email' => $user->email], request: $request);

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $user,
        ], 'Login berhasil.');
    }

    public function me(Request $request)
    {
        return $this->success($request->user()->load(['role', 'paroki', 'stasi', 'lingkungan']));
    }

    public function logout(Request $request, AuditService $audit)
    {
        $request->user()->currentAccessToken()?->delete();
        $audit->record('auth.logout', $request->user(), request: $request);

        return $this->success(message: 'Logout berhasil.');
    }
}
