FROM --platform=linux/amd64 ubuntu:22.04

# This dockerfile is built only with local testing in mind
# It is NOT for production use as many packages are outdated
# and things are not properly configured.

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install PHP 8.0 and basic extensions from Ubuntu packages
# We use Ubuntu 22.04 native packages because PHP 8.0 is EOL but Ubuntu still patches security flaws
RUN apt-get update \
    && apt-get install -y \
        curl \
        ca-certificates \
        zip \
        unzip \
        git \
        libcap2-bin \
        libpng-dev \
    && apt-get install -y php8.0 php8.0-cli php8.0-dev \
       php8.0-bcmath \
       php8.0-curl \
       php8.0-dba \
       php8.0-gd \
       php8.0-gmp \
       php8.0-imap \
       php8.0-intl \
       php8.0-ldap \
       php8.0-mbstring \
       php8.0-mysql \
       php8.0-pgsql \
       php8.0-readline \
       php8.0-soap \
       php8.0-sqlite3 \
       php8.0-tidy \
       php8.0-xml \
       php8.0-zip \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy shared configuration and scripts
COPY shared/ /docker-shared/
RUN chmod +x /docker-shared/*.sh

# Install development libraries for PECL extensions
RUN /docker-shared/install-dev-libs.sh

# Install extensions from PECL
RUN /docker-shared/install-pecl-extensions.sh 8.0

# Install Composer
RUN /docker-shared/install-composer.sh

# Configure PHP for Laravel and enable extensions
COPY shared/php-laravel.ini /etc/php/8.0/cli/conf.d/99-laravel.ini
COPY shared/php-extensions.ini /etc/php/8.0/cli/conf.d/98-extensions.ini

# Set environment variables for Laravel logging
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=debug

# Set up Laravel directories and permissions
RUN /docker-shared/setup-laravel-dirs.sh

WORKDIR /var/www/html
EXPOSE 8000

# Start PHP's built in web server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
