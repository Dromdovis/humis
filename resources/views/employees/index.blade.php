@extends('layouts.app')

@section('title', 'Darbuotojai - Humis')
@section('page-title', 'Darbuotojai')
@section('page-subtitle', 'Valdykite darbuotojų įgūdžius ir informaciją')

@section('header-actions')
    <form action="{{ route('employees.sync') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn--primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            Atnaujinti
        </button>
    </form>
@endsection

@section('content')
@php
    $sortBy = $sortBy ?? 'name';
    $sortDir = $sortDir ?? 'asc';
    $nextNameDir = ($sortBy === 'name' && $sortDir === 'asc') ? 'desc' : 'asc';
    $nextTasksDir = ($sortBy === 'tasks' && $sortDir === 'asc') ? 'desc' : 'asc';
@endphp
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Visi darbuotojai ({{ $employees->count() }})</h2>
    </div>
    @if($employees->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => $nextNameDir]) }}" class="sort-link">
                            Darbuotojas
                            @if($sortBy === 'name')
                                <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    @if($sortDir === 'asc')<path d="M7 15l5 5 5-5"/>@else<path d="M7 9l5-5 5 5"/>@endif
                                </svg>
                            @endif
                        </a>
                    </th>
                    <th>Įgūdžiai</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'tasks', 'dir' => $nextTasksDir]) }}" class="sort-link">
                            Aktyvios užduotys
                            @if($sortBy === 'tasks')
                                <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    @if($sortDir === 'asc')<path d="M7 15l5 5 5-5"/>@else<path d="M7 9l5-5 5 5"/>@endif
                                </svg>
                            @endif
                        </a>
                    </th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                <tr>
                    <td>
                        <div class="user-row">
                            <div class="avatar" style="background: {{ $employee->color ?? '#6366f1' }}">
                                {{ substr($employee->name, 0, 1) }}
                            </div>
                            <div class="user-row__info">
                                <div class="user-row__name">{{ $employee->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($employee->skills->count() > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @foreach($employee->skills->sortBy('name')->take(8) as $skill)
                                    @php
                                        $lvl = (int) $skill->pivot->level;
                                        $skillClass = match (true) {
                                            $lvl >= 5 => 'employee-skill employee-skill--5',
                                            $lvl === 4 => 'employee-skill employee-skill--4',
                                            $lvl === 3 => 'employee-skill employee-skill--3',
                                            $lvl === 2 => 'employee-skill employee-skill--2',
                                            default => 'employee-skill employee-skill--01',
                                        };
                                    @endphp
                                    <span class="{{ $skillClass }}" title="Lygis: {{ $lvl }}/5">
                                        {{ $skill->name }} <span class="employee-skill__lvl">{{ $lvl }}</span>
                                    </span>
                                @endforeach
                                @if($employee->skills->count() > 8)
                                    <span class="badge badge--neutral" title="Visi įgūdžiai — detalių puslapyje">+{{ $employee->skills->count() - 8 }}</span>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--text-muted); font-size: 13px;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge--info" title="Skaičiuota paskutinio „Atnaujinti“ metu (aktyvios būsenos ClickUp)">{{ $employee->cached_active_tasks_count !== null ? $employee->cached_active_tasks_count : '—' }}</span>
                    </td>
                    <td>
                        <a href="{{ route('employees.show', $employee) }}" class="btn btn--secondary btn--sm">
                            Detalės
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="card__body">
            <div class="empty-state">
                <div class="empty-state__icon">👥</div>
                <div class="empty-state__title">Nėra darbuotojų</div>
                <p class="empty-state__text">Atnaujinkite darbuotojus iš ClickUp</p>
                <form action="{{ route('employees.sync') }}" method="POST" style="margin-top: 20px;">
                    @csrf
                    <button type="submit" class="btn btn--primary">Atnaujinti dabar</button>
                </form>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
.sort-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: inherit;
    text-decoration: none;
    font-weight: 600;
}
.sort-link:hover { color: var(--color-primary); }
.sort-icon { opacity: 0.75; }
.employee-skill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 12px;
    font-weight: 500;
    padding: 2px 8px;
    border-radius: 6px;
    border: 1px solid transparent;
}
.employee-skill__lvl {
    font-size: 10px;
    opacity: 0.85;
    font-weight: 600;
}
.employee-skill--5 { background: #dcfce7; color: #166534; border-color: #86efac; }
.employee-skill--4 { background: #ecfccb; color: #3f6212; border-color: #bef264; }
.employee-skill--3 { background: #fef9c3; color: #854d0e; border-color: #fde047; }
.employee-skill--2 { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
.employee-skill--01 { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
</style>
@endpush
@endsection
