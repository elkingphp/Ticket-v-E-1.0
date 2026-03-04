<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketStatusFactory;

class TicketStatus extends Model
{
    use HasFactory, \Modules\Core\Domain\Traits\MustBeApproved;


    protected $table = 'tickets.ticket_statuses';

    protected $fillable = ['name', 'color', 'is_default', 'is_final'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_final' => 'boolean',
    ];
}
