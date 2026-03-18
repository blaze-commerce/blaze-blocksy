#!/bin/bash
set -euo pipefail
# enforce-read-before-edit.sh — Theme file integrity gate (BLOCKING)
# For ALL files in this repo (excluding custom.php/js/css/functions.php handled by protect-custom-files.sh):
#   1. Write tool: always blocked (no full overwrites)
#   2. Edit tool: old_string must be >= 10 chars (proof file was read before editing)
#   3. Edit tool: new_string must start with old_string (append-only — preserve existing code)
# PreToolUse — Edit|Write|NotebookEdit

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
[ -z "$FILE" ] && exit 0
FILE="${FILE/#\~/$HOME}"

# Scope to this repo only
REPO_ROOT=$(git -C "$(dirname "${BASH_SOURCE[0]}")" rev-parse --show-toplevel 2>/dev/null || true)
if [ -z "$REPO_ROOT" ] || [[ "$FILE" != "$REPO_ROOT/"* ]]; then
  exit 0
fi

BASENAME=$(basename "$FILE")

# Skip files handled by protect-custom-files.sh
case "$BASENAME" in
  custom.php|custom.js|custom.css|functions.php) exit 0 ;;
esac

# ── Block Write tool (full overwrite) ────────────────────────────────────────
if [[ "$TOOL" == "Write" ]]; then
  echo "BLOCKED: Cannot overwrite $BASENAME in the child theme." >&2
  echo "Read the file first, then use Edit — append new code BELOW the last line of existing code." >&2
  exit 2
fi

# ── Edit tool: verify file was read before editing ───────────────────────────
OLD=$(echo "$INPUT" | jq -r '.tool_input.old_string // empty')
OLD_LEN=${#OLD}

if [ "$OLD_LEN" -lt 10 ]; then
  echo "BLOCKED: $BASENAME (child theme) — READ the file before editing." >&2
  echo "old_string must be >= 10 chars, matching the actual last lines of the file, to confirm you have verified the current content." >&2
  exit 2
fi

# ── Edit tool: enforce append-only (new code goes after existing code) ────────
NEW=$(echo "$INPUT" | jq -r '.tool_input.new_string // empty')

TMP_OLD=$(mktemp)
TMP_NEW=$(mktemp)
trap 'rm -f "$TMP_OLD" "$TMP_NEW"' EXIT

printf '%s' "$OLD" > "$TMP_OLD"
printf '%s' "$NEW" > "$TMP_NEW"

OLD_BYTES=$(wc -c < "$TMP_OLD")
NEW_BYTES=$(wc -c < "$TMP_NEW")

# new_string must not be shorter (no code removal)
if [ "$NEW_BYTES" -lt "$OLD_BYTES" ]; then
  echo "BLOCKED: $BASENAME (child theme) — edit removes existing code." >&2
  echo "Never delete existing child theme code. Append new code below the last line instead." >&2
  exit 2
fi

# new_string must START with old_string byte-for-byte (append-only rule)
if ! head -c "$OLD_BYTES" "$TMP_NEW" | diff -q "$TMP_OLD" - > /dev/null 2>&1; then
  echo "BLOCKED: $BASENAME (child theme) — existing code must not be modified." >&2
  echo "Rule: append new code BELOW the last line. Do not alter existing lines." >&2
  echo "For bug fixes: add a WP hook override (remove_filter/add_filter) appended at the bottom," >&2
  echo "or wrap original code in if(!function_exists()) and append the corrected version." >&2
  exit 2
fi

exit 0
