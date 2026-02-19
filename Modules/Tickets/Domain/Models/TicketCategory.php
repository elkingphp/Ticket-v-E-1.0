<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketCategoryFactory;

class TicketCategory extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_categories';

    protected $fillable = ['name', 'sla_hours', 'stage_id'];

    public function stage()
    {
        return $this->belongsTo(TicketStage::class, 'stage_id');
    }

    public function complaints()
    {
        return $this->hasMany(TicketComplaint::class, 'category_id');
    }

    public function routing()
    {
        return $this->morphOne(TicketRouting::class, 'entity');
    }
}
