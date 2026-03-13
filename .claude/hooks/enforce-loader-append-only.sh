#!/usr/bin/env bash
# LOADER APPEND-ONLY GATE — Blocks non-append edits to loader files
# Loader files: custom/custom.php|css|js, functions.php, includes/scripts.php
# Write: always blocked (full rewrites prohibited)
# Edit: blocked if new_string does NOT contain old_string (content removed/replaced)
set -euo pipefail

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')

FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
[ -z "$FILE_PATH" ] && exit 0

# Match loader files only
if ! echo "$FILE_PATH" | grep -qE '(custom/(custom\.(php|css|js))|functions\.php|includes/scripts\.php)$'; then
  exit 0
fi

BASENAME=$(basename "$FILE_PATH")

BLOCK_MSG() {
  echo "" >&2
  echo "BLOCKED: $BASENAME is an append-only loader file. Full rewrites are prohibited." >&2
  echo "" >&2
  echo "  custom.php  → append require_once lines only" >&2
  echo "  custom.css  → append @import or :root {} blocks only" >&2
  echo "  custom.js   → append wp_enqueue_script calls only" >&2
  echo "  functions.php → append to \$required_files array only" >&2
  echo "  scripts.php   → append wp_enqueue_* calls only" >&2
  echo "" >&2
  echo "  Feature logic belongs in includes/ modules, not loader files." >&2
  echo "" >&2
  exit 2
}

# Write: always block
if [ "$TOOL" = "Write" ]; then
  BLOCK_MSG
fi

# Edit: block if new_string does not contain old_string (removal/replacement)
if [ "$TOOL" = "Edit" ]; then
  OLD=$(echo "$INPUT" | jq -r '.tool_input.old_string // empty')
  NEW=$(echo "$INPUT" | jq -r '.tool_input.new_string // empty')

  # If old_string is empty, it's an append — allow
  [ -z "$OLD" ] && exit 0

  # Block if new does not contain old (content was removed or replaced)
  if ! echo "$NEW" | grep -qF "$OLD"; then
    BLOCK_MSG
  fi
fi

exit 0
