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
    <div class="modal__content" style="max-width: 440px;">
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
                    <label class="form-label">Technologijos pavadinimas *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="pvz. PHP, Laravel, React" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Kategorija</label>
                    <input type="hidden" name="category" value="{{ old('category') }}">
                    <div class="creatable-select" id="category-select">
                        <button type="button" class="creatable-select__trigger form-input" aria-haspopup="listbox">
                            <span class="creatable-select__value">
                                @if(old('category'))
                                    {{ old('category') }}
                                @else
                                    <span class="creatable-select__placeholder">Pasirinkite arba sukurkite...</span>
                                @endif
                            </span>
                            <svg class="creatable-select__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="creatable-select__dropdown">
                            <div class="creatable-select__search-wrapper">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <input type="text" class="creatable-select__search" placeholder="Ieškoti arba rašyti naują..." autocomplete="off">
                            </div>
                            <ul class="creatable-select__options" role="listbox">
                                @php
                                    $allCategories = collect($existingCategories ?? [])->merge(['Backend', 'Frontend', 'DevOps', 'Design'])->unique()->sort()->values();
                                @endphp
                                @foreach($allCategories as $cat)
                                    <li class="creatable-select__option" data-value="{{ $cat }}" role="option">{{ $cat }}</li>
                                @endforeach
                            </ul>
                            <div class="creatable-select__create" style="display: none;">
                                <span class="creatable-select__create-label">Sukurti:</span>
                                <span class="creatable-select__create-value"></span>
                            </div>
                            <div class="creatable-select__empty" style="display: none;">Nieko nerasta</div>
                        </div>
                    </div>
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
        background: rgba(0, 0, 0, 0.5);
    }
    .modal__content {
        position: relative;
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
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

    .creatable-select {
        position: relative;
    }
    .creatable-select__trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        text-align: left;
        width: 100%;
        background: var(--bg-white);
    }
    .creatable-select__placeholder {
        color: var(--text-muted);
    }
    .creatable-select__arrow {
        flex-shrink: 0;
        color: var(--text-muted);
        transition: transform 0.2s;
    }
    .creatable-select.is-open .creatable-select__arrow {
        transform: rotate(180deg);
    }
    .creatable-select__dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        z-index: 100;
        max-height: 260px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .creatable-select:not(.is-open) .creatable-select__dropdown {
        display: none;
    }
    .creatable-select.is-open .creatable-select__dropdown {
        display: flex;
    }
    .creatable-select__search-wrapper {
        position: relative;
        padding: 8px;
        border-bottom: 1px solid var(--border-color);
    }
    .creatable-select__search {
        width: 100%;
        padding: 8px 8px 8px 32px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        font-size: 14px;
        outline: none;
    }
    .creatable-select__search:focus {
        border-color: var(--color-primary);
    }
    .creatable-select__options {
        list-style: none;
        margin: 0;
        padding: 4px;
        overflow-y: auto;
        flex: 1;
    }
    .creatable-select__option {
        padding: 8px 12px;
        cursor: pointer;
        border-radius: var(--radius-sm);
        font-size: 14px;
        transition: background 0.1s;
    }
    .creatable-select__option:hover {
        background: var(--bg-body);
    }
    .creatable-select__option--selected {
        background: #e8f5e9;
        font-weight: 500;
    }
    .creatable-select__option--hidden {
        display: none;
    }
    .creatable-select__create {
        padding: 8px 12px;
        margin: 4px 4px;
        cursor: pointer;
        border-radius: var(--radius-sm);
        font-size: 14px;
        color: var(--color-primary);
        font-weight: 500;
        border-top: 1px solid var(--border-color);
    }
    .creatable-select__create:hover {
        background: #e8f5e9;
    }
    .creatable-select__create-label {
        margin-right: 4px;
    }
    .creatable-select__create-value {
        font-style: italic;
    }
    .creatable-select__empty {
        padding: 12px 16px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
    }
</style>
@endpush

@push('scripts')
<script>
    function openSkillModal() {
        document.getElementById('skill-modal').style.display = 'flex';
        setTimeout(function() {
            document.querySelector('#skill-modal input[name="name"]').focus();
        }, 50);
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

    (function() {
        const container = document.getElementById('category-select');
        if (!container) return;

        const trigger = container.querySelector('.creatable-select__trigger');
        const valueDisplay = container.querySelector('.creatable-select__value');
        const hiddenInput = container.closest('form').querySelector('input[name="category"]');
        const searchInput = container.querySelector('.creatable-select__search');
        const optionsList = container.querySelector('.creatable-select__options');
        const options = container.querySelectorAll('.creatable-select__option');
        const createBtn = container.querySelector('.creatable-select__create');
        const createValue = container.querySelector('.creatable-select__create-value');
        const emptyState = container.querySelector('.creatable-select__empty');

        const existingValues = Array.from(options).map(o => o.dataset.value.toLowerCase());

        trigger.addEventListener('click', function() {
            container.classList.toggle('is-open');
            if (container.classList.contains('is-open')) {
                searchInput.value = '';
                filterOptions('');
                setTimeout(() => searchInput.focus(), 50);
            }
        });

        function selectValue(val) {
            hiddenInput.value = val;
            valueDisplay.innerHTML = val || '<span class="creatable-select__placeholder">Pasirinkite arba sukurkite...</span>';
            container.classList.remove('is-open');

            options.forEach(o => {
                o.classList.toggle('creatable-select__option--selected', o.dataset.value === val);
            });
        }

        options.forEach(function(option) {
            option.addEventListener('click', function() {
                selectValue(this.dataset.value);
            });
        });

        createBtn.addEventListener('click', function() {
            const newVal = searchInput.value.trim();
            if (newVal) selectValue(newVal);
        });

        searchInput.addEventListener('input', function() {
            filterOptions(this.value.trim());
        });

        function filterOptions(query) {
            const lowerQuery = query.toLowerCase();
            let visibleCount = 0;
            let exactMatch = false;

            options.forEach(function(option) {
                const label = option.dataset.value.toLowerCase();
                const matches = !query || label.includes(lowerQuery);
                option.classList.toggle('creatable-select__option--hidden', !matches);
                if (matches) visibleCount++;
                if (label === lowerQuery) exactMatch = true;
            });

            if (query && !exactMatch) {
                createBtn.style.display = 'block';
                createValue.textContent = '"' + query + '"';
            } else {
                createBtn.style.display = 'none';
            }

            emptyState.style.display = (visibleCount === 0 && !query) ? 'block' : 'none';
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#category-select')) {
                container.classList.remove('is-open');
            }
        });
    })();
</script>
@endpush
@endsection
