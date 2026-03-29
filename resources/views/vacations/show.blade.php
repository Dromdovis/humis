@extends('layouts.app')

@section('title', 'Atostogos - ' . $vacation->employee->name)

@section('content')
<div class="main__header">
    <h1 class="main__title">Atostogos: {{ $vacation->employee->name }}</h1>
    <p class="main__subtitle">{{ $vacation->start_date->format('Y-m-d') }} - {{ $vacation->end_date->format('Y-m-d') }} ({{ $vacation->duration_days }} d.)</p>
</div>

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
            <table class="table">
                <thead>
                    <tr>
                        <th>Užduotis</th>
                        <th>Pavaduotojas</th>
                        <th>Terminas</th>
                        <th>Būsena</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vacation->taskAssignments as $assignment)
                    <tr>
                        <td>
                            <div style="font-weight: 500;">{{ $assignment->task_name }}</div>
                            @if($assignment->time_estimate_hours)
                                <div style="font-size: 13px; color: var(--text-secondary);">
                                    ~{{ $assignment->time_estimate_hours }}h
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($assignment->is_excluded)
                                <span class="badge badge--neutral">Išskirta</span>
                                @if($assignment->exclude_reason)
                                    <div style="font-size: 12px; color: var(--text-muted);">{{ $assignment->exclude_reason }}</div>
                                @endif
                            @elseif($assignment->substitute)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="avatar avatar--sm" style="background: {{ $assignment->substitute->color ?? '#1877f2' }}">
                                        {{ substr($assignment->substitute->name, 0, 1) }}
                                    </div>
                                    {{ $assignment->substitute->name }}
                                </div>
                            @else
                                <span style="color: var(--text-muted);">Nepaskirta</span>
                            @endif
                        </td>
                        <td>{{ $assignment->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            @if($assignment->is_processed)
                                <span class="badge badge--success">✓ Sinchronizuota</span>
                            @elseif($assignment->is_excluded)
                                <span class="badge badge--neutral">Praleista</span>
                            @else
                                <span class="badge badge--warning">Laukia</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!$vacation->tasks_reassigned)
                <div style="padding: 16px; border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn--success" onclick="showProcessModal()">
                        🚀 Sinchronizuoti į ClickUp
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

<div id="process-modal" class="modal" style="display: none;">
    <div class="modal__backdrop" onclick="closeProcessModal()"></div>
    <div class="modal__content">
        <div class="modal__header">
            <h3 class="modal__title">⚠️ Patvirtinkite sinchronizaciją</h3>
        </div>
        <div class="modal__body">
            <div class="alert alert--warning" style="margin-bottom: 16px;">
                <strong>Dėmesio!</strong> Šis veiksmas pridės pavaduotojus prie užduočių ClickUp sistemoje.
            </div>
            <p><strong>Bus paveiktos užduotys:</strong></p>
            <ul style="margin: 12px 0; padding-left: 20px; color: var(--text-secondary);">
                @foreach($vacation->taskAssignments->where('is_excluded', false)->whereNotNull('substitute_id') as $assignment)
                    <li>{{ $assignment->task_name }} → {{ $assignment->substitute?->name }}</li>
                @endforeach
            </ul>
            <p style="font-size: 13px; color: var(--text-muted);">
                Tai pridės pavaduotojus kaip papildomus assignee. Originalus darbuotojas liks priskirtas.
            </p>
        </div>
        <div class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeProcessModal()">Atšaukti</button>
            <button type="button" class="btn btn--success" onclick="document.getElementById('process-form').submit();">
                🚀 Patvirtinti ir sinchronizuoti
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
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
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
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
    .modal__header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
    }
    .modal__title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
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
