<?php

namespace Tests\Feature\App\PostoTrabalho;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostoTrabalhoStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_store_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posto-trabalho.store'), [
            'nome' => 'Posto de Trabalho '.fake()->company,
            'uuid' => fake()->uuid,
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('posto-trabalho.index');
    }
}
