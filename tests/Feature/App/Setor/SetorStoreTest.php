<?php

namespace Tests\Feature\App\Setor;

use App\Models\PostoTrabalho;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SetorStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_store_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $postoTrabalho = PostoTrabalho::factory()->create();

        $response = $this->actingAs($user)->post(route('setor.store'), [
            'nome' => 'Setor '.fake()->company,
            'uuid' => fake()->uuid,
            'postos_trabalho_uuid' => $postoTrabalho->uuid,
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('setor.index');
    }
}
