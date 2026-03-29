@extends('layouts.app')

@section('title', 'Įgūdžiai - Humis')
@section('page-title', 'Įgūdžiai')
@section('page-subtitle', 'Technologijos ir kompetencijos naudojamos rekomendacijoms')

@section('header-actions')
    <button type="button" onclick="openSkillModal()" class="btn btn--primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Naujas įgūdis
    </button>
@endsection

@section('content')
@forelse($skills as $category => $categorySkills)
    <div class="card" style="margin-bottom: 24px;">
        <div class="card__header">
            <h2 class="card__title">{{ $category ?? 'Kita' }}</h2>
            <span class="badge badge--neutral">{{ $categorySkills->count() }}</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Pavadinimas</th>
                    <th style="width: 120px;">Darbuotojai</th>
                    <th style="width: 120px;">Projektai</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorySkills as $skill)
                <tr>
                    <td style="font-weight: 500; color: var(--text-dark);">{{ $skill->name }}</td>
                    <td><span class="badge badge--info">{{ $skill->employees_count }}</span></td>
                    <td><span class="badge badge--neutral">{{ $skill->projects_count }}</span></td>
                    <td>
                        <form action="{{ route('skills.destroy', $skill) }}" method="POST" onsubmit="return confirm('Ištrinti {{ $skill->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn--secondary btn--sm btn--icon" title="Ištrinti">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@empty
    <div class="card">
        <div class="card__body">
            <div class="empty-state">
                <div class="empty-state__icon">⭐</div>
                <div class="empty-state__title">Nėra įgūdžių</div>
                <p class="empty-state__text">Pridėkite technologijas kurias naudoja jūsų komanda</p>
                <button type="button" onclick="openSkillModal()" class="btn btn--primary" style="margin-top: 20px;">
                    Naujas įgūdis
                </button>
            </div>
        </div>
    </div>
@endforelse

<div id="skill-modal" class="modal" style="display: none;">
    <div class="modal__backdrop" onclick="closeSkillModal()"></div>
    <div class="modal__content" style="max-width: 400px;">
        <div class="modal__header">
            <h3 class="modal__title">Naujas įgūdis</h3>
            <button type="button" class="modal__close" onclick="closeSkillModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('skills.store') }}" method="POST">
            @csrf
            <div class="modal__body">
                @if($errors->any())
                    <div class="alert alert--danger" style="margin-bottom: 16px;">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Pavadinimas *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="pvz. PHP, Laravel, React" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Kategorija</label>
                    <input type="text" name="category" value="{{ old('category') }}" class="form-input" list="categories" placeholder="pvz. Backend, Frontend">
                    <datalist id="categories">
                        @foreach($existingCategories ?? [] as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                        <option value="Backend">
                        <option value="Frontend">
                        <option value="DevOps">
                        <option value="Design">
                    </datalist>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeSkillModal()">Atšaukti</button>
                <button type="submit" class="btn btn--primary">Išsaugoti</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .modal__close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-muted);
        padding: 4px;
        border-radius: 4px;
    }
    .modal__close:hover {
        background: var(--bg-body);
        color: var(--text-primary);
    }
</style>
@endpush

@push('scripts')
<script>
    function openSkillModal() {
        document.getElementById('skill-modal').style.display = 'flex';
        document.querySelector('#skill-modal input[name="name"]').focus();
    }

    function closeSkillModal() {
        document.getElementById('skill-modal').style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSkillModal();
    });

    @if($errors->any())
        openSkillModal();
    @endif
</script>
@endpush
@endsection
