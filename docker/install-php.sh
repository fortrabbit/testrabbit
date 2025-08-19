#!/bin/bash
set -e

PHP_VERSION=${1:-"8.2"}
echo "Installing PHP ${PHP_VERSION}..."

add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y \
    php$PHP_VERSION \
    php$PHP_VERSION-apcu \
    php$PHP_VERSION-bcmath \
    php$PHP_VERSION-cli \
    php$PHP_VERSION-curl \
    php$PHP_VERSION-dba \
    php$PHP_VERSION-dev \
    php$PHP_VERSION-gd \
    php$PHP_VERSION-gmp \
    php$PHP_VERSION-igbinary \
    php$PHP_VERSION-imagick \
    php$PHP_VERSION-imap \
    php$PHP_VERSION-intl \
    php$PHP_VERSION-ldap \
    php$PHP_VERSION-mbstring \
    php$PHP_VERSION-memcached \
    php$PHP_VERSION-mongodb \
    php$PHP_VERSION-mysql \
    php$PHP_VERSION-pgsql \
    php$PHP_VERSION-readline \
    php$PHP_VERSION-redis \
    php$PHP_VERSION-soap \
    php$PHP_VERSION-sqlite3 \
    php$PHP_VERSION-ssh2 \
    php$PHP_VERSION-tidy \
    php$PHP_VERSION-xml \
    php$PHP_VERSION-yaml \
    php$PHP_VERSION-zip

apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

echo "PHP ${PHP_VERSION} installation completed successfully"
