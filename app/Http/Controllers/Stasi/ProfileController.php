<?php

namespace App\Http\Controllers\Stasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the Stasi profile.
     */
    public function edit(): View
    {
        $this->authorizeRole('stasi');
        
        $stasi = Auth::user()->stasi;

        return view('stasi.profile.edit', compact('stasi'));
    }

    /**
     * Update the Stasi profile in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeRole('stasi');
        
        $stasi = Auth::user()->stasi;

        $request->validate([
            'name' => 'required|string|max:255',
            'leader_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $stasi->update([
            'name' => $request->name,
            'leader_name' => $request->leader_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect()->route('stasi.profile.edit')
            ->with('success', 'Profil Stasi berhasil diperbarui.');
    }

    protected function authorizeRole(string $role)
    {
        if (!Auth::check()) {
            abort(redirect()->route('login'));
        }

        $user = Auth::user();
        if (!isset($user->role) || $user->role->name !== $role) {
            abort(403, 'Unauthorized action.');
        }
    }
}
