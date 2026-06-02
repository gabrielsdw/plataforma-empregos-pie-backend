FROM php:8.4-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    cron \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    postgresql-client \
    zip \
    unzip

# Instalar extensões PHP necessárias para Laravel
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Instalar extensão Redis usada pelo driver phpredis
RUN mkdir -p /usr/src/php/ext/redis && \
    curl -L https://github.com/phpredis/phpredis/archive/refs/tags/6.1.0.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 && \
    echo 'redis' >> /usr/src/php-available-exts && \
    docker-php-ext-install redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar working directory
WORKDIR /var/www

# Copiar apenas composer.json e composer.lock primeiro (para aproveitar cache do Docker)
COPY composer.json composer.lock ./

# Instalar dependências do Composer (sem rodar scripts que precisam do código completo)
RUN composer install --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Agora copiar todo o código da aplicação
COPY . /var/www

# Criar diretório de logs se não existir
RUN mkdir -p /var/www/storage/logs

# Corrigir permissões de storage e bootstrap/cache para www-data
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Finalizar a instalação do Composer com autoload e scripts
RUN composer dump-autoload --optimize

# Copiar configurações customizadas do PHP
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini 