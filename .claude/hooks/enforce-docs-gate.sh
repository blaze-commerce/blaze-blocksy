#!/usr/bin/env bash
# DOCUMENTATION GATE — Blocks push/PR if includes/ or governance files changed without README.md update
set -euo pipefail

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')

[ "$TOOL" = "Bash" ] || exit 0

COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')
echo "$COMMAND" | grep -qE 'git push|gh pr create' || exit 0

CWD=$(echo "$INPUT" | jq -r '.cwd // ""' 2>/dev/null || echo "")
[ -n "$CWD" ] && cd "$CWD" 2>/dev/null || true
if ! git rev-parse --git-dir &>/dev/null 2>&1; then
  exit 0
fi

# Get files changed since origin/main
CHANGED=$(git diff origin/main...HEAD --name-only 2>/dev/null || true)
[ -z "$CHANGED" ] && exit 0

# Check if governance/source files are in the diff
if ! echo "$CHANGED" | grep -qE '^(includes/|\.claude/|CLAUDE\.md)'; then
  exit 0  # No relevant files changed — no docs required
fi

# Check if README.md was also updated
if echo "$CHANGED" | grep -q '^README\.md'; then
  exit 0  # README updated — allow
fi

echo "" >&2
echo "BLOCKED: Documentation gate — governance or source files changed but README.md not updated." >&2
echo "" >&2
echo "  Files changed requiring docs:" >&2
echo "$CHANGED" | grep -E '^(includes/|\.claude/|CLAUDE\.md)' | sed 's/^/    /' >&2
echo "" >&2
echo "  Update README.md to reflect the changes, then push." >&2
echo "" >&2
exit 2
