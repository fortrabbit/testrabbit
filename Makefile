PHP_VERSION ?= "8.0"
DOCKER_USER ?= 1000

test: install

build:
ifndef GITHUB_ACTIONS
	DOCKER_BUILDKIT=1 docker-compose build --build-arg PHP_VERSION=${PHP_VERSION}
endif

install: build
ifdef GITHUB_ACTIONS
	docker-compose run --user=${DOCKER_USER} --rm php bash -c '\
	  composer config -g github-oauth.github.com ${GITHUB_AUTH} && \
	  composer install --no-interaction --prefer-dist --no-scripts --no-cache'
else
	docker-compose run --rm php composer install
endif

update: build
	docker-compose run --rm php composer update

up: build
	docker-compose up
