<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationTaskAssignment extends Model
{
    protected $fillable = [
        'vacation_id',
        'clickup_task_id',
        'task_name',
        'substitute_id',
        'is_excluded',
        'exclude_reason',
        'time_estimate_hours',
        'due_date',
        'priority',
        'is_processed',
    ];

    protected $casts = [
        'is_excluded' => 'boolean',
        'is_processed' => 'boolean',
        'due_date' => 'date',
        'time_estimate_hours' => 'integer',
    ];

    public function vacation(): BelongsTo
    {
        return $this->belongsTo(Vacation::class);
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'substitute_id');
    }

    /**
     * Check if this task should be reassigned
     */
    public function shouldReassign(): bool
    {
        return !$this->is_excluded && $this->substitute_id !== null;
    }
}
