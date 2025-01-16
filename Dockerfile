FROM php:8.2-fpm

RUN docker-php-ext-install pdo
COPY app/ /var/www/html/
WORKDIR /var/www/html/

RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/* \

RUN pecl install redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

EXPOSE 9000
