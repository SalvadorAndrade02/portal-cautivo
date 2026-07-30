<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_user_id',
        'business_id',
        'device_id',
        'radius_session_id',
        'username',
        'ip_address',
        'mac_address',
        'nas_ip_address',
        'nas_identifier',
        'started_at',
        'last_update_at',
        'ended_at',
        'duration_seconds',
        'input_bytes',
        'output_bytes',
        'termination_reason',
        'status',
        'metadata',
        'visitor_id',
        'access_type',
    ];

    protected function casts(): array
    {
        return [
            'portal_user_id' => 'integer',
            'business_id' => 'integer',
            'device_id' => 'integer',
            'started_at' => 'datetime',
            'last_update_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_seconds' => 'integer',
            'input_bytes' => 'integer',
            'output_bytes' => 'integer',
            'metadata' => 'array',
            'visitor_id' => 'integer',
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

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
