#!/usr/bin/env bash
# Invalide le cache CSS/JS front — sans PHP (local, CI, ou SSH sur le serveur).
#
# Usage :
#   ./deploy/bump-asset-version.sh
#   ./deploy/bump-asset-version.sh a1b2c3d
#
# Écrit writable/deploy_version.txt dans le dépôt ; incluez ce fichier dans votre
# déploiement si vous synchronisez writable/. Sinon, le mtime des CSS/JS suffit.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/writable/deploy_version.txt"

if [[ $# -ge 1 ]]; then
  VERSION="$1"
else
  if command -v git >/dev/null 2>&1 && git -C "$ROOT" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    VERSION="$(git -C "$ROOT" rev-parse --short HEAD)"
  else
    VERSION="$(date -u +%Y%m%d%H%M%S)"
  fi
fi

printf '%s\n' "$VERSION" >"$OUT"
echo "Front asset version: $VERSION"
echo "Fichier: $OUT"
