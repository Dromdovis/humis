<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Skill;
use App\Models\Vacation;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * ClickUp užduočių skaičiai kiekvienam darbuotojui (pagal clickup_user_id)
     * Užpildoma iš išorės prieš skaičiuojant rekomendacijas
     */
    private array $clickupTaskCounts = [];

    /**
     * Nustatyti ClickUp užduočių skaičius
     */
    public function setClickupTaskCounts(array $counts): void
    {
        $this->clickupTaskCounts = $counts;
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

        $taskTags = collect($task['tags'] ?? [])->pluck('name')->map(fn($t) => strtolower($t));
        $taskListName = strtolower($task['list']['name'] ?? '');
        $taskFolderName = strtolower($task['folder']['name'] ?? '');

        $allSkills = Skill::all();

        return $employees->map(function ($employee) use (
            $task, $taskTags, $taskListName, $taskFolderName, $allSkills, $vacationStart, $vacationEnd
        ) {
            $scores = [];
            $details = [];

            // 1. ĮGŪDŽIŲ ATITIKIMAS (max 40 taškų)
            $skillScore = $this->calculateSkillScore($employee, $taskTags, $taskListName, $taskFolderName, $allSkills);
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
            $projectScore = $this->calculateProjectScore($employee, $taskListName, $taskFolderName);
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
     * Įgūdžių atitikimo skaičiavimas
     */
    private function calculateSkillScore(
        Employee $employee,
        Collection $taskTags,
        string $taskListName,
        string $taskFolderName,
        Collection $allSkills
    ): array {
        $maxScore = 40;
        $score = 0;
        $matchedSkills = [];

        $employeeSkills = $employee->skills->mapWithKeys(function ($skill) {
            return [strtolower($skill->name) => $skill->pivot->level];
        });

        if ($employeeSkills->isEmpty()) {
            return [
                'score' => $maxScore * 0.3, // Bazinis balas jei nėra įgūdžių
                'details' => 'Įgūdžiai nenurodyti'
            ];
        }

        $keywords = $taskTags->merge([$taskListName, $taskFolderName])
            ->filter()
            ->unique();

        foreach ($allSkills as $skill) {
            $skillNameLower = strtolower($skill->name);
            
            foreach ($keywords as $keyword) {
                if (str_contains($keyword, $skillNameLower) || str_contains($skillNameLower, $keyword)) {
                    if ($employeeSkills->has($skillNameLower)) {
                        $level = $employeeSkills->get($skillNameLower);
                        $skillPoints = ($level / 5) * 10; // Max 10 taškų per skill
                        $score += $skillPoints;
                        $matchedSkills[] = "{$skill->name} (Lv.{$level})";
                    }
                    break;
                }
            }
        }

        // Jei nerasta tiesioginio atitikimo, duoti bazinius taškus pagal bendrą skill lygį
        if (empty($matchedSkills)) {
            $avgLevel = $employeeSkills->avg() ?? 0;
            $score = ($avgLevel / 5) * ($maxScore * 0.5);
            return [
                'score' => round($score),
                'details' => 'Bendras įgūdžių vidurkis: ' . round($avgLevel, 1)
            ];
        }

        $score = min($maxScore, $score);

        return [
            'score' => round($score),
            'details' => 'Atitinka: ' . implode(', ', array_slice($matchedSkills, 0, 3))
        ];
    }

    /**
     * Užimtumo skaičiavimas - mažiau užduočių = didesnis balas
     * Naudoja REALIAS ClickUp užduotis + lokalius pavaduojamus task'us
     */
    private function calculateWorkloadScore(Employee $employee): array
    {
        $maxScore = 30;

        // 1. ClickUp realios užduotys (jei turime duomenis)
        $clickupTasks = 0;
        if ($employee->clickup_user_id && isset($this->clickupTaskCounts[$employee->clickup_user_id])) {
            $clickupTasks = $this->clickupTaskCounts[$employee->clickup_user_id];
        }

        // 2. Lokalūs pavaduojimai (iš mūsų sistemos)
        $substitutingTasks = $employee->taskAssignments()
            ->whereHas('vacation', function ($q) {
                $q->where('tasks_reassigned', false)
                  ->where('end_date', '>=', now());
            })
            ->count();

        $totalTasks = $clickupTasks + $substitutingTasks;

        if ($totalTasks === 0) {
            return [
                'score' => $maxScore,
                'details' => 'Laisvas (0 užduočių)'
            ];
        } elseif ($totalTasks <= 2) {
            return [
                'score' => round($maxScore * 0.85),
                'details' => "Mažai užimtas ({$totalTasks} užd.)"
            ];
        } elseif ($totalTasks <= 4) {
            return [
                'score' => round($maxScore * 0.7),
                'details' => "Vidutiniškai užimtas ({$totalTasks} užd.)"
            ];
        } elseif ($totalTasks <= 6) {
            return [
                'score' => round($maxScore * 0.5),
                'details' => "Užimtas ({$totalTasks} užd.)"
            ];
        } elseif ($totalTasks <= 10) {
            return [
                'score' => round($maxScore * 0.3),
                'details' => "Labai užimtas ({$totalTasks} užd.)"
            ];
        } else {
            return [
                'score' => round($maxScore * 0.1),
                'details' => "Perkrautas ({$totalTasks} užd.)"
            ];
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
     * Projekto patirties skaičiavimas
     */
    private function calculateProjectScore(
        Employee $employee,
        string $taskListName,
        string $taskFolderName
    ): array {
        $maxScore = 10;

        $matchingProject = $employee->projects()
            ->where(function ($query) use ($taskListName, $taskFolderName) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$taskListName}%"])
                    ->orWhereRaw('LOWER(name) LIKE ?', ["%{$taskFolderName}%"]);
            })
            ->first();

        if ($matchingProject) {
            return [
                'score' => $maxScore,
                'details' => 'Dirba projekte: ' . $matchingProject->name
            ];
        }

        // Duoti dalinį balą jei anksčiau buvo priskirtas panašioms užduotims
        $previousAssignment = $employee->taskAssignments()
            ->where(function ($query) use ($taskListName, $taskFolderName) {
                $query->whereRaw('LOWER(task_name) LIKE ?', ["%{$taskListName}%"])
                    ->orWhereRaw('LOWER(task_name) LIKE ?', ["%{$taskFolderName}%"]);
            })
            ->exists();

        if ($previousAssignment) {
            return [
                'score' => $maxScore * 0.5,
                'details' => 'Turėjo panašių užduočių'
            ];
        }

        return [
            'score' => 0,
            'details' => 'Naujas projektas'
        ];
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
