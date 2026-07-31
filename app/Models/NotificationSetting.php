<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'notification_flag_id',
        'user_id',
    ];

    public function flag(): BelongsTo
    {
        return $this->belongsTo(NotificationFlag::class, 'notification_flag_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
