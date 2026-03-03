#!/bin/bash
set -eo pipefail
# block-force-push.sh - Block accidental force pushes
# PreToolUse hook for Bash

INPUT=$(cat)
CMD=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

if echo "$CMD" | grep -qE 'git push.*(--force|-f)(\s|$)'; then
  echo "BLOCKED: Force push is not allowed. Open a PR or ask the repo owner." >&2
  exit 2
fi

exit 0
