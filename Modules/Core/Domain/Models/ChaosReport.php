<?php

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ChaosReport extends Model
{
    protected $fillable = [
        'test_name',
        'type',
        'result',
        'metrics',
        'summary',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}