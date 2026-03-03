# Claude Command: Commit and PR

Combines the commit workflow with creating a pull request for the current branch.

## Steps

1. Follow all instructions from `.claude/commands/commitpush.md` (commit + push)
2. Create a pull request with `gh pr create`:
   - Analyze all commits in the branch for full scope
   - Write a comprehensive description: summary bullets, test plan checklist, related issues
   - Use a clear title based on the primary changes

## Options

- `--no-verify`: Skip pre-commit checks

## Notes

- Commit process must succeed before push or PR creation is attempted
- If push fails due to conflicts, resolve manually before creating the PR
- PR covers all commits from where the branch diverged from the base branch
- Call out breaking changes and include screenshots if UI changes are involved
