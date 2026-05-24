#!/usr/bin/env bash
# Vérifie qu’un dossier release/ (artifact CI) est complet avant envoi FTP.
# Usage : ./deploy/verify-release.sh [chemin/vers/release]

set -euo pipefail

root="${1:-.}"
root="${root%/}"

must_exist() {
  if [[ ! -e "${root}/${1}" ]]; then
    echo "verify-release: manquant — ${1}" >&2
    exit 1
  fi
}

must_file() {
  if [[ ! -f "${root}/${1}" ]]; then
    echo "verify-release: fichier manquant — ${1}" >&2
    exit 1
  fi
}

must_exist app
must_file app/Helpers/cms_helper.php
must_file app/Helpers/admin_helper.php
must_exist public
must_exist vendor
must_exist writable
must_exist deploy
must_file vendor/autoload.php
must_file index.php
must_file spark
must_file composer.json
must_file public/index.php
must_file public/.htaccess
must_file .htaccess
must_file public/assets/css/govgenz-tokens.css
must_file public/assets/css/govgenz-components.css
must_file public/assets/css/govgenz-template.css
must_file public/assets/css/govgenz-front-pages.css
must_file public/js/front/govgenz-template.js
must_file public/assets/img/govgenz-logo.svg

for dir in cache logs session debugbar uploads backups; do
  must_exist "writable/${dir}"
  must_file "writable/${dir}/index.html"
done

css_count="$(find "${root}/public/assets/css" -type f -name '*.css' 2>/dev/null | wc -l | tr -d ' ')"
js_count="$(find "${root}/public/js" -type f -name '*.js' 2>/dev/null | wc -l | tr -d ' ')"
file_count="$(find "${root}" -type f 2>/dev/null | wc -l | tr -d ' ')"

if [[ "${css_count}" -lt 10 ]]; then
  echo "verify-release: trop peu de CSS dans public/assets/css (${css_count})" >&2
  exit 1
fi

if [[ "${js_count}" -lt 5 ]]; then
  echo "verify-release: trop peu de JS dans public/js (${js_count})" >&2
  exit 1
fi

if [[ "${file_count}" -lt 500 ]]; then
  echo "verify-release: trop peu de fichiers au total (${file_count}, attendu ≥ 500 avec vendor/)" >&2
  exit 1
fi

echo "verify-release: OK"
echo "  fichiers totaux : ${file_count}"
echo "  public CSS      : ${css_count}"
echo "  public JS       : ${js_count}"
du -sh "${root}/vendor" "${root}/public" "${root}/app" "${root}/writable" 2>/dev/null || true
