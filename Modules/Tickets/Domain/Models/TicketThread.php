<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketThreadFactory;

class TicketThread extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_threads';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'type',
        'is_read_by_staff',
        'is_read_by_user',
    ];

    protected $casts = [
        'is_read_by_staff' => 'boolean',
        'is_read_by_user' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class, 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'thread_id');
    }

    public function scopePublic($query)
    {
        return $query->where('type', 'message');
    }

    public function getIsInternalAttribute()
    {
        return $this->type === 'internal_note';
    }
}
