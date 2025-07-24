#!/bin/bash
set -e

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

echo "Development libraries installation completed successfully"
