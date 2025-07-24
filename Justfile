default: start

start: install
    docker-compose --progress=plain up -d

stop:
    docker-compose down

build:
    #!/usr/bin/env bash
    if [ -z "$GITHUB_ACTIONS" ]; then
        DOCKER_BUILDKIT=1 docker-compose --progress=plain build
    fi

install: build
    #!/usr/bin/env bash
    if [ -n "$GITHUB_ACTIONS" ]; then
        docker-compose run --rm php74 bash -c '\
          composer config -g github-oauth.github.com ${GITHUB_AUTH} && \
          composer install --no-interaction --prefer-dist --no-scripts --no-cache'
    else
        docker-compose run --rm php74 composer install
    fi

update: build
    docker-compose run --rm php74 composer update



clean:
    rm -f public/imagick/tmp/img.*
