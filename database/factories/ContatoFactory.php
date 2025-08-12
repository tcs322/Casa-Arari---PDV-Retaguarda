<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contato>
 */
class ContatoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'descricao' => $this->faker->text('100'),
            'telefone_comercial' => $this->faker->phoneNumber(),
            'telefone_residencial' => $this->faker->phoneNumber(),
            'celular_pessoal' => $this->faker->phoneNumber(),
            'celular_comercial' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'cliente_uuid' => Cliente::inRandomOrder()->first()->uuid,
        ];
    }
}
