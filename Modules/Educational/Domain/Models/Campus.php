<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Domain\Traits\MustBeApproved;

class Campus extends Model
{
    use HasFactory, MustBeApproved;

    protected $connection = 'pgsql';
    protected $table = 'education.campuses';

    protected $guarded = ['id'];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function programs()
    {
        return $this->belongsToMany(
            Program::class,
            'education.campus_program',
            'campus_id',
            'program_id'
        );
    }
}
