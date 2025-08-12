<?php

namespace Tests\Feature\App\Departamento;

use App\Models\Departamento;
use App\Models\PostoTrabalho;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepartamentoUpdateTest extends TestCase
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
        $departamento = Departamento::factory()->create();

        $response = $this->actingAs($user)->put(route('departamento.update', [
            'uuid' => $departamento->uuid
        ]), [
            'nome' => fake()->company,
            'postos_trabalho_uuid' => $postoTrabalho->uuid,
            'setores_uuid' => $setor->uuid,
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('departamento.index');
    }
}
