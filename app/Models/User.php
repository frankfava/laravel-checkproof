<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\HasCustomBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasCustomBuilder, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'role',
    ];

    protected $attributes = [
        'name' => null,
        'email' => null,
        'role' => UserRole::User,
        'active' => true,
        'password' => null,
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'email' => 'string',
            'role' => UserRole::class,
            'active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function searchBy(): array
    {
        return ['name', 'email'];
    }

    public function routeNotificationForMail()
    {
        return $this->name ? [$this->email => $this->name] : $this->email;
    }

    // ===== Attributes

    /** Can the authenticated user update this user (uses Policy) */
    public function canEdit(): Attribute
    {
        return Attribute::make(get: fn () => auth()->check() ? auth()->user()->can('update', $this) : null);
    }

    // ==== Scopes

    /** Scope using active flag */
    public function scopeActive($query, bool $flag = true)
    {
        $query
            ->where('active', $flag);
    }

    /** Filter those that have specific roles */
    public function scopeByRoles($query, string|array|UserRole $roles = [], bool $in = true)
    {
        $roles = is_array($roles) ? $roles : [$roles];

        $roles = array_unique(array_map(function ($role) {
            return strtolower(trim($role instanceof UserRole ? $role->value : $role));
        }, $roles));

        if (! empty($roles)) {
            call_user_func([$query, $in ? 'whereIn' : 'whereNotIn'], 'role', $roles);
        }
    }

    /** Filter those that DONT have specific roles */
    public function scopeNotByRoles($query, string|array|UserRole $roles = [])
    {
        $query->byRoles(roles: $roles, in: false);
    }

    // ===== Relationships

    /** Orders of this user */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
