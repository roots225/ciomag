<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'role_id' => fn () => Role::firstOrCreate(
                ['slug' => 'author'],
                ['name' => 'Auteur', 'permissions' => ['posts.create', 'posts.edit']]
            )->id,
            'email' => fake()->unique()->safeEmail(),
            'username' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 999),
            'password_hash' => static::$password ??= Hash::make('password'),
            'display_name' => $name,
            'bio' => fake()->sentence(14),
            'avatar_url' => null,
            'status' => UserStatus::Active,
            'social_links' => [
                'linkedin' => 'https://linkedin.com',
                'twitter' => 'https://x.com',
            ],
            'last_login_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }
}
