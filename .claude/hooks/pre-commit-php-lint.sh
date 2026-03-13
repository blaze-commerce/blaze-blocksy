#!/usr/bin/env bash
# PHP LINT GATE — PHPCS + PHPStan on staged PHP files
# Adapted from global version: removed wp-content/ path filter (repo root IS the theme)
# Blocks on PHPCS errors; PHPStan failures are warnings only
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

# Get all staged PHP files (no wp-content/ filter — repo root is the theme)
CHANGED=$(git diff --cached --name-only --diff-filter=ACMR 2>/dev/null \
  | grep '\.php$' || true)

[ -z "$CHANGED" ] && exit 0

FAILURES=()

# PHPCS — WordPress Coding Standards (blocking on errors, not warnings)
if command -v phpcs &>/dev/null; then
  # shellcheck disable=SC2086
  PHPCS_OUT=$(phpcs --standard=WordPress-Core,WordPress-Docs \
    --extensions=php \
    --report=summary \
    --severity=5 \
    $CHANGED 2>&1 || true)
  if echo "$PHPCS_OUT" | grep -qE '^ERROR|^FOUND [0-9]+ ERROR'; then
    FAILURES+=("PHPCS: WordPress coding standards errors found")
    echo ""
    echo "$PHPCS_OUT"
    echo ""
  fi
fi

# PHPStan — level 5 (warn only)
if command -v phpstan &>/dev/null; then
  # shellcheck disable=SC2086
  PHPSTAN_OUT=$(phpstan analyse --level=5 --no-progress $CHANGED 2>&1 || true)
  if echo "$PHPSTAN_OUT" | grep -qE '\[ERROR\]|Found [0-9]+ error'; then
    echo ""
    echo "PHP LINT GATE — PHPStan warnings (non-blocking):"
    echo "$PHPSTAN_OUT"
    echo "  Run: phpstan analyse --level=5 <file> to review"
    echo ""
  fi
fi

if [[ ${#FAILURES[@]} -gt 0 ]]; then
  echo ""
  echo "PHP LINT GATE — PHPCS errors block commit:"
  for f in "${FAILURES[@]}"; do
    echo "  $f"
  done
  echo ""
  echo "  Fix PHPCS errors: phpcs --standard=WordPress-Core,WordPress-Docs <file>"
  echo "  Auto-fix where possible: phpcbf --standard=WordPress-Core <file>"
  echo ""
  exit 2
fi

exit 0
