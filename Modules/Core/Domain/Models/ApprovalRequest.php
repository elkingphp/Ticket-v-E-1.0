<?php

namespace Modules\Core\Domain\Models;

use Modules\Users\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the parent approvable model.
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the approval steps associated with this request.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_level', 'asc');
    }

    /**
     * Get the user who requested the approval.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected the request.
     */
    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the current pending step based on the approval level.
     */
    public function currentStep()
    {
        return $this->steps()
            ->where('status', 'pending')
            ->where('step_level', $this->approval_level)
            ->first();
    }
}
