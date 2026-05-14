<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'student_external_id',
        'university_id',
        'college_id',
        'department_id',
        'batch_id',
        'section_id',
        'subject_id',
        'delegate_id',
        'is_received',
        'received_at',
        'delivered_by',
    ];

    protected $casts = [
        'is_received' => 'boolean',
        'received_at' => 'datetime',
    ];

    // ─── Scopes ────────────────────────────────────────────────
    public function scopeDelivered($query)
    {
        return $query->where('is_received', true);
    }

    public function scopeUndelivered($query)
    {
        return $query->where('is_received', false);
    }

    public function scopeInSection($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    public function scopeInRange($query, $start, $end)
    {
        return $query->whereBetween('student_external_id', [$start, $end]);
    }

    public function scopeForDelegate($query, int $userId)
    {
        return $query->where('delegate_id', $userId);
    }

    // ─── Relationships ─────────────────────────────────────────
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function operationLogs()
    {
        return $this->hasMany(OperationLog::class);
    }
}
