<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class AccountDataSeeder extends Seeder
{
    public function run(): void
    {
        $accountId = (int) config('seeding.account_id');
        $vacanciesCount = max(1, (int) config('seeding.vacancies_count', 5));
        $applicationsCount = max(1, (int) config('seeding.applications_count', 3));

        if ($accountId <= 0) {
            throw new InvalidArgumentException('The seeding.account_id config value is required.');
        }

        $account = User::query()->findOrFail($accountId);

        if ($account->role === 'business') {
            $this->seedBusinessAccount($account, $vacanciesCount, $applicationsCount);
            return;
        }

        if ($account->role === 'candidate') {
            $this->seedCandidateAccount($account, $applicationsCount, $vacanciesCount);
            return;
        }

        throw new InvalidArgumentException("Unsupported account role [{$account->role}].");
    }

    private function seedBusinessAccount(User $business, int $vacanciesCount, int $applicationsPerVacancy): void
    {
        $candidates = $this->ensureCandidatePool(max($applicationsPerVacancy, 4));

        for ($index = 0; $index < $vacanciesCount; $index++) {
            $status = $index % 4 === 3 ? 'draft' : 'published';
            $vacancy = Vacancy::query()->create([
                'business_id' => $business->id,
                ...$this->buildVacancyPayload($index, $status),
            ]);

            if ($status !== 'published') {
                continue;
            }

            $selectedCandidates = $candidates
                ->shuffle()
                ->take(min($applicationsPerVacancy, $candidates->count()))
                ->values();

            foreach ($selectedCandidates as $candidateIndex => $candidate) {
                VacancyApplication::query()->firstOrCreate(
                    [
                        'vacancy_id' => $vacancy->id,
                        'candidate_id' => $candidate->id,
                    ],
                    [
                        'phone' => $candidate->phone ?: $this->fakePhone($candidateIndex + 1),
                        'portfolio_url' => sprintf('https://portfolio.example.com/candidato-%d', $candidate->id),
                        'cover_letter' => sprintf(
                            'Tenho interesse nesta oportunidade na %s e acredito que minha experiencia pode contribuir com a vaga de %s.',
                            $business->company_name ?: $business->name,
                            $vacancy->title
                        ),
                        'status' => 'applied',
                        'applied_at' => Carbon::now()->subDays($candidateIndex + 1),
                    ]
                );
            }
        }
    }

    private function seedCandidateAccount(User $candidate, int $applicationsCount, int $supportVacanciesCount): void
    {
        $availableVacancies = Vacancy::query()
            ->where('status', 'published')
            ->where('business_id', '!=', $candidate->id)
            ->get();

        if ($availableVacancies->count() < $applicationsCount) {
            $businesses = $this->ensureBusinessPool(max(2, $supportVacanciesCount));

            foreach ($businesses as $index => $business) {
                Vacancy::query()->create([
                    'business_id' => $business->id,
                    ...$this->buildVacancyPayload($index + 20, 'published'),
                ]);
            }

            $availableVacancies = Vacancy::query()
                ->where('status', 'published')
                ->where('business_id', '!=', $candidate->id)
                ->get();
        }

        $alreadyAppliedIds = VacancyApplication::query()
            ->where('candidate_id', $candidate->id)
            ->pluck('vacancy_id');

        $targetVacancies = $availableVacancies
            ->whereNotIn('id', $alreadyAppliedIds)
            ->shuffle()
            ->take($applicationsCount)
            ->values();

        foreach ($targetVacancies as $index => $vacancy) {
            VacancyApplication::query()->firstOrCreate(
                [
                    'vacancy_id' => $vacancy->id,
                    'candidate_id' => $candidate->id,
                ],
                [
                    'phone' => $candidate->phone ?: $this->fakePhone($candidate->id),
                    'portfolio_url' => sprintf('https://portfolio.example.com/%s', $candidate->id),
                    'cover_letter' => sprintf(
                        'Estou me candidatando a vaga de %s porque tenho interesse em crescer profissionalmente e contribuir com resultados consistentes.',
                        $vacancy->title
                    ),
                    'status' => 'applied',
                    'applied_at' => Carbon::now()->subDays($index + 1),
                ]
            );
        }
    }

    private function ensureCandidatePool(int $count): Collection
    {
        return collect(range(1, $count))->map(function (int $index) {
            return User::query()->firstOrCreate(
                ['email' => sprintf('seed.candidate.%d@example.com', $index)],
                [
                    'role' => 'candidate',
                    'name' => sprintf('Candidato Seed %d', $index),
                    'phone' => $this->fakePhone($index),
                    'password' => Hash::make('password'),
                ]
            );
        });
    }

    private function ensureBusinessPool(int $count): Collection
    {
        return collect(range(1, $count))->map(function (int $index) {
            return User::query()->firstOrCreate(
                ['email' => sprintf('seed.business.%d@example.com', $index)],
                [
                    'role' => 'business',
                    'name' => sprintf('Empresa Seed %d', $index),
                    'company_name' => sprintf('Empresa Seed %d', $index),
                    'cnpj' => $this->fakeCnpj($index),
                    'website' => sprintf('https://empresa-seed-%d.example.com', $index),
                    'password' => Hash::make('password'),
                ]
            );
        });
    }

    private function buildVacancyPayload(int $index, string $status): array
    {
        $titles = [
            'Desenvolvedor Full Stack',
            'Analista de Suporte',
            'Designer de Produto',
            'Pessoa Estagiaria em Desenvolvimento',
            'Analista de Dados',
            'Especialista em Infraestrutura',
        ];
        $employmentTypes = ['clt', 'pj', 'estagio', 'temporario'];
        $locations = [
            'Ituiutaba, MG',
            'Remoto',
            'Hibrido - Ituiutaba, MG',
            'Uberlandia, MG',
        ];
        $salaryRanges = [
            [2500, 3500],
            [3500, 5000],
            [5000, 7000],
            [7000, 9500],
            [1200, 1800],
        ];
        $descriptions = [
            'Atuacao em time multidisciplinar com foco em entregas continuas, qualidade de software e colaboracao proxima com stakeholders.',
            'Responsavel por apoiar a operacao, identificar melhorias e contribuir com evolucao de processos e rotinas da area.',
            'Participacao ativa no planejamento, desenvolvimento e sustentacao de funcionalidades voltadas a experiencia do usuario.',
        ];
        $requirements = [
            "Boa comunicacao\nExperiencia com trabalho em equipe\nOrganizacao e senso de prioridade",
            "Conhecimento tecnico compativel com a funcao\nProatividade\nDisponibilidade para aprender continuamente",
            "Capacidade analitica\nCompromisso com prazos\nBoa escrita e documentacao",
        ];

        [$salaryMin, $salaryMax] = $salaryRanges[$index % count($salaryRanges)];
        $publishedAt = $status === 'published' ? Carbon::now()->subDays(($index % 7) + 1) : null;

        return [
            'title' => $titles[$index % count($titles)],
            'employment_type' => $employmentTypes[$index % count($employmentTypes)],
            'location' => $locations[$index % count($locations)],
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'description' => $descriptions[$index % count($descriptions)],
            'requirements' => $requirements[$index % count($requirements)],
            'status' => $status,
            'published_at' => $publishedAt,
        ];
    }

    private function fakePhone(int $index): string
    {
        return sprintf('(34) 9%04d-%04d', 1000 + $index, 2000 + $index);
    }

    private function fakeCnpj(int $index): string
    {
        return sprintf('99.999.999/%04d-%02d', 1000 + $index, 10 + ($index % 89));
    }
}
