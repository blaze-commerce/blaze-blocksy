#!/usr/bin/env bash
# CONVENTIONAL COMMIT GATE — Blocks non-conventional commit messages
set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // .command // ""' 2>/dev/null || echo "")

# Only intercept git commit
if ! echo "$COMMAND" | grep -qE '^git commit'; then
  exit 0
fi

CWD=$(echo "$INPUT" | jq -r '.cwd // ""' 2>/dev/null || echo "")
[[ -n "$CWD" ]] && cd "$CWD" 2>/dev/null || true
if ! git rev-parse --git-dir &>/dev/null 2>&1; then
  exit 0
fi

# Extract commit message from -m flag (double or single quoted)
MSG=""
if echo "$COMMAND" | grep -qE '\-m\s+"'; then
  MSG=$(echo "$COMMAND" | sed -n 's/.*-m "\([^"]*\)".*/\1/p' | head -1 || true)
elif echo "$COMMAND" | grep -qE "\-m\s+'"; then
  MSG=$(echo "$COMMAND" | sed -n "s/.*-m '\\([^']*\\)'.*/\\1/p" | head -1 || true)
fi

# Can't parse (heredoc, no -m, or interactive) — skip
[[ -z "$MSG" ]] && exit 0
echo "$MSG" | grep -qE '^\$\(cat|^<<' && exit 0

FIRST_LINE=$(echo "$MSG" | head -1)
VALID='^(feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert)(\(.+\))?!?:'

if ! echo "$FIRST_LINE" | grep -qE "$VALID"; then
  echo ""
  echo "CONVENTIONAL COMMIT GATE — Message does not follow conventional commit format."
  echo "  Got: \"$FIRST_LINE\""
  echo ""
  echo "  Valid prefixes: feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert"
  echo "  Examples:"
  echo "    feat(auth): add OAuth login"
  echo "    fix: correct null pointer in cart"
  echo "    chore: bump dependency versions"
  echo ""
  exit 2
fi

exit 0
