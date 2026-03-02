# Claude Command: Commit

Create well-formatted commits using conventional commit messages (no emoji).

## Usage

```
/commit [--no-verify]
```

## Steps

1. Run pre-commit checks unless `--no-verify`
2. `git status` — if nothing staged, `git add` all modified/new files
3. `git diff` — understand what's changing
4. If multiple distinct concerns detected, split into separate commits
5. Write commit message: `<type>: <description>` (plain, no emoji)

## Commit Types

`feat` · `fix` · `docs` · `style` · `refactor` · `perf` · `test` · `chore`

## Rules

- Present tense, imperative mood — "add feature" not "added feature"
- First line under 72 chars
- Do NOT add Claude as co-author or Co-Authored-By header
- Split commits when changes touch unrelated concerns

## When to Split

- Different concerns (feature code vs. config vs. docs)
- Different change types mixed together
- Changes large enough to review separately

## Examples

- `feat: add currency visibility toggle`
- `fix: resolve sticky header z-index on mobile`
- `docs: update CLAUDE.md with git workflow rules`
- `chore: add Claude CLI team safety hooks and settings`
- `refactor: simplify error handling in checkout module`
