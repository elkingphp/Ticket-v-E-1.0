<?php

namespace Modules\Core\Domain\Models;

class SystemSetting extends BaseModel
{
    protected $table = 'system_settings';

    protected $fillable = [
        'name',
        'value',
        'module',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];
}
