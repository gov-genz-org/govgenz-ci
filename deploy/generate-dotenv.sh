#!/usr/bin/env bash
# Génère un .env complet (écrase le fichier cible — pas d’ajout / fusion).
# Usage : APP_BASE_URL=... DATABASE_PASSWORD=... ./deploy/generate-dotenv.sh chemin/vers/.env

set -euo pipefail

out="${1:-.env}"

require() {
  local name="$1"
  if [[ -z "${!name:-}" ]]; then
    echo "generate-dotenv: variable obligatoire manquante : ${name}" >&2
    exit 1
  fi
}

require APP_BASE_URL
require DATABASE_NAME
require DATABASE_USERNAME
require DATABASE_PASSWORD
require ENCRYPTION_KEY

CI_ENVIRONMENT="${CI_ENVIRONMENT:-production}"
DATABASE_HOSTNAME="${DATABASE_HOSTNAME:-localhost}"
DATABASE_PORT="${DATABASE_PORT:-3306}"
APP_FORCE_HTTPS="${APP_FORCE_HTTPS:-true}"
SESSION_EXPIRATION="${SESSION_EXPIRATION:-7200}"
EMAIL_FROM="${EMAIL_FROM:-noreply@govgenz.org}"
EMAIL_FROM_NAME="${EMAIL_FROM_NAME:-Gov Gen Z}"
JOIN_NOTIFICATION_TO="${JOIN_NOTIFICATION_TO:-apps@govgenz.org}"
EMAIL_PROTOCOL="${EMAIL_PROTOCOL:-mail}"
STAFF_INVITE_EXPIRY_HOURS="${STAFF_INVITE_EXPIRY_HOURS:-24}"
ANALYTICS_ENABLED="${ANALYTICS_ENABLED:-true}"
ANALYTICS_GA4_ID="${ANALYTICS_GA4_ID:-}"
ANALYTICS_PRIVACY_SLUG="${ANALYTICS_PRIVACY_SLUG:-mentions-legales}"
PROJECTS_USE_PATH_PREFIX="${PROJECTS_USE_PATH_PREFIX:-true}"
POSITIONS_USE_PATH_PREFIX="${POSITIONS_USE_PATH_PREFIX:-true}"
PROJECTS_HOST="${PROJECTS_HOST:-}"
PROJECTS_BASE_URL="${PROJECTS_BASE_URL:-}"
POSITIONS_HOST="${POSITIONS_HOST:-}"
POSITIONS_BASE_URL="${POSITIONS_BASE_URL:-}"
APP_ASSET_VERSION="${APP_ASSET_VERSION:-}"

sq() {
  printf '%s' "$1" | sed "s/'/'\\\\''/g"
}

mkdir -p "$(dirname "$out")"
rm -f "$out"

tmp="$(mktemp)"
trap 'rm -f "$tmp"' EXIT

{
  echo "# Généré par GitHub Actions (deploy/generate-dotenv.sh) — ne pas fusionner avec un autre .env."
  echo "# Commit : ${GITHUB_SHA:-local}"
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# ENVIRONMENT"
  echo "#--------------------------------------------------------------------"
  echo ""
  echo "CI_ENVIRONMENT = ${CI_ENVIRONMENT}"
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# APP"
  echo "#--------------------------------------------------------------------"
  echo ""
  echo "app.baseURL = '$(sq "$APP_BASE_URL")'"
  echo "app.forceGlobalSecureRequests = ${APP_FORCE_HTTPS}"
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# DATABASE"
  echo "#--------------------------------------------------------------------"
  echo ""
  echo "database.default.hostname = $(sq "$DATABASE_HOSTNAME")"
  echo "database.default.database = $(sq "$DATABASE_NAME")"
  echo "database.default.username = $(sq "$DATABASE_USERNAME")"
  echo "database.default.password = '$(sq "$DATABASE_PASSWORD")'"
  echo "database.default.DBDriver = MySQLi"
  echo "database.default.port = ${DATABASE_PORT}"
  echo "database.default.charset = utf8mb4"
  echo "database.default.DBCollat = utf8mb4_unicode_ci"
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# ENCRYPTION"
  echo "#--------------------------------------------------------------------"
  echo ""
  if [[ "${ENCRYPTION_KEY}" == hex2bin:* ]]; then
    echo "encryption.key = ${ENCRYPTION_KEY}"
  else
    echo "encryption.key = '$(sq "$ENCRYPTION_KEY")'"
  fi
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# SESSION"
  echo "#--------------------------------------------------------------------"
  echo ""
  echo "session.driver = CodeIgniter\\Session\\Handlers\\FileHandler"
  echo "session.expiration = ${SESSION_EXPIRATION}"
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# EMAIL"
  echo "#--------------------------------------------------------------------"
  echo ""
  echo "email.fromEmail = '$(sq "$EMAIL_FROM")'"
  echo "email.fromName = '$(sq "$EMAIL_FROM_NAME")'"
  echo "join.notification.to = '$(sq "$JOIN_NOTIFICATION_TO")'"
  echo "email.protocol = $(sq "$EMAIL_PROTOCOL")"
  echo "staff.invite.expiryHours = ${STAFF_INVITE_EXPIRY_HOURS}"
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# ANALYTICS"
  echo "#--------------------------------------------------------------------"
  echo ""
  echo "analytics.enabled = ${ANALYTICS_ENABLED}"
  if [[ -n "${ANALYTICS_GA4_ID}" ]]; then
    echo "analytics.ga4MeasurementId = $(sq "$ANALYTICS_GA4_ID")"
  fi
  echo "analytics.privacyPageSlug = $(sq "$ANALYTICS_PRIVACY_SLUG")"
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# Hôtes / URLs (projets & positions)"
  echo "#--------------------------------------------------------------------"
  echo ""
  echo "app.projectsUsePathPrefix = ${PROJECTS_USE_PATH_PREFIX}"
  echo "app.positionsUsePathPrefix = ${POSITIONS_USE_PATH_PREFIX}"
  if [[ -n "${PROJECTS_HOST}" ]]; then
    echo "app.projectsHost = $(sq "$PROJECTS_HOST")"
  fi
  if [[ -n "${PROJECTS_BASE_URL}" ]]; then
    echo "app.projectsBaseURL = '$(sq "$PROJECTS_BASE_URL")'"
  fi
  if [[ -n "${POSITIONS_HOST}" ]]; then
    echo "app.positionsHost = $(sq "$POSITIONS_HOST")"
  fi
  if [[ -n "${POSITIONS_BASE_URL}" ]]; then
    echo "app.positionsBaseURL = '$(sq "$POSITIONS_BASE_URL")'"
  fi
  echo ""
  echo "#--------------------------------------------------------------------"
  echo "# ASSETS VERSION (cache-bust CSS/JS publics)"
  echo "#--------------------------------------------------------------------"
  echo ""
  if [[ -n "${APP_ASSET_VERSION}" ]]; then
    echo "app.assetVersion = $(sq "$APP_ASSET_VERSION")"
  else
    echo "# app.assetVersion ="
  fi
  echo ""
} > "$tmp"

mv "$tmp" "$out"
trap - EXIT
chmod 600 "$out"
echo "Fichier .env généré (écrasement complet) : ${out}"
