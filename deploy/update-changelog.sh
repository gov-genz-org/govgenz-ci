#!/usr/bin/env bash
# Ajoute une section release dans CHANGELOG.md (Keep a Changelog).
# Usage : ./deploy/update-changelog.sh v1.2.0 [minor|patch] [commit-sha]
set -euo pipefail

TAG="${1:?tag requis (ex. v1.2.0)}"
BUMP="${2:-minor}"
REF="${3:-HEAD}"
CHANGELOG="${CHANGELOG:-CHANGELOG.md}"

ver="${TAG#v}"
date=$(date -u +%Y-%m-%d)

if [[ ! -f "$CHANGELOG" ]]; then
  cat >"$CHANGELOG" <<'EOF'
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

EOF
fi

if grep -qF "## [${ver}]" "$CHANGELOG"; then
  echo "CHANGELOG : section [${ver}] déjà présente"
  exit 0
fi

prev=$(
  git tag -l 'v[0-9]*.[0-9]*.[0-9]*' --sort=-v:refname 2>/dev/null \
    | grep -v "^${TAG}$" | head -n1 || true
)

if [[ -n "$prev" ]]; then
  range="${prev}..${REF}"
else
  range="${REF}"
fi

log=$(
  git log "$range" --pretty=format:'- %s (%h)' --no-merges 2>/dev/null || true
)
if [[ -z "$log" ]]; then
  log=$(
    git log "$range" --pretty=format:'- %s (%h)' --first-parent 2>/dev/null || true
  )
fi
if [[ -z "$log" ]]; then
  log="- _No commits listed._"
fi

kind="Release"
if [[ "$BUMP" == "patch" ]]; then
  kind="Hotfix"
fi

section_file=$(mktemp)
{
  printf '\n## [%s] - %s\n\n' "$ver" "$date"
  printf '### %s\n\n' "$kind"
  printf '%s\n' "$log"
} >"$section_file"

export CHANGELOG section_file ver
python3 <<'PY'
import os
import pathlib

changelog = pathlib.Path(os.environ["CHANGELOG"])
section = pathlib.Path(os.environ["section_file"]).read_text()
ver = os.environ["ver"]
text = changelog.read_text()
marker = "## [Unreleased]\n"
if f"## [{ver}]" in text:
    raise SystemExit(0)
if marker not in text:
    raise SystemExit(f"Missing {marker.strip()} in {changelog}")
changelog.write_text(text.replace(marker, marker + section + "\n", 1))
PY

rm -f "$section_file"
echo "CHANGELOG : section [${ver}] ajoutée"
