<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_business_can_publish_vacancy(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
            'company_name' => 'Tech Solutions',
        ]);

        $token = auth('api')->login($business);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/vacancies', [
                'title' => 'Desenvolvedor Front-end Senior',
                'employment_type' => 'clt',
                'location' => 'Sao Paulo, SP',
                'salary_min' => 5000,
                'salary_max' => 8000,
                'description' => 'Construir interfaces para o portal de vagas.',
                'requirements' => 'React, TypeScript e boas praticas de UI.',
                'status' => 'published',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Desenvolvedor Front-end Senior')
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('vacancies', [
            'title' => 'Desenvolvedor Front-end Senior',
            'business_id' => $business->id,
            'status' => 'published',
        ]);
    }

    public function test_candidate_cannot_publish_vacancy(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
        ]);

        $token = auth('api')->login($candidate);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/vacancies', [
                'title' => 'Desenvolvedor Front-end Senior',
                'employment_type' => 'clt',
                'location' => 'Sao Paulo, SP',
                'description' => 'Construir interfaces para o portal de vagas.',
                'requirements' => 'React, TypeScript e boas praticas de UI.',
            ]);

        $response
            ->assertForbidden()
            ->assertJsonPath(
                'message',
                'Only authenticated businesses can publish vacancies'
            );
    }
}
