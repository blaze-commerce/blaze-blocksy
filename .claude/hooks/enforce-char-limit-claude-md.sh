#!/usr/bin/env bash
# CHAR LIMIT GATE — Blocks edits that would exceed char limits on governance files
# CLAUDE.md → 8,000 chars | .claude/recommended/* → 3,000 | .claude/commands/* → 2,000
set -euo pipefail

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')

[ "$TOOL" = "Write" ] || [ "$TOOL" = "Edit" ] || exit 0

FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
[ -z "$FILE_PATH" ] && exit 0

# Determine limit based on file path
LIMIT=0
if echo "$FILE_PATH" | grep -qE 'CLAUDE\.md$'; then
  LIMIT=8000
elif echo "$FILE_PATH" | grep -qE '\.claude/recommended/'; then
  LIMIT=3000
elif echo "$FILE_PATH" | grep -qE '\.claude/commands/'; then
  LIMIT=2000
else
  exit 0
fi

BASENAME=$(basename "$FILE_PATH")

if [ "$TOOL" = "Write" ]; then
  CONTENT=$(echo "$INPUT" | jq -r '.tool_input.content // empty')
  NEW_SIZE=${#CONTENT}
elif [ "$TOOL" = "Edit" ]; then
  OLD=$(echo "$INPUT" | jq -r '.tool_input.old_string // empty')
  NEW=$(echo "$INPUT" | jq -r '.tool_input.new_string // empty')
  # Estimate new size: current file size - removed chars + added chars
  CURRENT_SIZE=0
  if [ -f "$FILE_PATH" ]; then
    CURRENT_SIZE=$(wc -c < "$FILE_PATH" | tr -d ' ')
  fi
  OLD_LEN=${#OLD}
  NEW_LEN=${#NEW}
  NEW_SIZE=$(( CURRENT_SIZE - OLD_LEN + NEW_LEN ))
fi

if [ "$NEW_SIZE" -gt "$LIMIT" ]; then
  echo "" >&2
  echo "BLOCKED: $BASENAME would be ${NEW_SIZE}c (limit: ${LIMIT}c)." >&2
  echo "" >&2
  echo "  Consolidate or move content to a separate file first." >&2
  echo "  Then retry the edit." >&2
  echo "" >&2
  exit 2
fi

exit 0
