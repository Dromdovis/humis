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
<div class="card" style="margin-top: 24px; border-color: var(--danger, #dc2626);">
    <div class="card__header">
        <h2 class="card__title" style="color: var(--danger, #dc2626);">Pavojinga zona</h2>
    </div>
    <div class="card__body">
        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px;">
            Išvalyti <strong>visus</strong> Humis duomenis: darbuotojus, projektus, atostogas, įgūdžius, nustatymus, žurnalą ir Laravel naudotojus.
            <strong>ClickUp nekeičiamas</strong> — tai tik lokali kopija DB. Po išvalymo reikės vėl sinchronizuoti iš naujo workspace.
        </p>
        <form action="{{ route('settings.reset') }}" method="POST" onsubmit="return confirm('Ar tikrai išvalyti visus duomenis? Šio veiksmo atšaukti negalima.');">
            @csrf
            <div style="margin-bottom: 12px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 14px; color: var(--text-dark);">
                    <input type="checkbox" name="confirm_reset" value="1" {{ old('confirm_reset') ? 'checked' : '' }} style="margin-top: 3px;">
                    Suprantu, kad duomenys bus negrįžtamai ištrinti
                </label>
                @error('confirm_reset')
                    <div style="color: var(--danger, #dc2626); font-size: 13px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </div>
            <div style="margin-bottom: 12px;">
                <label for="reset_password" style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Humis slaptažodis (kaip prisijungiant)</label>
                <input type="password" name="reset_password" id="reset_password" autocomplete="current-password"
                       class="form-input"
                       style="max-width: 320px; width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: var(--radius); background: var(--bg-body); color: var(--text-dark);"
                       required>
                @error('reset_password')
                    <div style="color: var(--danger, #dc2626); font-size: 13px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn--danger">
                Išvalyti visus duomenis
            </button>
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
</style>
@endpush
@endsection
