<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Vacation;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * ClickUp darbo krūvis valandomis kiekvienam darbuotojui (pagal clickup_user_id)
     */
    private array $clickupWorkloadHours = [];

    public function setClickupWorkloadHours(array $hours): void
    {
        $this->clickupWorkloadHours = $hours;
    }

    /**
     * Apskaičiuoti tinkamumo balą kiekvienam darbuotojui konkrečiai užduočiai
     * 
     * @param array $task ClickUp užduoties duomenys
     * @param int $vacationEmployeeId Atostogaujančio darbuotojo ID (išskirti)
     * @param \Carbon\Carbon|null $vacationStart Atostogų pradžia
     * @param \Carbon\Carbon|null $vacationEnd Atostogų pabaiga
     * @return Collection Darbuotojai su tinkamumo balais
     */
    public function calculateRecommendations(
        array $task,
        int $vacationEmployeeId,
        $vacationStart = null,
        $vacationEnd = null
    ): Collection {
        $employees = Employee::where('is_active', true)
            ->where('id', '!=', $vacationEmployeeId)
            ->with('skills')
            ->get();

        $taskListId = data_get($task, 'list.id', '');
        $taskFolderId = data_get($task, 'folder.id', '');
        $taskSpaceId = data_get($task, 'space.id', '');
        $taskListName = strtolower(data_get($task, 'list.name', ''));
        $taskFolderName = strtolower(data_get($task, 'folder.name', ''));

        $taskProject = null;
        if ($taskListId) {
            $taskProject = Project::where('clickup_list_id', $taskListId)->first();
        }
        if (!$taskProject && $taskFolderId) {
            $taskProject = Project::where('clickup_folder_id', $taskFolderId)->first();
        }
        if (!$taskProject && $taskSpaceId) {
            $taskProject = Project::where('clickup_space_id', $taskSpaceId)->first();
        }

        $projectSkills = $taskProject ? $taskProject->skills()->withPivot('required_level', 'is_primary')->get() : collect();

        return $employees->map(function ($employee) use (
            $task, $taskProject, $projectSkills, $taskListName, $taskFolderName, $vacationStart, $vacationEnd
        ) {
            $scores = [];
            $details = [];

            // 1. ĮGŪDŽIŲ ATITIKIMAS (max 40 taškų)
            $skillScore = $this->calculateSkillScore($employee, $projectSkills);
            $scores['skills'] = $skillScore['score'];
            $details['skills'] = $skillScore['details'];

            // 2. UŽIMTUMAS (max 30 taškų) - mažiau užduočių = geresnis balas
            $workloadScore = $this->calculateWorkloadScore($employee);
            $scores['workload'] = $workloadScore['score'];
            $details['workload'] = $workloadScore['details'];

            // 3. PRIEINAMUMAS (max 20 taškų) - ar nėra atostogose
            $availabilityScore = $this->calculateAvailabilityScore($employee, $vacationStart, $vacationEnd);
            $scores['availability'] = $availabilityScore['score'];
            $details['availability'] = $availabilityScore['details'];

            // 4. PATIRTIS SU PROJEKTU (max 10 taškų)
            $projectScore = $this->calculateProjectScore($employee, $taskProject, $taskListName, $taskFolderName);
            $scores['project'] = $projectScore['score'];
            $details['project'] = $projectScore['details'];

            // Bendras balas (0-100)
            $totalScore = array_sum($scores);
            $totalScore = min(100, max(0, $totalScore)); // Riboti 0-100

            return [
                'employee' => $employee,
                'score' => round($totalScore),
                'scores' => $scores,
                'details' => $details,
            ];
        })->sortByDesc('score')->values();
    }

    /**
     * Įgūdžių atitikimo skaičiavimas pagal projekto reikalaujamus skills
     */
    private function calculateSkillScore(Employee $employee, Collection $projectSkills): array
    {
        $maxScore = 40;

        $employeeSkills = $employee->skills->keyBy('id')->mapWithKeys(function ($skill) {
            return [$skill->id => $skill->pivot->level];
        });

        if ($employeeSkills->isEmpty()) {
            return ['score' => round($maxScore * 0.3), 'details' => 'Įgūdžiai nenurodyti'];
        }

        if ($projectSkills->isEmpty()) {
            $avgLevel = $employeeSkills->avg() ?? 0;
            return [
                'score' => round(($avgLevel / 5) * ($maxScore * 0.5)),
                'details' => 'Projektas neturi reikalaujamų įgūdžių — vertinamas vidurkis: ' . round($avgLevel, 1)
            ];
        }

        $totalWeight = 0;
        $weightedScore = 0;
        $matchedSkills = [];

        foreach ($projectSkills as $projSkill) {
            $requiredLevel = $projSkill->pivot->required_level ?: 3;
            $isPrimary = $projSkill->pivot->is_primary;
            $weight = $isPrimary ? 2 : 1;
            $totalWeight += $weight;

            if ($employeeSkills->has($projSkill->id)) {
                $empLevel = $employeeSkills->get($projSkill->id);
                $ratio = min(1, $empLevel / $requiredLevel);
                $weightedScore += $ratio * $weight;
                $matchedSkills[] = "{$projSkill->name} ({$empLevel}/{$requiredLevel})";
            }
        }

        if ($totalWeight === 0) {
            return ['score' => round($maxScore * 0.5), 'details' => 'Nėra svorių'];
        }

        $score = round(($weightedScore / $totalWeight) * $maxScore);

        if (empty($matchedSkills)) {
            return ['score' => 0, 'details' => 'Neturi reikalaujamų įgūdžių'];
        }

        return [
            'score' => min($maxScore, $score),
            'details' => 'Atitinka: ' . implode(', ', array_slice($matchedSkills, 0, 3))
        ];
    }

    /**
     * Užimtumo skaičiavimas pagal bendrą darbo valandų krūvį
     */
    private function calculateWorkloadScore(Employee $employee): array
    {
        $maxScore = 30;

        $clickupHours = 0;
        if ($employee->clickup_user_id && isset($this->clickupWorkloadHours[$employee->clickup_user_id])) {
            $clickupHours = $this->clickupWorkloadHours[$employee->clickup_user_id];
        }

        $substitutingHours = $employee->taskAssignments()
            ->whereHas('vacation', function ($q) {
                $q->where('tasks_reassigned', false)
                  ->where('end_date', '>=', now());
            })
            ->sum('time_estimate_hours') ?? 0;

        $totalHours = $clickupHours + $substitutingHours;

        if ($totalHours == 0) {
            return ['score' => $maxScore, 'details' => 'Laisvas (0h krūvis)'];
        } elseif ($totalHours <= 20) {
            $score = round($maxScore * 0.85);
            return ['score' => $score, 'details' => "Mažas krūvis ({$totalHours}h)"];
        } elseif ($totalHours <= 40) {
            $score = round($maxScore * 0.7);
            return ['score' => $score, 'details' => "Vidutinis krūvis ({$totalHours}h)"];
        } elseif ($totalHours <= 60) {
            $score = round($maxScore * 0.5);
            return ['score' => $score, 'details' => "Didelis krūvis ({$totalHours}h)"];
        } elseif ($totalHours <= 80) {
            $score = round($maxScore * 0.3);
            return ['score' => $score, 'details' => "Labai didelis krūvis ({$totalHours}h)"];
        } else {
            $score = round($maxScore * 0.1);
            return ['score' => $score, 'details' => "Perkrautas ({$totalHours}h)"];
        }
    }

    /**
     * Prieinamumo skaičiavimas - ar darbuotojas pats neatostogauja
     */
    private function calculateAvailabilityScore(
        Employee $employee,
        $vacationStart,
        $vacationEnd
    ): array {
        $maxScore = 20;

        if (!$vacationStart || !$vacationEnd) {
            return [
                'score' => $maxScore,
                'details' => 'Prieinamas'
            ];
        }

        $conflictingVacation = Vacation::where('employee_id', $employee->id)
            ->where(function ($query) use ($vacationStart, $vacationEnd) {
                $query->whereBetween('start_date', [$vacationStart, $vacationEnd])
                    ->orWhereBetween('end_date', [$vacationStart, $vacationEnd])
                    ->orWhere(function ($q) use ($vacationStart, $vacationEnd) {
                        $q->where('start_date', '<=', $vacationStart)
                          ->where('end_date', '>=', $vacationEnd);
                    });
            })
            ->whereIn('status', ['approved', 'processed'])
            ->first();

        if ($conflictingVacation) {
            return [
                'score' => 0,
                'details' => '⚠️ Pats atostogauja: ' . 
                    $conflictingVacation->start_date->format('m-d') . ' - ' . 
                    $conflictingVacation->end_date->format('m-d')
            ];
        }

        return [
            'score' => $maxScore,
            'details' => 'Prieinamas nurodytu laikotarpiu'
        ];
    }

    /**
     * Projekto patirties skaičiavimas per tiesioginį DB ryšį
     */
    private function calculateProjectScore(
        Employee $employee,
        ?Project $taskProject,
        string $taskListName,
        string $taskFolderName
    ): array {
        $maxScore = 10;

        if ($taskProject) {
            $isProjectMember = $employee->projects()->where('projects.id', $taskProject->id)->exists();
            if ($isProjectMember) {
                return [
                    'score' => $maxScore,
                    'details' => 'Komandos narys: ' . $taskProject->name
                ];
            }
        }

        $previousAssignment = $employee->taskAssignments()
            ->where(function ($query) use ($taskListName, $taskFolderName) {
                if ($taskListName) {
                    $query->whereRaw('LOWER(task_name) LIKE ?', ["%{$taskListName}%"]);
                }
                if ($taskFolderName) {
                    $query->orWhereRaw('LOWER(task_name) LIKE ?', ["%{$taskFolderName}%"]);
                }
            })
            ->exists();

        if ($previousAssignment) {
            return [
                'score' => round($maxScore * 0.5),
                'details' => 'Turėjo panašių užduočių'
            ];
        }

        return ['score' => 0, 'details' => 'Naujas projektas'];
    }

    /**
     * Gauti rekomenduojamus darbuotojus su balais (paprastas formatas dropdown'ui)
     */
    public function getRecommendationsForDropdown(
        array $task,
        int $vacationEmployeeId,
        $vacationStart = null,
        $vacationEnd = null
    ): array {
        $recommendations = $this->calculateRecommendations(
            $task,
            $vacationEmployeeId,
            $vacationStart,
            $vacationEnd
        );

        return $recommendations->map(function ($rec) {
            $employee = $rec['employee'];
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'score' => $rec['score'],
                'color' => $employee->color ?? '#6366f1',
                'details' => $this->formatDetailsShort($rec['details']),
                'badge' => $this->getScoreBadge($rec['score']),
                'breakdown' => [
                    'skills' => [
                        'score' => $rec['scores']['skills'],
                        'max' => 40,
                        'label' => 'Įgūdžiai',
                        'details' => $rec['details']['skills'],
                        'icon' => '🎯',
                    ],
                    'workload' => [
                        'score' => $rec['scores']['workload'],
                        'max' => 30,
                        'label' => 'Užimtumas',
                        'details' => $rec['details']['workload'],
                        'icon' => '📊',
                    ],
                    'availability' => [
                        'score' => $rec['scores']['availability'],
                        'max' => 20,
                        'label' => 'Prieinamumas',
                        'details' => $rec['details']['availability'],
                        'icon' => '📅',
                    ],
                    'project' => [
                        'score' => $rec['scores']['project'],
                        'max' => 10,
                        'label' => 'Projekto patirtis',
                        'details' => $rec['details']['project'],
                        'icon' => '💼',
                    ],
                ],
            ];
        })->toArray();
    }

    /**
     * Suformatuoti trumpą detalių aprašymą
     */
    private function formatDetailsShort(array $details): string
    {
        $parts = [];
        if (isset($details['skills']) && strpos($details['skills'], 'Atitinka') === 0) {
            $parts[] = $details['skills'];
        }
        if (isset($details['availability']) && strpos($details['availability'], '⚠️') === 0) {
            $parts[] = $details['availability'];
        }
        if (isset($details['workload']) && strpos($details['workload'], 'Daug') === 0) {
            $parts[] = $details['workload'];
        }
        
        return implode(' | ', $parts) ?: 'Bendras įvertinimas';
    }

    /**
     * Gauti balo ženkliuką
     */
    private function getScoreBadge(int $score): array
    {
        if ($score >= 80) {
            return ['text' => 'Puikiai tinka', 'class' => 'badge--success'];
        } elseif ($score >= 60) {
            return ['text' => 'Gerai tinka', 'class' => 'badge--info'];
        } elseif ($score >= 40) {
            return ['text' => 'Tinka', 'class' => 'badge--warning'];
        } else {
            return ['text' => 'Mažai tinka', 'class' => 'badge--neutral'];
        }
    }
}
