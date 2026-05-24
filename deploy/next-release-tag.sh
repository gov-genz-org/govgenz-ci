#!/usr/bin/env bash
# Prochain tag semver vMAJOR.MINOR.PATCH (patch +1, ou v1.0.0 si aucun tag).
# Usage : TAG=$(./deploy/next-release-tag.sh) && echo "$TAG"
set -euo pipefail

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

echo "v${major}.${minor}.$((patch + 1))"
