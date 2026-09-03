<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandLog extends Model
{
    protected $fillable = [
        'command',
        'arguments',
        'options',
        'exit_code',
        'status',
        'output',
        'error',
        'duration',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'arguments' => 'array',
            'options' => 'array',
            'executed_at' => 'datetime',
            'duration' => 'float',
        ];
    }
}