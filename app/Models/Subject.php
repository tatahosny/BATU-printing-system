<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'term',
        'is_visible_to_general',
        'is_visible_to_section',
        'university_id',
        'college_id',
        'department_id',
        'batch_id',
        'section_id'
    ];

    // العلاقات لربط المادة بالشجرة الأكاديمية
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    // Scope للفلترة السريعة للمواد الظاهرة للمناديب
    public function scopeVisibleToGeneral($query)
    {
        return $query->where('is_visible_to_general', true);
    }

    public function scopeVisibleToSection($query)
    {
        return $query->where('is_visible_to_section', true);
    }
}
