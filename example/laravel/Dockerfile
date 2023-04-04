FROM php:8.1-fpm-alpine

RUN apk update

RUN apk add git
RUN apk add zip
RUN apk add unzip
RUN apk add php-mbstring php-json php-curl
RUN apk add php-xml

COPY . /var/www

RUN chmod -R 777 /var/www/storage

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /var/www

RUN cp .env.example /var/www/.env

RUN composer install

EXPOSE 80

