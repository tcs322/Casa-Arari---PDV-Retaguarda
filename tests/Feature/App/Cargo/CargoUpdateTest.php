<?php

namespace Tests\Feature\App\Cargo;

use App\Enums\SituacaoCargoEnum;
use App\Models\Cargo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CargoUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_update_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $cargo = Cargo::factory()->create();

        $response = $this->actingAs($user)->put(route('cargo.update', [
            'uuid' => $cargo->uuid
        ]), [
            'nome' => fake()->name,
            'situacao' => SituacaoCargoEnum::getRandomValue()
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('cargo.index');
    }
}
