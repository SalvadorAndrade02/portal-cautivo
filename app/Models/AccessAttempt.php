<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_user_id',
        'business_id',
        'device_id',
        'username',
        'ip_address',
        'mac_address',
        'result',
        'reason',
        'source',
        'metadata',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'portal_user_id' => 'integer',
            'business_id' => 'integer',
            'device_id' => 'integer',
            'metadata' => 'array',
            'attempted_at' => 'datetime',
        ];
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
