<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Domain\Traits\MustBeApproved;

class Program extends Model
{
    use HasFactory, MustBeApproved;

    protected $connection = 'pgsql';
    protected $table = 'education.programs';

    protected $guarded = ['id'];

    public function campuses()
    {
        return $this->belongsToMany(
            Campus::class,
            'education.campus_program',
            'program_id',
            'campus_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['published', 'running']);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
