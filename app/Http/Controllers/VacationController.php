<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Vacation;
use App\Models\VacationTaskAssignment;
use App\Services\ClickUpService;
use App\Models\Setting;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        $query = Vacation::with(['employee', 'defaultSubstitute', 'taskAssignments.substitute']);

        $sortBy = $request->get('sort', 'start_date');
        $sortDir = $request->get('dir', 'desc');

        $allowedSorts = ['name', 'start_date', 'duration', 'status'];
        $allowedDirs = ['asc', 'desc'];
        
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'start_date';
        if (!in_array($sortDir, $allowedDirs)) $sortDir = 'desc';

        switch ($sortBy) {
            case 'name':
                $query->join('employees', 'vacations.employee_id', '=', 'employees.id')
                      ->orderBy('employees.name', $sortDir)
                      ->select('vacations.*');
                break;
            case 'duration':
                $query->orderByRaw("JULIANDAY(end_date) - JULIANDAY(start_date) {$sortDir}");
                break;
            case 'status':
                $query->orderBy('tasks_reassigned', $sortDir);
                break;
            default:
                $query->orderBy($sortBy, $sortDir);
        }

        $vacations = $query->paginate(20)->withQueryString();

        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        return view('vacations.index', compact('vacations', 'sortBy', 'sortDir', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'employee_id.required' => 'Pasirinkite darbuotoją.',
            'employee_id.exists' => 'Pasirinktas darbuotojas nerastas.',
            'start_date.required' => 'Įveskite pradžios datą.',
            'start_date.date' => 'Neteisingas datos formatas.',
            'start_date.after_or_equal' => 'Pradžios data turi būti šiandienos arba vėlesnė.',
            'end_date.required' => 'Įveskite pabaigos datą.',
            'end_date.date' => 'Neteisingas datos formatas.',
            'end_date.after_or_equal' => 'Pabaigos data turi būti lygi arba vėlesnė už pradžios datą.',
        ]);

        $validated['status'] = 'approved';

        $vacation = Vacation::create($validated);

        $employee = Employee::find($validated['employee_id']);
        ActivityLog::log('vacation_created', "{$employee->name} atostogos: {$validated['start_date']} — {$validated['end_date']}");

        return redirect()->route('vacations.assign', $vacation)
            ->with('success', 'Atostogos sukurtos. Pasirinkite pavaduotojus užduotims.');
    }

    public function show(Vacation $vacation)
    {
        $vacation->load(['employee', 'defaultSubstitute', 'taskAssignments.substitute']);

        return view('vacations.show', compact('vacation'));
    }

    /**
     * Task assignment wizard
     */
    public function assign(
        Vacation $vacation,
        ClickUpService $clickUpService,
        RecommendationService $recommendationService
    ) {
        $vacation->load(['employee', 'defaultSubstitute', 'taskAssignments']);

        $teamId = config('services.clickup.team_id');
        $tasks = [];

        $vacationClickupUserId = $vacation->employee->clickup_user_id;

        if ($teamId && $vacationClickupUserId) {
            $response = $clickUpService->getTasksByAssignee(
                $teamId,
                $vacationClickupUserId
            );

            if ($response && isset($response['tasks'])) {
                $tasks = $response['tasks'];
                usort($tasks, function ($a, $b) {
                    $aDate = $a['due_date'] ?? PHP_INT_MAX;
                    $bDate = $b['due_date'] ?? PHP_INT_MAX;
                    return $aDate <=> $bDate;
                });
            }
        }

        $employeesByClickupId = Employee::whereNotNull('clickup_user_id')
            ->pluck('name', 'clickup_user_id')
            ->toArray();

        foreach ($tasks as &$task) {
            $otherAssignees = [];
            if (!empty($task['assignees'])) {
                foreach ($task['assignees'] as $assignee) {
                    $assigneeId = (string) $assignee['id'];
                    if ($assigneeId !== (string) $vacationClickupUserId) {
                        $otherAssignees[] = $employeesByClickupId[$assigneeId]
                            ?? ($assignee['username'] ?? $assignee['email'] ?? 'Nežinomas');
                    }
                }
            }
            $task['already_reassigned'] = !empty($otherAssignees);
            $task['current_substitutes'] = $otherAssignees;
        }
        unset($task);

        $employees = Employee::where('is_active', true)
            ->where('id', '!=', $vacation->employee_id)
            ->with('skills')
            ->orderBy('name')
            ->get();

        $recommendationEnabled = Setting::get('recommendation_engine', 'disabled') === 'enabled';
        $taskRecommendations = [];

        if ($recommendationEnabled) {
            $defaultHoursPerTask = 2;
            $clickupWorkloadHours = [];
            if ($teamId) {
                foreach ($employees as $employee) {
                    if ($employee->clickup_user_id) {
                        $empTasks = $clickUpService->getTasksByAssignee(
                            $teamId,
                            $employee->clickup_user_id
                        );
                        $totalHours = 0;
                        if (isset($empTasks['tasks'])) {
                            foreach ($empTasks['tasks'] as $empTask) {
                                $estimate = $empTask['time_estimate'] ?? null;
                                $totalHours += $estimate
                                    ? round($estimate / 3600000, 1)
                                    : $defaultHoursPerTask;
                            }
                        }
                        $clickupWorkloadHours[$employee->clickup_user_id] = $totalHours;
                    }
                }
            }

            $recommendationService->setClickupWorkloadHours($clickupWorkloadHours);

            foreach ($tasks as $task) {
                $taskRecommendations[$task['id']] = $recommendationService->getRecommendationsForDropdown(
                    $task,
                    $vacation->employee_id,
                    $vacation->start_date,
                    $vacation->end_date
                );
            }
        }

        return view('vacations.assign', compact('vacation', 'tasks', 'employees', 'taskRecommendations', 'recommendationEnabled'));
    }

    /**
     * API: Gauti rekomendacijas konkrečiai užduočiai
     */
    public function getRecommendations(
        Request $request,
        Vacation $vacation,
        RecommendationService $recommendationService
    ) {
        $validated = $request->validate([
            'task' => 'required|array',
        ]);

        $recommendations = $recommendationService->getRecommendationsForDropdown(
            $validated['task'],
            $vacation->employee_id,
            $vacation->start_date,
            $vacation->end_date
        );

        return response()->json($recommendations);
    }

    /**
     * Save task assignments
     */
    public function saveAssignments(Request $request, Vacation $vacation)
    {
        $validated = $request->validate([
            'assignments' => 'array',
            'assignments.*.clickup_task_id' => 'required|string',
            'assignments.*.task_name' => 'required|string',
            'assignments.*.substitute_id' => 'nullable|exists:employees,id',
            'assignments.*.is_excluded' => 'boolean',
            'assignments.*.exclude_reason' => 'nullable|string|max:255',
            'assignments.*.time_estimate_hours' => 'nullable|integer',
            'assignments.*.due_date' => 'nullable|date',
            'assignments.*.start_date' => 'nullable|date',
            'assignments.*.priority' => 'nullable|string',
            'assignments.*.task_status' => 'nullable|string',
            'assignments.*.task_status_color' => 'nullable|string',
            'assignments.*.task_url' => 'nullable|string',
            'assignments.*.task_tags' => 'nullable|string',
        ]);

        $vacation->taskAssignments()->where('is_processed', false)->delete();

        $processedTaskIds = $vacation->taskAssignments()
            ->where('is_processed', true)
            ->pluck('clickup_task_id')
            ->toArray();

        foreach ($validated['assignments'] ?? [] as $assignment) {
            if (in_array($assignment['clickup_task_id'], $processedTaskIds)) {
                continue;
            }

            VacationTaskAssignment::create([
                'vacation_id' => $vacation->id,
                'clickup_task_id' => $assignment['clickup_task_id'],
                'task_name' => $assignment['task_name'],
                'substitute_id' => $assignment['substitute_id'] ?? null,
                'is_excluded' => $assignment['is_excluded'] ?? false,
                'exclude_reason' => $assignment['exclude_reason'] ?? null,
                'time_estimate_hours' => $assignment['time_estimate_hours'] ?? null,
                'due_date' => $assignment['due_date'] ?? null,
                'start_date' => $assignment['start_date'] ?? null,
                'priority' => $assignment['priority'] ?? null,
                'task_status' => $assignment['task_status'] ?? null,
                'task_status_color' => $assignment['task_status_color'] ?? null,
                'task_url' => $assignment['task_url'] ?? null,
                'task_tags' => json_decode($assignment['task_tags'] ?? '[]', true),
            ]);
        }

        ActivityLog::log('tasks_reassigned', "{$vacation->employee->name} atostogų užduotys perskirstytos");

        return redirect()->route('vacations.show', $vacation)
            ->with('success', 'Užduočių paskirstymas išsaugotas');
    }

    /**
     * Process assignments - sync to ClickUp
     */
    public function process(Vacation $vacation, ClickUpService $clickUpService)
    {
        $assignments = $vacation->taskAssignments()
            ->where('is_excluded', false)
            ->whereNotNull('substitute_id')
            ->where('is_processed', false)
            ->with('substitute')
            ->get();

        $processed = 0;
        $errors = 0;

        foreach ($assignments as $assignment) {
            if ($assignment->substitute && $assignment->substitute->clickup_user_id) {
                $result = $clickUpService->addAssignee(
                    $assignment->clickup_task_id,
                    (int) $assignment->substitute->clickup_user_id
                );

                if ($result) {
                    $assignment->update(['is_processed' => true]);
                    $processed++;
                } else {
                    $errors++;
                }
            }
        }

        $vacation->update([
            'tasks_reassigned' => true,
            'processed_at' => now(),
            'status' => 'processed',
        ]);

        ActivityLog::log('tasks_processed', "{$vacation->employee->name} — {$processed} užduotys priskirtos ClickUp");

        if ($errors > 0) {
            return redirect()->route('vacations.show', $vacation)
                ->with('warning', "Perskirstyta {$processed} užduočių, bet {$errors} nepavyko.");
        }

        return redirect()->route('vacations.show', $vacation)
            ->with('success', "Sėkmingai perskirstyta {$processed} užduočių į ClickUp!");
    }

    /**
     * Delete vacation (only if not processed)
     */
    public function destroy(Vacation $vacation)
    {
        // Saugumo patikrinimas - neleisti trinti jei jau apdorota
        if ($vacation->tasks_reassigned) {
            return redirect()->route('vacations.index')
                ->with('error', 'Negalima ištrinti atostogų kurios jau buvo perskirstytos į ClickUp.');
        }

        $employeeName = $vacation->employee->name ?? 'Nežinomas';

        ActivityLog::log('vacation_deleted', "{$employeeName} atostogos ištrintos");

        $vacation->delete();

        return redirect()->route('vacations.index')
            ->with('success', "Atostogos ({$employeeName}) sėkmingai ištrintos.");
    }
}
