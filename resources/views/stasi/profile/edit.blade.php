@extends('layouts.app')

@section('title', 'Profil & Pengaturan Stasi')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   PROFILE EDIT STASI — Premium Modern UI
   ═══════════════════════════════════════════════════════════ */

:root {
    --primary: #4F46E5;
    --primary-light: #EEF2FF;
    --secondary: #10B981;
    --dark: #0F172A;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-300: #CBD5E1;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --gray-700: #334155;
    --radius-lg: 16px;
    --radius-md: 10px;
    --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.05);
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
    --transition: all 0.2s ease;
}

.profile-container { max-width: 800px; margin: 0 auto; }

.page-header { margin-bottom: 2rem; }
.page-title { font-size: 1.8rem; font-weight: 800; color: var(--dark); letter-spacing: -0.02em; margin-bottom: 0.5rem; }
.page-subtitle { font-size: 0.95rem; color: var(--gray-500); }

.card { background: white; border: 1px solid var(--gray-100); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 2rem; }
.card-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--gray-100); display: flex; align-items: center; gap: 1rem; background: var(--gray-50); }
.card-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; }
.card-title { font-size: 1.15rem; font-weight: 700; color: var(--dark); margin: 0; }
.card-desc { font-size: 0.85rem; color: var(--gray-500); margin-top: 0.2rem; }
.card-body { padding: 2rem; }

.form-group { margin-bottom: 1.5rem; }
.form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; }
.form-label span.req { color: #EF4444; }

.form-control { width: 100%; padding: 0.75rem 1rem; font-family: inherit; font-size: 0.95rem; border: 1px solid var(--gray-200); border-radius: var(--radius-md); background: var(--gray-50); color: var(--dark); transition: var(--transition); outline: none; }
.form-control:focus { background: white; border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }
textarea.form-control { resize: vertical; }

.invalid-feedback { font-size: 0.8rem; color: #EF4444; margin-top: 0.4rem; font-weight: 500; }
.form-hint { font-size: 0.8rem; color: var(--gray-500); margin-top: 0.4rem; display: flex; align-items: flex-start; gap: 0.4rem; line-height: 1.4; }
.form-hint svg { flex-shrink: 0; margin-top: 0.1rem; }

.btn-primary { background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; font-size: 0.95rem; font-weight: 600; border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1); }
.btn-primary:hover { background: #4338CA; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2); }

.alert { padding: 1rem 1.25rem; border-radius: var(--radius-md); font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
.alert-success { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
</style>
@endpush

@section('content')

<div class="profile-container">

    <div class="page-header anim-fade-up">
        <h1 class="page-title">Profil & Pengaturan Stasi</h1>
        <p class="page-subtitle">Kelola informasi stasi yang akan digunakan pada sistem dan kop surat resmi.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success anim-fade-up">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="card anim-fade-up delay-1">
        <div class="card-header">
            <div class="card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
                <h2 class="card-title">Informasi Dasar</h2>
                <p class="card-desc">Pastikan data yang Anda isi valid dan sesuai.</p>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('stasi.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="name">Nama Stasi <span class="req">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $stasi->name) }}" required placeholder="Contoh: Stasi St. Maria">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="leader_name">Nama Ketua Stasi</label>
                    <input type="text" name="leader_name" id="leader_name" class="form-control" value="{{ old('leader_name', $stasi->leader_name) }}" placeholder="Contoh: Bpk. Yohanes">
                    @error('leader_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-hint">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Nama ini akan otomatis tertera pada bagian tanda tangan (mengetahui) di bawah surat permohonan.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon / Kontak</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $stasi->phone) }}" placeholder="Contoh: 08123456789">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Alamat Stasi Lengkap</label>
                    <textarea name="address" id="address" class="form-control" rows="3" placeholder="Tuliskan alamat lengkap beserta kode pos...">{{ old('address', $stasi->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-hint">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Alamat ini akan digunakan dan dicetak secara otomatis di kop surat resmi Stasi.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Catatan internal stasi...">{{ old('notes', $stasi->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-100); display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
