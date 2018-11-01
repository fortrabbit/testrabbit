FROM php:7.2-apache

RUN a2enmod rewrite

RUN printf "\n" | pecl install apcu && docker-php-ext-enable apcu

ADD apache.conf /etc/apache2/sites-enabled/000-default.conf
