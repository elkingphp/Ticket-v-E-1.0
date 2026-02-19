<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketRoutingFactory;

class TicketRouting extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_routing';

    protected $fillable = ['entity_type', 'entity_id', 'group_id'];

    public function group()
    {
        return $this->belongsTo(TicketGroup::class, 'group_id');
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
