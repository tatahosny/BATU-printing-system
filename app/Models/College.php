<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    protected $fillable = ['name', 'university_id'];

    // كلية تنتمي لجامعة واحدة
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    // كلية لها أقسام كثيرة
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
