<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Compra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parcela>
 */
class ParcelaFactory extends Factory
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
            'numero' => $this->faker->numberBetween(1, 60),
            'quantidade_total_parcelas' => $this->faker->numberBetween(50, 60),
            'quantidade_real_parcelas' => $this->faker->numberBetween(30, 50),
            'compra_uuid' => Compra::inRandomOrder()->first()->uuid,
            'cliente_uuid' => Cliente::inRandomOrder()->first()->uuid,
        ];
    }
}
