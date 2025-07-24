#!/bin/bash
set -e

# Install Composer with version pinning for reproducible builds
COMPOSER_VERSION=2.8.10
COMPOSER_SHA256=28dbb6bd8bef31479c7985b774c130a8bda37dbe63c35b56f6cb6bc377427573
curl -sS https://getcomposer.org/download/$COMPOSER_VERSION/composer.phar -o /usr/local/bin/composer
echo "$COMPOSER_SHA256  /usr/local/bin/composer" | sha256sum --check
chmod +x /usr/local/bin/composer

echo "Composer installation completed successfully"
