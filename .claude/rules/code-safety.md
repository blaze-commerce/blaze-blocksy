# Code Safety (BLOCKING)
## custom/ is Append-Only (CRITICAL)
`custom/custom.php`, `custom/custom.css`, `custom/custom.js` are **loaders only**.
Writing feature code directly into these files is PROHIBITED — no exceptions.
Only permitted change: appending a `require_once` or `wp_enqueue_*` line to load a new dedicated file.
## Git Rules (BLOCKING)
- NEVER commit directly to `main` — always work in a branch and open a PR
- ALWAYS work in a git worktree — never edit files in the main checkout
- Run tests before committing (see `## Testing` in CLAUDE.md)
- Sync with main before starting: `git fetch origin && git merge origin/main`
## Verification Before Completion
Never claim "fixed" or "done" without showing evidence (test output, screenshot, or command result).
