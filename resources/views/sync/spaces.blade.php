@extends('layouts.app')

@section('title', 'ClickUp Spaces - Sinchronizacija')

@section('content')
<div class="main__header">
    <h1 class="main__title">ClickUp Spaces</h1>
    <p class="main__subtitle">Peržiūrėkite spaces ir list ID</p>
</div>

<div class="card">
    <div class="card__body" style="padding: 0;">
        @if($spaces && isset($spaces['spaces']))
            <table class="table">
                <thead>
                    <tr>
                        <th>Space pavadinimas</th>
                        <th>Space ID</th>
                        <th>Statusas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($spaces['spaces'] as $space)
                    <tr>
                        <td style="font-weight: 600;">{{ $space['name'] }}</td>
                        <td><code style="background: var(--bg-primary); padding: 4px 8px; border-radius: 4px;">{{ $space['id'] }}</code></td>
                        <td>
                            @if($space['private'] ?? false)
                                <span class="badge badge--neutral">Privatus</span>
                            @else
                                <span class="badge badge--success">Viešas</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state__icon">📁</div>
                <div class="empty-state__title">Nėra spaces</div>
                <p>Nepavyko gauti ClickUp spaces. Patikrinkite CLICKUP_TEAM_ID.</p>
            </div>
        @endif
    </div>
</div>

<div style="margin-top: 24px;">
    <a href="{{ route('sync.index') }}" class="btn btn--secondary">← Grįžti</a>
</div>
@endsection
