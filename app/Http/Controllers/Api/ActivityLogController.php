<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Models\ActivityLog;
use App\Models\CalonPenerima;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request, $id)
    {
        $calon = CalonPenerima::findOrFail($id);
        $this->authorize('view', $calon);

        $logs = ActivityLog::with('user')
            ->where('model_type', CalonPenerima::class)
            ->where('model_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return $this->success($logs, 'Activity logs loaded.');
    }
}
