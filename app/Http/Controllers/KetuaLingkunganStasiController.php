<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CalonPenerima;

class KetuaLingkunganStasiController extends Controller
{
    /**
     * Show the dashboard for ketua lingkungan stasi.
     */
    public function dashboard(): View
    {
        $this->authorizeRole('ketua_lingkungan_stasi');

        $user         = Auth::user();
        $lingkunganId = $user->lingkungan_id;

        // ── Status Counts ────────────────────────────────────────
        $totalCalon        = CalonPenerima::where('lingkungan_id', $lingkunganId)->count();
        $draft             = CalonPenerima::where('lingkungan_id', $lingkunganId)->where('status', 'draft')->count();
        $submittedToStasi  = CalonPenerima::where('lingkungan_id', $lingkunganId)->where('status', 'submitted_to_stasi')->count();
        $revisionRequested = CalonPenerima::where('lingkungan_id', $lingkunganId)->where('status', 'revision_requested')->count();
        $approvedByStasi   = CalonPenerima::where('lingkungan_id', $lingkunganId)->where('status', 'approved_by_stasi')->count();
        $sentToParoki      = CalonPenerima::where('lingkungan_id', $lingkunganId)->where('status', 'sent_to_paroki')->count();
        $rejected          = CalonPenerima::where('lingkungan_id', $lingkunganId)->where('status', 'rejected')->count();

        // ── Completion Rate ──────────────────────────────────────
        $completionRate = $totalCalon > 0
            ? round((($approvedByStasi + $sentToParoki) / $totalCalon) * 100)
            : 0;

        // ── Recent Candidates (with relations) ───────────────────
        $calons = CalonPenerima::where('lingkungan_id', $lingkunganId)
            ->with(['periodeBantuan'])
            ->latest()
            ->take(8)
            ->get();

        // ── Needs Action (draft + revision) ─────────────────────
        $needsAction = $draft + $revisionRequested;

        return view('ketua-lingkungan-stasi.dashboard', compact(
            'totalCalon',
            'draft',
            'submittedToStasi',
            'revisionRequested',
            'approvedByStasi',
            'sentToParoki',
            'rejected',
            'completionRate',
            'calons',
            'needsAction'
        ));
    }

    /**
     * Authorize the user to have the given role.
     *
     * @param string $role
     * @return void
     */
    protected function authorizeRole(string $role)
    {
        if (!Auth::check()) {
            // If not authenticated, redirect to login
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!isset($user->role) || $user->role->name !== $role) {
            // If not authorized, redirect to home or show error
            abort(403, 'Unauthorized action.');
        }
    }
}
