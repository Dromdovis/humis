<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\User;
use App\Models\Vacation;
use App\Models\VacationTaskAssignment;
use App\Services\ClickUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    /**
     * Išvalo visus programos duomenis (išskyrus migracijas ir schemą).
     * Reikalauja Humis slaptažodžio ir patvirtinimo.
     */
    public function resetAllData(Request $request)
    {
        if (! config('app.humis_password')) {
            return redirect()->route('settings.index')
                ->with('error', 'HUMIS_PASSWORD nenustatytas — duomenų išvalymas išjungtas.');
        }

        $request->validate([
            'reset_password' => 'required|string',
            'confirm_reset' => 'accepted',
        ], [
            'reset_password.required' => 'Įveskite slaptažodį.',
            'confirm_reset.accepted' => 'Pažymėkite, kad suprantate pasekmes.',
        ]);

        if ($request->input('reset_password') !== config('app.humis_password')) {
            return back()
                ->withInput()
                ->withErrors(['reset_password' => 'Neteisingas slaptažodis.']);
        }

        DB::transaction(function () {
            VacationTaskAssignment::query()->delete();
            Vacation::query()->delete();
            DB::table('project_employees')->delete();
            DB::table('project_skills')->delete();
            Project::query()->delete();
            DB::table('employee_skills')->delete();
            Employee::query()->delete();
            Skill::query()->delete();
            ActivityLog::query()->delete();
            Setting::query()->delete();
            User::query()->delete();

            DB::table('sessions')->delete();
            DB::table('password_reset_tokens')->delete();
            DB::table('cache')->delete();
            DB::table('cache_locks')->delete();
            DB::table('jobs')->delete();
            DB::table('job_batches')->delete();
            DB::table('failed_jobs')->delete();
        });

        Cache::flush();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Visi duomenys išvalyti. Prisijunkite iš naujo ir sinchronizuokite ClickUp, jei reikia.');
    }
}
