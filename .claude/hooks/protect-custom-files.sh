#!/bin/bash
set -euo pipefail
# protect-custom-files.sh — custom/ file protection gate (repo-local)
# custom.js + custom.css: completely read-only
# custom.php + functions.php: loader-only — require/include and asset enqueue calls only
# PreToolUse hook — Edit|Write|NotebookEdit

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
[ -z "$FILE" ] && exit 0
FILE="${FILE/#\~/$HOME}"

BASENAME=$(basename "$FILE")

# Only gate protected files
case "$BASENAME" in
  custom.php|custom.js|custom.css|functions.php) ;;
  *) exit 0 ;;
esac

# Scope to this repo only
REPO_ROOT=$(git -C "$(dirname "${BASH_SOURCE[0]}")" rev-parse --show-toplevel 2>/dev/null || true)
if [ -z "$REPO_ROOT" ] || [[ "$FILE" != "$REPO_ROOT/"* ]]; then
  exit 0
fi

# ── Modular path gate: .js/.css must be in approved directories ────────────
if [[ "$BASENAME" == *.js || "$BASENAME" == *.css ]]; then
  if ! echo "$FILE" | grep -qE '/(assets/(js|css)|custom/(js|css))/'; then
    EXT="${BASENAME##*.}"
    UPPER=$(echo "$EXT" | tr '[:lower:]' '[:upper:]')
    echo "BLOCKED: $BASENAME — ${UPPER} files must be placed in a modular directory." >&2
    echo "  Generic  : child-theme/assets/${EXT}/<name>.${EXT}" >&2
    echo "  Custom   : child-theme/custom/${EXT}/<name>.${EXT}" >&2
    echo "Then enqueue it in custom.php via wp_enqueue_script()/wp_enqueue_style()." >&2
    echo "NEVER add code directly to custom.js or custom.css." >&2
    exit 2
  fi
fi

# ── custom.js and custom.css: FULLY READ-ONLY ──────────────────────────────
if [[ "$BASENAME" == "custom.js" || "$BASENAME" == "custom.css" ]]; then
  EXT="${BASENAME##*.}"
  echo "BLOCKED: $BASENAME is permanently read-only." >&2
  echo "Do NOT add CSS/JS code to custom.js or custom.css." >&2
  echo "  Generic  : child-theme/assets/${EXT}/<name>.${EXT}" >&2
  echo "  Custom   : child-theme/custom/${EXT}/<name>.${EXT}" >&2
  echo "Enqueue the new file in custom.php." >&2
  exit 2
fi

# ── custom.php and functions.php: append-only with allowed content ──────────

# Block Write tool (overwrites entire file)
if [[ "$TOOL" == "Write" ]]; then
  echo "BLOCKED: Cannot overwrite $BASENAME." >&2
  echo "Use Edit (append-only): old_string = last few lines; new_string = those lines + new require_once/enqueue calls." >&2
  exit 2
fi

# Edit tool: enforce append-only + allowed content
OLD=$(echo "$INPUT" | jq -r '.tool_input.old_string // empty')
NEW=$(echo "$INPUT" | jq -r '.tool_input.new_string // empty')

TMP_OLD=$(mktemp)
TMP_NEW=$(mktemp)
trap 'rm -f "$TMP_OLD" "$TMP_NEW"' EXIT

printf '%s' "$OLD" > "$TMP_OLD"
printf '%s' "$NEW" > "$TMP_NEW"

OLD_LEN=$(wc -c < "$TMP_OLD")
NEW_LEN=$(wc -c < "$TMP_NEW")

# new_string must not be shorter (no content removal)
if [ "$NEW_LEN" -lt "$OLD_LEN" ]; then
  echo "BLOCKED: $BASENAME is append-only — cannot remove existing code." >&2
  exit 2
fi

# new_string must START with old_string (existing code preserved, new appended after)
if ! head -c "$OLD_LEN" "$TMP_NEW" | diff -q "$TMP_OLD" - > /dev/null 2>&1; then
  echo "BLOCKED: $BASENAME — existing code must not be modified." >&2
  echo "Only append new require/enqueue calls at the very end." >&2
  exit 2
fi

# Extract only the ADDED portion
ADDED=$(tail -c "+$((OLD_LEN + 1))" "$TMP_NEW")

# Validate each non-trivial line in the added portion
DISALLOWED=0
DISALLOWED_LINES=""
while IFS= read -r line; do
  trimmed="${line#"${line%%[![:space:]]*}"}"
  [ -z "$trimmed" ] && continue
  [[ "$trimmed" == //* ]] && continue
  [[ "$trimmed" == "/*"* ]] && continue
  [[ "$trimmed" == " *"* ]] && continue
  [[ "$trimmed" == "*/"* ]] && continue
  [[ "$trimmed" == "<?php"* ]] && continue
  [[ "$trimmed" == "?>"* ]] && continue
  [[ "$trimmed" == require* ]] && continue
  [[ "$trimmed" == include* ]] && continue
  [[ "$trimmed" =~ ^add_action\([\'\"](wp_enqueue_scripts|admin_enqueue_scripts|wp_print_styles|login_enqueue_scripts|enqueue_block_assets)[\'\"\ ,] ]] && continue
  [[ "$trimmed" == wp_enqueue_* ]] && continue
  [[ "$trimmed" == wp_register_* ]] && continue
  [[ "$trimmed" == wp_dequeue_* ]] && continue
  [[ "$trimmed" == wp_deregister_* ]] && continue
  # Everything else is blocked
  DISALLOWED=1
  DISALLOWED_LINES="${DISALLOWED_LINES}  → ${line}"$'\n'
done <<< "$ADDED"

if [ "$DISALLOWED" -eq 1 ]; then
  echo "BLOCKED: $BASENAME must only contain require_once/include and asset enqueue calls — no PHP logic." >&2
  echo "Create a modular file (e.g. custom/my-feature.php) and require_once it here." >&2
  echo "CSS/JS assets go in assets/ or custom/assets/." >&2
  echo "Disallowed lines:" >&2
  printf '%s' "$DISALLOWED_LINES" | head -10 >&2
  exit 2
fi

exit 0
