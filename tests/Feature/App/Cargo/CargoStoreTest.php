<?php

namespace Tests\Feature\App\Cargo;

use App\Enums\SituacaoCargoEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CargoStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_store_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('cargo.store'), [
            'nome' => fake()->name,
            'situacao' => SituacaoCargoEnum::getRandomValue()
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('cargo.index');
    }
}
