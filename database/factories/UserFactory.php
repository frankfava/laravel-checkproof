<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    // Default Password Used in Tests
    public static $defaultPassword = 'password';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $password;

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password ?: $password = Hash::make(self::$defaultPassword),
            'active' => $this->faker->boolean(90),
            'role' => $this->faker->randomElement(UserRole::cases())->value,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function role(string|UserRole $role)
    {
        $role = is_string($role) ? ($role == 'default' ? UserRole::default() : UserRole::from($role)) : $role;

        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    public function admin()
    {
        return $this->role(UserRole::Admin);
    }

    public function manager()
    {
        return $this->role(UserRole::Manager);
    }

    public function user()
    {
        return $this->role(UserRole::User);
    }
}
