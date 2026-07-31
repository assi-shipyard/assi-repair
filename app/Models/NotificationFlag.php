<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationFlag extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class, 'notification_flag_id');
    }
}
