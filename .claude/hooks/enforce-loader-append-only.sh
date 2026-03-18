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
    # Exception: allow comment-out of require/enqueue lines in PHP loaders only
    if [[ "$BASENAME" == "custom.php" || "$BASENAME" == "functions.php" ]]; then
      if python3 - "$OLD" "$NEW" << 'PYEOF'
import sys
old_lines = sys.argv[1].splitlines()
new_lines = sys.argv[2].splitlines()
PREFIXES = ('require', 'include', 'wp_enqueue', 'wp_dequeue', 'wp_register', 'wp_deregister', 'add_action')
if len(new_lines) < len(old_lines):
    sys.exit(1)
for old_line, new_line in zip(old_lines, new_lines):
    if old_line == new_line:
        continue
    o = old_line.lstrip()
    n = new_line.lstrip()
    uncommented = n.lstrip('/').lstrip('*').strip()
    if (n.startswith('//') or n.startswith('/*')) and \
       uncommented in (o, o.rstrip()) and \
       any(o.startswith(p) for p in PREFIXES):
        continue
    sys.exit(1)
sys.exit(0)
PYEOF
      then
        exit 0  # comment-out of require/enqueue line — allowed
      fi
    fi
    BLOCK_MSG
  fi
fi

exit 0
