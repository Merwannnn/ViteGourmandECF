FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

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
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN echo "APP_ENV=prod" > .env
RUN echo "APP_DEBUG=0" >> .env

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN php bin/console importmap:install --env=prod

RUN php bin/console cache:clear --env=prod

RUN php bin/console cache:warmup --env=prod

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["bash", "-c", "echo APP_ENV=$APP_ENV && echo APP_DEBUG=$APP_DEBUG && apache2-foreground"]