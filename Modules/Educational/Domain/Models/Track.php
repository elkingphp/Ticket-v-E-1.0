<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Modules\Core\Application\Traits\Auditable;

class Track extends Model
{
    use SoftDeletes, Auditable;

    protected $connection = 'pgsql';
    protected $table = 'education.tracks';

    protected $fillable = ['name', 'slug', 'code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($track) {
            if (empty($track->slug)) {
                $track->slug = Str::slug($track->name);
            }
        });
    }

    /**
     * Scope for active tracks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relationship: Job Profiles under this track.
     */
    public function jobProfiles()
    {
        return $this->hasMany(JobProfile::class);
    }

    /**
     * Relationship: Responsible users for this track.
     */
    public function responsibles()
    {
        return $this->belongsToMany(\Modules\Users\Domain\Models\User::class, 'education.track_responsibles', 'track_id', 'user_id')->withTimestamps();
    }
}
