@extends('layouts.app')

@section('title', 'Pradžia - Humis')
@section('page-title', 'Pradžia')
@section('page-subtitle', 'Sistemos apžvalga')

@section('content')
@php
    $pendingVacations = isset($upcomingVacations) ? $upcomingVacations->where('tasks_reassigned', false)->whereNull('scheduled_at') : collect();
    $scheduledVacations = isset($upcomingVacations) ? $upcomingVacations->whereNotNull('scheduled_at')->where('tasks_reassigned', false) : collect();
@endphp

@if($pendingVacations->count() > 0)
<div class="alert-card alert-card--warning" style="margin-bottom: 24px;">
    <div class="alert-card__icon">⚠️</div>
    <div class="alert-card__content">
        <div class="alert-card__title">Reikia priskirti pavaduotojus</div>
        <div class="alert-card__text">{{ $pendingVacations->count() }} atostogos laukia priskyrimo</div>
    </div>
    <a href="{{ route('vacations.index') }}?filter=pending" class="btn btn--primary">Priskirti</a>
</div>
@endif

@if($scheduledVacations->count() > 0)
<div class="alert-card alert-card--info" style="margin-bottom: 24px;">
    <div class="alert-card__icon">📅</div>
    <div class="alert-card__content">
        <div class="alert-card__title">Suplanuoti priskyrimai</div>
        <div class="alert-card__text">{{ $scheduledVacations->count() }} priskyrimai bus įvykdyti automatiškai</div>
    </div>
    <a href="{{ route('vacations.index') }}?filter=scheduled" class="btn btn--secondary">Peržiūrėti</a>
</div>
@endif

<div class="grid grid--3" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--blue">👥</div>
        <div class="stat-card__value">{{ $stats['employees'] ?? 0 }}</div>
        <div class="stat-card__label">Darbuotojai</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green">📁</div>
        <div class="stat-card__value">{{ $stats['projects'] ?? 0 }}</div>
        <div class="stat-card__label">Projektai</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--yellow">📅</div>
        <div class="stat-card__value">{{ $stats['upcoming_vacations'] ?? 0 }}</div>
        <div class="stat-card__label">Artėjančios atostogos</div>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Artėjančios atostogos</h2>
            <a href="{{ route('vacations.index') }}" class="btn btn--secondary btn--sm">Visos</a>
        </div>
        @if(isset($upcomingVacations) && count($upcomingVacations) > 0)
            <div class="vacation-list">
                @foreach($upcomingVacations->take(5) as $vacation)
                <div class="vacation-item {{ !$vacation->tasks_reassigned ? 'vacation-item--pending' : '' }}">
                    <div class="vacation-item__user">
                        <div class="avatar avatar--sm" style="background: {{ $vacation->employee->color ?? '#6366f1' }}">
                            {{ substr($vacation->employee->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="vacation-item__name">{{ $vacation->employee->name ?? 'Nežinomas' }}</div>
                            <div class="vacation-item__dates">{{ $vacation->start_date->format('m-d') }} – {{ $vacation->end_date->format('m-d') }}</div>
                        </div>
                    </div>
                    <div>
                        @if($vacation->tasks_reassigned)
                            <span class="badge badge--success">Priskirta</span>
                        @else
                            <a href="{{ route('vacations.assign', $vacation) }}" class="btn btn--primary btn--sm">Priskirti</a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="card__body">
                <div class="empty-state" style="padding: 32px;">
                    <div class="empty-state__icon">✅</div>
                    <div class="empty-state__title">Viskas tvarkoje</div>
                    <p class="empty-state__text">Nėra artėjančių atostogų</p>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Greiti veiksmai</h2>
        </div>
        <div class="card__body" style="display: flex; flex-direction: column; gap: 8px;">
            <a href="{{ route('vacations.index') }}" class="quick-link">
                <div class="quick-link__icon" style="background: var(--accent-bg);">📅</div>
                <div>
                    <div class="quick-link__title">Atostogos</div>
                    <div class="quick-link__desc">Valdyti darbuotojų atostogas</div>
                </div>
            </a>
            <a href="{{ route('employees.index') }}" class="quick-link">
                <div class="quick-link__icon" style="background: var(--info-bg);">👥</div>
                <div>
                    <div class="quick-link__title">Darbuotojai</div>
                    <div class="quick-link__desc">Peržiūrėti komandos narius</div>
                </div>
            </a>
            <a href="{{ route('sync.index') }}" class="quick-link">
                <div class="quick-link__icon" style="background: var(--success-bg);">🔄</div>
                <div>
                    <div class="quick-link__title">Sinchronizacija</div>
                    <div class="quick-link__desc">ClickUp duomenų būsena</div>
                </div>
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
.alert-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    border-radius: var(--radius-lg);
}
.alert-card--warning {
    background: var(--warning-bg);
    border: 1px solid #fcd34d;
}
.alert-card--info {
    background: var(--info-bg);
    border: 1px solid #93c5fd;
}
.alert-card__icon {
    font-size: 28px;
}
.alert-card__content {
    flex: 1;
}
.alert-card__title {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 2px;
}
.alert-card__text {
    font-size: 14px;
    color: var(--text-secondary);
}
.vacation-list {
    display: flex;
    flex-direction: column;
}
.vacation-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-light);
}
.vacation-item:last-child {
    border-bottom: none;
}
.vacation-item--pending {
    background: var(--warning-bg);
}
.vacation-item__user {
    display: flex;
    align-items: center;
    gap: 12px;
}
.vacation-item__name {
    font-weight: 500;
    color: var(--text-dark);
}
.vacation-item__dates {
    font-size: 13px;
    color: var(--text-secondary);
}
</style>
@endpush
@endsection
