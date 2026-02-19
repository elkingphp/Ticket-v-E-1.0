<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketSubComplaintFactory;

class TicketSubComplaint extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_sub_complaints';

    protected $fillable = ['name', 'complaint_id'];

    public function complaint()
    {
        return $this->belongsTo(TicketComplaint::class, 'complaint_id');
    }
}
