<?php

namespace Tests\Feature\App\Departamento;

use App\Models\Departamento;
use App\Models\PostoTrabalho;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepartamentoStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_store_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $postoTrabalho = PostoTrabalho::factory()->create();
        $setor = Setor::factory()->create();

        $response = $this->actingAs($user)->post(route('departamento.store'), [
            'nome' => fake()->company,
            'postos_trabalho_uuid' => $postoTrabalho->uuid,
            'setores_uuid' => $setor->uuid,
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('departamento.index');
    }
}
