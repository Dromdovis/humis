<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Skill;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('skills')
            ->where('is_active', true)
            ->orderBy('name')
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
}
