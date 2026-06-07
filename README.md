# Plataforma de Empregos PIE - Backend

API REST da plataforma de empregos. Este guia e voltado para desenvolvedores que precisam entender a estrutura do codigo, subir o ambiente localmente e contribuir com seguranca.

## Objetivo do projeto

O backend centraliza:

- autenticacao com JWT;
- cadastro de candidatos e empresas;
- gestao de vagas;
- candidaturas em vagas;
- atualizacao de perfil e download de curriculos.

## Stack

- PHP `^8.2`
- Laravel `^12`
- JWT via `php-open-source-saver/jwt-auth`
- PostgreSQL no fluxo Docker de desenvolvimento
- Vite para assets do Laravel
- PHPUnit para testes

## Estrutura do codigo

As pastas mais importantes para quem vai contribuir sao:

- `app/Http/Controllers/Api/`: recebe a requisicao HTTP e devolve a resposta JSON.
- `app/Http/Requests/`: valida payloads de entrada antes da regra de negocio.
- `app/Repositories/Api/`: concentra a logica de negocio e acesso aos modelos.
- `app/Models/`: modelos Eloquent como `User`, `Vacancy` e `VacancyApplication`.
- `routes/api/`: separa as rotas da API por dominio.
- `database/migrations/`: evolucao do schema.
- `database/seeders/`: carga inicial de dados.
- `tests/Feature/`: cobertura dos fluxos principais da API.

## Organizacao das rotas

O arquivo `routes/api.php` carrega automaticamente todos os arquivos dentro de `routes/api/`.

Principais grupos:

- `routes/api/auth.php`
  - `POST /auth/login`
  - `POST /auth/register/seeker`
  - `POST /auth/register/business`
  - `POST /auth/logout`
  - `POST /auth/refresh`
  - `GET /auth/me`
  - `POST /auth/profile`
  - `GET /auth/resume`
- `routes/api/vacancies.php`
  - `GET /vacancies`
  - `POST /vacancies`
  - `GET /vacancies/{vacancyId}`
  - `PUT /vacancies/{vacancyId}`
  - `POST /vacancies/{vacancyId}/apply`
  - `PATCH /vacancies/{vacancyId}/close`
  - `GET /vacancies/published`
  - `GET /vacancies/applicants`
  - `GET /vacancies/applications/me`
  - `GET /vacancies/applications/{applicationId}/resume`

## Fluxo interno esperado

Ao implementar uma funcionalidade nova, siga o padrao ja presente no projeto:

1. adicionar ou ajustar a rota em `routes/api/...`;
2. criar um `FormRequest` em `app/Http/Requests/...` quando houver validacao;
3. delegar a logica do controller para um repositorio em `app/Repositories/Api/...`;
4. atualizar ou criar migrations, models e seeders se houver impacto em dados;
5. cobrir o comportamento com teste em `tests/Feature/`.

Esse padrao mantem controller enxuto, validacao centralizada e regra de negocio fora da camada HTTP.

## Pre-requisitos

Voce pode rodar o projeto de duas formas.

### Opcao 1: com Docker

- Docker
- Docker Compose

### Opcao 2: ambiente local

- PHP 8.2+
- Composer
- Node.js
- npm
- PostgreSQL

## Configuracao do ambiente

Na raiz do backend:

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan jwt:secret
```

Se for usar banco local, ajuste as variaveis de conexao no `.env`.

## Subindo o projeto

### Desenvolvimento com Docker

O arquivo `docker-compose.dev.yml` sobe:

- API Laravel em `http://localhost:8000`
- PostgreSQL em `localhost:5432`

Comandos:

```bash
docker compose -f docker-compose.dev.yml up -d
php artisan migrate
php artisan storage:link
```

Observacao: o servico `api` do compose de desenvolvimento ja executa migrations no boot antes de iniciar o servidor.

### Desenvolvimento sem Docker

```bash
php artisan migrate
php artisan storage:link
composer run dev
```

O script `composer run dev` inicia:

- servidor Laravel;
- listener de fila;
- visualizacao de logs com `pail`;
- Vite em modo desenvolvimento.

## Banco e dados iniciais

Para aplicar migrations e popular dados iniciais:

```bash
php artisan migrate --seed
```

Os seeders ficam em `database/seeders/`.

## Convencoes para contribuicao

- mantenha regras de negocio fora dos controllers;
- use `FormRequest` para validacao de entrada;
- preserve o envelope de resposta JSON ja adotado pelos controllers base;
- adicione migration para qualquer mudanca estrutural no banco;
- inclua ou atualize testes de feature quando alterar comportamento da API.

## Problemas comuns

- `401 Unauthorized`: confira `JWT_SECRET`, login e envio do token Bearer.
- erro de banco ao subir com Docker: verifique se o PostgreSQL terminou o healthcheck.
- upload/download de curriculo falhando: execute `php artisan storage:link` e revise o disco configurado.

## Como contribuir

Fluxo sugerido:

1. crie uma branch para a alteracao;
2. implemente a mudanca seguindo o padrao de rota + request + repository + teste;
3. rode `composer test`;
4. revise impacto em migrations, seeders e variaveis de ambiente;
5. abra a contribuicao com contexto suficiente para reproducao e validacao.
