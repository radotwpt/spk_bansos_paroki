<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CalonPenerima;
use App\Models\User;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected function serializeCandidate(CalonPenerima $candidate, User $user): array
    {
        $data = $candidate->loadMissing(['periodeBantuan', 'paroki', 'stasi', 'lingkungan', 'sawResult', 'penerimaBantuan'])->toArray();

        if (! $user->hasRole('ketua_lingkungan_stasi', 'stasi')) {
            $data['nik'] = $this->maskIdentity($candidate->nik);
            $data['nomor_kk'] = $this->maskIdentity($candidate->nomor_kk);
        }

        return $data;
    }

    protected function maskIdentity(?string $value): ?string
    {
        if ($value === null || strlen($value) <= 4) {
            return $value;
        }

        return str_repeat('*', max(strlen($value) - 4, 0)).substr($value, -4);
    }
}
