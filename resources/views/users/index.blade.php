@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="nb-card anim-fade-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800;">Manajemen Pengguna</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:0.3rem;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Pengguna
        </a>
    </div>

    <div class="table-container">
        <table class="nb-table">
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Lingkungan</th>
                    <th style="width: 150px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="font-weight: 600;">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span style="background: var(--gray-200); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; border: 1.5px solid var(--black);">
                            {{ $user->role?->label ?? $user->role?->name ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $user->lingkungan?->name ?? '-' }}</td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline btn-sm" style="padding: 0.4rem 0.6rem;">Edit</a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: var(--red); color: white; padding: 0.4rem 0.6rem;">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem;">Belum ada data pengguna.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
