<?php

namespace Tests\Feature\App\Servidor;

use App\Enums\SituacaoCargoEnum;
use App\Models\Cargo;
use App\Models\Equipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServidorStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_store_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $cargo = Cargo::factory()->create();
        $equipe = Equipe::factory()->create();

        $response = $this->actingAs($user)->post(route('servidor.store'), [
            'nome' => fake()->name,
            'cargo_uuid' => $cargo->uuid,
            'equipe_uuid' => $equipe->uuid,
            'email' => fake()->email,
            'data_nascimento' => Carbon::now()->addDecades(-2),
            'data_admissao' => Carbon::now(),
            'matricula' => fake()->randomNumber(5)
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('servidor.index');
    }
}
