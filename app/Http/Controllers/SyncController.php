<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Skill;
use App\Services\ClickUpService;

class SyncController extends Controller
{
    public function index(ClickUpService $clickUpService)
    {
        $teams = $clickUpService->getTeams();
        $currentTeamId = config('services.clickup.team_id');

        $stats = [
            'employees' => Employee::count(),
            'projects' => Project::count(),
            'skills' => Skill::count(),
        ];

        return view('sync.index', compact('teams', 'currentTeamId', 'stats'));
    }

    public function employees(ClickUpService $clickUpService)
    {
        $teamId = config('services.clickup.team_id');

        if (!$teamId) {
            return redirect()->route('sync.index')
                ->with('error', 'CLICKUP_TEAM_ID nenustatytas .env faile');
        }

        $result = $clickUpService->syncTeamMembers($teamId);

        if ($result['success']) {
            return redirect()->route('employees.index')
                ->with('success', "Sinchronizuota {$result['synced']} darbuotojų iš ClickUp");
        }

        return redirect()->route('sync.index')
            ->with('error', 'Nepavyko sinchronizuoti darbuotojų: ' . ($result['message'] ?? 'Nežinoma klaida'));
    }

    public function spaces(ClickUpService $clickUpService)
    {
        $teamId = config('services.clickup.team_id');

        if (!$teamId) {
            return redirect()->route('sync.index')
                ->with('error', 'CLICKUP_TEAM_ID nenustatytas .env faile');
        }

        $spaces = $clickUpService->getSpaces($teamId);

        return view('sync.spaces', compact('spaces'));
    }
}
