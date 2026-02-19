<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketComplaintFactory;

class TicketComplaint extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_complaints';

    protected $fillable = ['name', 'sla_hours', 'category_id'];

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function subComplaints()
    {
        return $this->hasMany(TicketSubComplaint::class, 'complaint_id');
    }

    public function routing()
    {
        return $this->morphOne(TicketRouting::class, 'entity');
    }
}
