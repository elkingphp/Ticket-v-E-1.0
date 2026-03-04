<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Domain\Traits\MustBeApproved;

class Room extends Model
{
    use HasFactory, MustBeApproved;

    protected $connection = 'pgsql';
    protected $table = 'education.rooms';

    protected $guarded = ['id'];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }
}
