<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Domain\Traits\MustBeApproved;

class Building extends Model
{
    use HasFactory, MustBeApproved;

    protected $connection = 'pgsql';
    protected $table = 'education.buildings';

    protected $guarded = ['id'];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }
}
