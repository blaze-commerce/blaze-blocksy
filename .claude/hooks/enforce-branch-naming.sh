#!/usr/bin/env bash
# BRANCH NAMING GATE — Blocks worktree creation with non-conventional branch names
# Required prefix: feat/ fix/ chore/ docs/ + kebab-case
# Upgraded from global warning-only (exit 0) to blocking (exit 2)
set -euo pipefail

INPUT=$(cat)
BRANCH=$(echo "$INPUT" | jq -r '.branch // ""')

[ -z "$BRANCH" ] && exit 0

if ! echo "$BRANCH" | grep -qE '^(feat|fix|chore|docs)/[a-z0-9][a-z0-9-]*$'; then
  echo "" >&2
  echo "BLOCKED: Branch name does not follow naming conventions." >&2
  echo "" >&2
  echo "  Got:      $BRANCH" >&2
  echo "  Required: <prefix>/<kebab-case-description>" >&2
  echo "" >&2
  echo "  Valid prefixes: feat/ fix/ chore/ docs/" >&2
  echo "  Examples:" >&2
  echo "    feat/add-product-filter" >&2
  echo "    fix/cart-total-rounding" >&2
  echo "    chore/update-dependencies" >&2
  echo "    docs/add-hook-reference" >&2
  echo "" >&2
  exit 2
fi

exit 0
