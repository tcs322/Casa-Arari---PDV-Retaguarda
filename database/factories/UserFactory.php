<?php

namespace Database\Factories;

use App\Enums\SituacaoUsuarioEnum;
use App\Enums\TipoUsuarioEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
     /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'name' => fake()->name(),
            'email' => 'dev@dev.com',
            'password' => Hash::make('secret'),
            'role' => TipoUsuarioEnum::ADMIN(),
            'must_change_password' => false,
            'situacao' => 1
        ];
    }
}
