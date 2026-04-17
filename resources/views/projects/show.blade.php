@extends('layouts.app')

@section('title', $project->name . ' - Projektai')
@section('page-title', $project->name)
@section('page-subtitle', $project->client_name ?? 'ClickUp projektas')

@section('content')
@php
    $skillsByCategory = $allSkills->groupBy(fn ($s) => $s->category ?: 'Kita')->sortKeys();
@endphp
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Projekto technologijos</h2>
    </div>
    <div class="card__body">
        <form action="{{ route('projects.update', $project) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ $project->name }}">
            <input type="hidden" name="client_name" value="{{ $project->client_name }}">
            <input type="hidden" name="clickup_list_id" value="{{ $project->clickup_list_id }}">

            @if($allSkills->count() > 0)
                @foreach($skillsByCategory as $category => $categorySkills)
                    <div class="project-tech-category">
                        <h3 class="project-tech-category__title">{{ $category }}</h3>
                        <div class="skill-tags">
                            @foreach($categorySkills->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $skill)
                                <label class="skill-tag {{ $project->skills->contains($skill->id) ? 'skill-tag--selected' : '' }}">
                                    <input type="checkbox" name="skills[]" value="{{ $skill->id }}" {{ $project->skills->contains($skill->id) ? 'checked' : '' }}>
                                    <span>{{ $skill->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
            <p style="color: var(--text-muted); margin-bottom: 20px;">
                Pirmiausia sukurkite įgūdžius <a href="{{ route('skills.index') }}">Įgūdžių puslapyje</a>
            </p>
            @endif

            <button type="submit" class="btn btn--primary" style="margin-top: 8px;">Išsaugoti</button>
        </form>
    </div>
</div>

@push('styles')
<style>
.project-tech-category {
    margin-bottom: 20px;
}
.project-tech-category:last-of-type {
    margin-bottom: 0;
}
.project-tech-category__title {
    margin: 0 0 10px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--text-muted);
}
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
