#!/usr/bin/env bash
#
# Boot smoke test: verify a running testrabbit instance serves its pages and the
# JSON test contract on whatever PHP image is under test. It asserts the app
# RUNS and RESPONDS — not that every platform feature is present (a given image
# may legitimately lack imagick, mongodb, etc.).
#
# Usage: scripts/smoke.sh http://localhost:8000

set -euo pipefail

BASE="${1:-http://localhost:8000}"

# Wait for the server to come up (max ~20s).
for i in $(seq 1 40); do
    if curl -fsS -o /dev/null "$BASE/"; then
        break
    fi
    sleep 0.5
    if [ "$i" -eq 40 ]; then
        echo "Server did not come up at $BASE"
        exit 1
    fi
done

echo "--- GET / ---"
home="$(curl -fsS "$BASE/")"
case "$home" in
    *"<title>TestRabbit</title>"*) echo "homepage OK" ;;
    *) echo "homepage did not contain the expected title"; exit 1 ;;
esac

echo "--- GET /tests/Extension (valid JSON contract) ---"
resp="$(curl -fsS "$BASE/tests/Extension")"
echo "$resp" | jq -e 'has("success") and has("message")' >/dev/null
echo "test JSON contract OK"

echo "--- GET /php-errors/emit?type=info (200 JSON) ---"
emit="$(curl -fsS "$BASE/php-errors/emit?type=info")"
echo "$emit" | jq -e '.status == "logged"' >/dev/null
echo "emit OK"

echo "Smoke test passed for $BASE"
