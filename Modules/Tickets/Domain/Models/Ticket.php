<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketFactory;

class Ticket extends Model
{
    use HasFactory;


    protected $table = 'tickets.tickets';

    protected $fillable = [
        'uuid',
        'ticket_number',
        'user_id',
        'stage_id',
        'category_id',
        'complaint_id',
        'subject',
        'details',
        'status_id',
        'priority_id',
        'assigned_group_id',
        'assigned_to',
        'due_at',
        'closed_at',
        'reopened_at',
        'locked_by',
        'locked_at',
        'auto_close_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
        'locked_at' => 'datetime',
        'auto_close_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class, 'user_id');
    }

    public function stage()
    {
        return $this->belongsTo(TicketStage::class, 'stage_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function complaint()
    {
        return $this->belongsTo(TicketComplaint::class, 'complaint_id');
    }

    public function subComplaints()
    {
        return $this->belongsToMany(TicketSubComplaint::class, 'tickets.ticket_sub_complaint_pivot', 'ticket_id', 'sub_complaint_id');
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function assignedGroup()
    {
        return $this->belongsTo(TicketGroup::class, 'assigned_group_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class, 'assigned_to');
    }

    public function threads()
    {
        return $this->hasMany(TicketThread::class, 'ticket_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id');
    }

    public function lockedBy()
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class, 'locked_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(TicketAuditLog::class, 'ticket_id');
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class, 'ticket_id')->latest();
    }

    public function isOverdue(): bool
    {
        return $this->due_at && now()->greaterThan($this->due_at);
    }
}
