<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    protected $fillable = [
        'clickup_space_id',
        'clickup_folder_id',
        'clickup_list_id',
        'name',
        'client_name',
        'description',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'project_skills')
            ->withPivot('required_level', 'is_primary')
            ->withTimestamps();
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'project_employees')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get primary technologies for this project
     */
    public function primarySkills(): BelongsToMany
    {
        return $this->skills()->wherePivot('is_primary', true);
    }

    /**
     * Get team lead for this project
     */
    public function lead(): ?Employee
    {
        return $this->employees()->wherePivot('role', 'lead')->first();
    }
}
