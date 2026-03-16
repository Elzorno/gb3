#!/usr/bin/env bash
set -euo pipefail

# GB3 rewrite acceptance check against copied legacy data.
#
# Usage:
#   bash tools/gb3-acceptance-check.sh
#
# Optional overrides:
#   LEGACY_ROOT=/var/www
#   REWRITE_ROOT=/var/www-rewrite
#   EXPORT_JSON=/tmp/gb2-legacy-export.json

LEGACY_ROOT="${LEGACY_ROOT:-/var/www}"
REWRITE_ROOT="${REWRITE_ROOT:-/var/www-rewrite}"
EXPORT_JSON="${EXPORT_JSON:-/tmp/gb2-legacy-export.json}"
LEGACY_DB="${LEGACY_ROOT}/data/app.sqlite"
REWRITE_DB="${REWRITE_ROOT}/database/database.sqlite"

say(){ printf '==> %s\n' "$*"; }

test -d "${LEGACY_ROOT}" || { echo "ERROR: LEGACY_ROOT not found: ${LEGACY_ROOT}" >&2; exit 2; }
test -d "${REWRITE_ROOT}" || { echo "ERROR: REWRITE_ROOT not found: ${REWRITE_ROOT}" >&2; exit 3; }
test -f "${LEGACY_DB}" || { echo "ERROR: legacy DB not found: ${LEGACY_DB}" >&2; exit 4; }

say "Acceptance check start"
say "legacy_root=${LEGACY_ROOT}"
say "rewrite_root=${REWRITE_ROOT}"
say "legacy_db=${LEGACY_DB}"
say "rewrite_db=${REWRITE_DB}"

say "Step 1/6 export legacy data"
php "${LEGACY_ROOT}/tools/gb2-export-legacy-json.php" "${EXPORT_JSON}"

say "Step 2/6 import dry-run into rewrite db"
php "${LEGACY_ROOT}/tools/gb2-import-legacy-json.php" "${EXPORT_JSON}" "${REWRITE_DB}" --dry-run

say "Step 3/6 import into rewrite db"
php "${LEGACY_ROOT}/tools/gb2-import-legacy-json.php" "${EXPORT_JSON}" "${REWRITE_DB}" --truncate

say "Step 4/6 semantic parity compare"
php "${LEGACY_ROOT}/tools/gb2-compare-legacy-rewrite.php" "${LEGACY_DB}" "${REWRITE_DB}"

say "Step 5/6 feature tests"
(
  cd "${REWRITE_ROOT}"
  php artisan test --testsuite=Feature
)

say "Step 6/6 rewrite DB spot checks"
sqlite3 "${REWRITE_DB}" "select 'kids',count(*) from kids union all select 'assignments',count(*) from assignments union all select 'submissions',count(*) from submissions union all select 'bonus_instances',count(*) from bonus_instances;"
sqlite3 "${REWRITE_DB}" "select status,count(*) from submissions group by status order by status;"

say "ACCEPTANCE PASS"
