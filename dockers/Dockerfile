FROM php:8-alpine
COPY . /home/app
WORKDIR /home/app

RUN apk add --update linux-headers

RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS}
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN pecl install redis
RUN docker-php-ext-enable redis
RUN apk del pcre-dev ${PHPIZE_DEPS}

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN composer install

