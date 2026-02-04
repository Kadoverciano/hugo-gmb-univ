#!/bin/sh

WATCH_DIR="${WATCH_DIR:-/home/svc_user/hugo-site}"
DEBOUNCE_WINDOW="${DEBOUNCE_WINDOW:-10}"
LOCK_FILE="/run/hugo-inotify.lock"
SCRIPT_PATH="/usr/local/bin/process_sites.sh"

[ -d "$WATCH_DIR" ] || {
  echo "Watch directory $WATCH_DIR does not exist!"
  exit 1
}

echo "Watching ${WATCH_DIR}"

inotifywait -m -e create -e move --format '%w%f' "$WATCH_DIR" | while read -r fullpath; do
  if [ -d "$fullpath" ]; then
    echo "$(date '+%F %T') New directory detected: $fullpath"
    (
      exec 9>"$LOCK_FILE"
      if flock -n 9; then
        echo "$(date '+%F %T') Triggering $SCRIPT_PATH after debounce ($DEBOUNCE_WINDOW sec)..."
        sleep "$DEBOUNCE_WINDOW"
        /bin/bash "$SCRIPT_PATH"
      else
        echo "$(date '+%F %T') Skipping trigger, lock held"
      fi
    ) &
  fi
done
