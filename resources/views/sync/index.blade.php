@extends('layouts.app')

@section('title', 'Sinchronizacija - Humis')
@section('page-title', 'Sinchronizacijos būsena')
@section('page-subtitle', 'ClickUp duomenų sinchronizacija')

@section('header-actions')
    <div style="display: flex; gap: 8px;">
        <form action="{{ route('sync.employees') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn--secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Darbuotojai
            </button>
        </form>
        <form action="{{ route('projects.sync') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn--secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                Projektai
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div class="card__body" style="padding: 24px;">
        @if($teams && isset($teams['teams'][0]))
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; background: var(--success-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 18px; font-weight: 600; color: var(--text-dark);">{{ $teams['teams'][0]['name'] }}</div>
                    <div style="color: var(--text-secondary);">ClickUp workspace prisijungtas</div>
                </div>
                <span class="badge badge--success" style="font-size: 14px; padding: 8px 16px;">Aktyvus</span>
            </div>
        @else
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; background: var(--danger-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 18px; font-weight: 600; color: var(--text-dark);">Neprisijungta</div>
                    <div style="color: var(--text-secondary);">Patikrinkite ClickUp API konfigūraciją</div>
                </div>
                <span class="badge badge--danger" style="font-size: 14px; padding: 8px 16px;">Klaida</span>
            </div>
        @endif
    </div>
</div>

<div class="grid grid--3" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--blue">👥</div>
        <div class="stat-card__value">{{ $stats['employees'] ?? 0 }}</div>
        <div class="stat-card__label">Darbuotojai sistemoje</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">📁</div>
        <div class="stat-card__value">{{ $stats['projects'] ?? 0 }}</div>
        <div class="stat-card__label">Projektai sistemoje</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--yellow">⭐</div>
        <div class="stat-card__value">{{ $stats['skills'] ?? 0 }}</div>
        <div class="stat-card__label">Įgūdžiai sistemoje</div>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Darbuotojų sinchronizacija</h2>
        </div>
        <div class="card__body">
            <div class="sync-info">
                <div class="sync-info__row">
                    <span class="sync-info__label">ClickUp nariai</span>
                    <span class="sync-info__value">{{ $teams['teams'][0]['members'] ? count($teams['teams'][0]['members']) : 0 }}</span>
                </div>
                <div class="sync-info__row">
                    <span class="sync-info__label">Sistemoje</span>
                    <span class="sync-info__value">{{ $stats['employees'] ?? 0 }}</span>
                </div>
            </div>
            <form action="{{ route('sync.employees') }}" method="POST" style="margin-top: 16px;">
                @csrf
                <button type="submit" class="btn btn--primary btn--block">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Sinchronizuoti darbuotojus
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Projektų sinchronizacija</h2>
        </div>
        <div class="card__body">
            <div class="sync-info">
                <div class="sync-info__row">
                    <span class="sync-info__label">ClickUp Lists</span>
                    <span class="sync-info__value">—</span>
                </div>
                <div class="sync-info__row">
                    <span class="sync-info__label">Sistemoje</span>
                    <span class="sync-info__value">{{ $stats['projects'] ?? 0 }}</span>
                </div>
            </div>
            <form action="{{ route('projects.sync') }}" method="POST" style="margin-top: 16px;">
                @csrf
                <button type="submit" class="btn btn--primary btn--block">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Sinchronizuoti projektus
                </button>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.sync-info {
    background: var(--bg-body);
    border-radius: var(--radius);
    padding: 12px 16px;
}
.sync-info__row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border-light);
}
.sync-info__row:last-child {
    border-bottom: none;
}
.sync-info__label {
    color: var(--text-secondary);
    font-size: 14px;
}
.sync-info__value {
    font-weight: 600;
    color: var(--text-dark);
}
.btn--block {
    width: 100%;
    justify-content: center;
}
</style>
@endpush
@endsection
