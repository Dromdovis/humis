@extends('layouts.app')

@section('title', 'Nustatymai - Humis')
@section('page-title', 'Nustatymai')
@section('page-subtitle', 'Sistemos konfigūracija')

@section('content')
<div class="grid grid--2">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">ClickUp integracija</h2>
        </div>
        <div class="card__body">
            @if($teams && isset($teams['teams'][0]))
                <div style="display: flex; align-items: center; gap: 16px; padding: 20px; background: var(--accent-bg); border-radius: var(--radius-lg); border: 1px solid var(--accent-light);">
                    <div style="flex: 1;">
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Workspace</div>
                        <div style="font-size: 16px; font-weight: 600; color: var(--text-dark);">{{ $teams['teams'][0]['name'] }}</div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">{{ count($teams['teams'][0]['members'] ?? []) }} komandos narių</div>
                    </div>
                    <span class="badge badge--success">Prisijungta</span>
                </div>
            @else
                <div class="alert alert--danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    ClickUp integracija nekonfigūruota
                </div>
                <p style="color: var(--text-secondary); font-size: 13px;">
                    Kreipkitės į administratorių dėl ClickUp API konfigūracijos.
                </p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Rekomendacijų variklis</h2>
        </div>
        <div class="card__body">
            <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px;">
                Kai įjungtas, perskirstymo vedlyje bus rodomi automatiniai pavaduotojų pasiūlymai su tinkamumo procentais pagal įgūdžius, darbo krūvį, prieinamumą ir projekto patirtį.
            </p>

            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: var(--bg-body); border-radius: var(--radius); border: 1px solid var(--border-color);">
                    <div>
                        <div style="font-weight: 600; color: var(--text-dark);">Automatinės rekomendacijos</div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 2px;">
                            @if($recommendationEngine === 'enabled')
                                Variklis įjungtas — pavaduotojai rekomenduojami automatiškai
                            @else
                                Variklis išjungtas — pavaduotojus renkate rankiniu būdu
                            @endif
                        </div>
                    </div>
                    <label class="toggle">
                        <input type="hidden" name="recommendation_engine" value="disabled">
                        <input type="checkbox"
                               name="recommendation_engine"
                               value="enabled"
                               {{ $recommendationEngine === 'enabled' ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <span class="toggle__slider"></span>
                    </label>
                </div>
            </form>
        </div>
    </div>
</div>

@if(config('app.humis_password'))
<div class="card" style="margin-top: 24px;">
    <div class="card__header">
        <h2 class="card__title">Išvalyti duomenis</h2>
    </div>
    <div class="card__body">
        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px;">
            Išvalyti <strong>visus</strong> Humis duomenis: darbuotojus, projektus, atostogas, įgūdžius, nustatymus, žurnalą ir Laravel naudotojus.
            <strong>ClickUp nekeičiamas</strong> — tai tik lokali kopija DB. Po išvalymo reikės vėl sinchronizuoti iš naujo workspace.
        </p>
        <button type="button" class="btn btn--danger" onclick="openResetModal()">
            Išvalyti visus duomenis
        </button>
    </div>
</div>

<div id="reset-modal" class="modal" style="display: none;">
    <div class="modal__backdrop" onclick="closeResetModal()"></div>
    <div class="modal__content" style="max-width: 460px;">
        <div class="modal__header">
            <h3 class="modal__title">Išvalyti visus duomenis</h3>
            <button type="button" class="modal__close" onclick="closeResetModal()" aria-label="Uždaryti">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('settings.reset') }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 14px; color: var(--text-dark);">
                        <input type="checkbox" name="confirm_reset" value="1" {{ old('confirm_reset') ? 'checked' : '' }} style="margin-top: 3px;">
                        Suprantu, kad duomenys bus negrįžtamai ištrinti
                    </label>
                    @error('confirm_reset')
                        <div style="color: var(--danger, #dc2626); font-size: 13px; margin-top: 6px;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="reset_password" class="form-label">Humis slaptažodis</label>
                    <input type="password" name="reset_password" id="reset_password" autocomplete="current-password" class="form-input" required>
                    @error('reset_password')
                        <div style="color: var(--danger, #dc2626); font-size: 13px; margin-top: 6px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeResetModal()">Atšaukti</button>
                <button type="submit" class="btn btn--danger">Išvalyti visus duomenis</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('styles')
<style>
.toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle__slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--border-color, #d1d5db);
    border-radius: 26px;
    transition: 0.3s;
}

.toggle__slider::before {
    content: "";
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle input:checked + .toggle__slider {
    background: var(--accent, #10b981);
}

.toggle input:checked + .toggle__slider::before {
    transform: translateX(22px);
}

.modal {
    position: fixed;
    inset: 0;
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
    max-width: 460px;
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
    margin: 0;
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

@if(config('app.humis_password'))
@push('scripts')
<script>
(function () {
    const modal = document.getElementById('reset-modal');
    if (!modal) return;

    window.openResetModal = function () {
        modal.style.display = 'flex';
        const pwd = document.getElementById('reset_password');
        if (pwd) setTimeout(function () { pwd.focus(); }, 0);
    };
    window.closeResetModal = function () {
        modal.style.display = 'none';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            window.closeResetModal();
        }
    });

    @if($errors->has('confirm_reset') || $errors->has('reset_password'))
        window.openResetModal();
    @endif
})();
</script>
@endpush
@endif

@endsection
