#!/bin/bash
set -e

# Install PECL extensions with version-specific handling
# Usage: ./install-pecl-extensions.sh <php_version>

PHP_VERSION=${1:-"8.2"}
echo "Installing PECL extensions for PHP ${PHP_VERSION}..."

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
