#!/usr/bin/env bash
# Prochaine version cible sur develop après une release (tag v* sur main).
# Ex. v1.2.0 → 1.3.0
# Usage : VER=$(./deploy/next-develop-version.sh v1.2.0) && echo "$VER"
set -euo pipefail

tag="${1:?tag requis (ex. v1.2.0)}"
ver="${tag#v}"
IFS=. read -r major minor patch <<< "$ver"
major=${major:-0}
minor=${minor:-0}

echo "${major}.$((minor + 1)).0"
