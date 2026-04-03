#!/usr/bin/env bash
set -euo pipefail
# enforce-enqueue-filemtime.sh — Block static version strings in wp_enqueue_style/script
# Forces filemtime() or dynamic version for all local theme assets.
# PreToolUse hook — Edit|Write|NotebookEdit

INPUT=$(cat)
TOOL=$(echo "$INPUT" | jq -r '.tool_name // empty')
[ -z "$TOOL" ] && exit 0

# ── Only intercept Edit or Write ─────────────────────────────────────────────
[ "$TOOL" = "Edit" ] || [ "$TOOL" = "Write" ] || exit 0

# ── Determine file path and content ──────────────────────────────────────────
FILE=$(echo "$INPUT" | jq -r '.tool_input.file_path // empty')
[ -z "$FILE" ] && exit 0

case "$FILE" in
  *.php) ;;
  *) exit 0 ;;
esac

if [ "$TOOL" = "Edit" ]; then
  CONTENT=$(echo "$INPUT" | jq -r '.tool_input.new_string // empty')
else
  CONTENT=$(echo "$INPUT" | jq -r '.tool_input.content // empty')
fi
[ -z "$CONTENT" ] && exit 0

# ── Quick bail: no enqueue call in content ───────────────────────────────────
if ! echo "$CONTENT" | grep -qiE 'wp_enqueue_(style|script)[[:space:]]*\('; then
  exit 0
fi

# ── Python parser: find enqueue calls with static version strings ────────────
# Write Python to a temp file (avoids single-quote/heredoc-in-subshell issues)
TMP_PY=$(mktemp /tmp/efmt-XXXXXX.py)
trap 'rm -f "$TMP_PY"' EXIT

cat > "$TMP_PY" << 'PYEOF'
import sys, os, re

content = os.environ.get("_EFMT_CONTENT", "")

LOCAL_URL_MARKERS = [
    "get_stylesheet_directory_uri", "get_template_directory_uri",
    "BLAZE_BLOCKSY_URL", "BLAZE_BLOCKSY_PATH",
    "plugins_url(", "plugin_dir_url(", "get_theme_file_uri",
    "get_parent_theme_file_uri", "$template_uri", "$stylesheet_uri",
]

def is_external_cdn(src_arg):
    stripped = src_arg.strip().strip("'\"")
    if re.match(r"^https?://", stripped):
        for marker in LOCAL_URL_MARKERS:
            if marker in src_arg:
                return False
        return True
    return False

def is_static_version(ver_arg):
    stripped = ver_arg.strip()
    return bool(re.match(r"""^['"]\d[\d.\-]*['"]$""", stripped))

def is_dynamic_version(ver_arg):
    stripped = ver_arg.strip()
    if stripped.lower() in ("null", "false", "true"):
        return True
    if stripped.startswith("$"):
        return True
    if re.match(r"^[A-Z][A-Z0-9_]{2,}$", stripped):
        return True  # ALL_CAPS constant
    if "filemtime" in stripped:
        return True
    if "(" in stripped:
        return True  # any function/method call
    return False

def extract_args(content, start):
    depth = 0
    args = []
    cur = ""
    i = start
    in_sq = in_dq = False
    while i < len(content):
        ch = content[i]
        if ch == "'" and not in_dq:
            in_sq = not in_sq
            cur += ch
        elif ch == '"' and not in_sq:
            in_dq = not in_dq
            cur += ch
        elif in_sq or in_dq:
            cur += ch
        elif ch == "(":
            depth += 1
            cur += ch
        elif ch == ")":
            if depth == 0:
                if cur.strip():
                    args.append(cur.strip())
                return args
            depth -= 1
            cur += ch
        elif ch == "," and depth == 0:
            args.append(cur.strip())
            cur = ""
        else:
            cur += ch
        i += 1
    return args

pattern = re.compile(r"wp_enqueue_(style|script)\s*\(", re.IGNORECASE)
violations = []

for m in pattern.finditer(content):
    try:
        paren = content.index("(", m.start())
    except ValueError:
        continue
    args = extract_args(content, paren + 1)
    if len(args) < 4:
        continue
    src_arg = args[1]
    ver_arg = args[3]
    if is_external_cdn(src_arg):
        continue
    if is_static_version(ver_arg) and not is_dynamic_version(ver_arg):
        handle = args[0].strip().strip("'\"")
        fname = m.group(0).rstrip("(").strip()
        print(f"{fname}('{handle}', ..., {ver_arg})")
PYEOF

export _EFMT_CONTENT="$CONTENT"
VIOLATIONS=$(python3 "$TMP_PY")
unset _EFMT_CONTENT

if [ -n "$VIOLATIONS" ]; then
  echo "" >&2
  echo "BLOCKED: Static version string in wp_enqueue_style/wp_enqueue_script." >&2
  echo "" >&2
  echo "  Violations found:" >&2
  echo "$VIOLATIONS" | while IFS= read -r line; do
    echo "    -> $line -- static version detected" >&2
  done
  echo "" >&2
  echo "  Use filemtime() for automatic cache busting on local theme assets:" >&2
  echo "    wp_enqueue_style( 'handle', BLAZE_BLOCKSY_URL . '/file.css', array(), filemtime( BLAZE_BLOCKSY_PATH . '/file.css' ) );" >&2
  echo "    wp_enqueue_script( 'handle', BLAZE_BLOCKSY_URL . '/file.js', array(), filemtime( BLAZE_BLOCKSY_PATH . '/file.js' ), true );" >&2
  echo "" >&2
  echo "  Or use a dynamic alternative:" >&2
  echo "    \$theme_version, BLAZE_BLOCKSY_VERSION, wp_get_theme()->get('Version'), null, false" >&2
  echo "" >&2
  echo "  CDN/external URLs with static versions are allowed (pinned by design)." >&2
  echo "" >&2
  exit 2
fi

exit 0
