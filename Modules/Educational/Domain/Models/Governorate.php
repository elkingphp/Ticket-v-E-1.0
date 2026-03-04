<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'education.governorates';

    protected $fillable = ['name_ar', 'name_en', 'status'];

    // Add get_name attribute to return appropriate language
    public function getNameAttribute()
    {
        return app()->getLocale() == 'ar' ? $this->name_ar : ($this->name_en ?? $this->name_ar);
    }
}
