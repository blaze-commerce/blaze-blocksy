# Claude Command: Commit and Push

This command combines the commit workflow with pushing changes to the current branch.

## Usage

```
/commitpush
```

Or with options:

```
/commitpush --no-verify
```

## What This Command Does

1. **First, takes note of all changes:**

   - Reviews and documents what files have been modified, added, or deleted
   - Understands the scope and nature of the changes being committed

2. **Refactors code and ensures build success:**

   - Refactors the code following the guidelines in .claude/commands/refactor.md for code quality
   - Run `pnpm lint` command to ensure code quality
   - Fixes any lint errors that are discovered before proceeding
   - Runs the build command to ensure there are no build errors
   - Fixes any build errors that are discovered before proceeding
   - Ensures code follows project conventions and quality standards

3. **Updates file structure documentation:**

   - Loads and scans `docs/project-structure.md` to understand the current documented structure
   - Analyzes the changes from step 1 to determine if file structure updates are needed
   - Updates `docs/project-structure.md` if:
     - New files or directories have been added that should be documented
     - Existing files have been moved, renamed, or deleted
     - The brief summary of any file's purpose needs updating based on changes
     - New documentation files have been created that should be listed
   - Ensures the file structure documentation remains accurate and comprehensive

4. **Then, executes the commit workflow:**

   - Loads and follows all instructions from `.claude/commands/commit.md`
   - Runs pre-commit checks (unless `--no-verify` is specified)
   - Stages files if needed
   - Analyzes changes and creates appropriate commit messages without adding Claude as a committer
   - Creates one or more commits as needed
   - Once again, please don't add Claude cod or Co-Authored-By Claude as a committer

5. **Finally, pushes the changes:**
   - Pushes all commits to the current branch
   - Uses `git push` to sync with the remote repository

## Command Options

- `--no-verify`: Skip running the pre-commit checks (lint, build, etc.)

## Important Notes

- This command will first complete the entire commit process before pushing
- If the commit process fails, no push will be attempted
- The push will include all commits created during the commit phase
- If the push fails (e.g., due to conflicts), you'll need to resolve them manually
