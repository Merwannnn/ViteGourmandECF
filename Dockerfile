FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        zip \
        gd \
    && pecl install mongodb-1.21.4 \
    && docker-php-ext-enable mongodb \
    && php -r "echo 'MongoDB extension: '.phpversion('mongodb').PHP_EOL;" \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php bin/console cache:clear --env=prod

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80