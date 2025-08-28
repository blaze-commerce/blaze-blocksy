# Semantic Versioning and Release Management

This document explains the automated versioning strategy and release management process for the Blocksy Child Theme.

## Overview

This repository uses **Semantic Versioning (SemVer)** with automated release management through GitHub Actions. Version numbers follow the `MAJOR.MINOR.PATCH` format:

- **MAJOR**: Breaking changes that require manual intervention
- **MINOR**: New features that are backward compatible  
- **PATCH**: Bug fixes and minor improvements

## Conventional Commits

The automated versioning system analyzes commit messages to determine the appropriate version bump. Use these conventional commit formats:

### Commit Message Format

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Commit Types and Version Bumps

| Commit Type | Version Bump | Example |
|-------------|--------------|---------|
| `fix:` | **PATCH** | `fix: resolve mobile menu alignment issue` |
| `feat:` | **MINOR** | `feat: add responsive visibility classes` |
| `feat!:` or `fix!:` | **MAJOR** | `feat!: restructure theme template hierarchy` |
| `BREAKING CHANGE:` in footer | **MAJOR** | Any commit with `BREAKING CHANGE:` in the footer |

### Additional Commit Types (No Version Bump)

- `docs:` - Documentation changes
- `style:` - Code formatting, missing semicolons, etc.
- `refactor:` - Code refactoring without feature changes
- `test:` - Adding or updating tests
- `chore:` - Build process, dependency updates, etc.

## Examples

### Patch Release (Bug Fix)
```bash
git commit -m "fix: correct z-index for sticky header on mobile devices"
```
**Result**: `1.2.3` → `1.2.4`

### Minor Release (New Feature)
```bash
git commit -m "feat: add dark mode support for theme components"
```
**Result**: `1.2.3` → `1.3.0`

### Major Release (Breaking Change)
```bash
git commit -m "feat!: remove deprecated CSS classes and restructure responsive utilities

BREAKING CHANGE: The following CSS classes have been removed:
- .old-mobile-class
- .deprecated-tablet-class

Migration guide: Replace old classes with new responsive utilities."
```
**Result**: `1.2.3` → `2.0.0`

## Automated Release Process

### Trigger Conditions

The release workflow automatically runs when:
1. Pull requests are **approved and merged** into the `main` or `master` branch (required by branch protection rules)
2. Direct pushes to `main` are blocked and will not trigger the workflow

### What Happens During Release

1. **Commit Analysis**: Scans commit messages since the last release
2. **Version Calculation**: Determines the appropriate version bump
3. **File Updates**: Updates the `Version` field in `style.css` header
4. **Git Operations**: Creates a commit with version update and tags the release
5. **Release Creation**: Generates a GitHub release with auto-generated changelog
6. **ZIP Distribution**: Creates a downloadable ZIP file of the theme

### File Updates

The workflow automatically updates:
- **`style.css`**: Adds or updates the `Version:` field in the WordPress theme header

Example of updated header:
```css
/**
 * Theme Name: Blocksy Child
 * Description: Blocksy Child theme
 * Author: Creative Themes
 * Template: blocksy
 * Version: 1.2.3
 * Text Domain: blocksy
 */
```

## WordPress Theme Standards

The versioning follows WordPress theme development standards:

- **Version Format**: Uses semantic versioning (`MAJOR.MINOR.PATCH`)
- **Header Requirement**: Version is stored in the `style.css` header comment
- **Distribution**: Creates ZIP files compatible with WordPress theme installation
- **Changelog**: Maintains release notes for each version

## Best Practices

### Branch Protection Requirements

This repository enforces comprehensive branch protection on `main`:
- **Pull Request approval is required** before merging (minimum 1 approval)
- **All required status checks must pass** before merge is allowed
- **Direct pushes to `main` are blocked** - all changes must go through PRs
- **Conversation resolution required** before merging
- **Stale reviews are dismissed** when new commits are pushed
- **Force pushes are prevented** to maintain history integrity
- **Branch deletion is prevented** to protect the main branch

These rules integrate with the automated release process by ensuring releases are only created from approved, verified changes.

### Pre-commit Hooks (Required)

All developers must install and use pre-commit hooks for code quality and commit message validation:

```bash
# Install pre-commit hooks (one-time setup)
./scripts/setup-pre-commit.sh

# Or manually:
pip install pre-commit
pre-commit install
pre-commit install --hook-type commit-msg
```

**Automated Validations:**
- **Conventional commit format** enforcement
- **PHP syntax checking** for all PHP files
- **CSS/SCSS linting** with automatic fixes
- **File formatting** (trailing whitespace, line endings)
- **Security checks** (private key detection, merge conflicts)
- **File size limits** (prevents large files from being committed)

### Pull Request Validation

Every pull request is automatically validated with:
- **Commit message format** validation for all commits in the PR
- **PR title format** validation (must follow conventional commit format)
- **Breaking change detection** and documentation requirements
- **Code quality checks** via pre-commit hooks

### For Developers

1. **Install Pre-commit Hooks**: Run `./scripts/setup-pre-commit.sh` for initial setup
2. **Use Conventional Commits**: Always follow the conventional commit format (enforced by hooks)
3. **Write Clear Descriptions**: Make commit messages descriptive and specific
4. **Group Related Changes**: Use a single commit for related changes when possible
5. **Test Before Merging**: Ensure all changes work correctly before merging PRs
6. **Respect Branch Protection**: All changes must go through PR review and pass status checks
7. **Follow PR Guidelines**: PR titles must also follow conventional commit format

### For Pull Requests

1. **Conventional PR Titles**: Use conventional commit format in PR titles (automatically validated)
2. **Detailed Descriptions**: Explain what changes were made and why
3. **Breaking Changes**: Clearly document any breaking changes (automatically detected)
4. **Testing Instructions**: Include steps to test the changes
5. **Commit Message Quality**: Ensure all commits follow conventional format (automatically validated)
6. **Approvals & Checks**: Request required approvals and ensure all status checks pass before merging
7. **Conversation Resolution**: Resolve all review conversations before merging

### Examples of Good Commit Messages

```bash
# Good examples
git commit -m "fix: resolve mobile navigation menu overflow on small screens"
git commit -m "feat: add custom CSS classes for Judge.me review carousel"
git commit -m "docs: update installation instructions in README"
git commit -m "refactor: optimize responsive CSS media queries"

# Avoid these
git commit -m "fix stuff"
git commit -m "update"
git commit -m "changes"
```

## Troubleshooting

### If Automatic Release Fails

1. Check the GitHub Actions logs for error details
2. Ensure commit messages follow conventional format
3. Verify the `style.css` file is not corrupted
4. Check repository permissions for the GitHub Actions bot

### Manual Version Override

If you need to manually set a version:

1. Create a branch from `main`: `git checkout -b release/manual-bump-X.Y.Z`
2. Update the `Version:` field in `style.css`
3. Create a commit: `git commit -m "chore: manual version bump to X.Y.Z"`
4. Push the branch and open a PR. Request required approvals and wait for all status checks to pass.
5. After the PR is merged, create a tag on the merge commit: `git tag -a "vX.Y.Z" -m "Release vX.Y.Z" <merge_commit_sha>` then `git push origin --tags`

Note: Direct pushes to `main` are blocked by branch protection rules; all changes must go through PRs.

### Rollback Process

The workflow includes automatic rollback on failure:
- Removes created tags
- Reverts version update commits
- Prevents incomplete releases

## Release Artifacts

Each release creates:
- **Git Tag**: `vX.Y.Z` format
- **GitHub Release**: With auto-generated changelog
- **ZIP File**: `blocksy-child-vX.Y.Z.zip` for WordPress installation
  - ZIP filename includes version for identification
  - Extracted folder is consistently named `blocksy-child` (without version)
- **Changelog**: Lists all changes since the previous release

## Integration with WordPress

The generated ZIP files are ready for WordPress installation:
1. Download the ZIP from the GitHub release
2. Upload via WordPress Admin → Appearance → Themes → Add New → Upload Theme
3. Activate the theme

**Note**: When extracted, the ZIP file will always create a folder named `blocksy-child` regardless of the version number, ensuring consistent folder naming across all releases.

## Version History

All versions and their changes are tracked in:
- GitHub Releases page
- Git tags
- Automatic changelog generation

This ensures complete traceability of all theme changes and versions.
