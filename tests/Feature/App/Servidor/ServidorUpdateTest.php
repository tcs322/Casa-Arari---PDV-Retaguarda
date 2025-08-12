<?php

namespace Tests\Feature\App\Servidor;

use App\Models\Cargo;
use App\Models\Equipe;
use App\Models\Servidor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServidorUpdateTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_if_update_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $cargo = Cargo::factory()->create();
        $equipe = Equipe::factory()->create();
        $servidor = Servidor::factory()->create();

        $response = $this->actingAs($user)->put(route('servidor.update', [
            'servidor' => $servidor->uuid
        ]), [
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
