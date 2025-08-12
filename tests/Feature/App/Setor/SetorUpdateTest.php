<?php

namespace Tests\Feature\App\Setor;

use App\Models\PostoTrabalho;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SetorUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_update_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $postoTrabalho = PostoTrabalho::factory()->create();
        $setor = Setor::factory()->create();

        $response = $this->actingAs($user)->put(route('setor.update', [
            'uuid' => $setor->uuid
        ]), [
            'nome' => 'Setor '.fake()->company,
            'postos_trabalho_uuid' => $postoTrabalho->uuid,
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('setor.index');
    }
}
