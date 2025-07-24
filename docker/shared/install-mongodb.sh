#!/bin/bash
set -e

# Install PECL extensions with version-specific handling
# Usage: ./install-pecl-extensions.sh <php_version>

PHP_VERSION=${1:-"8.2"}
echo "Installing mongodb extension for PHP ${PHP_VERSION}..."

if [[ "$PHP_VERSION" == "7.4" || "$PHP_VERSION" == "8.0" ]]; then
    MONGODB_VERSION="mongodb-1.20.1"
    echo "Using MongoDB extension version 1.20.1 for PHP 7.4 and 8.0 compatibility"
else
    MONGODB_VERSION="mongodb"
    echo "Using latest MongoDB extension version for PHP ${PHP_VERSION}"
fi

pecl install ${MONGODB_VERSION}
