ARG PHP_VERSION="8.0"
FROM php:${PHP_VERSION}-apache-buster

RUN echo 'deb-src http://deb.debian.org/debian buster main' >> /etc/apt/sources.list
#RUN echo 'deb-src http://deb.debian.org/debian-security buster/updates main' >> /etc/apt/sources.list
#RUN echo 'deb-src http://deb.debian.org/debian buster-updates main' >> /etc/apt/sources.list
RUN echo 'APT::Get::Install-Recommends "false";' > /etc/apt/apt.conf.d/skip-suggestions
RUN echo 'apt-get install -yq -o=Dpkg::Use-Pty=0 "$@"' \
    > /usr/local/bin/ai ; chmod +x /usr/local/bin/ai
RUN apt-get update -q > /dev/null

RUN ai \
    git \
    gnupg \
    openssh-client \
    unzip \
    wget \
    imagemagick \
    libmagickwand-dev

# Install composer
ENV COMPOSER_VERSION=2.0.12
ENV COMPOSER_SHA256=82ea8c1537cfaceb7e56f6004c7ccdf99ddafce7237c07374d920e635730a631
RUN wget  -O /usr/local/bin/composer https://getcomposer.org/download/$COMPOSER_VERSION/composer.phar && \
    echo "$COMPOSER_SHA256  /usr/local/bin/composer" | sha256sum --check && \
    chmod +x /usr/local/bin/composer

# Image Magick version 7 has not been packaged for Ubuntu yet, so we build our own.
# Here using `mk-build-deps` to automatically install all build dependencies of imagemagick.
#RUN ai \
#    devscripts \
#    equivs \
#    libwebp-dev
#RUN mk-build-deps -t 'apt-get -o Debug::pkgProblemResolver=yes -y' -i imagemagick
#RUN mv imagemagick-build-deps*.deb /tmp
#ENV imagemagick_ver="7.0.10-62"
#RUN set -ex && mkdir -p /usr/src/imagick &&\
#    cd /usr/src/imagick &&\
#    wget -O imagemagick-$imagemagick_ver.tar.gz https://github.com/ImageMagick/ImageMagick/archive/$imagemagick_ver.tar.gz &&\
#    tar -xzf imagemagick-$imagemagick_ver.tar.gz
#RUN cd /usr/src/imagick/imagemagick-$imagemagick_ver &&\
#    ./configure --prefix=/opt/imagick --with-quantum-depth=8 --disable-hdri &&\
#    make &&\
#    make install &&\
#    ldconfig /opt/imagick # && rm -rf /usr/src/imagick

RUN docker-php-ext-install \
    pdo_mysql

RUN pecl install apcu &&\
    pecl install mongodb &&\
    docker-php-ext-enable apcu mongodb

# use github version for now until release from https://pecl.php.net/get/imagick is ready for PHP 8
RUN mkdir -p /usr/src/php/ext/imagick; \
    curl -fsSL https://github.com/Imagick/imagick/archive/06116aa24b76edaf6b1693198f79e6c295eda8a9.tar.gz \
        | tar xvz -C "/usr/src/php/ext/imagick" --strip 1; \
    docker-php-ext-install imagick

# Get the version of mysql client that we actually run in production
# https://geert.vanderkelen.org/2018/mysql8-unattended-dpkg/
#COPY docker/php/repo.mysql.com.gpg /tmp/mysql.gpg
#RUN apt-key add /tmp/mysql.gpg && \
#    rm /tmp/mysql.gpg && \
#    echo "deb http://repo.mysql.com/apt/debian stretch mysql-5.6" | \
#        tee /etc/apt/sources.list.d/mysql56.list && \
#    apt-get update && \
#    apt-get install -y mysql-client=5.6.* \
#    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure apache
RUN a2enmod rewrite
ADD docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

# Set up users that works on linux and in github actions
RUN groupadd user -g 1000 && \
    useradd -m -u 1000 -g 1000 user && \
    useradd -m -u 1001 -g 1000 runner && \
    mkdir -p /srv/app/testrabbit && \
    chown 1000:1000 /srv/app

USER user

WORKDIR /srv/app/testrabbit


ENV PATH "$PATH:vendor/bin:bin"

RUN mkdir ~/.ssh && ln -s /run/secrets/host_ssh_key ~/.ssh/id_rsa
