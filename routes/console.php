<?php

use App\Models\User;
use Database\Seeders\AccountDataSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('accounts:seed-data {account : ID ou e-mail da conta} {--vacancies=5 : Quantidade de vagas a criar} {--applications=3 : Quantidade de candidaturas a criar}', function (string $account) {
    $user = is_numeric($account)
        ? User::query()->find((int) $account)
        : User::query()->where('email', $account)->first();

    if (! $user) {
        $this->error("Conta [{$account}] nao encontrada.");
        return 1;
    }

    config([
        'seeding.account_id' => $user->id,
        'seeding.vacancies_count' => max(1, (int) $this->option('vacancies')),
        'seeding.applications_count' => max(1, (int) $this->option('applications')),
    ]);

    $exitCode = Artisan::call('db:seed', [
        '--class' => AccountDataSeeder::class,
        '--no-interaction' => true,
    ]);

    $output = trim(Artisan::output());

    if ($output !== '') {
        $this->line($output);
    }

    if ($exitCode !== 0) {
        $this->error('Falha ao executar o seeder de dados da conta.');
        return $exitCode;
    }

    $this->info("Dados de demonstração criados para {$user->email} ({$user->role}).");

    return 0;
})->purpose('Executa um seeder para popular dados de uma conta especifica de candidato ou empresa.');
