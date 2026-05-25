<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsWithApi;

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        $user = $request->user();

        return $this->success([
            'token' => $user->createToken('api-token')->plainTextToken,
            'user' => $user,
        ], 'Login berhasil.');
    }

    public function me(Request $request)
    {
        return $this->success($request->user(), 'Data user login berhasil diambil.');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($request->boolean('all_devices')) {
            $user?->tokens()->delete();
        } else {
            $token = $user?->currentAccessToken();

            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }
        }

        return $this->success(null, 'Logout berhasil.');
    }
}
