<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Vacation;
use App\Models\VacationTaskAssignment;
use App\Services\ClickUpService;
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
        ]);

        $validated['status'] = 'approved';

        $vacation = Vacation::create($validated);

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

        if ($teamId && $vacation->employee->clickup_user_id) {
            $response = $clickUpService->getTasksByAssignee(
                $teamId,
                $vacation->employee->clickup_user_id
            );

            if ($response && isset($response['tasks'])) {
                $tasks = $response['tasks'];
            }
        }

        $employees = Employee::where('is_active', true)
            ->where('id', '!=', $vacation->employee_id)
            ->with('skills')
            ->orderBy('name')
            ->get();

        // Gauti užduočių skaičius VISIEMS potencialiems pavaduotojams iš ClickUp
 
        $clickupTaskCounts = [];
        if ($teamId) {
            foreach ($employees as $employee) {
                if ($employee->clickup_user_id) {
                    $empTasks = $clickUpService->getTasksByAssignee(
                        $teamId,
                        $employee->clickup_user_id
                    );
                    $clickupTaskCounts[$employee->clickup_user_id] = 
                        isset($empTasks['tasks']) ? count($empTasks['tasks']) : 0;
                }
            }
        }

        // Nustatyti užduočių skaičius recommendation service'ui
        $recommendationService->setClickupTaskCounts($clickupTaskCounts);

        // Apskaičiuoti rekomendacijas kiekvienai užduočiai
        $taskRecommendations = [];
        foreach ($tasks as $task) {
            $taskRecommendations[$task['id']] = $recommendationService->getRecommendationsForDropdown(
                $task,
                $vacation->employee_id,
                $vacation->start_date,
                $vacation->end_date
            );
        }

        return view('vacations.assign', compact('vacation', 'tasks', 'employees', 'taskRecommendations'));
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
            'assignments.*.priority' => 'nullable|string',
            'schedule_type' => 'required|in:now,scheduled',
            'scheduled_date' => 'required_if:schedule_type,scheduled|nullable|date|after_or_equal:today',
        ]);

        $vacation->taskAssignments()->delete();

        foreach ($validated['assignments'] ?? [] as $assignment) {
            VacationTaskAssignment::create([
                'vacation_id' => $vacation->id,
                'clickup_task_id' => $assignment['clickup_task_id'],
                'task_name' => $assignment['task_name'],
                'substitute_id' => $assignment['substitute_id'] ?? null,
                'is_excluded' => $assignment['is_excluded'] ?? false,
                'exclude_reason' => $assignment['exclude_reason'] ?? null,
                'time_estimate_hours' => $assignment['time_estimate_hours'] ?? null,
                'due_date' => $assignment['due_date'] ?? null,
                'priority' => $assignment['priority'] ?? null,
            ]);
        }

        if ($validated['schedule_type'] === 'scheduled' && $validated['scheduled_date']) {
            $vacation->update([
                'scheduled_at' => $validated['scheduled_date'],
            ]);
            
            return redirect()->route('vacations.show', $vacation)
                ->with('success', 'Priskyrimas suplanuotas ' . \Carbon\Carbon::parse($validated['scheduled_date'])->format('Y-m-d'));
        }

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
        $vacation->delete();

        return redirect()->route('vacations.index')
            ->with('success', "Atostogos ({$employeeName}) sėkmingai ištrintos.");
    }
}
