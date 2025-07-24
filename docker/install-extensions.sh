#!/bin/bash
set -e

# Install PECL extensions with version-specific handling
# Usage: ./install-extensions.sh <php_version>

PHP_VERSION=${1:-"8.2"}
echo "Installing PECL extensions for PHP ${PHP_VERSION}..."

echo "Installing development libraries for PECL extensions..."
apt-get update
apt-get install -y \
    php-pear \
    libssl-dev \
    libmagickwand-dev \
    libmemcached-dev \
    libz-dev \
    pkg-config \
    libgpgme-dev \
    libssh2-1-dev \
    libyaml-dev
apt-get -y autoremove
apt-get clean
rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

echo "Installing core extensions..."
pecl install apcu
pecl install imagick
pecl install memcached

echo "Installing additional extensions..."
pecl install gnupg
pecl install igbinary
pecl install oauth
pecl install redis
pecl install ssh2
pecl install yaml

echo "PECL extensions installation completed successfully"
