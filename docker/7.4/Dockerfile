# This dockerfile is built only with local testing in mind
# It is NOT for production use as many packages are outdated
# and things are not properly configured.
FROM --platform=linux/amd64 ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install PHP 7.4 and basic extensions from Ubuntu packages
# We do this because Ubuntu still patched security flaws in 2025
# https://ubuntu.com/security/notices/USN-7400-1
RUN apt-get update \
    && apt-get install -y \
        ca-certificates \
        curl \
        git \
        libcap2-bin \
        libpng-dev \
        unzip \
        zip \
    && apt-get install -y php7.4 php7.4-cli php7.4-dev \
       php7.4-bcmath \
       php7.4-curl \
       php7.4-dba \
       php7.4-gd \
       php7.4-gmp \
       php7.4-imap \
       php7.4-intl \
       php7.4-ldap \
       php7.4-mbstring \
       php7.4-mysql \
       php7.4-pgsql \
       php7.4-readline \
       php7.4-soap \
       php7.4-sqlite3 \
       php7.4-tidy \
       php7.4-xml \
       php7.4-zip \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy shared configuration and scripts
COPY shared/ /docker-shared/
RUN chmod +x /docker-shared/*.sh

# Install Composer
RUN /docker-shared/install-composer.sh

# Install development libraries for PECL extensions
RUN /docker-shared/install-dev-libs.sh

# Install extensions from PECL
RUN /docker-shared/install-extensions.sh 7.4

# Install mongodb (slow to build, so kept in separate docker cache layer)
RUN /docker-shared/install-mongodb.sh 7.4

# Enable extensions
RUN cp /docker-shared/php-extensions.ini /etc/php/7.4/cli/conf.d/98-extensions.ini

# Configure for Laravel
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=debug
RUN /docker-shared/setup-laravel-dirs.sh \
&&  cp /docker-shared/php-laravel.ini /etc/php/7.4/cli/conf.d/99-laravel.ini

WORKDIR /var/www/html

# Use PHP's built in web server
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
