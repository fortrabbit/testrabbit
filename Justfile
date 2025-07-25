default: start

start: build
    docker-compose --progress=plain up -d --remove-orphans

stop:
    docker-compose down

build:
    #!/usr/bin/env bash
    if [ -z "$GITHUB_ACTIONS" ]; then
        docker-compose --progress=plain build
    fi

#install: build
#    #!/usr/bin/env bash
#    rm composer.lock
#    if [ -n "$GITHUB_ACTIONS" ]; then
#        docker-compose run --rm php81 bash -c '\
#          composer config -g github-oauth.github.com ${GITHUB_AUTH} && \
#          composer install --no-interaction --prefer-dist --no-scripts --no-cache'
#    else
#        docker-compose run --rm php81 composer install
#    fi

test:
    #!/usr/bin/env bash
    set -e
    for port in 8074 8080 8081 8082 8083 8084; do
        echo "--- Testing on port $port ---"
        response=$(curl -fsSL "http://localhost:$port/tests/MySQL")

        # Print the JSON structure without the message
        echo "$response" | jq 'del(.message)'

        # The actual test to verify success
        echo "$response" | grep -q '"success":true'
    done
    echo "All tests passed!"

clean:
    rm -f public/imagick/tmp/img.*
    rm -f public/gd/tmp/img.*
