#!/bin/bash
set -eo pipefail
# enforce-worktree.sh - Block file edits in main git checkout (require worktrees)
# PreToolUse hook for Edit, Write, NotebookEdit

INPUT=$(cat)
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')

if [ -z "$FILE" ]; then
  exit 0
fi

FILE_DIR=$(dirname "$FILE")

GIT_ROOT=$(git -C "$FILE_DIR" rev-parse --show-toplevel 2>/dev/null)
if [ -z "$GIT_ROOT" ]; then
  exit 0
fi

GIT_DIR=$(git -C "$FILE_DIR" rev-parse --git-dir 2>/dev/null)
if echo "$GIT_DIR" | grep -q "worktrees"; then
  exit 0
fi

echo "BLOCKED: '$FILE' is in the main git checkout of '$GIT_ROOT'." >&2
echo "Create a worktree first: git worktree add .worktrees/<branch-name> -b <branch-name>" >&2
exit 2
