<?php

namespace Usamamuneerchaudhary\Notifier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Usamamuneerchaudhary\Notifier\Traits\HasTenant;

class NotificationChannel extends Model
{
    use HasTenant;

    protected $table = 'notifier_channels';
    protected $fillable = [
        'tenant_id',
        'title',
        'type',
        'icon',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'channel', 'type');
    }
}

