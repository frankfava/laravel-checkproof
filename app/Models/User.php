<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $attributes = [
        'name' => null,
        'email' => null,
        'role' => UserRole::User,
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

    public function scopeActive($query, bool $flag = true)
    {
        $query
            ->where('active', $flag);
    }

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

    public function scopeNotByRoles($query, string|array|UserRole $roles = [])
    {
        $query->byRoles(roles: $roles, in: false);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
