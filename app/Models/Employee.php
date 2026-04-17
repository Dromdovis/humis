<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'clickup_user_id',
        'name',
        'email',
        'role',
        'position',
        'max_weekly_hours',
        'color',
        'profile_picture',
        'is_active',
        'cached_active_tasks_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_weekly_hours' => 'integer',
        'cached_active_tasks_count' => 'integer',
    ];

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'employee_skills')
            ->withPivot('level')
            ->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_employees')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function substitutingVacations(): HasMany
    {
        return $this->hasMany(Vacation::class, 'default_substitute_id');
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(VacationTaskAssignment::class, 'substitute_id');
    }

    /**
     * Check if employee is on vacation on a specific date
     */
    public function isOnVacation(\DateTime $date = null): bool
    {
        $date = $date ?? now();

        return $this->vacations()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->whereIn('status', ['approved', 'processed'])
            ->exists();
    }

    /**
     * Get skill level for a specific skill
     */
    public function getSkillLevel(int $skillId): int
    {
        $skill = $this->skills()->where('skill_id', $skillId)->first();
        return $skill ? $skill->pivot->level : 0;
    }
}
