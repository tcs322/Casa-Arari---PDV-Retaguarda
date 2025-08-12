<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
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
            'nome' => $this->faker->name(),
            'cpf_cnpj' => $this->faker->numerify('##.###.###/0001-##'),
            'endereco' => $this->faker->address(),
            'cep' => $this->faker->postcode(),
            'cidade' => $this->faker->city(),
            'uf' => 'PA',
            'numero' => 22,
            'complemento' => $this->faker->streetAddress(),
            'email' => $this->faker->email(),
            'site' => $this->faker->url(),
        ];
    }
}
