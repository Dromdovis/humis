@extends('layouts.app')

@section('title', 'Nustatymai - Humis')
@section('page-title', 'Nustatymai')
@section('page-subtitle', 'Sistemos konfigūracija')

@section('content')
<div class="grid grid--2">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">ClickUp integracija</h2>
        </div>
        <div class="card__body">
            @if($clickupUser && isset($clickupUser['user']))
                <div style="display: flex; align-items: center; gap: 16px; padding: 20px; background: var(--accent-bg); border-radius: var(--radius-lg); border: 1px solid var(--accent-light);">
                    <div class="avatar avatar--lg" style="background: {{ $clickupUser['user']['color'] ?? '#10b981' }}">
                        {{ substr($clickupUser['user']['username'] ?? 'U', 0, 1) }}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 16px; font-weight: 600; color: var(--text-dark);">{{ $clickupUser['user']['username'] ?? 'Vartotojas' }}</div>
                        <div style="font-size: 13px; color: var(--text-secondary);">{{ $clickupUser['user']['email'] ?? '' }}</div>
                    </div>
                    <span class="badge badge--success">Prisijungta</span>
                </div>

                @if($teams && isset($teams['teams'][0]))
                <div style="margin-top: 16px; padding: 16px; background: var(--bg-body); border-radius: var(--radius);">
                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Workspace</div>
                    <div style="font-weight: 600;">{{ $teams['teams'][0]['name'] }}</div>
                    <div style="font-size: 13px; color: var(--text-secondary);">{{ count($teams['teams'][0]['members'] ?? []) }} komandos narių</div>
                </div>
                @endif
            @else
                <div class="alert alert--danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    ClickUp integracija nekonfigūruota
                </div>
                <p style="color: var(--text-secondary); font-size: 13px;">
                    Kreipkitės į administratorių dėl ClickUp API konfigūracijos.
                </p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Apie sistemą</h2>
        </div>
        <div class="card__body">
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">Sistema</span>
                    <span class="info-value">Humis v1.0</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Paskirtis</span>
                    <span class="info-value">Žmogiškųjų išteklių valdymas</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Integracija</span>
                    <span class="info-value">ClickUp API</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.info-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.info-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
}
.info-item:last-child {
    border-bottom: none;
}
.info-label {
    color: var(--text-secondary);
    font-size: 14px;
}
.info-value {
    font-weight: 500;
    color: var(--text-dark);
}
</style>
@endpush
@endsection
