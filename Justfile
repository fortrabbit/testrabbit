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

# Smoke test: hit the MySQL feature test on every PHP version and assert success.
# testrabbit is a zero-dependency plain-PHP app (IN-1649) — no composer install
# step, so the containers just serve the source directly on 7.4 → 8.5.
test:
    #!/usr/bin/env bash
    set -e
    for port in 8074 8080 8081 8082 8083 8084 8085; do
        echo "--- Testing on port $port ---"
        response=$(curl -fsSL "http://localhost:$port/tests/MySQL")

        # Print the JSON structure without the message
        echo "$response" | jq 'del(.message)'

        # The actual test to verify success
        echo "$response" | grep -q '"success":true'
    done
    echo "All tests passed!"

clean:
    rm -f public/imagick/tmp/img.* public/imagick/tmp/perf.*
    rm -f public/gd/tmp/img.*
