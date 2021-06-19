FROM php:7.4-alpine

RUN apk update

RUN apk add git
RUN apk add apache2 php7-apache2 php7-mbstring php7-json php-curl
RUN apk add php7-xml php7-simplexml php7-session php7-tokenizer php7-openssl

COPY . /var/www/html

COPY ./process/apache_process /var/www/html/apache_process
COPY ./process/polling_process /var/www/html/polling_process
COPY ./start_processes.sh /var/www/html/start_processes.sh


ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

RUN chown -R www-data:www-data /var/www/html
RUN chmod +x /var/www/html/start_processes.sh
RUN chmod +x /var/www/html/polling_process
RUN chmod +x /var/www/html/apache_process
RUN chmod -R 777 /var/www/html/storage

RUN sed -i '/LoadModule rewrite_module/s/^#//g' /etc/apache2/httpd.conf
RUN sed -i '/LoadModule session_module/s/^#//g' /etc/apache2/httpd.conf

RUN sed -ri -e 's!/var/www/localhost/htdocs!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/httpd.conf
RUN sed -i 's/AllowOverride\ None/AllowOverride\ All/g' /etc/apache2/httpd.conf

RUN rm  -rf /tmp/* /var/cache/apk/*

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /var/www/html

RUN cp .env.example /var/www/html/.env

RUN composer install

VOLUME /var/www/html/storage

EXPOSE 80

CMD sh start_processes.sh

