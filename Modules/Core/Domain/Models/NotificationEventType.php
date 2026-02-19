<?php

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEventType extends Model
{
    protected $fillable = [
        'key',
        'title',
        'description',
        'category',
        'is_mandatory',
        'available_channels',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'available_channels' => 'array',
    ];
}