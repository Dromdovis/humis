<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacation extends Model
{
    protected $fillable = [
        'employee_id',
        'default_substitute_id',
        'start_date',
        'end_date',
        'status',
        'bss_reference',
        'notes',
        'tasks_reassigned',
        'scheduled_at',
        'processed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'tasks_reassigned' => 'boolean',
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function defaultSubstitute(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'default_substitute_id');
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(VacationTaskAssignment::class);
    }

    /**
     * Get vacation duration in days
     */
    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if vacation overlaps with another date range
     */
    public function overlaps(\DateTime $start, \DateTime $end): bool
    {
        return $this->start_date <= $end && $this->end_date >= $start;
    }

    /**
     * Scope for upcoming vacations
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now())
            ->orderBy('start_date');
    }

    /**
     * Scope for vacations that need processing
     */
    public function scopeNeedsProcessing($query)
    {
        return $query->where('status', 'approved')
            ->where('tasks_reassigned', false);
    }

    /**
     * Scope for vacations with scheduled assignments
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
            ->where('tasks_reassigned', false)
            ->orderBy('scheduled_at');
    }

    /**
     * Scope for assignments due to execute
     */
    public function scopeReadyToExecute($query)
    {
        return $query->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->where('tasks_reassigned', false);
    }

    /**
     * Check if assignment is scheduled for future
     */
    public function isScheduledForFuture(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }
}
