@extends('layouts.app')

@section('title', 'Atostogos - ' . $vacation->employee->name)
@section('page-title', 'Atostogos: ' . $vacation->employee->name)
@section('page-subtitle', $vacation->start_date->format('Y-m-d') . ' – ' . $vacation->end_date->format('Y-m-d') . ' (' . $vacation->duration_days . ' d.)')

@section('content')

<div class="grid grid--3" style="margin-bottom: 24px;">
    <div class="stat-card" style="text-align: left;">
        <div class="stat__label" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">Būsena</div>
        @if($vacation->tasks_reassigned)
            <span class="badge badge--success">✓ Priskirta</span>
        @elseif($vacation->scheduled_at)
            <span class="badge badge--info">📅 Suplanuota</span>
        @else
            <span class="badge badge--warning">⏳ Nepriskirta</span>
        @endif
    </div>
    @if($vacation->scheduled_at && !$vacation->tasks_reassigned)
    <div class="stat-card" style="text-align: left;">
        <div class="stat__label" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">Suplanuota vykdyti</div>
        <div style="font-size: 18px; font-weight: 600;">{{ $vacation->scheduled_at->format('Y-m-d') }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">
            @if($vacation->scheduled_at->isToday())
                Šiandien
            @elseif($vacation->scheduled_at->isFuture())
                po {{ $vacation->scheduled_at->diffInDays(now()) }} d.
            @else
                prieš {{ now()->diffInDays($vacation->scheduled_at) }} d.
            @endif
        </div>
    </div>
    @else
    <div class="stat-card" style="text-align: left;">
        <div class="stat__label" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">Užduočių</div>
        <div style="font-size: 18px; font-weight: 600;">{{ $vacation->taskAssignments->count() }}</div>
    </div>
    @endif
    <div class="stat-card" style="text-align: left;">
        <div class="stat__label" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">Atostogų pradžia</div>
        <div style="font-size: 18px; font-weight: 600;">{{ $vacation->start_date->format('m-d') }}</div>
        <div style="font-size: 12px; color: var(--text-muted);">
            @if($vacation->start_date->isFuture())
                po {{ $vacation->start_date->diffInDays(now()) }} d.
            @else
                prasidėjo
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Užduočių paskirstymas</h2>
        @if(!$vacation->tasks_reassigned)
            <a href="{{ route('vacations.assign', $vacation) }}" class="btn btn--primary btn--sm">
                ✏️ Redaguoti paskirstymą
            </a>
        @endif
    </div>
    <div class="card__body" style="padding: 0;">
        @if($vacation->taskAssignments->count() > 0)
            <div class="task-cards">
                @foreach($vacation->taskAssignments as $assignment)
                @php
                    $daysLeft = $assignment->due_date
                        ? (int) now()->startOfDay()->diffInDays($assignment->due_date->startOfDay(), false)
                        : null;
                @endphp
                <div class="task-card {{ $assignment->is_excluded ? 'task-card--excluded' : '' }}">
                    <div class="task-card__header">
                        <div class="task-card__title-row">
                            <a href="{{ $assignment->task_url ?? 'https://app.clickup.com/t/' . $assignment->clickup_task_id }}" 
                               target="_blank" 
                               class="task-card__name">
                                {{ $assignment->task_name }}
                            </a>
                            <div class="task-card__status-badge">
                                @if($assignment->is_processed)
                                    <span class="badge badge--success">✓ Atnaujinta</span>
                                @elseif($assignment->is_excluded)
                                    <span class="badge badge--neutral">Praleista</span>
                                @else
                                    <span class="badge badge--warning">Laukia atnaujinimo</span>
                                @endif
                            </div>
                        </div>
                        <div class="task-card__meta">
                            @if($assignment->task_status)
                                <span class="task-status-badge" style="--status-color: {{ $assignment->task_status_color ?? '#87909e' }};">
                                    <span class="task-status-badge__dot"></span>
                                    {{ strtoupper($assignment->task_status) }}
                                </span>
                            @endif
                            @if(!empty($assignment->task_tags))
                                @foreach($assignment->task_tags as $tag)
                                    <span class="task-tag" style="background: {{ $tag['bg'] ?? '#e0e0e0' }};">
                                        {{ $tag['name'] }}
                                    </span>
                                @endforeach
                            @endif
                            @if($assignment->time_estimate_hours)
                                <span class="time-estimate-badge">⏱ {{ $assignment->time_estimate_hours }}h</span>
                            @endif
                            @if($assignment->priority)
                                @php
                                    $priorityColors = ['urgent' => '#f50000', 'high' => '#ffcc00', 'normal' => '#6fddff', 'low' => '#d8d8d8'];
                                    $pColor = $priorityColors[$assignment->priority] ?? '#d8d8d8';
                                @endphp
                                <span class="priority-badge" style="--priority-color: {{ $pColor }};">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $pColor }}" stroke="{{ $pColor }}" stroke-width="2">
                                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                        <line x1="4" y1="22" x2="4" y2="15"></line>
                                    </svg>
                                    {{ ucfirst($assignment->priority) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="task-card__body">
                        <div class="task-card__info">
                            <div class="task-card__info-item">
                                <span class="task-card__info-label">Pridėtas pavaduotojas</span>
                                @if($assignment->is_excluded)
                                    <span style="color: var(--text-muted); font-size: 13px;">—</span>
                                @elseif($assignment->substitute)
                                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                                        <div class="avatar avatar--sm" style="background: {{ $assignment->substitute->color ?? '#1877f2' }}">
                                            {{ substr($assignment->substitute->name, 0, 1) }}
                                        </div>
                                        <span style="font-weight: 500;">{{ $assignment->substitute->name }}</span>
                                    </div>
                                @else
                                    <span style="color: var(--text-muted); font-size: 13px;">Nepaskirta</span>
                                @endif
                            </div>
                            <div class="task-card__info-item">
                                <span class="task-card__info-label">Terminas</span>
                                @if($assignment->due_date)
                                    <div>
                                        <span style="font-weight: 500;">
                                            @if($assignment->start_date)
                                                {{ $assignment->start_date->format('m-d') }} → {{ $assignment->due_date->format('m-d') }}
                                            @else
                                                iki {{ $assignment->due_date->format('Y-m-d') }}
                                            @endif
                                        </span>
                                        <div style="font-size: 11px; margin-top: 1px; color: {{ $daysLeft !== null && $daysLeft < 0 ? '#dc2626' : ($daysLeft !== null && $daysLeft <= 3 ? '#d97706' : 'var(--text-muted)') }};">
                                            @if($daysLeft < 0)
                                                Vėluoja {{ abs($daysLeft) }} d.
                                            @elseif($daysLeft === 0)
                                                Šiandien
                                            @else
                                                Liko {{ $daysLeft }} {{ $daysLeft === 1 ? 'diena' : ($daysLeft < 10 && $daysLeft % 10 >= 2 ? 'dienos' : 'dienų') }}
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span style="color: var(--text-muted); font-size: 13px;">Nenustatytas</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(!$vacation->tasks_reassigned)
                <div style="padding: 20px 24px; border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn--success" onclick="showProcessModal()">
                        Atnaujinti ClickUp
                    </button>
                    <form id="process-form" action="{{ route('vacations.process', $vacation) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state__icon">📋</div>
                <div class="empty-state__title">Užduotys nepaskirstytos</div>
                <p>Perskirstykite darbuotojo užduotis kitiems</p>
                <a href="{{ route('vacations.assign', $vacation) }}" class="btn btn--primary" style="margin-top: 16px;">
                    Perskirstyti užduotis
                </a>
            </div>
        @endif
    </div>
</div>

<div id="process-modal" class="modal process-modal" style="display: none;">
    <div class="modal__backdrop" onclick="closeProcessModal()"></div>
    <div class="modal__content process-modal__box">
        <div class="process-modal__header">
            <div class="process-modal__icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div>
                <h3 class="process-modal__title">Patvirtinkite atnaujinimą</h3>
                <p class="process-modal__subtitle">Pavaduotojai bus pridėti ClickUp užduotims</p>
            </div>
        </div>
        <div class="modal__body process-modal__body">
            <div class="process-modal__notice">
                <strong>Dėmesio.</strong> Šis veiksmas pridės pavaduotojus prie užduočių ClickUp sistemoje.
            </div>
            <p class="process-modal__section-title">Bus paveiktos užduotys</p>
            <ul class="process-modal__list">
                @foreach($vacation->taskAssignments->where('is_excluded', false)->whereNotNull('substitute_id') as $assignment)
                    <li>
                        <span class="process-modal__task-name">{{ $assignment->task_name }}</span>
                        <span class="process-modal__arrow">→</span>
                        <span class="process-modal__substitute">{{ $assignment->substitute?->name }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="process-modal__footnote">
                Tai pridės pavaduotojus kaip papildomus darbuotojus prie užduoties, bet originalūs darbuotojai liks priskirti.
            </p>
        </div>
        <div class="modal__footer process-modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeProcessModal()">Atšaukti</button>
            <button type="button" class="btn btn--success process-modal__confirm" onclick="document.getElementById('process-form').submit();">
                Patvirtinti ir atnaujinti
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .task-cards {
        display: flex;
        flex-direction: column;
    }
    .task-card {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.1s;
    }
    .task-card:last-child {
        border-bottom: none;
    }
    .task-card:hover {
        background: #fafbfc;
    }
    .task-card--excluded {
        opacity: 0.55;
    }
    .task-card__header {
        margin-bottom: 10px;
    }
    .task-card__title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 6px;
    }
    .task-card__name {
        font-weight: 600;
        font-size: 15px;
        color: var(--accent);
        text-decoration: underline;
    }
    .task-card__name:hover {
        opacity: 0.8;
    }
    .task-card__meta {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .task-card__body {
        display: flex;
        gap: 24px;
    }
    .task-card__info {
        display: flex;
        gap: 40px;
        flex: 1;
    }
    .task-card__info-item {
        min-width: 0;
    }
    .task-card__info-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .task-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        padding: 2px 8px;
        border-radius: 4px;
        background: color-mix(in srgb, var(--status-color) 15%, transparent);
        color: var(--status-color);
        white-space: nowrap;
    }
    .task-status-badge__dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--status-color);
        flex-shrink: 0;
    }
    .task-tag {
        color: #fff;
        padding: 1px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 500;
        text-shadow: 0 0 2px rgba(0,0,0,0.3);
    }
    .time-estimate-badge {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-dark);
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 4px;
    }
    .priority-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 11px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .process-modal__box {
        max-width: 440px;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    }
    .process-modal__header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 22px 24px 18px;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(180deg, #fafbfc 0%, var(--bg-white) 100%);
    }
    .process-modal__icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #fff7ed;
        color: #ea580c;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .process-modal__title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
        line-height: 1.25;
    }
    .process-modal__subtitle {
        margin: 4px 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }
    .process-modal__body {
        padding: 20px 24px 22px;
    }
    .process-modal__notice {
        font-size: 14px;
        line-height: 1.5;
        color: #9a3412;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 18px;
    }
    .process-modal__notice strong {
        color: #c2410c;
    }
    .process-modal__section-title {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: var(--text-muted);
    }
    .process-modal__list {
        list-style: none;
        margin: 0 0 16px;
        padding: 0;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        max-height: 200px;
        overflow-y: auto;
    }
    .process-modal__list li {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding: 10px 14px;
        font-size: 14px;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-white);
    }
    .process-modal__list li:last-child {
        border-bottom: none;
    }
    .process-modal__task-name {
        font-weight: 500;
        color: var(--text-dark);
    }
    .process-modal__arrow {
        color: var(--text-muted);
        font-size: 13px;
    }
    .process-modal__substitute {
        color: var(--accent);
        font-weight: 500;
    }
    .process-modal__footnote {
        margin: 0;
        font-size: 13px;
        line-height: 1.55;
        color: var(--text-secondary);
        padding-top: 4px;
        border-top: 1px dashed var(--border-color);
    }
    .process-modal__footer {
        padding: 16px 24px 20px;
        background: #fafbfc;
    }
    .process-modal__confirm {
        min-width: 180px;
        font-weight: 600;
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
    }
    .modal__content {
        position: relative;
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        max-width: 500px;
        width: 100%;
        margin: 20px;
        max-height: 80vh;
        overflow-y: auto;
    }
    .modal__body {
        padding: 24px;
    }
    .modal__footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .btn--success {
        background: var(--accent);
        color: white;
    }
    .btn--success:hover {
        background: var(--accent-hover);
    }
</style>
@endpush

@push('scripts')
<script>
    function showProcessModal() {
        document.getElementById('process-modal').style.display = 'flex';
    }

    function closeProcessModal() {
        document.getElementById('process-modal').style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProcessModal();
        }
    });
</script>
@endpush
@endsection
