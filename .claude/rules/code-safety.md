# Code Safety (BLOCKING)
## custom/ is Append-Only (CRITICAL)
`custom/custom.php`, `custom/custom.css`, `custom/custom.js` are **loaders only**.
Writing feature code directly into these files is PROHIBITED — no exceptions.
Only permitted change: appending a `require_once` or `wp_enqueue_*` line to load a new dedicated file.
## includes/ is Modular-Only (CRITICAL)
`functions.php` and `includes/scripts.php` are **loaders only**.
Writing feature code directly into these files is PROHIBITED — no exceptions.
Only permitted change: append a path to `$required_files` in `functions.php`, or append a `wp_enqueue_*` call in `scripts.php`.
All generic features must be dedicated files in `includes/features/`, `includes/customization/`, etc.
All generic PHP functions MUST use the `blaze_blocksy_` prefix.
CSS/JS: one asset file per feature — no monolithic assets.
## Git Rules (BLOCKING)
- NEVER commit directly to `main` — always work in a branch and open a PR
- ALWAYS work in a git worktree — never edit files in the main checkout
- Run tests before committing (see `## Testing` in CLAUDE.md)
- Sync with main before starting: `git fetch origin && git merge origin/main`
## Verification Before Completion
Never claim "fixed" or "done" without showing evidence (test output, screenshot, or command result).
