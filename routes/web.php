<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\VacationController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
// Autentifikacija
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/prisijungti', [AuthController::class, 'showLogin'])->name('login');
Route::post('/prisijungti', [AuthController::class, 'login']);
Route::post('/atsijungti', [AuthController::class, 'logout'])->name('logout');

// ═══════════════════════════════════════════════════════════════════════════
// Apsaugotos routes (reikia prisijungti)
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware('auth')->group(function () {
    // Pagrindinis puslapis nukreipia į atostogas
    Route::get('/', function () {
        return redirect()->route('vacations.index');
    })->name('dashboard');

    // Darbuotojai
    Route::get('/darbuotojai', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/darbuotojai/sinchronizuoti', [EmployeeController::class, 'sync'])->name('employees.sync');
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

    // Nustatymai
    Route::get('/nustatymai', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/nustatymai', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/nustatymai/isvalyti', [SettingsController::class, 'resetAllData'])->name('settings.reset');

    // Žurnalas
    Route::get('/zurnalas', [ActivityLogController::class, 'index'])->name('activity-log.index');
});
