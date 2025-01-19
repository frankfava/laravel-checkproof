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
}
