#!/usr/bin/env bash
# Couverture PHPUnit via le conteneur govgenz-local (PCOV).
set -euo pipefail

LOCAL_DIR="$(cd "$(dirname "$0")/../.." && pwd)/govgenz-local"
if [[ ! -f "${LOCAL_DIR}/docker-compose.yml" ]]; then
  echo "Répertoire govgenz-local introuvable : ${LOCAL_DIR}" >&2
  exit 1
fi

cd "${LOCAL_DIR}"
docker compose exec web bash -lc '
  cd /var/www/html
  mkdir -p build/coverage
  vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-text --coverage-clover build/coverage/clover.xml
  echo ""
  echo "Rapport HTML : build/coverage/html/index.html"
  echo "Clover     : build/coverage/clover.xml"
'
