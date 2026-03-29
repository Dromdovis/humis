<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Vacation;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'employees' => Employee::where('is_active', true)->count(),
            'projects' => Project::where('is_active', true)->count(),
            'upcoming_vacations' => Vacation::upcoming()->count(),
        ];

        $upcomingVacations = Vacation::with('employee')
            ->where('start_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        return view('dashboard', compact('stats', 'upcomingVacations'));
    }
}
