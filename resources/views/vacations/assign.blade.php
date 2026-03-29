@extends('layouts.app')

@section('title', 'Perskirstyti užduotis - ' . $vacation->employee->name)

@section('content')
<div class="main__header">
    <h1 class="main__title">Perskirstyti užduotis</h1>
    <p class="main__subtitle">
        {{ $vacation->employee->name }} atostogos: 
        {{ $vacation->start_date->format('Y-m-d') }} - {{ $vacation->end_date->format('Y-m-d') }}
    </p>
</div>

@if($vacation->defaultSubstitute)
<div class="alert alert--info">
    <strong>BSS nurodė pavaduotoją:</strong> {{ $vacation->defaultSubstitute->name }}
    <span style="margin-left: 8px;">— galite naudoti kaip numatytąjį arba pasirinkti kitą kiekvienai užduočiai</span>
</div>
@endif

{{-- Patvirtinimo modalas atostogaujantiems --}}
<div id="vacation-confirm-modal" class="confirm-modal" style="display: none;">
    <div class="confirm-modal__backdrop"></div>
    <div class="confirm-modal__content">
        <div class="confirm-modal__icon">⚠️</div>
        <h3 class="confirm-modal__title">Darbuotojas atostogauja</h3>
        <p class="confirm-modal__message" id="vacation-confirm-message"></p>
        <div class="confirm-modal__actions">
            <button type="button" class="btn btn--secondary" onclick="cancelVacationConfirm()">
                Atšaukti
            </button>
            <button type="button" class="btn btn--warning" onclick="proceedWithVacationAssignment()">
                Suprantu, priskirti
            </button>
        </div>
    </div>
</div>

{{-- Rekomendacijų legenda --}}
<div class="card" style="margin-bottom: 16px;">
    <div class="card__body" style="padding: 12px 16px;">
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <span style="font-weight: 500; color: var(--text-secondary);">Tinkamumo įvertinimas:</span>
            <span class="badge badge--success">80-100% Puikiai tinka</span>
            <span class="badge badge--info">60-79% Gerai tinka</span>
            <span class="badge badge--warning">40-59% Tinka</span>
            <span class="badge badge--neutral">0-39% Mažai tinka</span>
        </div>
    </div>
</div>

<form action="{{ route('vacations.assign.save', $vacation) }}" method="POST">
    @csrf

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">
                {{ $vacation->employee->name }} aktyvios užduotys ({{ count($tasks) }})
            </h2>
        </div>
        <div class="card__body" style="padding: 0;">
            @if(count($tasks) > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Užduotis</th>
                            <th>Terminas</th>
                            <th>Prioritetas</th>
                            <th style="width: 35%;">Pavaduotojas</th>
                            <th>Praleisti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $index => $task)
                        @php
                            $existingAssignment = $vacation->taskAssignments->firstWhere('clickup_task_id', $task['id']);
                            $timeEstimate = isset($task['time_estimate']) ? round($task['time_estimate'] / 3600000, 1) : null;
                            $dueDate = isset($task['due_date']) ? date('Y-m-d', $task['due_date'] / 1000) : null;
                            $priority = $task['priority']['priority'] ?? null;
                            $recommendations = $taskRecommendations[$task['id']] ?? [];
                        @endphp
                        <tr>
                            <td>
                                <input type="hidden" name="assignments[{{ $index }}][clickup_task_id]" value="{{ $task['id'] }}">
                                <input type="hidden" name="assignments[{{ $index }}][task_name]" value="{{ $task['name'] }}">
                                <input type="hidden" name="assignments[{{ $index }}][time_estimate_hours]" value="{{ $timeEstimate }}">
                                <input type="hidden" name="assignments[{{ $index }}][due_date]" value="{{ $dueDate }}">
                                <input type="hidden" name="assignments[{{ $index }}][priority]" value="{{ $priority }}">

                                <div style="font-weight: 500;">{{ $task['name'] }}</div>
                                @if($task['list']['name'] ?? null)
                                    <div style="font-size: 12px; color: var(--text-secondary);">
                                        📁 {{ $task['list']['name'] }}
                                    </div>
                                @endif
                                @if(!empty($task['tags']))
                                    <div style="font-size: 11px; margin-top: 4px; display: flex; gap: 4px; flex-wrap: wrap;">
                                        @foreach($task['tags'] as $tag)
                                            <span style="background: {{ $tag['tag_bg'] ?? '#e0e0e0' }}; color: {{ $tag['tag_fg'] ?? '#333' }}; padding: 1px 6px; border-radius: 3px;">
                                                {{ $tag['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($timeEstimate)
                                    <div style="font-size: 12px; color: var(--text-muted);">
                                        ⏱️ ~{{ $timeEstimate }}h
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($dueDate)
                                    <span class="{{ strtotime($dueDate) < strtotime($vacation->end_date) ? 'badge badge--danger' : '' }}">
                                        {{ $dueDate }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($priority)
                                    @switch($priority)
                                        @case('urgent')
                                            <span class="badge badge--danger">Skubu</span>
                                            @break
                                        @case('high')
                                            <span class="badge badge--warning">Aukštas</span>
                                            @break
                                        @case('normal')
                                            <span class="badge badge--info">Normalus</span>
                                            @break
                                        @default
                                            <span class="badge badge--neutral">{{ $priority }}</span>
                                    @endswitch
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td style="position: relative;">
                                <div class="recommendation-select-wrapper">
                                    <select name="assignments[{{ $index }}][substitute_id]" 
                                            class="form-input form-select recommendation-select" 
                                            data-task-id="{{ $task['id'] }}"
                                            data-recommendations='@json($recommendations)'>
                                        <option value="">-- Pasirinkti pavaduotoją --</option>
                                        
                                        {{-- BSS numatytas pavaduotojas viršuje --}}
                                        @if($vacation->defaultSubstitute)
                                            @php
                                                $bssRec = collect($recommendations)->firstWhere('id', $vacation->defaultSubstitute->id);
                                                $bssScore = $bssRec['score'] ?? 50;
                                            @endphp
                                            <option value="{{ $vacation->defaultSubstitute->id }}" 
                                                    data-score="{{ $bssScore }}"
                                                    data-color="{{ $vacation->defaultSubstitute->color ?? '#6366f1' }}"
                                                    {{ !$existingAssignment && !$existingAssignment?->substitute_id ? 'selected' : '' }}>
                                                ⭐ {{ $vacation->defaultSubstitute->name }} (BSS) — {{ $bssScore }}%
                                            </option>
                                        @endif

                                        {{-- Rekomenduojami darbuotojai surikiuoti pagal balą --}}
                                        @foreach($recommendations as $rec)
                                            @if(!$vacation->defaultSubstitute || $rec['id'] !== $vacation->defaultSubstitute->id)
                                                @php
                                                    $scoreClass = '';
                                                    if ($rec['score'] >= 80) $scoreClass = 'score-excellent';
                                                    elseif ($rec['score'] >= 60) $scoreClass = 'score-good';
                                                    elseif ($rec['score'] >= 40) $scoreClass = 'score-average';
                                                    else $scoreClass = 'score-low';
                                                @endphp
                                                <option value="{{ $rec['id'] }}" 
                                                        data-score="{{ $rec['score'] }}"
                                                        data-color="{{ $rec['color'] }}"
                                                        data-details="{{ $rec['details'] }}"
                                                        class="{{ $scoreClass }}"
                                                        {{ $existingAssignment?->substitute_id == $rec['id'] ? 'selected' : '' }}>
                                                    {{ $rec['name'] }} — {{ $rec['score'] }}%
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    
                                    {{-- Balo indikatorius --}}
                                    <div class="score-indicator" id="score-{{ $task['id'] }}"></div>
                                    
                                    {{-- Info mygtukas detalėms --}}
                                    <button type="button" 
                                            class="info-btn" 
                                            id="info-btn-{{ $task['id'] }}"
                                            onclick="showRecommendationDetails('{{ $task['id'] }}')"
                                            title="Rodyti rekomendacijos detales"
                                            style="display: none;">
                                        ℹ️
                                    </button>
                                </div>
                                
                                {{-- Detalių popup --}}
                                <div class="recommendation-details-popup" id="details-popup-{{ $task['id'] }}" style="display: none;">
                                    <div class="popup-header">
                                        <span class="popup-title">Kodėl rekomenduojamas?</span>
                                        <button type="button" class="popup-close" onclick="hideRecommendationDetails('{{ $task['id'] }}')">&times;</button>
                                    </div>
                                    <div class="popup-body" id="details-content-{{ $task['id'] }}"></div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" 
                                           name="assignments[{{ $index }}][is_excluded]" 
                                           value="1"
                                           {{ $existingAssignment?->is_excluded ? 'checked' : '' }}
                                           onchange="toggleExclude(this)">
                                    <span style="font-size: 13px;">Praleisti</span>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="form-footer" style="padding: 20px; border-top: 1px solid var(--border-color); background: var(--bg-body);">
                    <div class="schedule-options" style="margin-bottom: 16px;">
                        <div style="font-weight: 600; margin-bottom: 12px;">Kada vykdyti priskyrimą ClickUp?</div>
                        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                            <label class="schedule-option" style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; padding: 12px 16px; border: 2px solid var(--border-color); border-radius: 8px; flex: 1; min-width: 200px;">
                                <input type="radio" name="schedule_type" value="now" checked style="margin-top: 3px;">
                                <div>
                                    <div style="font-weight: 500;">Išsaugoti ir vėliau vykdyti</div>
                                    <div style="font-size: 13px; color: var(--text-secondary);">Priskyrimai bus išsaugoti, bet nevykdomi ClickUp</div>
                                </div>
                            </label>
                            <label class="schedule-option" style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; padding: 12px 16px; border: 2px solid var(--border-color); border-radius: 8px; flex: 1; min-width: 200px;">
                                <input type="radio" name="schedule_type" value="scheduled" style="margin-top: 3px;">
                                <div>
                                    <div style="font-weight: 500;">Suplanuoti konkrečiai datai</div>
                                    <div style="font-size: 13px; color: var(--text-secondary);">Priskyrimai bus vykdomi nurodytą dieną</div>
                                    <input type="date" 
                                           name="scheduled_date" 
                                           class="form-input" 
                                           style="margin-top: 8px; width: 100%;" 
                                           min="{{ date('Y-m-d') }}"
                                           max="{{ $vacation->start_date->format('Y-m-d') }}"
                                           value="{{ $vacation->start_date->subDays(1)->format('Y-m-d') }}"
                                           disabled>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn--primary">
                            💾 Išsaugoti paskirstymą
                        </button>
                        <a href="{{ route('vacations.show', $vacation) }}" class="btn btn--secondary">
                            Atšaukti
                        </a>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state__icon">✅</div>
                    <div class="empty-state__title">Nėra aktyvių užduočių</div>
                    <p>Šis darbuotojas neturi priskirtų užduočių ClickUp sistemoje</p>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 8px;">
                        Patikrinkite ar CLICKUP_TEAM_ID teisingai nustatytas .env faile
                    </p>
                </div>
            @endif
        </div>
    </div>
</form>

<style>
.recommendation-select-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.recommendation-select {
    flex: 1;
    padding-right: 50px;
    font-size: 13px;
}

.score-indicator {
    position: absolute;
    right: 45px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    pointer-events: none;
}

.score-indicator.excellent {
    background: var(--color-success-light, #d1fae5);
    color: var(--color-success, #059669);
}

.score-indicator.good {
    background: var(--color-info-light, #dbeafe);
    color: var(--color-info, #2563eb);
}

.score-indicator.average {
    background: var(--color-warning-light, #fef3c7);
    color: var(--color-warning, #d97706);
}

.score-indicator.low {
    background: var(--color-neutral-light, #f3f4f6);
    color: var(--color-neutral, #6b7280);
}

.info-btn {
    width: 28px;
    height: 28px;
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 50%;
    background: white;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.info-btn:hover {
    background: var(--color-primary-light, #e0e7ff);
    border-color: var(--color-primary, #4f46e5);
}

.recommendation-details-popup {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 8px;
    background: white;
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    min-width: 320px;
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    background: var(--bg-secondary, #f9fafb);
    border-radius: 8px 8px 0 0;
}

.popup-title {
    font-weight: 600;
    color: var(--text-primary, #111827);
}

.popup-close {
    width: 24px;
    height: 24px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 18px;
    color: var(--text-secondary, #6b7280);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
}

.popup-close:hover {
    background: var(--color-danger-light, #fee2e2);
    color: var(--color-danger, #dc2626);
}

.popup-body {
    padding: 16px;
}

.breakdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color-light, #f3f4f6);
}

.breakdown-item:last-child {
    border-bottom: none;
}

.breakdown-icon {
    font-size: 18px;
    width: 24px;
    text-align: center;
}

.breakdown-info {
    flex: 1;
}

.breakdown-label {
    font-weight: 500;
    font-size: 13px;
    color: var(--text-primary, #111827);
}

.breakdown-details {
    font-size: 12px;
    color: var(--text-secondary, #6b7280);
    margin-top: 2px;
}

.breakdown-score {
    text-align: right;
    min-width: 60px;
}

.breakdown-score-value {
    font-weight: 700;
    font-size: 14px;
}

.breakdown-score-max {
    font-size: 11px;
    color: var(--text-muted, #9ca3af);
}

.breakdown-progress {
    width: 100%;
    height: 4px;
    background: var(--border-color-light, #f3f4f6);
    border-radius: 2px;
    margin-top: 4px;
    overflow: hidden;
}

.breakdown-progress-bar {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.breakdown-progress-bar.excellent { background: var(--color-success, #059669); }
.breakdown-progress-bar.good { background: var(--color-info, #2563eb); }
.breakdown-progress-bar.average { background: var(--color-warning, #d97706); }
.breakdown-progress-bar.low { background: var(--color-neutral, #6b7280); }

.total-score {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 2px solid var(--border-color, #e5e7eb);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-score-label {
    font-weight: 600;
    font-size: 14px;
}

.total-score-value {
    font-size: 24px;
    font-weight: 700;
}

.total-score-value.excellent { color: var(--color-success, #059669); }
.total-score-value.good { color: var(--color-info, #2563eb); }
.total-score-value.average { color: var(--color-warning, #d97706); }
.total-score-value.low { color: var(--color-neutral, #6b7280); }

.recommendation-select option {
    padding: 8px;
}

.recommendation-select option[data-score] {
    font-weight: 500;
}

.recommendation-select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.badge--success {
    background: var(--color-success-light, #d1fae5);
    color: var(--color-success, #059669);
}

.badge--info {
    background: var(--color-info-light, #dbeafe);
    color: var(--color-info, #2563eb);
}

.badge--warning {
    background: var(--color-warning-light, #fef3c7);
    color: var(--color-warning, #d97706);
}

.badge--neutral {
    background: var(--color-neutral-light, #f3f4f6);
    color: var(--color-neutral, #6b7280);
}

.confirm-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.confirm-modal__backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.2s ease;
}

.confirm-modal__content {
    position: relative;
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
}

.confirm-modal__icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.confirm-modal__title {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin: 0 0 12px 0;
}

.confirm-modal__message {
    color: var(--text-secondary, #6b7280);
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 24px 0;
}

.confirm-modal__message strong {
    color: var(--text-primary, #111827);
    display: block;
    margin-bottom: 8px;
    font-size: 15px;
}

.confirm-modal__actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.confirm-modal__actions .btn {
    min-width: 120px;
}

.btn--warning {
    background: var(--color-warning, #f59e0b);
    color: white;
    border: none;
}

.btn--warning:hover {
    background: #d97706;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
let recommendationsData = {};
let previousSelectValues = {};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.recommendation-select').forEach(function(select) {
        const taskId = select.dataset.taskId;

        try {
            recommendationsData[taskId] = JSON.parse(select.dataset.recommendations || '[]');
        } catch(e) {
            recommendationsData[taskId] = [];
        }

        previousSelectValues[taskId] = select.value;
        
        updateScoreIndicator(select);
        
        select.addEventListener('change', function() {
            handleSelectChange(this, taskId);
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.recommendation-details-popup') && 
            !e.target.closest('.info-btn')) {
            document.querySelectorAll('.recommendation-details-popup').forEach(function(popup) {
                popup.style.display = 'none';
            });
        }
    });
});

let pendingVacationConfirm = null;

function handleSelectChange(select, taskId) {
    const selectedId = parseInt(select.value);
    
    if (!selectedId) {
        updateScoreIndicator(select);
        hideRecommendationDetails(taskId);
        previousSelectValues[taskId] = select.value;
        return;
    }

    const recommendations = recommendationsData[taskId] || [];
    const rec = recommendations.find(r => r.id === selectedId);

    // Patikrinti ar pasirinktas asmuo atostogauja (availability score = 0)
    if (rec && rec.breakdown && rec.breakdown.availability && rec.breakdown.availability.score === 0) {
        const employeeName = rec.name;
        const availabilityDetails = rec.breakdown.availability.details || 'atostogauja tuo pačiu laikotarpiu';

        pendingVacationConfirm = {
            select: select,
            taskId: taskId,
            value: select.value
        };

        showVacationConfirmModal(employeeName, availabilityDetails);
        return;
    }

    previousSelectValues[taskId] = select.value;
    updateScoreIndicator(select);
    hideRecommendationDetails(taskId);
}

function showVacationConfirmModal(employeeName, details) {
    const modal = document.getElementById('vacation-confirm-modal');
    const message = document.getElementById('vacation-confirm-message');

    const cleanDetails = details.replace('⚠️ ', '');
    
    message.innerHTML = `
        <strong>${employeeName}</strong>
        ${cleanDetails}
        <br><br>
        Ar tikrai norite priskirti šį darbuotoją pavaduoti?
    `;
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function cancelVacationConfirm() {
    const modal = document.getElementById('vacation-confirm-modal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    
    if (pendingVacationConfirm) {
        const { select, taskId } = pendingVacationConfirm;
        select.value = previousSelectValues[taskId] || '';
        updateScoreIndicator(select);
        pendingVacationConfirm = null;
    }
}

function proceedWithVacationAssignment() {
    const modal = document.getElementById('vacation-confirm-modal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    
    if (pendingVacationConfirm) {
        const { select, taskId, value } = pendingVacationConfirm;
        previousSelectValues[taskId] = value;
        updateScoreIndicator(select);
        hideRecommendationDetails(taskId);
        pendingVacationConfirm = null;
    }
}

function updateScoreIndicator(select) {
    const taskId = select.dataset.taskId;
    const indicator = document.getElementById('score-' + taskId);
    const infoBtn = document.getElementById('info-btn-' + taskId);
    const selectedOption = select.options[select.selectedIndex];
    
    if (!indicator) return;
    
    if (selectedOption && selectedOption.value && selectedOption.dataset.score) {
        const score = parseInt(selectedOption.dataset.score);
        indicator.textContent = score + '%';
        indicator.style.display = 'block';

        if (infoBtn) infoBtn.style.display = 'flex';

        indicator.className = 'score-indicator';
        if (score >= 80) {
            indicator.classList.add('excellent');
        } else if (score >= 60) {
            indicator.classList.add('good');
        } else if (score >= 40) {
            indicator.classList.add('average');
        } else {
            indicator.classList.add('low');
        }
    } else {
        indicator.style.display = 'none';
        if (infoBtn) infoBtn.style.display = 'none';
    }
}

function showRecommendationDetails(taskId) {
    const select = document.querySelector(`.recommendation-select[data-task-id="${taskId}"]`);
    const popup = document.getElementById('details-popup-' + taskId);
    const content = document.getElementById('details-content-' + taskId);
    
    if (!select || !popup || !content) return;
    
    const selectedId = parseInt(select.value);
    if (!selectedId) return;

    const recommendations = recommendationsData[taskId] || [];
    const rec = recommendations.find(r => r.id === selectedId);

    if (!rec) {
        content.innerHTML = '<p style="color: var(--text-secondary);">Detalės neprieinamos</p>';
        popup.style.display = 'block';
        return;
    }

    let html = `<div class="employee-name" style="font-weight: 600; font-size: 16px; margin-bottom: 12px;">${rec.name}</div>`;
    
    if (rec.breakdown) {
        const categories = ['skills', 'workload', 'availability', 'project'];
        
        categories.forEach(function(cat) {
            const item = rec.breakdown[cat];
            if (!item) return;
            
            const percentage = Math.round((item.score / item.max) * 100);
            const scoreClass = getScoreClass(percentage);
            
            html += `
                <div class="breakdown-item">
                    <span class="breakdown-icon">${item.icon}</span>
                    <div class="breakdown-info">
                        <div class="breakdown-label">${item.label}</div>
                        <div class="breakdown-details">${item.details}</div>
                        <div class="breakdown-progress">
                            <div class="breakdown-progress-bar ${scoreClass}" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                    <div class="breakdown-score">
                        <div class="breakdown-score-value">${item.score}</div>
                        <div class="breakdown-score-max">iš ${item.max}</div>
                    </div>
                </div>
            `;
        });
    }

    const totalScoreClass = getScoreClass(rec.score);
    html += `
        <div class="total-score">
            <span class="total-score-label">Bendras įvertinimas</span>
            <span class="total-score-value ${totalScoreClass}">${rec.score}%</span>
        </div>
    `;
    
    content.innerHTML = html;

    document.querySelectorAll('.recommendation-details-popup').forEach(function(p) {
        if (p.id !== 'details-popup-' + taskId) {
            p.style.display = 'none';
        }
    });
    
    popup.style.display = 'block';
}

function hideRecommendationDetails(taskId) {
    const popup = document.getElementById('details-popup-' + taskId);
    if (popup) popup.style.display = 'none';
}

function getScoreClass(score) {
    if (score >= 80) return 'excellent';
    if (score >= 60) return 'good';
    if (score >= 40) return 'average';
    return 'low';
}

function toggleExclude(checkbox) {
    const row = checkbox.closest('tr');
    const select = row.querySelector('select');
    const taskId = select.dataset.taskId;
    
    select.disabled = checkbox.checked;
    
    if (checkbox.checked) {
        select.value = '';
        updateScoreIndicator(select);
        hideRecommendationDetails(taskId);
    }
}

document.querySelectorAll('input[name="schedule_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const dateInput = document.querySelector('input[name="scheduled_date"]');
        const scheduleOptions = document.querySelectorAll('.schedule-option');
        
        scheduleOptions.forEach(function(opt) {
            opt.style.borderColor = 'var(--border-color)';
        });
        
        if (this.value === 'scheduled') {
            dateInput.disabled = false;
            dateInput.required = true;
            this.closest('.schedule-option').style.borderColor = 'var(--accent)';
        } else {
            dateInput.disabled = true;
            dateInput.required = false;
            this.closest('.schedule-option').style.borderColor = 'var(--accent)';
        }
    });

    if (radio.checked) {
        radio.closest('.schedule-option').style.borderColor = 'var(--accent)';
    }
});
</script>
@endsection
