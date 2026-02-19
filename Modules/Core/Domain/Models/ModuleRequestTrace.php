<?php

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleRequestTrace extends Model
{
    protected $fillable = [
        'request_id',
        'module_slug',
        'module_state',
        'latency_ms',
        'http_method',
        'url',
        'status_code',
        'user_id',
        'ip_address',
    ];

    protected $casts = [
        'latency_ms' => 'integer',
        'status_code' => 'integer',
        'user_id' => 'integer',
    ];
}