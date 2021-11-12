FROM php:7.4-apache
ENV DEBIAN_FRONTEND=noninteractive
RUN a2enmod rewrite
ADD apache.conf /etc/apache2/sites-enabled/000-default.conf

RUN mkdir -p /srv/app/usx-testing/ && chown -R www-data:www-data /srv
WORKDIR /srv/app/usx-testing/


RUN apt-get update
RUN apt-get install -q -y imagemagick libmagickwand-dev
RUN apt-get install -q -y libmemcached-dev



RUN yes '' | pecl install apcu-5.1.18     && docker-php-ext-enable apcu
RUN echo 'apt-get install -y --no-install-recommends $@' > /usr/local/bin/ai; chmod +x /usr/local/bin/ai
RUN echo 'apt-cache search $@'                           > /usr/local/bin/as; chmod +x /usr/local/bin/as
RUN apt-get install -q -y libgeoip-dev
RUN yes '' | pecl install geoip-1.1.1     && docker-php-ext-enable geoip



RUN yes '' | pecl install gnupg-1.4.0     && docker-php-ext-enable gnupg
RUN yes '' | pecl install igbinary-3.1.2  && docker-php-ext-enable igbinary
RUN yes '' | pecl install imagick-3.4.3   && docker-php-ext-enable imagick
RUN yes '' | pecl install libsodium-1.0.7 && docker-php-ext-enable libsodium
RUN yes '' | pecl install memcached-3.1.5 && docker-php-ext-enable memcached
RUN yes '' | pecl install mongodb-17.4    && docker-php-ext-enable mongodb
RUN yes '' | pecl install oauth-2.0.5     && docker-php-ext-enable oauth
RUN yes '' | pecl install redis-5.2.1     && docker-php-ext-enable redis
RUN yes '' | pecl install ssh2-1.2        && docker-php-ext-enable ssh2
RUN yes '' | pecl install yaml-2.0.4      && docker-php-ext-enable yaml
