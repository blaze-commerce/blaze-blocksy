# Claude Command: Commit

Create well-formatted commits using conventional commit messages (no emoji).

## Usage

```
/commit
```

Or with options:

```
/commit --no-verify
```

## What This Command Does

1. Unless specified with `--no-verify`, automatically runs pre-commit checks
2. Checks which files are staged with `git status`
3. If 0 files are staged, automatically adds all modified and new files with `git add`
4. Performs a `git diff` to understand what changes are being committed
5. Analyzes the diff to determine if multiple distinct logical changes are present
6. If multiple distinct changes are detected, suggests breaking the commit into multiple smaller commits
7. For each commit (or the single commit if not split), creates a commit message using conventional commit format

## Commit Message Format

Use the format `<type>: <description>` — plain text, no emoji:

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc)
- `refactor`: Code changes that neither fix bugs nor add features
- `perf`: Performance improvements
- `test`: Adding or fixing tests
- `chore`: Changes to the build process, tools, etc.

## Best Practices

- **Verify before committing**: Ensure code is linted, builds correctly, and tests pass
- **Atomic commits**: Each commit should contain related changes that serve a single purpose
- **Split large changes**: If changes touch multiple concerns, split them into separate commits
- **Present tense, imperative mood**: Write commit messages as commands (e.g., "add feature" not "added feature")
- **Concise first line**: Keep the first line under 72 characters

## Examples

Good commit messages:

- `feat: add user authentication system`
- `fix: resolve memory leak in rendering process`
- `docs: update API documentation with new endpoints`
- `refactor: simplify error handling logic in parser`
- `fix: resolve linter warnings in component files`
- `chore: improve developer tooling setup process`
- `feat: implement business logic for transaction validation`
- `fix: address minor styling inconsistency in header`
- `fix: patch critical security vulnerability in auth flow`
- `style: reorganize component structure for better readability`

Example of splitting commits:

- First commit: `feat: add new solc version type definitions`
- Second commit: `docs: update documentation for new solc versions`
- Third commit: `chore: update package.json dependencies`
- Fourth commit: `feat: add type definitions for new API endpoints`
- Fifth commit: `test: add unit tests for new solc version features`
- Sixth commit: `fix: resolve linting issues in new code`

## Guidelines for Splitting Commits

When analyzing the diff, consider splitting commits based on these criteria:

1. **Different concerns**: Changes to unrelated parts of the codebase
2. **Different types of changes**: Mixing features, fixes, refactoring, etc.
3. **File patterns**: Changes to different types of files (e.g., source code vs documentation)
4. **Logical grouping**: Changes that would be easier to understand or review separately
5. **Size**: Very large changes that would be clearer if broken down

## Command Options

- `--no-verify`: Skip running the pre-commit checks

## Important Notes

- By default, pre-commit checks will run to ensure code quality
- If these checks fail, you'll be asked if you want to proceed with the commit anyway or fix the issues first
- If specific files are already staged, the command will only commit those files
- If no files are staged, it will automatically stage all modified and new files
- Do NOT add Claude as co-author or Co-Authored-By header
- Before committing, the command will review the diff to identify if multiple commits would be more appropriate
- If suggesting multiple commits, it will help you stage and commit the changes separately
- Always reviews the commit diff to ensure the message matches the changes
