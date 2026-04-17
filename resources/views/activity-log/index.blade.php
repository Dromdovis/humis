@extends('layouts.app')

@section('title', 'Žurnalas - Humis')
@section('page-title', 'Veiksmų žurnalas')
@section('page-subtitle', 'Sistemos veiksmų istorija')

@section('content')
<div class="card">
    <div class="card__body" style="padding: 0;">
        @if($logs->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Data ir laikas</th>
                        <th style="width: 140px;">Vykdytojas</th>
                        <th style="width: 160px;">Veiksmas</th>
                        <th>Aprašymas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td style="font-size: 13px; color: var(--text-secondary);">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 6px;">
                                <span style="width: 24px; height: 24px; border-radius: 50%; background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600;">A</span>
                                <span style="font-size: 13px;">Administratorius</span>
                            </span>
                        </td>
                        <td>
                            @switch($log->action)
                                @case('vacation_created')
                                    <span class="badge badge--info">Atostogos sukurtos</span>
                                    @break
                                @case('vacation_deleted')
                                    <span class="badge badge--danger">Atostogos ištrintos</span>
                                    @break
                                @case('tasks_reassigned')
                                    <span class="badge badge--success">Užduotys perskirstytos</span>
                                    @break
                                @case('tasks_processed')
                                    <span class="badge badge--warning">Priskirta ClickUp</span>
                                    @break
                                @case('employees_synced')
                                    <span class="badge badge--info">Darbuotojai atnaujinti</span>
                                    @break
                                @case('settings_changed')
                                    <span class="badge badge--neutral">Nustatymai pakeisti</span>
                                    @break
                                @default
                                    <span class="badge badge--neutral">{{ $log->action }}</span>
                            @endswitch
                        </td>
                        <td style="font-size: 13px;">{{ $log->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($logs->hasPages())
                <div style="padding: 16px; border-top: 1px solid var(--border-color);">
                    {{ $logs->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state__icon">📋</div>
                <div class="empty-state__title">Žurnalas tuščias</div>
                <p>Kol kas neužfiksuota jokių veiksmų</p>
            </div>
        @endif
    </div>
</div>
@endsection
