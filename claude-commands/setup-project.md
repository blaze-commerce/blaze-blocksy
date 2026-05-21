# Setup Project

Bootstrap a new Blaze Commerce project using the Blocksy child theme. This command handles credential setup, command installation, and project initialization.

## What This Command Does

1. **Checks for local project config** in Claude Code memory
2. **If missing, asks for credentials** (ClickUp API key, Figma token, project IDs)
3. **Saves credentials to Claude Code memory** (local only, never committed to git)
4. **Copies claude-commands** from the child theme to the WordPress root `.claude/commands/`
5. **Creates project-specific files** (CHANGELOG.md, GO-LIVE.md if they don't exist)

## Implementation

### Step 1: Detect Environment

```python
import os, json

# Find the WordPress root (parent of wp-content)
theme_dir = None
for candidate in [
    os.path.join(os.getcwd(), "wp-content/themes/blocksy-child"),
    "/www/*/public/wp-content/themes/blocksy-child",
]:
    import glob
    matches = glob.glob(candidate)
    if matches:
        theme_dir = matches[0]
        break

if not theme_dir:
    print("ERROR: Cannot find blocksy-child theme directory")
    exit(1)

# Derive WordPress root (3 levels up from theme dir)
wp_root = os.path.dirname(os.path.dirname(os.path.dirname(theme_dir)))
print(f"Theme directory: {theme_dir}")
print(f"WordPress root: {wp_root}")
```

### Step 2: Check for Existing Config

The project config is stored in Claude Code's memory directory, which is per-project and never committed to git.

Check if the config file exists at the Claude Code memory path. The memory path follows the pattern:
```
~/.claude/projects/{sanitized-wp-root-path}/memory/project-config.json
```

If the config file exists, read it and confirm the credentials are still valid. If it doesn't exist, proceed to Step 3.

### Step 3: Ask for Credentials

If no config exists, ask the user for the following using the AskUserQuestion tool or direct prompts:

**Required credentials:**

| Field | Description | Example |
|-------|-------------|---------|
| `clickup_api_key` | ClickUp API token (starts with `pk_`) | `pk_12345678_ABCDEF...` |
| `clickup_workspace_id` | ClickUp Workspace ID | `36771024` |
| `clickup_list_id` | ClickUp Task List ID for this project | `901815988136` |
| `clickup_doc_id` | ClickUp Doc ID for project documents | `13256g-163598` |
| `figma_token` | Figma Personal Access Token | `figd_r47HPR...` |
| `figma_file_key` | Figma file key (from URL) | `abc123XYZ` |
| `figma_handover_page` | Figma page name or ID containing handover | `HANDOVER` |
| `client_name` | Client/project name | `Byron Bay Candles` |
| `site_url` | Site URL | `https://example.kinsta.cloud` |

**Optional (can be added later):**

| Field | Description |
|-------|-------------|
| `clickup_page_ids` | Map of doc filenames to ClickUp page IDs |
| `team_members` | Map of member IDs to names |
| `git_remote` | Git remote URL |

### Step 4: Save Config to Memory

Save the config as `project-config.json` in the Claude Code memory directory:

```python
config = {
    "client_name": client_name,
    "site_url": site_url,
    "clickup": {
        "api_key": clickup_api_key,
        "workspace_id": workspace_id,
        "list_id": list_id,
        "doc_id": doc_id,
        "page_ids": {}
    },
    "figma": {
        "token": figma_token,
        "file_key": figma_file_key,
        "handover_page": handover_page,
        "nodes": {
            "colors": null,
            "typography": null,
            "forms": null
        }
    }
}
# Write to Claude Code memory path (derive from wp_root)
```

### Step 5: Install Claude Commands

Copy all `.md` files from the child theme's `claude-commands/` to the WordPress root's `.claude/commands/`:

```bash
# Create target directory
mkdir -p ${WP_ROOT}/.claude/commands/

# Copy all command files
cp ${THEME_DIR}/claude-commands/*.md ${WP_ROOT}/.claude/commands/

# Verify
ls -la ${WP_ROOT}/.claude/commands/
```

### Step 6: Initialize Project Files

Create these files at the WordPress root if they don't exist:

- `CHANGELOG.md` — with standard header and first entry
- `GO-LIVE.md` — with standard template
- `PROJECT-AUDIT.md` — empty template

### Step 7: Verify Setup

Run verification checks:

```bash
# WordPress is installed
wp core version

# Blocksy theme is installed
wp theme list --name=blocksy --format=table

# Child theme is active
wp theme list --status=active --format=table

# WooCommerce is active (if e-commerce project)
wp plugin is-active woocommerce && echo "WooCommerce: active" || echo "WooCommerce: not active"
```

### Output

After setup, display:

```
PROJECT SETUP COMPLETE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Client:       {client_name}
Site URL:     {site_url}
Theme:        Blocksy Child (active)
ClickUp:      Connected (list {list_id})
Figma:        Connected (file {file_key})

Commands installed:
  /setup-foundation  — Apply Figma colors, typography, layout to Blocksy
  /setup-project     — This command (re-run to update config)

Next steps:
  1. Run /setup-foundation to apply Figma handover design
  2. Review Customizer settings in wp-admin
  3. Start building pages with Gutenberg

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## Important Notes

- **Credentials are NEVER committed to git** — they live in Claude Code's memory directory only
- **Re-running this command is safe** — it will detect existing config and offer to update
- **Each project clone gets its own config** — memory is per-project-path
- **If Claude Code memory is cleared**, re-run this command to re-enter credentials
