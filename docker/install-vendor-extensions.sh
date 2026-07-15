#!/bin/bash
#
# Install the commercial / third-party PHP extensions the fortrabbit platform
# provides — phalcon, blackfire, newrelic — so the Extension test can also pass
# in the local image.
#
# BEST-EFFORT BY DESIGN: this is a non-production test image, and these vendor
# sources are fragile (New Relic's Debian repo still uses a legacy dsa1024 key
# that modern apt rejects, Phalcon isn't always packaged, etc.). Every step is
# guarded so a single broken source only skips that one extension — it must
# NEVER fail the image build. The script always exits 0.
#
# Usage: ./install-vendor-extensions.sh <php_version>

PHP_VERSION=${1:-"8.4"}
echo "Installing vendor extensions (best-effort) for PHP ${PHP_VERSION}..."

apt-get update || true
apt-get install -y gnupg ca-certificates || true

# --- Phalcon: official packagecloud repo (prebuilt; needs ext-psr) ---
(
    set -e
    curl -fsSL https://packagecloud.io/install/repositories/phalcon/stable/script.deb.sh | bash
    apt-get install -y "php${PHP_VERSION}-psr" "php${PHP_VERSION}-phalcon"
) || echo "phalcon: skipped"

# --- Blackfire probe (modern signed repo) ---
(
    set -e
    curl -fsSL https://packages.blackfire.io/gpg.key \
        | gpg --dearmor -o /usr/share/keyrings/blackfire-archive-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/blackfire-archive-keyring.gpg] http://packages.blackfire.io/debian any main" \
        > /etc/apt/sources.list.d/blackfire.list
    apt-get update
    apt-get install -y blackfire-php
) || echo "blackfire: skipped"

# --- New Relic PHP agent via tarball (avoids their apt repo's legacy key) ---
(
    set -e
    cd /tmp
    curl -fsSL "https://download.newrelic.com/php_agent/release/newrelic-php5-latest-linux.tar.gz" -o newrelic.tar.gz
    tar -xzf newrelic.tar.gz
    cd newrelic-php5-*-linux
    # Installs the extension .so + newrelic.ini for every detected PHP (no
    # license key needed just to load the extension).
    NR_INSTALL_SILENT=1 ./newrelic-install install
) || echo "newrelic: skipped"

apt-get clean || true
rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* || true

echo "Vendor extension step done (best-effort)"
exit 0
