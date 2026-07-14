#!/bin/bash
#
# Install the commercial / third-party PHP extensions the fortrabbit platform
# provides — blackfire, newrelic, phalcon — so the Extension test can also pass
# in the local image. These come from vendor apt repos, not ondrej/php.
#
# Best-effort: each install is guarded with `|| true` so a vendor-repo hiccup on
# a given PHP version doesn't break the whole (non-production) test image — the
# Extension test would simply still report that one as missing.
#
# Usage: ./install-vendor-extensions.sh <php_version>

set -e

PHP_VERSION=${1:-"8.4"}
echo "Installing vendor extensions (blackfire, newrelic, phalcon) for PHP ${PHP_VERSION}..."

# gpg is needed to dearmor the vendor signing keys.
apt-get update
apt-get install -y gnupg

# --- Phalcon (from ondrej/php; Phalcon 5 needs ext-psr) ---
apt-get install -y php${PHP_VERSION}-psr php${PHP_VERSION}-phalcon || true

# --- Blackfire probe ---
curl -fsSL https://packages.blackfire.io/gpg.key \
    | gpg --dearmor -o /usr/share/keyrings/blackfire-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/blackfire-archive-keyring.gpg] http://packages.blackfire.io/debian any main" \
    > /etc/apt/sources.list.d/blackfire.list
apt-get update
apt-get install -y blackfire-php || true

# --- New Relic PHP agent ---
curl -fsSL https://download.newrelic.com/548C16BF.gpg \
    | gpg --dearmor -o /usr/share/keyrings/newrelic-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/newrelic-archive-keyring.gpg] https://apt.newrelic.com/debian/ newrelic non-free" \
    > /etc/apt/sources.list.d/newrelic.list
apt-get update
NR_INSTALL_SILENT=1 apt-get install -y newrelic-php5 || true
# Enables and configures the extension .so (no license key needed just to load it).
NR_INSTALL_SILENT=1 newrelic-install install || true

apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

echo "Vendor extension installation completed"
