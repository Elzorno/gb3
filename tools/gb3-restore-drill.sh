#!/usr/bin/env bash
set -euo pipefail

# In-place restore drill for forward-only cutover model.
# Restores DB and uploads from snapshots, then runs quick integrity and smoke checks.
#
# Usage:
#   bash tools/gb3-restore-drill.sh <db_snapshot.sqlite> <uploads_snapshot.tar.gz>
#
# Example:
#   bash tools/gb3-restore-drill.sh \
#     /tmp/gb3-rewrite-db-20260316T221025Z.sqlite \
#     /tmp/gb3-rewrite-snapshots/gb2-uploads-20260316T221025Z.tar.gz

DB_SNAPSHOT="${1:-}"
UPLOADS_SNAPSHOT="${2:-}"
REWRITE_DB="${REWRITE_DB:-/var/www-rewrite/database/database.sqlite}"
UPLOADS_DIR="${UPLOADS_DIR:-/var/www/data/uploads}"
WWW_ROOT="${WWW_ROOT:-/var/www}"
REWRITE_ROOT="${REWRITE_ROOT:-/var/www-rewrite}"

say(){ printf '==> %s\n' "$*"; }
fail(){ printf 'ERROR: %s\n' "$*" >&2; exit 2; }

[[ -n "$DB_SNAPSHOT" ]] || fail "missing db snapshot argument"
[[ -n "$UPLOADS_SNAPSHOT" ]] || fail "missing uploads snapshot argument"
[[ -f "$DB_SNAPSHOT" ]] || fail "db snapshot not found: $DB_SNAPSHOT"
[[ -f "$UPLOADS_SNAPSHOT" ]] || fail "uploads snapshot not found: $UPLOADS_SNAPSHOT"
[[ -f "$REWRITE_DB" ]] || fail "rewrite db not found: $REWRITE_DB"
[[ -d "$UPLOADS_DIR" ]] || fail "uploads dir not found: $UPLOADS_DIR"

stamp="$(date -u +%Y%m%dT%H%M%SZ)"
backup_db="/tmp/gb3-restore-prestate-${stamp}.sqlite"
backup_uploads="/tmp/gb3-restore-prestate-uploads-${stamp}.tar.gz"

start_ts="$(date +%s)"

say "Restore drill start"
say "db_snapshot=$DB_SNAPSHOT"
say "uploads_snapshot=$UPLOADS_SNAPSHOT"

say "Step 1/6 backup current state"
cp "$REWRITE_DB" "$backup_db"
tar -czf "$backup_uploads" -C /var/www/data uploads

say "Step 2/6 restore rewrite DB snapshot"
cp "$DB_SNAPSHOT" "$REWRITE_DB"

say "Step 3/6 restore uploads snapshot"
rm -rf "$UPLOADS_DIR"
mkdir -p /var/www/data
tar -xzf "$UPLOADS_SNAPSHOT" -C /var/www/data

say "Step 4/6 integrity checks"
sqlite3 "$REWRITE_DB" "PRAGMA quick_check;" | cat
php "$WWW_ROOT/healthz.php" | cat

say "Step 5/6 quick app smoke tests"
(
  cd "$REWRITE_ROOT"
  php artisan test tests/Feature/KidAuthRoutesTest.php tests/Feature/SubmissionReviewFlowTest.php tests/Feature/InfractionPrivilegeFlowTest.php | cat
)

say "Step 6/6 summarize duration"
end_ts="$(date +%s)"
dur="$((end_ts - start_ts))"
say "Restore drill PASS"
say "duration_seconds=$dur"
say "prestate_db_backup=$backup_db"
say "prestate_uploads_backup=$backup_uploads"
