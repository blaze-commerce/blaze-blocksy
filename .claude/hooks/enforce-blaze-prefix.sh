#!/usr/bin/env bash
# BLAZE PREFIX GATE — Blocks top-level functions without blaze_blocksy_ or blaze_custom_ prefix
# Applies to Write/Edit on .php files under includes/ or custom/ (excluding the 3 loaders)
set -euo pipefail

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')

# Only intercept Write or Edit
[ "$TOOL" = "Write" ] || [ "$TOOL" = "Edit" ] || exit 0

FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
[ -z "$FILE_PATH" ] && exit 0

# Must be a PHP file under includes/ or custom/ — but not the loader files themselves
echo "$FILE_PATH" | grep -qE '\.php$' || exit 0
echo "$FILE_PATH" | grep -qE '/(includes|custom)/' || exit 0
# Exclude the append-only loaders (governed by enforce-loader-append-only.sh)
echo "$FILE_PATH" | grep -qE '/custom/custom\.php$' && exit 0

# Get content to check (Write → content; Edit → new_string)
CONTENT=$(echo "$INPUT" | jq -r '.tool_input.content // .tool_input.new_string // empty')
[ -z "$CONTENT" ] && exit 0

# Find top-level function definitions (column 0, not indented = not class methods)
# Match: "function name(" at start of line
BAD=$(echo "$CONTENT" | grep -nE '^function [a-zA-Z_]' \
  | grep -vE '^[0-9]+:function (blaze_blocksy_|blaze_custom_)' \
  | sed 's/^[0-9]*://' \
  | grep -oE 'function [a-zA-Z_][a-zA-Z0-9_]*' \
  | awk '{print $2}' || true)

[ -z "$BAD" ] && exit 0

echo "" >&2
echo "BLOCKED: Top-level functions in includes/ and custom/ must use blaze_blocksy_ or blaze_custom_ prefix." >&2
echo "" >&2
echo "  Unprefixed functions found:" >&2
while IFS= read -r fn; do
  [ -n "$fn" ] && echo "    function ${fn}()" >&2
done <<< "$BAD"
echo "" >&2
echo "  Rename to: blaze_blocksy_<name>() or blaze_custom_<name>()" >&2
echo "  Class methods and closures are exempt (must be indented)." >&2
echo "" >&2
exit 2
