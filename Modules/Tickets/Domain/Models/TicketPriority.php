<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketPriorityFactory;

class TicketPriority extends Model
{
    use HasFactory, \Modules\Core\Domain\Traits\MustBeApproved;


    protected $table = 'tickets.ticket_priorities';

    protected $fillable = ['name', 'color', 'is_default', 'sla_multiplier'];

    protected $casts = [
        'is_default' => 'boolean',
        'sla_multiplier' => 'float',
    ];
}
