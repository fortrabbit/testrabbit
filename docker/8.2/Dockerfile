FROM --platform=linux/amd64 ubuntu:20.04

ARG WWWGROUP
ARG NODE_VERSION=16

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys E5267A6C \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C300EE8C \
    && echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu focal main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.2-cli php8.2-dev \
       php8.2-pgsql php8.2-sqlite3 php8.2-gd \
       php8.2-curl \
       php8.2-imap php8.2-mysql php8.2-mbstring \
       php8.2-xml php8.2-zip php8.2-bcmath php8.2-soap \
       php8.2-intl php8.2-readline \
       php8.2-ldap \
       php8.2-msgpack php8.2-igbinary php8.2-redis php8.2-swoole \
       php8.2-memcached php8.2-pcov php8.2-xdebug \
       php8.2-apcu php8.2-mongodb php8.2-imagick \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
#    && curl -sL https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - \
#    && apt-get install -y nodejs \
#    && npm install -g npm \
#    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
#    && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
#    && apt-get update \
#    && apt-get install -y yarn \
#    && apt-get install -y mysql-client \
#    && apt-get install -y postgresql-client \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.2

#RUN groupadd --force -g $WWWGROUP sail
#RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

#COPY start-container /usr/local/bin/start-container
#COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
#COPY php.ini /etc/php/8.2/cli/conf.d/99-sail.ini
#RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

#ENTRYPOINT ["start-container"]
