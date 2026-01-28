# Claude Command: Commit and PR

This command combines the commit workflow with creating a pull request for the current branch.

1. **Executes the commit push workflow:**

   - Loads and follows all instructions from `.claude/commands/commitpush.md`

2. **Finally, creates a pull request:**
   - Uses GitHub CLI (`gh pr create`) to create a pull request
   - Analyzes all commits in the branch to understand the full scope of changes
   - Creates a comprehensive PR description including:
     - Summary of changes with bullet points
     - Test plan with actionable items
     - Links to relevant issues if applicable
   - Uses a clear, descriptive title based on the primary changes
   - Sets appropriate labels and reviewers if configured

## Command Options

- `--no-verify`: Skip running the pre-commit checks (lint, build, etc.)

## Important Notes

- This command will first complete the entire commit process before pushing
- If the commit process fails, no push or PR creation will be attempted
- The push will include all commits created during the commit phase
- If the push fails (e.g., due to conflicts), you'll need to resolve them manually before PR creation
- The PR will include all commits from the point where the branch diverged from the base branch
- PR description will be comprehensive and include testing instructions
- The PR title will be based on the primary feature/fix being introduced

## PR Description Format

The pull request will include:

- **Summary**: Clear bullet points describing what was changed
- **Test Plan**: Actionable checklist for reviewing and testing the changes
- **Related Issues**: Links to GitHub issues if applicable
- **Breaking Changes**: Called out if any exist
- **Screenshots**: If UI changes are involved

## Example PR Titles

- `feat: implement user authentication system`
- `fix: resolve memory leak in tournament creation`
- `refactor: improve code organization for better maintainability`
- `docs: update API documentation with new endpoints`
