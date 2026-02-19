<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketGroupFactory;

class TicketGroup extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_groups';

    protected $fillable = ['name', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function members()
    {
        return $this->belongsToMany(\Modules\Users\Domain\Models\User::class, 'tickets.ticket_group_members', 'group_id', 'user_id')
            ->withPivot('is_leader')
            ->withTimestamps();
    }
}
