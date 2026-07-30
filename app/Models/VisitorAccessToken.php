<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorAccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'device_id',
        'access_username',
        'token_hash',
        'expires_at',
        'used_at',
        'last_used_at',
        'revoked_at',
        'status',
        'metadata',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected function casts(): array
    {
        return [
            'visitor_id' => 'integer',
            'device_id' => 'integer',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
