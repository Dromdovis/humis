@extends('layouts.app')

@section('title', $project->name . ' - Projektai')
@section('page-title', $project->name)
@section('page-subtitle', $project->client_name ?? 'ClickUp projektas')

@section('content')
<div class="grid grid--2">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Tech Stack</h2>
        </div>
        <div class="card__body">
            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $project->name }}">
                <input type="hidden" name="client_name" value="{{ $project->client_name }}">
                <input type="hidden" name="clickup_list_id" value="{{ $project->clickup_list_id }}">

                @if($allSkills->count() > 0)
                <div class="form-group" style="margin-bottom: 20px;">
                    <div class="skill-tags">
                        @foreach($allSkills as $skill)
                            <label class="skill-tag {{ $project->skills->contains($skill->id) ? 'skill-tag--selected' : '' }}">
                                <input type="checkbox" name="skills[]" value="{{ $skill->id }}" {{ $project->skills->contains($skill->id) ? 'checked' : '' }}>
                                <span>{{ $skill->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @else
                <p style="color: var(--text-muted); margin-bottom: 20px;">
                    Pirmiausia sukurkite įgūdžius <a href="{{ route('skills.index') }}">Įgūdžių puslapyje</a>
                </p>
                @endif

                <button type="submit" class="btn btn--primary">Išsaugoti</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Komanda</h2>
        </div>
        <div class="card__body">
            @if($project->employees->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($project->employees as $employee)
                        <a href="{{ route('employees.show', $employee) }}" class="team-member">
                            <div class="avatar avatar--sm" style="background: {{ $employee->color ?? '#6366f1' }}">
                                {{ substr($employee->name, 0, 1) }}
                            </div>
                            <span>{{ $employee->name }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <p style="color: var(--text-muted);">Komanda bus automatiškai nustatyta pagal užduočių priskyrimus</p>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.skill-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.skill-tag {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    background: var(--bg-body);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.15s;
}
.skill-tag:hover {
    border-color: var(--accent);
}
.skill-tag input {
    display: none;
}
.skill-tag--selected {
    background: var(--accent-bg);
    border-color: var(--accent);
    color: var(--accent-hover);
}
.team-member {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--text-primary);
    transition: background 0.15s;
}
.team-member:hover {
    background: var(--bg-body);
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.skill-tag input').forEach(function(input) {
    input.addEventListener('change', function() {
        this.closest('.skill-tag').classList.toggle('skill-tag--selected', this.checked);
    });
});
</script>
@endpush
@endsection
