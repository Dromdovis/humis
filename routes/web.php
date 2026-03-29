<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\VacationController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
// Autentifikacija
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/prisijungti', [AuthController::class, 'showLogin'])->name('login');
Route::post('/prisijungti', [AuthController::class, 'login']);
Route::post('/registruotis', [AuthController::class, 'register'])->name('register');
Route::post('/tikrinti-el-pasta', [AuthController::class, 'checkEmail'])->name('check-email');
Route::post('/atsijungti', [AuthController::class, 'logout'])->name('logout');

// ═══════════════════════════════════════════════════════════════════════════
// Apsaugotos routes (reikia prisijungti)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {
    // Dashboard (Pradžia)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Darbuotojai
    Route::get('/darbuotojai', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/darbuotojai/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/darbuotojai/{employee}/igudziai', [EmployeeController::class, 'updateSkills'])->name('employees.skills.update');

    // Atostogos
    Route::get('/atostogos', [VacationController::class, 'index'])->name('vacations.index');
    Route::post('/atostogos', [VacationController::class, 'store'])->name('vacations.store');
    Route::get('/atostogos/{vacation}', [VacationController::class, 'show'])->name('vacations.show');
    Route::get('/atostogos/{vacation}/perskirstyti', [VacationController::class, 'assign'])->name('vacations.assign');
    Route::post('/atostogos/{vacation}/perskirstyti', [VacationController::class, 'saveAssignments'])->name('vacations.assign.save');
    Route::post('/atostogos/{vacation}/apdoroti', [VacationController::class, 'process'])->name('vacations.process');
    Route::post('/atostogos/{vacation}/rekomendacijos', [VacationController::class, 'getRecommendations'])->name('vacations.recommendations');
    Route::delete('/atostogos/{vacation}', [VacationController::class, 'destroy'])->name('vacations.destroy');

    // Projektai
    Route::get('/projektai', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projektai/sinchronizuoti', [ProjectController::class, 'sync'])->name('projects.sync');
    Route::get('/projektai/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projektai/{project}', [ProjectController::class, 'update'])->name('projects.update');

    // Įgūdžiai
    Route::get('/igudziai', [SkillController::class, 'index'])->name('skills.index');
    Route::post('/igudziai', [SkillController::class, 'store'])->name('skills.store');
    Route::delete('/igudziai/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');

    // Sinchronizacija
    Route::get('/sinchronizacija', [SyncController::class, 'index'])->name('sync.index');
    Route::post('/sinchronizacija/darbuotojai', [SyncController::class, 'employees'])->name('sync.employees');
    Route::get('/sinchronizacija/erdves', [SyncController::class, 'spaces'])->name('sync.spaces');

    // Nustatymai
    Route::get('/nustatymai', [SettingsController::class, 'index'])->name('settings.index');
});
