<?php

namespace Modules\Core\Domain\Models;

use Modules\Users\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    /**
     * Get the request that owns this step.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    /**
     * Get the user who acted on this step.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    /**
     * Check if step is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
