#!/usr/bin/env bash
# Deploy script for SIREKA ASSI (Rocky Linux 9).
# Usage: scripts/deploy.sh            -> pull, install, build, migrate, cache
#        scripts/deploy.sh --no-pull  -> skip git pull (code already updated)
set -Eeuo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

pull_code=true
[[ "${1:-}" == "--no-pull" ]] && pull_code=false

log() { printf '\n==> %s\n' "$1"; }
fail() { printf 'ERROR: %s\n' "$1" >&2; exit 1; }
trap 'fail "Deploy gagal pada baris $LINENO. Aplikasi mungkin dalam mode maintenance; periksa lalu jalankan: php artisan up"' ERR

[[ -f .env ]] || fail ".env tidak ditemukan"
[[ "$(id -u)" -ne 0 ]] || fail "Jangan jalankan sebagai root; gunakan user aplikasi"

# Vite 6 requires Node >= 18
node_major="$(node -p 'process.versions.node.split(".")[0]')"
(( node_major >= 18 )) || fail "Node >= 18 diperlukan (terpasang: $(node -v))"

# APP_URL must be https, otherwise Vite assets are blocked as mixed content
grep -Eq '^APP_URL=https://' .env || fail "APP_URL di .env harus diawali https://"

if $pull_code; then
    log "Pull kode (fast-forward only)"
    git pull --ff-only
fi

log "Mode maintenance ON"
php artisan down --retry=60 || true

log "Install dependensi PHP"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

log "Install dependensi Node & build aset"
npm ci --no-audit --no-fund
npm run build

# A leftover dev-server marker makes Laravel ignore public/build
rm -f public/hot

log "Migrasi database"
php artisan migrate --force

log "Refresh cache"
php artisan optimize:clear
php artisan optimize
php artisan queue:restart || true

log "Mode maintenance OFF"
php artisan up

log "Deploy selesai"
