@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header anim-fade-up">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Selamat datang, {{ Auth::user()->name }}</p>
</div>

<div class="nb-alert alert-info anim-fade-up">
    <strong>Info:</strong> Silakan gunakan menu di sebelah kiri untuk navigasi.
</div>
@endsection
