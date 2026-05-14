<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'subject_id',
        'quantity',
        'initial_quantity',
    ];

    protected $casts = [
        'quantity'         => 'integer',
        'initial_quantity' => 'integer',
    ];

    // ─── Scopes ────────────────────────────────────────────────
    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeMainStore($query)
    {
        // المخزن الرئيسي = الأدمن (بدون hardcoded ID — نستخدم Role)
        return $query->whereHas('user', fn($q) => $q->where('role', 'admin'));
    }

    public function scopeDelegateInventories($query)
    {
        return $query->whereHas('user', fn($q) => $q->whereIn('role', ['general_delegate', 'section_delegate']));
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    // ─── Helpers ───────────────────────────────────────────────
    public function getDistributedQuantityAttribute(): int
    {
        return max(0, $this->initial_quantity - $this->quantity);
    }

    // ─── Relationships ─────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
