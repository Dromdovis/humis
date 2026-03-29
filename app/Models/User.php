<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
        'clickup_user_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Susietas darbuotojas
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Ar vartotojas yra administratorius
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Ar vartotojas yra projekto vadovas
     */
    public function isProjectManager(): bool
    {
        return $this->role === 'project_manager' || $this->role === 'admin';
    }

    /**
     * Gauti role lietuviškai
     */
    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administratorius',
            'project_manager' => 'Projekto vadovas',
            default => 'Vartotojas',
        };
    }
}
