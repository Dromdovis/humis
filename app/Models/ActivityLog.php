<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['action', 'description', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function log(string $action, string $description): static
    {
        return static::create([
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
