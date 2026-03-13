#!/usr/bin/env bash
# DEV LOGGING GATE — Blocks unguarded debug statements on commit/push
set -euo pipefail

INPUT=$(cat)
COMMAND=$(echo "$INPUT" | jq -r '.command // ""' 2>/dev/null || echo "")

# Only intercept git commit or push
if ! echo "$COMMAND" | grep -qE '^git (commit|push)'; then
  exit 0
fi

# Must be in a git repo
if ! git rev-parse --git-dir &>/dev/null 2>&1; then
  exit 0
fi

VIOLATIONS=()
CURRENT_FILE=""

while IFS= read -r line; do
  # Track current file from diff headers
  if [[ "$line" =~ ^\+\+\+\ b/(.+\.(js|ts|mjs|cjs|jsx|tsx))$ ]]; then
    CURRENT_FILE="${BASH_REMATCH[1]}"
    continue
  fi
  if [[ "$line" =~ ^\+\+\+\ b/(.+\.php)$ ]]; then
    CURRENT_FILE="${BASH_REMATCH[1]}"
    continue
  fi
  if [[ "$line" =~ ^\+\+\+\  ]]; then
    CURRENT_FILE=""
    continue
  fi

  # Only process added lines (not context or removals)
  [[ "$line" =~ ^\+[^+] ]] || continue
  [[ -z "$CURRENT_FILE" ]] && continue

  CONTENT="${line:1}"

  # JS/TS: flag bare console.log (must use console.debug inside dev guard)
  if [[ "$CURRENT_FILE" =~ \.(js|ts|mjs|cjs|jsx|tsx)$ ]]; then
    if echo "$CONTENT" | grep -qE 'console\.log\('; then
      VIOLATIONS+=("  [$CURRENT_FILE] console.log — replace with: if (process.env.NODE_ENV !== 'production') { console.debug(...) }")
    fi
  fi

  # PHP: flag var_dump and print_r (no guarded form is acceptable)
  if [[ "$CURRENT_FILE" =~ \.php$ ]]; then
    if echo "$CONTENT" | grep -qE 'var_dump\(|print_r\('; then
      VIOLATIONS+=("  [$CURRENT_FILE] var_dump/print_r — remove or wrap in WP_DEBUG guard")
    fi
  fi

done < <(git diff --cached 2>/dev/null || echo "")

if [[ ${#VIOLATIONS[@]} -gt 0 ]]; then
  echo ""
  echo "DEV LOGGING GATE — Unguarded debug statements detected:"
  for v in "${VIOLATIONS[@]}"; do
    echo "$v"
  done
  echo ""
  echo "JS fix:  if (process.env.NODE_ENV !== 'production') { console.debug('[module/fn]', data) }"
  echo "PHP fix: if (defined('WP_DEBUG') && WP_DEBUG) { error_log('[Class::method] ' . print_r(\$data, true)) }"
  exit 2
fi

exit 0
