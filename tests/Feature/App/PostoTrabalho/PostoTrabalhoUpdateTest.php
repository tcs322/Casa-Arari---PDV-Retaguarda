<?php

namespace Tests\Feature\App\PostoTrabalho;

use App\Models\PostoTrabalho;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostoTrabalhoUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_update_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $postoTrabalho = PostoTrabalho::factory()->create();

        $response = $this->actingAs($user)->put(route('posto-trabalho.update', [
            'uuid' => $postoTrabalho->uuid
        ]), [
            'nome' => 'Posto de Trabalho '.fake()->company,
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('posto-trabalho.index');
    }
}
