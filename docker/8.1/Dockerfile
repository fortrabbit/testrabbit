FROM --platform=linux/amd64 ubuntu:24.04

# This dockerfile is built only with local testing in mind
# It is NOT for production use as many packages are outdated
# and things are not properly configured.

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install PHP 8.1 and basic extensions from Ubuntu packages
# Ubuntu 24.04 has PHP 8.1 available in universe repository
RUN apt-get update \
    && apt-get install -y curl ca-certificates zip unzip git libcap2-bin libpng-dev \
    && apt-get install -y php8.1 php8.1-cli php8.1-dev \
       php8.1-bcmath \
       php8.1-curl \
       php8.1-dba \
       php8.1-gd \
       php8.1-gmp \
       php8.1-imap \
       php8.1-intl \
       php8.1-ldap \
       php8.1-mbstring \
       php8.1-mysql \
       php8.1-pgsql \
       php8.1-readline \
       php8.1-soap \
       php8.1-sqlite3 \
       php8.1-tidy \
       php8.1-xml \
       php8.1-zip \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy shared configuration and scripts
COPY shared/ /docker-shared/
RUN chmod +x /docker-shared/*.sh

# Install development libraries for PECL extensions
RUN /docker-shared/install-dev-libs.sh

# Install extensions from PECL
RUN /docker-shared/install-pecl-extensions.sh 8.1

# Install Composer
RUN /docker-shared/install-composer.sh

# Configure PHP for Laravel and enable extensions
COPY shared/php-laravel.ini /etc/php/8.1/cli/conf.d/99-laravel.ini
COPY shared/php-extensions.ini /etc/php/8.1/cli/conf.d/98-extensions.ini

# Set environment variables for Laravel logging
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=debug

# Set up Laravel directories and permissions
RUN /docker-shared/setup-laravel-dirs.sh

WORKDIR /var/www/html
EXPOSE 8000

# Start PHP's built in web server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
