FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    librabbitmq-dev \
    && docker-php-ext-install pcntl sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-req=ext-amqp

COPY . .
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data var/
