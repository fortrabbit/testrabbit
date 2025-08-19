#!/bin/bash
set -e

# Install any remaining PHP extensions that aren't available through APT packages
# Usage: ./install-pecl.sh <php_version>

PHP_VERSION=${1:-"8.2"}
echo "Installing additional extensions for PHP ${PHP_VERSION}..."

# Install any dependencies needed for PECL extensions
apt-get update
apt-get install -y \
    libgpgme-dev \
    pkg-config \
    php-pear

# Create a single ini file for all PECL extensions
PECL_INI_FILE="/etc/php/$PHP_VERSION/cli/conf.d/98-pecl.ini"
echo "; Extensions installed via PECL" > $PECL_INI_FILE

# Install extensions only if not already available as packages
if ! dpkg -l | grep -q "php$PHP_VERSION-oauth"; then
    pecl install oauth
    echo "extension=oauth.so" >> $PECL_INI_FILE
fi

if ! dpkg -l | grep -q "php$PHP_VERSION-gnupg"; then
    pecl install gnupg
    echo "extension=gnupg.so" >> $PECL_INI_FILE
fi

# Clean up
apt-get -y autoremove
apt-get clean
rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

echo "Extensions installation completed successfully"
