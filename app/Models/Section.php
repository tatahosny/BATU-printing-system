<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['batch_id', 'name', 'range_start', 'range_end'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(\App\Models\DelegateAssignment::class);
    }
}
