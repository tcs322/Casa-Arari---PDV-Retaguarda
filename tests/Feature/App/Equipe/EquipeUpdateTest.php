<?php

namespace Tests\Feature\App\Equipe;

use App\Enums\SituacaoEquipeEnum;
use App\Models\Equipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipeUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_update_is_working_with_correct_params(): void
    {
        $user = User::factory()->create();
        $equipe = Equipe::factory()->create();

        $response = $this->actingAs($user)->put(route('equipe.update', [
            'uuid' => $equipe->uuid
        ]), [
            'nome' => fake()->name,
            'situacao' => SituacaoEquipeEnum::getRandomValue()
        ]);

        $response->assertStatus(302); // redirected
        $response->assertRedirectToRoute('equipe.index');
    }
}
