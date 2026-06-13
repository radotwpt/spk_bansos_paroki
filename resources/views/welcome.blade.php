@extends('layouts.auth')

@section('title', 'SPK Bansos — Sistem Pendukung Keputusan Bantuan Sosial')

@push('styles')
<style>
.welcome-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--black);
    position: relative;
    overflow: hidden;
}

.welcome-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(245,197,24,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(245,197,24,0.05) 1px, transparent 1px);
    background-size: 40px 40px;
}

.welcome-content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2rem;
}

.welcome-content h1 {
    color: var(--white);
    font-size: clamp(3rem, 8vw, 6rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 1rem;
}

.welcome-content h1 span {
    color: var(--yellow);
}

.welcome-content p {
    color: var(--gray-300);
    font-size: 1.1rem;
    margin-bottom: 2.5rem;
}

.welcome-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="welcome-hero">
    <div class="welcome-content anim-fade-up">
        <h1>SPK<br><span>Bansos</span></h1>
        <p>Sistem Pendukung Keputusan Bantuan Sosial</p>
        <div class="welcome-actions">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Masuk ke Sistem</a>
        </div>
    </div>
</div>
@endsection
