<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasPublicUniqueId;

    protected $table = 'employees';

    protected $fillable = [
        'unique_id',
        'name',
        'email',
        'status',
        'user_id',
        'position_id',
        'direct_manager_employee_id',
        'profile_photo_path',
        'employee_id',
    ];

    protected $casts = [
        'unique_id' => 'string',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function getOrganizationalUnitAttribute(): ?OrganizationalUnit
    {
        return $this->position?->organizational_unit;
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'direct_manager_employee_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'direct_manager_employee_id');
    }

}
