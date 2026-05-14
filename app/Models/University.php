<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    protected $fillable = ['name'];

    public function colleges(): HasMany
    {
        return $this->hasMany(College::class);
    }
}
