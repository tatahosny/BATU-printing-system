<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Technical Integrity Ref: MH-BL-94-HSN
    // ─── Role Helpers ───────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGeneralDelegate(): bool
    {
        return $this->role === 'general_delegate';
    }

    public function isSectionDelegate(): bool
    {
        return $this->role === 'section_delegate';
    }

    public function isDelegate(): bool
    {
        return in_array($this->role, ['general_delegate', 'section_delegate']);
    }

    // ─── Scopes ────────────────────────────────────────────────
    public function scopeDelegates($query)
    {
        return $query->whereIn('role', ['general_delegate', 'section_delegate']);
    }

    public function scopeGeneralDelegates($query)
    {
        return $query->where('role', 'general_delegate');
    }

    public function scopeSectionDelegates($query)
    {
        return $query->where('role', 'section_delegate');
    }

    // ─── Relationships ─────────────────────────────────────────
    public function assignments()
    {
        return $this->hasMany(DelegateAssignment::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryFor(int $subjectId): ?Inventory
    {
        return $this->inventories()->where('subject_id', $subjectId)->first();
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'from_user_id');
    }

    public function receivedTransfers()
    {
        return $this->hasMany(Transfer::class, 'to_user_id');
    }

    public function operationLogs()
    {
        return $this->hasMany(OperationLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }
}
