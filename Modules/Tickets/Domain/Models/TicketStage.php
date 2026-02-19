<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketStageFactory;

class TicketStage extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_stages';

    protected $fillable = ['name', 'sla_hours'];

    public function categories()
    {
        return $this->hasMany(TicketCategory::class, 'stage_id');
    }

    public function routing()
    {
        return $this->morphOne(TicketRouting::class, 'entity');
    }
}
