#!/usr/bin/env bash
set -euo pipefail

# Single-host production go-live preflight for GB3 rewrite.
#
# Usage:
#   bash tools/gb3-go-live-preflight.sh [--full]
#
# --full reruns acceptance + rehearsal checks.

FULL=0
if [[ "${1:-}" == "--full" ]]; then
  FULL=1
fi

LEGACY_ROOT="${LEGACY_ROOT:-/var/www}"
REWRITE_ROOT="${REWRITE_ROOT:-/var/www-rewrite}"
LEGACY_DB="${LEGACY_DB:-/var/www/data/app.sqlite}"
REWRITE_DB="${REWRITE_DB:-/var/www-rewrite/database/database.sqlite}"
SNAP_DB_GLOB="${SNAP_DB_GLOB:-/tmp/gb3-rewrite-db-*.sqlite}"
SNAP_UP_GLOB="${SNAP_UP_GLOB:-/tmp/gb3-rewrite-snapshots/gb2-uploads-*.tar.gz}"
GO_LIVE_OWNER="${GO_LIVE_OWNER:-}"
RESTORE_OWNER="${RESTORE_OWNER:-}"

say(){ printf '==> %s\n' "$*"; }
fail(){ printf 'FAIL: %s\n' "$*" >&2; exit 2; }

say "GB3 go-live preflight start"
say "mode=$([[ "$FULL" -eq 1 ]] && echo full || echo quick)"

[[ -d "$LEGACY_ROOT" ]] || fail "legacy root missing: $LEGACY_ROOT"
[[ -d "$REWRITE_ROOT" ]] || fail "rewrite root missing: $REWRITE_ROOT"
[[ -f "$LEGACY_DB" ]] || fail "legacy db missing: $LEGACY_DB"
[[ -f "$REWRITE_DB" ]] || fail "rewrite db missing: $REWRITE_DB"

latest_db_snap="$(ls -1t $SNAP_DB_GLOB 2>/dev/null | head -n 1 || true)"
latest_up_snap="$(ls -1t $SNAP_UP_GLOB 2>/dev/null | head -n 1 || true)"

[[ -n "$latest_db_snap" ]] || fail "no rewrite db snapshot found matching: $SNAP_DB_GLOB"
[[ -n "$latest_up_snap" ]] || fail "no uploads snapshot found matching: $SNAP_UP_GLOB"

say "latest_db_snapshot=$latest_db_snap"
say "latest_uploads_snapshot=$latest_up_snap"

say "Health check"
health_json="$(php "$LEGACY_ROOT/healthz.php")"
php -r '$j=json_decode($argv[1], true); if(!is_array($j)){fwrite(STDERR,"bad health json\n"); exit(3);} if(!(($j["status"]??"")==="ok" && !empty($j["app"]) && !empty($j["db"]) && !empty($j["uploads"]))){fwrite(STDERR,"health not ok\n"); exit(4);} echo "health_ok\n";' "$health_json" >/dev/null
say "health=ok"

say "DB integrity"
legacy_qc="$(sqlite3 "$LEGACY_DB" "PRAGMA quick_check;")"
rewrite_qc="$(sqlite3 "$REWRITE_DB" "PRAGMA quick_check;")"
[[ "$legacy_qc" == "ok" ]] || fail "legacy db quick_check=$legacy_qc"
[[ "$rewrite_qc" == "ok" ]] || fail "rewrite db quick_check=$rewrite_qc"
say "db_quick_check=ok"

if [[ "$FULL" -eq 1 ]]; then
  say "Run acceptance script"
  bash "$REWRITE_ROOT/tools/gb3-acceptance-check.sh"

  say "Run rehearsal script"
  bash "$LEGACY_ROOT/tools/gb2-rewrite-rehearsal.sh" "$LEGACY_ROOT" "$LEGACY_DB" /tmp/gb2-legacy-export.json /tmp/gb2-rewrite.sqlite
fi

status="GO"
if [[ -z "$GO_LIVE_OWNER" || -z "$RESTORE_OWNER" ]]; then
  status="GO_WITH_OWNER_WARNING"
fi

say "owner_go_live=${GO_LIVE_OWNER:-UNSET}"
say "owner_restore=${RESTORE_OWNER:-UNSET}"
say "PRECHECK_RESULT=$status"
