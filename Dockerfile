FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Configuration Apache Symfony
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

# Extensions PHP nécessaires
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
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie du projet
COPY . .

# Fichier .env temporaire uniquement pour le build Docker
# Les vraies valeurs viendront de Render au démarrage
RUN echo "APP_ENV=prod" > .env \
    && echo "APP_DEBUG=0" >> .env \
    && echo "APP_SECRET=dummy_secret_for_build" >> .env \
    && echo "DATABASE_URL=mysql://dummy:dummy@127.0.0.1:3306/dummy" >> .env

# Installation PHP
# Les scripts Symfony sont désactivés volontairement
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Génération des assets Symfony
RUN php bin/console asset-map:compile --env=prod

# Nettoyage cache de build
RUN rm -rf var/cache/* \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

EXPOSE 80

# Au démarrage Render possède les vraies variables d'environnement
CMD ["bash", "-c", "php bin/console cache:clear --env=prod && chown -R www-data:www-data var && apache2-foreground"]