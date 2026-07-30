<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'privacy_notice_version',
        'terms_version',
        'privacy_accepted_at',
        'terms_accepted_at',
        'marketing_consent',
        'marketing_consent_at',
        'ip_address',
        'mac_address',
        'user_agent',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'visitor_id' => 'integer',
            'privacy_accepted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'marketing_consent_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
