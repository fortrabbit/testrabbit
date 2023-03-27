PHP_VERSION ?= "7.4"

test: install

build:
ifndef GITHUB_ACTIONS
	DOCKER_BUILDKIT=1 docker-compose build --build-arg PHP_VERSION=${PHP_VERSION}
endif

install: build
ifdef GITHUB_ACTIONS
	docker-compose run --rm php bash -c '\
	  composer config -g github-oauth.github.com ${GITHUB_AUTH} && \
	  composer install --no-interaction --prefer-dist --no-scripts --no-cache'
else
	docker-compose run --rm php composer install
endif

update: build
	docker-compose run --rm php composer update

up: build
	docker-compose up
down:
	docker-compose down

clean:
	rm -f public/imagick/tmp/img.*


### wip...
html2text = html2text -nobs -style pretty -width 120
visit_mongo:
	curl -s -D- http://localhost/mongodb | $(html2text)

do_mongo:
	sudo su -s /bin/bash www-data -c 'php /T/fortrabbit/testrabbit/do/mango.php'
