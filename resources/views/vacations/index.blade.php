@extends('layouts.app')

@section('title', 'Atostogos - Humis')
@section('page-title', 'Atostogos')
@section('page-subtitle', 'Valdykite darbuotojų atostogas ir užduočių perskirstymą')

@section('header-actions')
    <button type="button" onclick="openCreateModal()" class="btn btn--primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Naujos atostogos
    </button>
@endsection

@php
    $sortBy = $sortBy ?? 'start_date';
    $sortDir = $sortDir ?? 'desc';
@endphp

@section('content')
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Visos atostogos</h2>
    </div>
    @if($vacations->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => ($sortBy === 'name' && $sortDir === 'asc') ? 'desc' : 'asc']) }}" class="sort-link">
                            Darbuotojas
                            @if($sortBy === 'name')
                                <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    @if($sortDir === 'asc')
                                        <path d="M7 15l5 5 5-5"/>
                                    @else
                                        <path d="M7 9l5-5 5 5"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="sort-icon sort-icon--inactive" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 15l5 5 5-5"/><path d="M7 9l5-5 5 5"/></svg>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'start_date', 'dir' => ($sortBy === 'start_date' && $sortDir === 'asc') ? 'desc' : 'asc']) }}" class="sort-link">
                            Datos
                            @if($sortBy === 'start_date')
                                <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    @if($sortDir === 'asc')
                                        <path d="M7 15l5 5 5-5"/>
                                    @else
                                        <path d="M7 9l5-5 5 5"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="sort-icon sort-icon--inactive" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 15l5 5 5-5"/><path d="M7 9l5-5 5 5"/></svg>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'duration', 'dir' => ($sortBy === 'duration' && $sortDir === 'asc') ? 'desc' : 'asc']) }}" class="sort-link">
                            Trukmė
                            @if($sortBy === 'duration')
                                <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    @if($sortDir === 'asc')
                                        <path d="M7 15l5 5 5-5"/>
                                    @else
                                        <path d="M7 9l5-5 5 5"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="sort-icon sort-icon--inactive" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 15l5 5 5-5"/><path d="M7 9l5-5 5 5"/></svg>
                            @endif
                        </a>
                    </th>
                    <th>Pavaduotojai</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'dir' => ($sortBy === 'status' && $sortDir === 'asc') ? 'desc' : 'asc']) }}" class="sort-link">
                            Būsena
                            @if($sortBy === 'status')
                                <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    @if($sortDir === 'asc')
                                        <path d="M7 15l5 5 5-5"/>
                                    @else
                                        <path d="M7 9l5-5 5 5"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="sort-icon sort-icon--inactive" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 15l5 5 5-5"/><path d="M7 9l5-5 5 5"/></svg>
                            @endif
                        </a>
                    </th>
                    <th style="width: 180px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($vacations as $vacation)
                @php
                    $allSubstitutes = collect();

                    if ($vacation->defaultSubstitute) {
                        $allSubstitutes->push($vacation->defaultSubstitute);
                    }

                    foreach ($vacation->taskAssignments as $assignment) {
                        if ($assignment->substitute && !$allSubstitutes->contains('id', $assignment->substitute->id)) {
                            $allSubstitutes->push($assignment->substitute);
                        }
                    }
                @endphp
                <tr>
                    <td>
                        <div class="user-row">
                            <div class="avatar avatar--sm" style="background: {{ $vacation->employee->color ?? '#10b981' }}">
                                {{ substr($vacation->employee->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="user-row__name">{{ $vacation->employee->name ?? 'Nežinomas' }}</span>
                        </div>
                    </td>
                    <td style="color: var(--text-secondary);">
                        {{ $vacation->start_date->format('Y-m-d') }} – {{ $vacation->end_date->format('Y-m-d') }}
                    </td>
                    <td>{{ $vacation->duration_days }} d.</td>
                    <td>
                        @if($allSubstitutes->count() > 0)
                            <div class="substitutes-list">
                                @foreach($allSubstitutes->take(3) as $substitute)
                                    <div class="avatar avatar--xs" 
                                         style="background: {{ $substitute->color ?? '#6366f1' }}"
                                         title="{{ $substitute->name }}">
                                        {{ substr($substitute->name, 0, 1) }}
                                    </div>
                                @endforeach
                                @if($allSubstitutes->count() > 3)
                                    <span class="substitutes-more" title="{{ $allSubstitutes->skip(3)->pluck('name')->join(', ') }}">
                                        +{{ $allSubstitutes->count() - 3 }}
                                    </span>
                                @endif
                                @if($allSubstitutes->count() === 1)
                                    <span class="substitute-name">{{ $allSubstitutes->first()->name }}</span>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        @if($vacation->tasks_reassigned)
                            <span class="badge badge--success">Priskirta</span>
                        @elseif($vacation->scheduled_at)
                            <span class="badge badge--info" title="Suplanuota: {{ $vacation->scheduled_at->format('Y-m-d') }}">
                                📅 {{ $vacation->scheduled_at->format('m-d') }}
                            </span>
                        @else
                            <span class="badge badge--warning">Nepriskirta</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            @if(!$vacation->tasks_reassigned)
                                <a href="{{ route('vacations.assign', $vacation) }}" class="btn btn--primary btn--sm">
                                    Priskirti
                                </a>
                            @endif
                            <a href="{{ route('vacations.show', $vacation) }}" class="btn btn--secondary btn--sm">
                                Detalės
                            </a>
                            @if(!$vacation->tasks_reassigned)
                                <button type="button" 
                                        class="btn btn--danger btn--sm btn--icon" 
                                        onclick="confirmDelete({{ $vacation->id }}, '{{ $vacation->employee->name ?? 'Nežinomas' }}')"
                                        title="Ištrinti atostogas">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                                <form id="delete-form-{{ $vacation->id }}" action="{{ route('vacations.destroy', $vacation) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($vacations->hasPages())
        <div style="padding: 16px 24px; border-top: 1px solid var(--border-color);">
            {{ $vacations->links() }}
        </div>
        @endif
    @else
        <div class="card__body">
            <div class="empty-state">
                <div class="empty-state__icon">📅</div>
                <div class="empty-state__title">Nėra atostogų</div>
                <p class="empty-state__text">Pridėkite naują atostogų įrašą</p>
                <button type="button" onclick="openCreateModal()" class="btn btn--primary" style="margin-top: 20px;">
                    Naujos atostogos
                </button>
            </div>
        </div>
    @endif
</div>

<div id="create-modal" class="modal" style="display: none;">
    <div class="modal__backdrop" onclick="closeCreateModal()"></div>
    <div class="modal__content" style="max-width: 480px;">
        <div class="modal__header">
            <h3 class="modal__title">Naujos atostogos</h3>
            <button type="button" class="modal__close" onclick="closeCreateModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('vacations.store') }}" method="POST">
            @csrf
            <div class="modal__body">
                @if($errors->any())
                    <div class="alert alert--danger" style="margin-bottom: 16px;">
                        <ul style="margin: 0; padding-left: 18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Darbuotojas *</label>
                    @include('components.searchable-select', [
                        'name' => 'employee_id',
                        'options' => $employees->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'color' => $e->color ?? '#10b981'])->toArray(),
                        'value' => old('employee_id'),
                        'placeholder' => 'Pasirinkite darbuotoją...',
                        'searchPlaceholder' => 'Ieškoti darbuotojo...',
                        'required' => true,
                        'showAvatar' => true,
                    ])
                </div>

                <div class="grid grid--2">
                    <div class="form-group">
                        <label class="form-label">Pradžios data *</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pabaigos data *</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-input" required>
                    </div>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeCreateModal()">Atšaukti</button>
                <button type="submit" class="btn btn--primary">Tęsti → Perskirstyti</button>
            </div>
        </form>
    </div>
</div>

<div id="delete-modal" class="modal" style="display: none;">
    <div class="modal__backdrop" onclick="closeDeleteModal()"></div>
    <div class="modal__content">
        <div class="modal__header">
            <h3 class="modal__title">⚠️ Patvirtinkite trynimą</h3>
        </div>
        <div class="modal__body">
            <p>Ar tikrai norite ištrinti <strong id="delete-employee-name"></strong> atostogas?</p>
            <p style="color: var(--text-secondary); font-size: 13px; margin-top: 8px;">Šis veiksmas negrįžtamas.</p>
        </div>
        <div class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeDeleteModal()">Atšaukti</button>
            <button type="button" class="btn btn--danger" id="confirm-delete-btn">Ištrinti</button>
        </div>
    </div>
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
        transition: color 0.15s;
    }
    .sort-link:hover {
        color: var(--color-primary);
    }
    .sort-icon {
        opacity: 0.7;
    }
    .sort-icon--inactive {
        opacity: 0.3;
    }
    .sort-link:hover .sort-icon--inactive {
        opacity: 0.5;
    }

    .substitutes-list {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .avatar--xs {
        width: 24px;
        height: 24px;
        font-size: 11px;
        border: 2px solid var(--bg-white);
        margin-left: -6px;
    }
    .avatar--xs:first-child {
        margin-left: 0;
    }
    .substitutes-more {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 4px;
        cursor: help;
    }
    .substitute-name {
        font-size: 13px;
        color: var(--text-secondary);
        margin-left: 6px;
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
        max-width: 420px;
        width: 100%;
        margin: 20px;
    }
    .modal__header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal__title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
    }
    .modal__close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-muted);
        padding: 4px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }
    .modal__close:hover {
        background: var(--bg-body);
        color: var(--text-primary);
    }
    .modal__body {
        padding: 24px;
    }
    .modal__body .form-group:last-child {
        margin-bottom: 0;
    }
    .modal__footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
</style>
@endpush

@push('scripts')
<script>
    let deleteFormId = null;

    function openCreateModal() {
        document.getElementById('create-modal').style.display = 'flex';
        if (typeof initSearchableSelects === 'function') {
            initSearchableSelects();
        }
    }

    function closeCreateModal() {
        document.getElementById('create-modal').style.display = 'none';
    }

    function confirmDelete(vacationId, employeeName) {
        deleteFormId = vacationId;
        document.getElementById('delete-employee-name').textContent = employeeName;
        document.getElementById('delete-modal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').style.display = 'none';
        deleteFormId = null;
    }

    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        if (deleteFormId) {
            document.getElementById('delete-form-' + deleteFormId).submit();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeDeleteModal();
        }
    });

    // Auto-open modal if there are validation errors
    @if($errors->any())
        openCreateModal();
    @endif
</script>
@endpush
@endsection
