#!/bin/bash
set -euo pipefail
# block-credential-reads.sh — Blocks Read/Bash/SSH reads of credential files
# PreToolUse hook: exit 0 = allow, exit 2 = block

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')

# --- Credential path patterns (regex) ---
HOME_PATTERNS=(
  '\.ssh/'
  '\.gnupg/'
  '\.config/gnupg/'
  '\.aws/'
  '\.azure/'
  '\.config/gcloud/'
  '\.docker/config\.json'
  '\.docker/key\.json'
  '\.kube/config'
  '\.kube/.*\.kubeconfig'
  '\.npmrc'
  '\.yarnrc'
  '\.yarnrc\.yml'
  '\.git-credentials'
  '\.netrc'
  '\.vault[-_]token'
  '\.my\.cnf'
  '\.pgpass'
  '\.config/gh/hosts\.yml'
  '\.composer/auth\.json'
  '\.config/hub'
  '\.local/share/keyrings/'
  '\.config/filezilla/sitemanager\.xml'
  '\.s3cfg'
)

EXT_PATTERNS=(
  '\.pem$'
  '\.key$'
  '\.p12$'
  '\.pfx$'
  '\.jks$'
  '\.keystore$'
  '\.pkcs12$'
)

FILENAME_PATTERNS=(
  'wp-config\.php$'
  '/\.env$'
  '/\.env\.'
  '(^|/)\.env$'
  'secrets\.(json|ya?ml)$'
  'credentials\.(json|ya?ml)$'
  '\.htpasswd$'
  'config/master\.key$'
  '\.tfvars(\.json)?$'
  'terraform\.tfstate'
  'id_rsa'
  'id_ed25519'
  'id_ecdsa'
  'id_dsa'
)

SYSTEM_PATTERNS=(
  '/etc/shadow$'
  '/etc/gshadow$'
  '/etc/ssl/private/'
  '/root/\.ssh/'
  '/etc/letsencrypt/.*/privkey\.pem$'
)

# Build combined regex
build_regex() {
  local parts=()
  for p in "${HOME_PATTERNS[@]}"; do parts+=("(/(${p}))"); done
  for p in "${EXT_PATTERNS[@]}"; do parts+=("(${p})"); done
  for p in "${FILENAME_PATTERNS[@]}"; do parts+=("(${p})"); done
  for p in "${SYSTEM_PATTERNS[@]}"; do parts+=("(${p})"); done
  local IFS='|'
  echo "${parts[*]}"
}

CRED_REGEX=$(build_regex)

check_path() {
  local filepath="$1"
  [ -z "$filepath" ] && return 1
  # Expand ~ to actual home
  filepath="${filepath/#\~/$HOME}"
  if echo "$filepath" | grep -qEi "$CRED_REGEX"; then
    return 0  # matches = credential
  fi
  return 1
}

# --- Tool: Read ---
if [ "$TOOL" = "Read" ]; then
  FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
  if check_path "$FILE_PATH"; then
    echo "BLOCKED: Reading credential file is prohibited: $FILE_PATH" >&2
    exit 2
  fi
  exit 0
fi

# --- Tool: mcp__ssh-context__fs_read ---
if [ "$TOOL" = "mcp__ssh-context__fs_read" ]; then
  FILE_PATH=$(echo "$INPUT" | jq -r '.tool_input.path // empty')
  if check_path "$FILE_PATH"; then
    echo "BLOCKED: Reading remote credential file is prohibited: $FILE_PATH" >&2
    exit 2
  fi
  exit 0
fi

# --- Tool: Bash ---
if [ "$TOOL" = "Bash" ]; then
  COMMAND=$(echo "$INPUT" | jq -r '.tool_input.command // empty')
  [ -z "$COMMAND" ] && exit 0

  # File-reading commands to intercept
  READ_CMDS='(cat|head|tail|less|more|xxd|strings|od|base64|openssl|cp|scp|rsync|tee)'

  # Extract potential file paths from commands using read commands
  if echo "$COMMAND" | grep -qEi "^[[:space:]]*${READ_CMDS}[[:space:]]"; then
    ARGS=$(echo "$COMMAND" | sed -E "s/^[[:space:]]*${READ_CMDS}[[:space:]]+//" | tr ' ' '\n' | grep -v '^-' || true)
    while IFS= read -r arg; do
      [ -z "$arg" ] && continue
      if check_path "$arg"; then
        echo "BLOCKED: Bash command reads credential file: $arg" >&2
        exit 2
      fi
    done <<< "$ARGS"
  fi

  # Also check piped commands
  if echo "$COMMAND" | grep -qE '\|'; then
    PIPE_PARTS=$(echo "$COMMAND" | tr '|' '\n')
    while IFS= read -r part; do
      part=$(echo "$part" | sed 's/^[[:space:]]*//')
      if echo "$part" | grep -qEi "^${READ_CMDS}[[:space:]]"; then
        ARGS=$(echo "$part" | sed -E "s/^${READ_CMDS}[[:space:]]+//" | tr ' ' '\n' | grep -v '^-' || true)
        while IFS= read -r arg; do
          [ -z "$arg" ] && continue
          if check_path "$arg"; then
            echo "BLOCKED: Bash piped command reads credential file: $arg" >&2
            exit 2
          fi
        done <<< "$ARGS"
      fi
    done <<< "$PIPE_PARTS"
  fi

  # Check semicolon/&& chained commands
  if echo "$COMMAND" | grep -qE '(;|&&|\|\|)'; then
    CHAIN_PARTS=$(echo "$COMMAND" | sed 's/&&/\n/g; s/||/\n/g; s/;/\n/g')
    while IFS= read -r part; do
      part=$(echo "$part" | sed 's/^[[:space:]]*//')
      if echo "$part" | grep -qEi "^${READ_CMDS}[[:space:]]"; then
        ARGS=$(echo "$part" | sed -E "s/^${READ_CMDS}[[:space:]]+//" | tr ' ' '\n' | grep -v '^-' || true)
        while IFS= read -r arg; do
          [ -z "$arg" ] && continue
          if check_path "$arg"; then
            echo "BLOCKED: Bash chained command reads credential file: $arg" >&2
            exit 2
          fi
        done <<< "$ARGS"
      fi
    done <<< "$CHAIN_PARTS"
  fi

  exit 0
fi

# Unknown tool — allow
exit 0
