FROM --platform=linux/amd64 ubuntu:24.04

# This dockerfile is built only with local testing in mind
# It is NOT for production use as many packages are outdated
# and things are not properly configured.

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install PHP 8.2 and basic extensions from Ubuntu packages
# Ubuntu 24.04 includes PHP 8.2 in its repositories along with PHP 8.3
RUN apt-get update \
    && apt-get install -y curl ca-certificates zip unzip git libcap2-bin libpng-dev \
    && apt-get install -y php8.2 php8.2-cli php8.2-dev \
       php8.2-bcmath \
       php8.2-curl \
       php8.2-dba \
       php8.2-gd \
       php8.2-gmp \
       php8.2-imap \
       php8.2-intl \
       php8.2-ldap \
       php8.2-mbstring \
       php8.2-mysql \
       php8.2-pgsql \
       php8.2-readline \
       php8.2-soap \
       php8.2-sqlite3 \
       php8.2-tidy \
       php8.2-xml \
       php8.2-zip \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy shared configuration and scripts
COPY shared/ /docker-shared/
RUN chmod +x /docker-shared/*.sh

# Install development libraries for PECL extensions
RUN /docker-shared/install-dev-libs.sh

# Install extensions from PECL
RUN /docker-shared/install-pecl-extensions.sh 8.2

# Install Composer
RUN /docker-shared/install-composer.sh

# Configure PHP for Laravel and enable extensions
COPY shared/php-laravel.ini /etc/php/8.2/cli/conf.d/99-laravel.ini
COPY shared/php-extensions.ini /etc/php/8.2/cli/conf.d/98-extensions.ini

# Set environment variables for Laravel logging
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=debug

# Set up Laravel directories and permissions
RUN /docker-shared/setup-laravel-dirs.sh

WORKDIR /var/www/html
EXPOSE 8000

# Start PHP's built in web server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
