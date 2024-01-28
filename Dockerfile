FROM php:8.1-apache

RUN a2enmod rewrite \
    && a2enmod headers \
    && service apache2 restart \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && apt-get update && apt-get install -y \
        zip \
        unzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
