# Claude Command: Commit and Push

Combines the commit workflow with pushing changes to the current branch.

## Usage

```
/commitpush [--no-verify]
```

## Steps

1. **Review changes** — note all modified, added, or deleted files and their scope
2. **Quality checks** — run lint if available; fix errors before proceeding
3. **Update docs** — if new/moved/deleted files exist, update `README.md` and `CLAUDE.md` to reflect current state; character limits must pass after any edits
4. **Commit** — follow all instructions from `.claude/commands/commit.md`
5. **Push** — `git push` to sync with remote

## Options

- `--no-verify`: Skip pre-commit checks

## Notes

- If commit fails, no push is attempted
- If push fails due to conflicts, resolve manually
- Do NOT add Claude as co-author or Co-Authored-By header
