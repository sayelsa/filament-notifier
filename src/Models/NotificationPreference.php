<?php
namespace Usamamuneerchaudhary\Notifier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Usamamuneerchaudhary\Notifier\Traits\HasTenant;

class NotificationPreference extends Model
{
    use HasTenant;

    protected $table = 'notifier_preferences';
    protected $fillable = [
        'tenant_id',
        'user_id',
        'event_key',
        'channels',
        'settings',
    ];

    protected $casts = [
        'channels' => 'array',
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
