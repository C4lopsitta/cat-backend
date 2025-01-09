FROM php:8.2-fpm

RUN docker-php-ext-install pdo
COPY app/ /var/www/html/
WORKDIR /var/www/html/

EXPOSE 9000
