#!/usr/bin/env bash
# ABSPATH GUARD GATE — Blocks Write of PHP modules without ABSPATH check
# Applies to .php files under includes/, woocommerce/, or custom/ (excluding the 3 loaders)
set -euo pipefail

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')

# Only intercept Write
[ "$TOOL" = "Write" ] || exit 0

FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
[ -z "$FILE_PATH" ] && exit 0

# Must be a PHP file
echo "$FILE_PATH" | grep -qE '\.php$' || exit 0

# Must be under includes/, woocommerce/, or custom/ — but not the loader files themselves
echo "$FILE_PATH" | grep -qE '/(includes|woocommerce|custom)/' || exit 0
# Exclude the 3 append-only loaders (governed by enforce-loader-append-only.sh)
echo "$FILE_PATH" | grep -qE '/custom/custom\.(php|css|js)$' && exit 0

CONTENT=$(echo "$INPUT" | jq -r '.tool_input.content // empty')

# Allow if ABSPATH guard already present (any common variant)
if echo "$CONTENT" | grep -qiE "defined\s*\(\s*'ABSPATH'\s*\)"; then
  exit 0
fi

echo "" >&2
echo "BLOCKED: PHP module files must include an ABSPATH guard." >&2
echo "" >&2
echo "  Add at the top of the file (after <?php):" >&2
echo "" >&2
echo "    defined( 'ABSPATH' ) || exit;" >&2
echo "" >&2
echo "  This prevents direct HTTP access to module files." >&2
echo "" >&2
exit 2
