<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'portal_user_id',
        'name',
        'device_type',
        'mac_address',
        'last_ip_address',
        'authorized',
        'blocked',
        'bypass_portal',
        'first_seen_at',
        'last_seen_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'business_id' => 'integer',
            'portal_user_id' => 'integer',
            'authorized' => 'boolean',
            'blocked' => 'boolean',
            'bypass_portal' => 'boolean',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class);
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
