<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SawCriterion;
use App\Models\SawWeight;
use App\Models\BansosPeriod;
use App\Models\SawResult;
use App\Services\SawCalculationService;
use Illuminate\Support\Facades\Auth;

class SawController extends Controller
{
    public function weights(Request $request, ?int $periodId = null)
    {
        $period = $periodId ? BansosPeriod::findOrFail($periodId) : null;

        $criteria = SawCriterion::with(['weights' => function ($q) use ($periodId) {
            $q->whereNull('bansos_period_id')->orWhere('bansos_period_id', $periodId);
        }])->get();

        $result = $criteria->map(function ($c) use ($periodId) {
            $weight = $c->weights->firstWhere('bansos_period_id', $periodId) ?? $c->weights->firstWhere('bansos_period_id', null);
            return [
                'key' => $c->key,
                'label' => $c->label,
                'type' => $c->type,
                'weight' => $weight ? (float) $weight->weight : 0.0,
            ];
        });

        return response()->json(['period' => $period, 'criteria' => $result]);
    }

    public function saveWeights(Request $request, ?int $periodId = null)
    {
        $period = $periodId ? BansosPeriod::findOrFail($periodId) : null;
        if ($period && $period->is_locked) {
            return response()->json(['message' => 'Period locked, cannot change weights'], 409);
        }

        $data = $request->validate([
            'weights' => 'required|array'
        ]);

        $weights = $data['weights'];
        $total = array_sum(array_map('floatval', $weights));
        if (abs($total - 1.0) > 0.0001) {
            return response()->json(['message' => 'Total weights must sum to 1.0'], 422);
        }

        foreach ($weights as $key => $value) {
            $criterion = SawCriterion::where('key', $key)->first();
            if (!$criterion) continue;

            SawWeight::updateOrCreate(
                ['saw_criterion_id' => $criterion->id, 'bansos_period_id' => $periodId],
                ['weight' => (float) $value]
            );
        }

        return response()->json(['message' => 'Weights saved']);
    }

    public function preview(Request $request, int $periodId, SawCalculationService $saw)
    {
        $period = BansosPeriod::findOrFail($periodId);

        $userId = Auth::id();
        $results = $saw->calculate($periodId, $userId, false);

        return response()->json(['period' => $period, 'preview' => $results]);
    }

    public function results(Request $request, int $periodId)
    {
        $period = BansosPeriod::findOrFail($periodId);

        $rows = SawResult::with(['calon', 'createdBy'])->where('bansos_period_id', $periodId)->orderBy('rank')->get();

        return response()->json(['period' => $period, 'data' => $rows]);
    }
}
