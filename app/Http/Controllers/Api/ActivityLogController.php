<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Models\ActivityLog;
use App\Models\CalonPenerima;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $validated = $request->validate([
            'user' => 'nullable|integer|exists:users,id',
            'action' => 'nullable|string|max:100',
            'model' => [
                'nullable',
                'string',
                Rule::in([
                    CalonPenerima::class,
                    \App\Models\GeneratedLetter::class,
                    \App\Models\DocumentTemplate::class,
                ]),
            ],
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'q' => 'nullable|string|max:120',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $query = ActivityLog::query()->with('user:id,name,email,role');

        $query->when(isset($validated['user']), fn ($q) => $q->where('user_id', (int) $validated['user']));
        $query->when(isset($validated['action']), fn ($q) => $q->where('action', 'like', '%'.$validated['action'].'%'));
        $query->when(isset($validated['model']), fn ($q) => $q->where('model_type', $validated['model']));
        $query->when(isset($validated['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $validated['date_from']));
        $query->when(isset($validated['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $validated['date_to']));
        $query->when(isset($validated['q']), function ($q) use ($validated) {
            $term = $validated['q'];
            $q->where(function ($inner) use ($term) {
                $inner->where('action', 'like', '%'.$term.'%')
                    ->orWhere('model_type', 'like', '%'.$term.'%')
                    ->orWhere('model_id', 'like', '%'.$term.'%');
            });
        });

        $perPage = (int) ($validated['per_page'] ?? 20);
        $logs = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success($logs, 'Activity logs loaded.');
    }

    public function byCandidate(Request $request, $id)
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
