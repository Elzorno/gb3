#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-http://192.168.1.15}"
LEGACY_ROOT="${LEGACY_ROOT:-/var/www-rewrite/legacy}"

printf 'GROUP|PATH|STATUS\n'
for grp in admin app api; do
  for f in "$LEGACY_ROOT/$grp"/*.php; do
    [[ -f "$f" ]] || continue
    b="$(basename "$f")"
    p="/$grp/$b"
    code="$(wget -T 5 --tries=1 --server-response -O /dev/null "$BASE_URL$p" 2>&1 | awk '/^  HTTP\// {c=$2} END{print c}')"
    printf '%s|%s|%s\n' "$grp" "$p" "${code:-ERR}"
  done
done | sort
