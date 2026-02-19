<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketAuditLogFactory;

class TicketAuditLog extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_audit_logs';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class, 'user_id');
    }
}
