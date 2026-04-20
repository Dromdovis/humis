<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Skill;
use App\Services\ClickUpService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        // Visada ta pati DB užklausa — rūšiavimas vyksta naršyklėje (be pilno perkrovimo į serverį).
        $employees = Employee::with('skills')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('employees.index', compact('employees'));
    }

    public function show(Employee $employee)
    {
        $employee->load(['skills', 'projects', 'vacations' => function ($query) {
            $query->orderBy('start_date', 'desc');
        }]);

        $allSkills = Skill::orderBy('category')->orderBy('name')->get();

        return view('employees.show', compact('employee', 'allSkills'));
    }

    public function updateSkills(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'skills' => 'array',
            'skills.*.skill_id' => 'required|exists:skills,id',
            'skills.*.level' => 'required|integer|min:0|max:5',
        ]);

        $skillsData = [];
        foreach ($validated['skills'] ?? [] as $skill) {
            if ($skill['level'] > 0) {
                $skillsData[$skill['skill_id']] = ['level' => $skill['level']];
            }
        }

        $employee->skills()->sync($skillsData);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Įgūdžiai atnaujinti');
    }

    public function sync(ClickUpService $clickUpService)
    {
        try {
            $teamId = config('services.clickup.team_id', env('CLICKUP_TEAM_ID'));
            $members = $clickUpService->getTeamMembers($teamId);

            if (empty($members)) {
                return redirect()->route('employees.index')
                    ->with('error', 'Nepavyko gauti darbuotojų iš ClickUp.');
            }

            $count = 0;
            foreach ($members as $member) {
                $user = $member['user'] ?? $member;
                $clickupId = (string) $user['id'];

                $employee = Employee::updateOrCreate(
                    ['clickup_user_id' => $clickupId],
                    [
                        'name' => $user['username'] ?? $user['email'] ?? 'Nežinomas',
                        'email' => $user['email'] ?? null,
                        'role' => $member['role'] ?? $user['role'] ?? 'member',
                        'color' => $user['color'] ?? null,
                        'profile_picture' => $user['profilePicture'] ?? null,
                        'is_active' => true,
                    ]
                );

                $taskResponse = $clickUpService->getTasksByAssignee($teamId, $clickupId);
                $activeCount = isset($taskResponse['tasks']) ? count($taskResponse['tasks']) : 0;
                $employee->update(['cached_active_tasks_count' => $activeCount]);

                $count++;
            }

            ActivityLog::log('employees_synced', "Atnaujinta {$count} darbuotojų iš ClickUp");

            return redirect()->route('employees.index')
                ->with('success', "Sėkmingai atnaujinta {$count} darbuotojų.");
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Sinchronizacijos klaida: ' . $e->getMessage());
        }
    }
}
