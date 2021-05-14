FROM php:7.4-alpine

RUN apk update

RUN apk add apache2 php7-apache2 php7-mbstring php7-json php-curl
RUN apk add php7-xml php7-simplexml php7-session php7-tokenizer php7-openssl

COPY . /var/www/html

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_DOCUMENT_ROOT=/var/www/html/example/public
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 777 /var/www/html/example/storage

RUN sed -i '/LoadModule rewrite_module/s/^#//g' /etc/apache2/httpd.conf
RUN sed -i '/LoadModule session_module/s/^#//g' /etc/apache2/httpd.conf

RUN sed -ri -e 's!/var/www/localhost/htdocs!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/httpd.conf
RUN sed -i 's/AllowOverride\ None/AllowOverride\ All/g' /etc/apache2/httpd.conf

RUN rm  -rf /tmp/* /var/cache/apk/*

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /var/www/html

RUN cp example/.env.example /var/www/html/example/.env

RUN composer install

RUN cd example && composer install

VOLUME /var/www/html/example/storage

EXPOSE 80

CMD  /usr/sbin/httpd -DFOREGROUND

