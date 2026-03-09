#!/bin/bash
set -eo pipefail
# pre-commit-test-gate.sh - Remind to run tests before git commit
# Only activates when a test runner is detected in the project
# Non-blocking: outputs a reminder, does not prevent commit

INPUT=$(cat)
TOOL_INPUT=$(echo "$INPUT" | jq -r '.tool_input.command // empty')

# Only intercept git commit commands
if ! echo "$TOOL_INPUT" | grep -qE '^git commit'; then
  exit 0
fi

CWD=$(echo "$INPUT" | jq -r '.cwd // empty')
cd "$CWD" 2>/dev/null || exit 0

# Check for common test runners — if none exist, skip
HAS_TESTS=false
[ -f "package.json" ] && grep -qE '"test"' package.json 2>/dev/null && HAS_TESTS=true
[ -f "composer.json" ] && grep -qE '"test"' composer.json 2>/dev/null && HAS_TESTS=true
([ -f "phpunit.xml" ] || [ -f "phpunit.xml.dist" ]) && HAS_TESTS=true
{ [ -f "pytest.ini" ] || [ -f "pyproject.toml" ]; } && HAS_TESTS=true
[ -f "Cargo.toml" ] && HAS_TESTS=true

if [ "$HAS_TESTS" = false ]; then
  exit 0
fi

echo "REMINDER: This project has tests. Ensure tests pass before committing. Run the test suite if you haven't already this session."
exit 0
