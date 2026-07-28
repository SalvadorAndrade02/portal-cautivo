<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'name',
        'local_number',
        'responsible_name',
        'email',
        'phone',
        'address',
        'status',
        'max_devices',
    ];

    protected function casts(): array
    {
        return [
            'plan_id' => 'integer',
            'max_devices' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function portalUsers(): HasMany
    {
        return $this->hasMany(PortalUser::class);
    }

    public function getEffectiveMaxDevicesAttribute(): int
    {
        return $this->max_devices
            ?? $this->plan?->max_devices
            ?? 1;
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function accessAttempts(): HasMany
    {
        return $this->hasMany(AccessAttempt::class);
    }

    public function accessSessions(): HasMany
    {
        return $this->hasMany(AccessSession::class);
    }
}
