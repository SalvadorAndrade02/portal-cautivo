<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'status',
        'registered_at',
        'last_access_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'last_access_at' => 'datetime',
        ];
    }

    public function interestAreas(): BelongsToMany
    {
        return $this->belongsToMany(InterestArea::class)
            ->withTimestamps();
    }

    public function consents(): HasMany
    {
        return $this->hasMany(VisitorConsent::class);
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(VisitorAccessToken::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function accessSessions(): HasMany
    {
        return $this->hasMany(AccessSession::class);
    }

    public function accessAttempts(): HasMany
    {
        return $this->hasMany(AccessAttempt::class);
    }
}
