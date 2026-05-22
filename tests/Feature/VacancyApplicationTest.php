<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_candidate_can_apply_to_published_vacancy(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
            'company_name' => 'Tech Solutions',
        ]);
        $candidate = User::factory()->create([
            'role' => 'candidate',
        ]);
        $vacancy = Vacancy::query()->create([
            'business_id' => $business->id,
            'title' => 'Desenvolvedor Backend',
            'employment_type' => 'clt',
            'location' => 'Remoto',
            'description' => 'Atuar na evolucao da API principal da plataforma.',
            'requirements' => 'PHP, Laravel e experiencia com APIs REST.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $token = auth('api')->login($candidate);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/vacancies/{$vacancy->id}/apply", [
                'phone' => '(11) 98765-4321',
                'portfolio_url' => 'https://portfolio.example.com/ana',
                'cover_letter' => 'Tenho experiencia com APIs robustas e quero contribuir com o time.',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.vacancy.id', $vacancy->id)
            ->assertJsonPath('data.candidate.id', $candidate->id)
            ->assertJsonPath('data.phone', '(11) 98765-4321')
            ->assertJsonPath('data.status', 'applied');

        $this->assertDatabaseHas('vacancy_applications', [
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
            'phone' => '(11) 98765-4321',
            'status' => 'applied',
        ]);
    }

    public function test_candidate_cannot_apply_twice_to_same_vacancy(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
            'company_name' => 'Tech Solutions',
        ]);
        $candidate = User::factory()->create([
            'role' => 'candidate',
        ]);
        $vacancy = Vacancy::query()->create([
            'business_id' => $business->id,
            'title' => 'Desenvolvedor Backend',
            'employment_type' => 'clt',
            'location' => 'Remoto',
            'description' => 'Atuar na evolucao da API principal da plataforma.',
            'requirements' => 'PHP, Laravel e experiencia com APIs REST.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $candidate->vacancyApplications()->create([
            'vacancy_id' => $vacancy->id,
            'phone' => '(11) 99999-9999',
            'cover_letter' => 'Carta inicial com mais de vinte caracteres.',
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $token = auth('api')->login($candidate);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/vacancies/{$vacancy->id}/apply", [
                'phone' => '(11) 98765-4321',
                'cover_letter' => 'Nova carta de apresentacao com mais de vinte caracteres.',
            ]);

        $response
            ->assertConflict()
            ->assertJsonPath('message', 'You have already applied to this vacancy');
    }

    public function test_authenticated_business_can_view_applicants_from_owned_vacancies(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
            'company_name' => 'Tech Solutions',
        ]);
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'Maria Silva',
        ]);
        $vacancy = Vacancy::query()->create([
            'business_id' => $business->id,
            'title' => 'Desenvolvedor Backend',
            'employment_type' => 'clt',
            'location' => 'Remoto',
            'description' => 'Atuar na evolucao da API principal da plataforma.',
            'requirements' => 'PHP, Laravel e experiencia com APIs REST.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $candidate->vacancyApplications()->create([
            'vacancy_id' => $vacancy->id,
            'phone' => '(11) 98765-4321',
            'portfolio_url' => 'https://portfolio.example.com/maria',
            'cover_letter' => 'Quero participar deste processo porque meu perfil se encaixa bem.',
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $token = auth('api')->login($business);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/vacancies/applicants');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.candidate.name', 'Maria Silva')
            ->assertJsonPath('data.0.vacancy.title', 'Desenvolvedor Backend')
            ->assertJsonPath('data.0.portfolio_url', 'https://portfolio.example.com/maria');
    }

    public function test_authenticated_candidate_can_view_own_applications(): void
    {
        $business = User::factory()->create([
            'role' => 'business',
            'company_name' => 'Tech Solutions',
        ]);
        $candidate = User::factory()->create([
            'role' => 'candidate',
        ]);
        $vacancy = Vacancy::query()->create([
            'business_id' => $business->id,
            'title' => 'Desenvolvedor Backend',
            'employment_type' => 'clt',
            'location' => 'Remoto',
            'description' => 'Atuar na evolucao da API principal da plataforma.',
            'requirements' => 'PHP, Laravel e experiencia com APIs REST.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $candidate->vacancyApplications()->create([
            'vacancy_id' => $vacancy->id,
            'phone' => '(11) 98765-4321',
            'cover_letter' => 'Carta de apresentacao com detalhes sobre a experiencia do candidato.',
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        $token = auth('api')->login($candidate);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/vacancies/applications/me');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.vacancy.title', 'Desenvolvedor Backend')
            ->assertJsonPath('data.0.phone', '(11) 98765-4321');
    }
}
