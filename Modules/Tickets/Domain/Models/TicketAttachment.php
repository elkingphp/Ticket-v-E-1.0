<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketAttachmentFactory;

class TicketAttachment extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_attachments';

    protected $fillable = [
        'ticket_id',
        'thread_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function thread()
    {
        return $this->belongsTo(TicketThread::class, 'thread_id');
    }
}
