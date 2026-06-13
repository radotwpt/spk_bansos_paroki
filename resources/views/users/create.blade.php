@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="nb-card anim-fade-up" style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800;">Tambah Pengguna</h2>
        <p style="margin: 0; color: var(--gray-500); font-size: 0.9rem;">Buat akun baru untuk akses sistem.</p>
    </div>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label" for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="role_id">Role / Peran</label>
            <select id="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->label ?? $role->name }}</option>
                @endforeach
            </select>
            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" id="lingkungan-group">
            <label class="form-label" for="lingkungan_id">Lingkungan (Hanya untuk Ketua Lingkungan)</label>
            <select id="lingkungan_id" name="lingkungan_id" class="form-control @error('lingkungan_id') is-invalid @enderror">
                <option value="">-- Pilih Lingkungan --</option>
                @foreach($lingkungans as $ling)
                <option value="{{ $ling->id }}" {{ old('lingkungan_id') == $ling->id ? 'selected' : '' }}>{{ $ling->name }}</option>
                @endforeach
            </select>
            @error('lingkungan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
        </div>
    </form>
</div>
@endsection
