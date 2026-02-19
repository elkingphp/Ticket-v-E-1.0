<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketEmailTemplateFactory;

class TicketEmailTemplate extends Model
{
    use HasFactory;


    protected $table = 'tickets.ticket_email_templates';

    protected $fillable = ['event_key', 'subject', 'body'];
}
