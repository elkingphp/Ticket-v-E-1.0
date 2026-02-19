<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Users\Domain\Models\User;

class TicketActivity extends Model
{
    use HasFactory;

    protected $table = 'tickets.ticket_activities';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'activity_type',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
