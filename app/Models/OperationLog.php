<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationLog extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'action_type',
        'ip_address',
        'user_agent'
    ];

    /**
     * علاقة السجل بالمندوب
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة السجل بالطالب
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
