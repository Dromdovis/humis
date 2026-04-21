@extends('layouts.app')

@section('title', 'Projektai - Humis')
@section('page-title', 'Projektai')
@section('page-subtitle', 'Valdykite projektus ir jų tech stack')

@section('header-actions')
    <form action="{{ route('projects.sync') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn--primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            Atnaujinti iš ClickUp
        </button>
    </form>
@endsection

@section('content')
<div class="card">
    <div class="card__header">
        <h2 class="card__title">Visi projektai ({{ $projects->count() }})</h2>
    </div>
    @if($projects->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Projektas</th>
                    <th>Tech Stack</th>
                    <th style="width: 120px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                <tr style="{{ $project->skills->count() === 0 ? 'background: var(--warning-bg);' : '' }}">
                    <td>
                        <div style="font-weight: 500; color: var(--text-dark);">{{ $project->name }}</div>
                        @if($project->description)
                            <div style="font-size: 13px; color: var(--text-secondary);">{{ Str::limit($project->description, 50) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($project->skills->count() > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @foreach($project->skills->take(3) as $skill)
                                    <span class="badge badge--neutral">{{ $skill->name }}</span>
                                @endforeach
                                @if($project->skills->count() > 3)
                                    <span class="badge badge--neutral">+{{ $project->skills->count() - 3 }}</span>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--text-muted); font-size: 13px;">Nenustatyta</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('projects.show', $project) }}" class="btn btn--secondary btn--sm">
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
                <div class="empty-state__icon">📁</div>
                <div class="empty-state__title">Nėra projektų</div>
                <p class="empty-state__text">Sinchronizuokite projektus iš ClickUp</p>
                <form action="{{ route('projects.sync') }}" method="POST" style="margin-top: 20px;">
                    @csrf
                    <button type="submit" class="btn btn--primary">
                        Atnaujinti iš ClickUp
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
