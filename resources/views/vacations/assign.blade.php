@extends('layouts.app')

@section('title', 'Perskirstyti užduotis - ' . $vacation->employee->name)
@section('page-title', 'Perskirstyti užduotis')
@section('page-subtitle', $vacation->employee->name . ' atostogos: ' . $vacation->start_date->format('Y-m-d') . ' – ' . $vacation->end_date->format('Y-m-d'))

@section('content')

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
                            <th>Užduotis</th>
                            <th>Vykdytojai</th>
                            <th>Trukmė</th>
                            <th>Terminas</th>
                            <th>Prioritetas</th>
                            <th style="width: 28%;">Pavaduotojas</th>
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
                            $priorityColor = data_get($task, 'priority.color', null);
                            $recommendations = $taskRecommendations[$task['id']] ?? [];
                            $alreadyReassigned = $task['already_reassigned'] ?? false;
                            $currentSubstitutes = $task['current_substitutes'] ?? [];
                            $isProcessed = $existingAssignment?->is_processed ?? false;
                            $taskAssignees = $task['assignees'] ?? [];
                        @endphp
                        <tr class="{{ $alreadyReassigned ? 'row--reassigned' : '' }}"
                            title="{{ $alreadyReassigned ? 'Ši užduotis jau turi papildomą vykdytoją' : '' }}"
                        >
                            <td>
                                <input type="hidden" name="assignments[{{ $index }}][clickup_task_id]" value="{{ $task['id'] }}">
                                <input type="hidden" name="assignments[{{ $index }}][task_name]" value="{{ $task['name'] }}">
                                <input type="hidden" name="assignments[{{ $index }}][time_estimate_hours]" value="{{ $timeEstimate }}">
                                <input type="hidden" name="assignments[{{ $index }}][due_date]" value="{{ $dueDate }}">
                                <input type="hidden" name="assignments[{{ $index }}][start_date]" value="{{ isset($task['start_date']) ? date('Y-m-d', $task['start_date'] / 1000) : '' }}">
                                <input type="hidden" name="assignments[{{ $index }}][priority]" value="{{ $priority }}">
                                <input type="hidden" name="assignments[{{ $index }}][task_status]" value="{{ data_get($task, 'status.status', '') }}">
                                <input type="hidden" name="assignments[{{ $index }}][task_status_color]" value="{{ data_get($task, 'status.color', '') }}">
                                <input type="hidden" name="assignments[{{ $index }}][task_url]" value="{{ $task['url'] ?? 'https://app.clickup.com/t/' . $task['id'] }}">
                                <input type="hidden" name="assignments[{{ $index }}][task_tags]" value="{{ json_encode(collect($task['tags'] ?? [])->map(fn($t) => ['name' => $t['name'], 'bg' => $t['tag_bg'] ?? '#e0e0e0'])->toArray()) }}">

                                <a href="{{ $task['url'] ?? 'https://app.clickup.com/t/' . $task['id'] }}" 
                                   target="_blank" 
                                   style="font-weight: 500; color: var(--accent); text-decoration: underline;"
                                   title="Atidaryti ClickUp užduotį">{{ $task['name'] }}</a>
                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                                    @if(data_get($task, 'status.status'))
                                        @php
                                            $statusColor = data_get($task, 'status.color', '#87909e');
                                        @endphp
                                        <span class="task-status-badge" style="--status-color: {{ $statusColor }};">
                                            <span class="task-status-badge__dot"></span>
                                            {{ strtoupper(data_get($task, 'status.status')) }}
                                        </span>
                                    @endif
                                    @if(data_get($task, 'list.name'))
                                        <span style="font-size: 12px; color: var(--text-secondary);">
                                            📁 {{ data_get($task, 'list.name') }}
                                        </span>
                                    @endif
                                </div>
                                @if($alreadyReassigned)
                                    <div class="reassigned-badge">
                                        ✓ Jau priskirta: {{ implode(', ', $currentSubstitutes) }}
                                    </div>
                                @endif
                                @if($isProcessed)
                                    <div class="reassigned-badge reassigned-badge--synced">
                                        ⬆ Atnaujinta ClickUp
                                    </div>
                                @endif
                                @if(!empty($task['tags']))
                                    <div style="font-size: 11px; margin-top: 4px; display: flex; gap: 4px; flex-wrap: wrap;">
                                        @foreach($task['tags'] as $tag)
                                            <span class="task-tag" style="background: {{ $tag['tag_bg'] ?? '#e0e0e0' }};">
                                                {{ $tag['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($taskAssignees))
                                    <div class="assignees-list">
                                        @foreach(array_slice($taskAssignees, 0, 3) as $assignee)
                                            <div class="avatar avatar--xs" 
                                                 style="background: {{ $assignee['color'] ?? '#6366f1' }};"
                                                 title="{{ $assignee['username'] ?? $assignee['email'] ?? 'Unknown' }}">
                                                {{ strtoupper(substr($assignee['username'] ?? $assignee['email'] ?? '?', 0, 1)) }}
                                            </div>
                                        @endforeach
                                        @if(count($taskAssignees) > 3)
                                            <span class="assignees-more" title="{{ collect(array_slice($taskAssignees, 3))->pluck('username')->join(', ') }}">
                                                +{{ count($taskAssignees) - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($timeEstimate)
                                    <span class="time-estimate-badge">{{ $timeEstimate }}h</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 12px;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($dueDate)
                                    @php
                                        $daysLeft = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($dueDate)->startOfDay(), false);
                                    @endphp
                                    <span class="{{ $daysLeft < 0 ? 'badge badge--danger' : (strtotime($dueDate) < strtotime($vacation->end_date) ? 'badge badge--danger' : '') }}" style="white-space: nowrap;">
                                        {{ $dueDate }}
                                    </span>
                                    <div style="font-size: 11px; margin-top: 2px; color: {{ $daysLeft < 0 ? '#dc2626' : ($daysLeft <= 3 ? '#d97706' : 'var(--text-muted)') }};">
                                        @if($daysLeft < 0)
                                            Vėluoja {{ abs($daysLeft) }} d.
                                        @elseif($daysLeft === 0)
                                            Šiandien
                                        @else
                                            Liko {{ $daysLeft }} {{ $daysLeft === 1 ? 'diena' : ($daysLeft < 10 && $daysLeft % 10 >= 2 ? 'dienos' : 'dienų') }}
                                        @endif
                                    </div>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($priority)
                                    @php
                                        $priorityColors = ['urgent' => '#f50000', 'high' => '#ffcc00', 'normal' => '#6fddff', 'low' => '#d8d8d8'];
                                        $pColor = $priorityColor ?? ($priorityColors[$priority] ?? '#d8d8d8');
                                    @endphp
                                    <span class="priority-badge" style="--priority-color: {{ $pColor }};">
                                        <span class="priority-badge__flag">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $pColor }}" stroke="{{ $pColor }}" stroke-width="2">
                                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                                <line x1="4" y1="22" x2="4" y2="15"></line>
                                            </svg>
                                        </span>
                                        {{ ucfirst($priority) }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td style="position: relative;">
                                <div class="recommendation-select-wrapper">
                                    @if($recommendationEnabled)
                                        @php
                                            $recOptions = collect($recommendations)->map(fn($r) => [
                                                'id' => $r['id'],
                                                'name' => $r['name'] . ' — ' . $r['score'] . '%',
                                                'color' => $r['color'] ?? '#10b981',
                                            ])->toArray();
                                        @endphp
                                        @include('components.searchable-select', [
                                            'name' => "assignments[{$index}][substitute_id]",
                                            'options' => $recOptions,
                                            'value' => $existingAssignment?->substitute_id,
                                            'placeholder' => '-- Pasirinkti pavaduotoją --',
                                            'searchPlaceholder' => 'Ieškoti darbuotojo...',
                                            'showAvatar' => true,
                                        ])
                                        
                                        <button type="button" 
                                                class="info-btn" 
                                                id="info-btn-{{ $task['id'] }}"
                                                onclick="showRecommendationDetails('{{ $task['id'] }}')"
                                                title="Rodyti rekomendacijos detales"
                                                data-recommendations='@json($recommendations)'>
                                            ℹ️
                                        </button>
                                    @else
                                        @include('components.searchable-select', [
                                            'name' => "assignments[{$index}][substitute_id]",
                                            'options' => $employees->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'color' => $e->color ?? '#10b981'])->toArray(),
                                            'value' => $existingAssignment?->substitute_id,
                                            'placeholder' => '-- Pasirinkti pavaduotoją --',
                                            'searchPlaceholder' => 'Ieškoti darbuotojo...',
                                            'showAvatar' => true,
                                        ])
                                    @endif
                                </div>
                                
                                @if($recommendationEnabled)
                                <div class="recommendation-details-popup" id="details-popup-{{ $task['id'] }}" style="display: none;">
                                    <div class="popup-header">
                                        <span class="popup-title">Kodėl rekomenduojamas?</span>
                                        <button type="button" class="popup-close" onclick="hideRecommendationDetails('{{ $task['id'] }}')">&times;</button>
                                    </div>
                                    <div class="popup-body" id="details-content-{{ $task['id'] }}"></div>
                                </div>
                                @endif
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
                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn--primary">
                            Vykdyti perskirstymą
                        </button>
                        <a href="{{ route('vacations.index') }}" class="btn btn--secondary">
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
.row--reassigned {
    background: #f0fdf4;
}

.row--reassigned td {
    border-left: 3px solid #059669;
}

.row--reassigned td:first-child {
    border-left-width: 3px;
}

.row--reassigned td:not(:first-child) {
    border-left: none;
}

.reassigned-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 500;
    color: #065f46;
    background: #d1fae5;
    padding: 2px 8px;
    border-radius: 4px;
    margin-top: 4px;
}

.reassigned-badge--synced {
    color: #1e40af;
    background: #dbeafe;
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
    font-weight: 500;
    text-shadow: 0 0 2px rgba(0,0,0,0.3);
}

.assignees-list {
    display: flex;
    align-items: center;
}

.assignees-list .avatar--xs {
    width: 26px;
    height: 26px;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    border: 2px solid var(--bg-white);
    margin-left: -6px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.assignees-list .avatar--xs:first-child {
    margin-left: 0;
}

.assignees-more {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-secondary);
    background: var(--bg-secondary);
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 4px;
    cursor: help;
}

.time-estimate-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    background: #f1f5f9;
    padding: 3px 10px;
    border-radius: 6px;
    white-space: nowrap;
}

.priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-dark);
    white-space: nowrap;
}

.priority-badge__flag {
    display: flex;
    align-items: center;
}

.recommendation-select-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.recommendation-select-wrapper .searchable-select {
    flex: 1;
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
const recommendationEnabled = {{ $recommendationEnabled ? 'true' : 'false' }};

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.recommendation-details-popup') && 
            !e.target.closest('.info-btn')) {
            document.querySelectorAll('.recommendation-details-popup').forEach(function(popup) {
                popup.style.display = 'none';
            });
        }
    });
});

function showRecommendationDetails(taskId) {
    const infoBtn = document.getElementById('info-btn-' + taskId);
    const popup = document.getElementById('details-popup-' + taskId);
    const content = document.getElementById('details-content-' + taskId);
    
    if (!infoBtn || !popup || !content) return;

    const wrapper = infoBtn.closest('.recommendation-select-wrapper');
    const hiddenInput = wrapper ? wrapper.querySelector('input[type="hidden"]') : null;
    const selectedId = hiddenInput ? parseInt(hiddenInput.value) : null;

    if (!selectedId) return;

    let recommendations = [];
    try {
        recommendations = JSON.parse(infoBtn.dataset.recommendations || '[]');
    } catch(e) {}

    const rec = recommendations.find(r => r.id === selectedId);

    if (!rec) {
        content.innerHTML = '<p style="color: var(--text-secondary);">Detalės neprieinamos</p>';
        popup.style.display = 'block';
        return;
    }

    let html = `<div style="font-weight: 600; font-size: 16px; margin-bottom: 12px;">${rec.name}</div>`;
    
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
    
    popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
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
    const ssWrapper = row.querySelector('.searchable-select');
    
    if (ssWrapper) {
        const trigger = ssWrapper.querySelector('.searchable-select__trigger');
        if (trigger) trigger.disabled = checkbox.checked;
        
        if (checkbox.checked) {
            const hiddenInput = ssWrapper.querySelector('input[type="hidden"]');
            if (hiddenInput) hiddenInput.value = '';
            const valueDisplay = ssWrapper.querySelector('.searchable-select__value');
            if (valueDisplay) {
                const placeholder = ssWrapper.querySelector('.searchable-select__trigger').dataset.placeholder || '-- Pasirinkti pavaduotoją --';
                valueDisplay.innerHTML = `<span class="searchable-select__placeholder">${placeholder}</span>`;
            }
            ssWrapper.querySelectorAll('.searchable-select__option--selected').forEach(
                opt => opt.classList.remove('searchable-select__option--selected')
            );
        }
    }
}

</script>
@endsection
