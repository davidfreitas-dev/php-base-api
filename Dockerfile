FROM php:8.3-fpm

RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    zip unzip curl git libzip-dev libonig-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libxml2-dev libssl-dev nano vim \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring zip opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
