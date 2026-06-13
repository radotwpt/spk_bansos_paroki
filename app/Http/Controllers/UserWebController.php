<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Role;
use App\Models\Lingkungan;

class UserWebController extends Controller
{
    /**
     * Show a listing of the resource.
     */
    public function index(): View
    {
        $this->authorizeRoles(['stasi', 'admin']);
        $currentUser = Auth::user();

        $query = User::with(['role', 'lingkungan']);

        if ($currentUser->hasRole('stasi')) {
            $query->where('stasi_id', $currentUser->stasi_id)
                  ->whereHas('role', function ($q) {
                      $q->where('name', 'ketua_lingkungan_stasi');
                  });
        }

        $users = $query->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorizeRoles(['stasi', 'admin']);
        $currentUser = Auth::user();

        if ($currentUser->hasRole('stasi')) {
            $roles = Role::where('name', 'ketua_lingkungan_stasi')->get();
            $lingkungans = Lingkungan::where('stasi_id', $currentUser->stasi_id)->get();
        } else {
            $roles = Role::all();
            $lingkungans = Lingkungan::all(); // Admin can see all
        }

        return view('users.create', compact('roles', 'lingkungans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeRoles(['stasi', 'admin']);
        $currentUser = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'lingkungan_id' => 'nullable|exists:lingkungans,id',
        ];

        // If stasi, ensure the selected lingkungan belongs to them
        if ($currentUser->hasRole('stasi')) {
            $rules['lingkungan_id'] = [
                'required',
                'exists:lingkungans,id',
                function ($attribute, $value, $fail) use ($currentUser) {
                    $exists = Lingkungan::where('id', $value)->where('stasi_id', $currentUser->stasi_id)->exists();
                    if (!$exists) {
                        $fail('Lingkungan tidak valid untuk stasi ini.');
                    }
                },
            ];
            $request->merge([
                'stasi_id' => $currentUser->stasi_id,
            ]);
        }

        $request->validate($rules);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'stasi_id' => $request->stasi_id ?? null,
            'lingkungan_id' => $request->lingkungan_id ?? null,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorizeRoles(['stasi', 'admin']);
        $currentUser = Auth::user();

        // Stasi can only edit ketua_lingkungan_stasi under their own stasi
        if ($currentUser->hasRole('stasi')) {
            if ($user->stasi_id !== $currentUser->stasi_id || !$user->hasRole('ketua_lingkungan_stasi')) {
                abort(403, 'Unauthorized access to this user.');
            }
            $roles = Role::where('name', 'ketua_lingkungan_stasi')->get();
            $lingkungans = Lingkungan::where('stasi_id', $currentUser->stasi_id)->get();
        } else {
            $roles = Role::all();
            $lingkungans = Lingkungan::all();
        }

        return view('users.edit', compact('user', 'roles', 'lingkungans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeRoles(['stasi', 'admin']);
        $currentUser = Auth::user();

        if ($currentUser->hasRole('stasi')) {
            if ($user->stasi_id !== $currentUser->stasi_id || !$user->hasRole('ketua_lingkungan_stasi')) {
                abort(403, 'Unauthorized access to this user.');
            }
            $request->merge([
                'stasi_id' => $currentUser->stasi_id,
            ]);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'lingkungan_id' => 'nullable|exists:lingkungans,id',
        ];

        if ($currentUser->hasRole('stasi')) {
            $rules['lingkungan_id'] = [
                'required',
                'exists:lingkungans,id',
                function ($attribute, $value, $fail) use ($currentUser) {
                    $exists = Lingkungan::where('id', $value)->where('stasi_id', $currentUser->stasi_id)->exists();
                    if (!$exists) {
                        $fail('Lingkungan tidak valid untuk stasi ini.');
                    }
                },
            ];
        }

        $request->validate($rules);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role_id' => $request->role_id,
            'stasi_id' => $request->stasi_id ?? $user->stasi_id,
            'lingkungan_id' => $request->lingkungan_id ?? $user->lingkungan_id,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorizeRoles(['stasi', 'admin']);
        $currentUser = Auth::user();

        if ($currentUser->hasRole('stasi')) {
            if ($user->stasi_id !== $currentUser->stasi_id || !$user->hasRole('ketua_lingkungan_stasi')) {
                abort(403, 'Unauthorized access to this user.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Authorize the user to have one of the given roles.
     *
     * @param array $roles
     * @return void
     */
    protected function authorizeRoles(array $roles)
    {
        if (!Auth::check()) {
            abort(redirect()->route('login'));
        }

        $user = Auth::user();
        if (!isset($user->role) || !in_array($user->role->name, $roles, true)) {
            abort(403, 'Unauthorized action.');
        }
    }
}