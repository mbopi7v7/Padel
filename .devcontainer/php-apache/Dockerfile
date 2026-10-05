FROM php:8.1-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    curl \
    libpq-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    mysqli \
    gd \
    zip \
    mbstring \
    xml \
    pdo_mysql \
    pdo_pgsql \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite

# Definir ServerName para evitar warnings en Apache
RUN echo "ServerName  sz33-UNAE2026-dw2" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

EXPOSE 80

