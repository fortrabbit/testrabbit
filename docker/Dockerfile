# This dockerfile is built only with local testing in mind
# It is NOT for production use as many packages are outdated
# and things are not properly configured.

FROM --platform=linux/amd64 ubuntu:24.04

ARG PHP_VERSION=8.2

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && apt-get install -y \
        ca-certificates \
        curl \
        git \
        libcap2-bin \
        libpng-dev \
        software-properties-common \
        unzip \
        zip \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP with extensions and tools
COPY docker/ /docker-scripts/
RUN chmod +x /docker-scripts/*.sh
RUN /docker-scripts/install-php.sh $PHP_VERSION
RUN /docker-scripts/install-pecl.sh $PHP_VERSION
RUN /docker-scripts/install-composer.sh

# Copy source code
WORKDIR /var/www/html
COPY ./ /var/www/html/

# Configure for Laravel
RUN cp /docker-scripts/php-laravel.ini /etc/php/$PHP_VERSION/cli/conf.d/99-laravel.ini
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=debug
RUN /docker-scripts/setup-laravel-dirs.sh

# Run composer install
RUN composer install --no-interaction --prefer-dist --no-scripts --no-cache

# Use PHP's built in web server. It is single-threaded by default, so one slow
# request (e.g. a faithful 504 that sleeps) would block the whole server and
# freeze the site. PHP_CLI_SERVER_WORKERS spawns multiple worker processes so
# concurrent requests — like a series of 503/504 error tests — are handled in
# parallel, closer to the FPM setup on the real platform.
ENV PHP_CLI_SERVER_WORKERS=5
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
