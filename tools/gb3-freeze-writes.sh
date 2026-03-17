#!/usr/bin/env bash
set -euo pipefail

# Toggle write freeze mode for rewrite app.
#
# Usage:
#   bash tools/gb3-freeze-writes.sh on [reason]
#   bash tools/gb3-freeze-writes.sh off
#   bash tools/gb3-freeze-writes.sh status

ACTION="${1:-status}"
REASON="${2:-maintenance-window}"
FLAG_PATH="${FLAG_PATH:-/var/www-rewrite/storage/framework/gb3_write_freeze.flag}"

say(){ printf '==> %s\n' "$*"; }

case "$ACTION" in
  on)
    mkdir -p "$(dirname "$FLAG_PATH")"
    ts="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    printf '{"frozen":true,"started_at":"%s","reason":"%s"}\n' "$ts" "$REASON" > "$FLAG_PATH"
    say "WRITE_FREEZE=ON"
    say "flag=$FLAG_PATH"
    say "started_at=$ts"
    say "reason=$REASON"
    ;;
  off)
    rm -f "$FLAG_PATH"
    say "WRITE_FREEZE=OFF"
    say "flag=$FLAG_PATH"
    ;;
  status)
    if [[ -f "$FLAG_PATH" ]]; then
      say "WRITE_FREEZE=ON"
      say "flag=$FLAG_PATH"
      say "meta=$(cat "$FLAG_PATH")"
    else
      say "WRITE_FREEZE=OFF"
      say "flag=$FLAG_PATH"
    fi
    ;;
  *)
    echo "Usage: $0 {on [reason]|off|status}" >&2
    exit 2
    ;;
esac
