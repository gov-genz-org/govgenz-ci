#!/usr/bin/env bash
# Prochain tag semver vMAJOR.MINOR.PATCH.
#   minor (défaut) : minor +1, patch → 0  (ex. v1.0.0 → v1.1.0)
#   patch          : patch +1             (ex. v1.1.0 → v1.1.1)
# Usage : TAG=$(./deploy/next-release-tag.sh [minor|patch]) && echo "$TAG"
set -euo pipefail

mode="${1:-minor}"
if [[ "$mode" != "minor" && "$mode" != "patch" ]]; then
  echo "Usage: $0 [minor|patch]" >&2
  exit 1
fi

latest=$(
  git tag -l 'v[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname 2>/dev/null | head -n1 || true
)

if [[ -z "$latest" ]]; then
  echo 'v1.0.0'
  exit 0
fi

ver="${latest#v}"
IFS=. read -r major minor patch <<< "$ver"
major=${major:-0}
minor=${minor:-0}
patch=${patch:-0}

if [[ "$mode" == "patch" ]]; then
  echo "v${major}.${minor}.$((patch + 1))"
else
  echo "v${major}.$((minor + 1)).0"
fi
