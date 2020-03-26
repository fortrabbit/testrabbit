FROM php:7.4-apache

RUN a2enmod rewrite

RUN printf "\n" | pecl install apcu && docker-php-ext-enable apcu

ADD apache.conf /etc/apache2/sites-enabled/000-default.conf

RUN mkdir -p /srv/app/usx-testing/ && chown -R www-data:www-data /srv
WORKDIR /srv/app/usx-testing/
