<?php

namespace Modules\Tickets\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tickets\Database\Factories\TicketComplaintFactory;

class TicketComplaint extends Model
{
    use HasFactory, \Modules\Core\Domain\Traits\MustBeApproved;


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

    public function roles()
    {
        return $this->belongsToMany(\Modules\Users\Domain\Models\Role::class, 'tickets.ticket_complaint_role');
    }

    public function scopeVisibleTo($query, $user)
    {
        $adminRole = \Modules\Settings\Domain\Models\Setting::where('key', 'tickets_admin_role')->value('value') ?: 'admin';

        if ($user->hasRole($adminRole)) {
            return $query;
        }

        $userRoleIds = $user->roles->pluck('id')->toArray();

        return $query->where(function ($q) use ($userRoleIds) {
            $q->whereDoesntHave('roles')
                ->orWhereHas('roles', fn($roleQuery) => $roleQuery->whereIn('roles.id', $userRoleIds));
        });
    }
}
