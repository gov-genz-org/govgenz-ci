#!/usr/bin/env bash
# Détecte le bump semver depuis le message du commit HEAD sur main :
#   minor — merge develop → main (release normale)
#   patch — merge fix/* ou hotfix/* → main (correction prod)
# Usage : BUMP=$(./deploy/detect-release-bump.sh)   # minor | patch
set -euo pipefail

msg=$(git log -1 --pretty=%B)
first_line=$(printf '%s\n' "$msg" | head -n1)

# Merge PR depuis fix/* ou hotfix/* (hotfix prod)
if echo "$msg" | grep -qiE 'Merge pull request #[0-9]+ from [^ ]+/(fix|hotfix)/'; then
  echo patch
  exit 0
fi

# Squash sur main : fix: / hotfix: uniquement (pas fix(ci): ni fix(admin) scope tooling)
if echo "$first_line" | grep -qiE '^(fix|hotfix):'; then
  echo patch
  exit 0
fi

# Merge PR develop → main (release normale)
if echo "$msg" | grep -qiE 'Merge pull request #[0-9]+ from [^ ]+/develop'; then
  echo minor
  exit 0
fi

# Défaut : release normale (typiquement develop → main)
echo minor
