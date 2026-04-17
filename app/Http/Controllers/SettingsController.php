<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\ClickUpService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(ClickUpService $clickUpService)
    {
        $teams = $clickUpService->getTeams();
        $recommendationEngine = Setting::get('recommendation_engine', 'disabled');

        return view('settings.index', compact('teams', 'recommendationEngine'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'recommendation_engine' => 'required|in:enabled,disabled',
        ]);

        Setting::set('recommendation_engine', $validated['recommendation_engine']);

        $status = $validated['recommendation_engine'] === 'enabled' ? 'įjungtas' : 'išjungtas';
        ActivityLog::log('settings_changed', "Rekomendacijų variklis {$status}");

        return redirect()->route('settings.index')
            ->with('success', 'Nustatymai išsaugoti.');
    }
}
