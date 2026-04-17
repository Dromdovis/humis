@extends('layouts.app')

@section('title', $employee->name . ' - Darbuotojai')
@section('page-title', $employee->name)

@section('content')
<div class="grid grid--2">

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Įgūdžiai</h2>
        </div>
        <div class="card__body">
            <form action="{{ route('employees.skills.update', $employee) }}" method="POST">
                @csrf
                @method('PUT')

                @if($allSkills->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 12px; max-height: 400px; overflow-y: auto;">
                        @foreach($allSkills->groupBy('category') as $category => $skills)
                            <div style="margin-bottom: 8px;">
                                <div style="font-weight: 600; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                                    {{ $category ?? 'Kita' }}
                                </div>
                                @foreach($skills as $skill)
                                    @php
                                        $employeeSkill = $employee->skills->firstWhere('id', $skill->id);
                                        $currentLevel = $employeeSkill ? $employeeSkill->pivot->level : 0;
                                    @endphp
                                    <div style="display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                                        <div style="flex: 1; font-weight: 500;">{{ $skill->name }}</div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <select name="skills[{{ $skill->id }}][level]" class="form-input" style="width: 120px; padding: 6px 8px; font-size: 13px;">
                                                <option value="0" {{ $currentLevel == 0 ? 'selected' : '' }}>Nenustatyta</option>
                                                <option value="1" {{ $currentLevel == 1 ? 'selected' : '' }}>1 - Pradedantis</option>
                                                <option value="2" {{ $currentLevel == 2 ? 'selected' : '' }}>2 - Bazinis</option>
                                                <option value="3" {{ $currentLevel == 3 ? 'selected' : '' }}>3 - Vidutinis</option>
                                                <option value="4" {{ $currentLevel == 4 ? 'selected' : '' }}>4 - Pažengęs</option>
                                                <option value="5" {{ $currentLevel == 5 ? 'selected' : '' }}>5 - Ekspertas</option>
                                            </select>
                                            <input type="hidden" name="skills[{{ $skill->id }}][skill_id]" value="{{ $skill->id }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn--primary" style="margin-top: 16px;">Išsaugoti įgūdžius</button>
                @else
                    <div class="empty-state">
                        <p>Pirmiausia sukurkite įgūdžius</p>
                        <a href="{{ route('skills.index') }}" class="btn btn--primary" style="margin-top: 12px;">Eiti į įgūdžius</a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card__header">
        <h2 class="card__title">Atostogų istorija</h2>
    </div>
    <div class="card__body" style="padding: 0;">
        @if($employee->vacations->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Datos</th>
                        <th>Trukmė</th>
                        <th>Pavaduotojas</th>
                        <th>Būsena</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->vacations as $vacation)
                    <tr>
                        <td>{{ $vacation->start_date->format('Y-m-d') }} - {{ $vacation->end_date->format('Y-m-d') }}</td>
                        <td>{{ $vacation->duration_days }} d.</td>
                        <td>{{ $vacation->defaultSubstitute->name ?? '-' }}</td>
                        <td>
                            @if($vacation->tasks_reassigned)
                                <span class="badge badge--success">Priskirta</span>
                            @else
                                <span class="badge badge--warning">Nepriskirta</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p>Šis darbuotojas dar nėra turėjęs atostogų</p>
            </div>
        @endif
    </div>
</div>
@endsection
