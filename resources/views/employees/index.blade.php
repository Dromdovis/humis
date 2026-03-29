@extends('layouts.app')

@section('title', 'Darbuotojai - Humis')
@section('page-title', 'Darbuotojai')
@section('page-subtitle', 'Valdykite darbuotojų įgūdžius ir informaciją')

@section('header-actions')
    <form action="{{ route('sync.employees') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn--primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            Sinchronizuoti
        </button>
    </form>
@endsection

@section('content')
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Visi darbuotojai ({{ $employees->count() }})</h2>
    </div>
    @if($employees->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Darbuotojas</th>
                    <th>Įgūdžiai</th>
                    <th>Užduotys</th>
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
                                <div class="user-row__email">{{ $employee->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($employee->skills->count() > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @foreach($employee->skills->take(4) as $skill)
                                    <span class="badge badge--neutral" title="Lygis: {{ $skill->pivot->level }}/5">
                                        {{ $skill->name }}
                                    </span>
                                @endforeach
                                @if($employee->skills->count() > 4)
                                    <span class="badge badge--neutral">+{{ $employee->skills->count() - 4 }}</span>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--text-muted); font-size: 13px;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge--info">{{ $employee->active_tasks_count ?? 0 }}</span>
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
                <p class="empty-state__text">Sinchronizuokite darbuotojus iš ClickUp</p>
                <form action="{{ route('sync.employees') }}" method="POST" style="margin-top: 20px;">
                    @csrf
                    <button type="submit" class="btn btn--primary">Sinchronizuoti dabar</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
