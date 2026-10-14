<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicUniqueId;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPublicUniqueId, HasRoles, Notifiable;

    public const TYPE_ADMIN = 'admin';

    public const TYPE_INTERNAL = 'internal';

    public const TYPE_CUSTOMER = 'customer';

    protected $attributes = [
        'account_type' => self::TYPE_INTERNAL,
        'is_active' => true,
        'must_change_password' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'email',
        'password',
        'unique_id',
        'account_type',
        'username',
        'name',
        'is_active',
        'company_id',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Override the username field for authentication.
     */
    public function username(): string
    {
        return 'employee_id';
    }

    public function is_customer(): bool
    {
        return $this->account_type === self::TYPE_CUSTOMER;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function customer_access(): HasMany
    {
        return $this->hasMany(CustomerProjectAccess::class);
    }
}
