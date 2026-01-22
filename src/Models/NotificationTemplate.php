<?php

namespace Usamamuneerchaudhary\Notifier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Usamamuneerchaudhary\Notifier\Traits\HasTenant;

class NotificationTemplate extends Model
{
    use HasTenant;

    protected $table = 'notifier_templates';
    protected $fillable = [
        'tenant_id',
        'name',
        'event_key',
        'subject',
        'content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(NotificationEvent::class, 'event_key', 'key');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
