<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'download_speed_mbps',
        'upload_speed_mbps',
        'session_timeout_minutes',
        'idle_timeout_minutes',
        'max_devices',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'download_speed_mbps' => 'integer',
            'upload_speed_mbps' => 'integer',
            'session_timeout_minutes' => 'integer',
            'idle_timeout_minutes' => 'integer',
            'max_devices' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
