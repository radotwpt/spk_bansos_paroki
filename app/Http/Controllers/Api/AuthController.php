<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsWithApi;

    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        $user = $request->user();
        $tokenName = $request->input('device_name') ?: sprintf('api-%s', now()->format('YmdHis'));
        $token = $user->createToken($tokenName)->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Login berhasil.');
    }

    public function me(Request $request)
    {
        return $this->success(new UserResource($request->user()), 'Data user login berhasil diambil.');
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
