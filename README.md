# Plataforma de Empregos PIE - API Backend

Esta é a API REST que serve de motor para a Plataforma de Empregos PIE. Foi desenvolvida utilizando o framework **Laravel** e fornece todos os serviços de autenticação, gestão de vagas e candidaturas.

## 🛠️ Tecnologias e Dependências

* **Framework Principal:** Laravel 11+ (PHP 8.2+)
* **Autenticação:** JSON Web Tokens (JWT) via tymon/jwt-auth
* **Base de Dados:** MySQL
* **Ambiente Local:** Docker & Docker Compose

## 🚀 Funcionalidades da API

* **Autenticação:** Registo de Candidatos, Registo de Empresas, Login unificado com emissão de token JWT e Gestão de sessão.
* **Gestão de Vagas:** Criação, listagem, edição e remoção de vagas de emprego.
* **Candidaturas:** Submissão de candidaturas por parte dos candidatos e visualização de inscritos por vaga (restrito a empresas).

## 🔧 Instalação e Configuração Local

Pré-requisitos: Docker e Docker Compose instalados (recomendado) ou ambiente PHP/Composer local.

Passo 1: Configurar as Variáveis de Ambiente
Copie o ficheiro de exemplo e configure os acessos (as configurações padrão já estão preparadas para o Docker):
    cp .env.example .env

Passo 2: Instalar Dependências do Composer
Instale os pacotes necessários do PHP:
    composer install

Passo 3: Subir o Ambiente Docker
Inicie os contentores em segundo plano (servidor web e base de dados MySQL):
    docker-compose up -d

Passo 4: Gerar as Chaves de Segurança
Gere a chave da aplicação Laravel e a assinatura secreta do JWT:
    php artisan key:generate
    php artisan jwt:secret

Passo 5: Preparar a Base de Dados
Execute as migrações para criar as tabelas e o seeder para popular os dados iniciais:
    php artisan migrate --seed

Passo 6: Expor o storage local
Crie o link simbólico para servir ficheiros guardados no storage público local do Laravel:
    php artisan storage:link

A API ficará disponível por padrão em: http://localhost:8000

## 📂 Estrutura de Pastas Chave

* app/Http/Controllers/Api/: Controladores que gerem as requisições HTTP e as respostas JSON.
* app/Models/: Modelos de dados Eloquent (User, Vacancy, VacancyApplication).
* app/Repositories/Api/: Camada de repositório isolando a lógica de consultas à base de dados.
* routes/api.php: Ficheiro centralizador das rotas da API.