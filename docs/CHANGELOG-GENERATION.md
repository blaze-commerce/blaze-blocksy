# Changelog Generation Guide

This document explains how the automated changelog generation works in the blaze-blocksy repository and how developers can contribute to maintaining high-quality release notes.

## Overview

The repository uses an enhanced changelog generation system that:
- **Automatically categorizes** conventional commits into proper changelog sections
- **Maintains manual entries** when developers add detailed information to the `[Unreleased]` section
- **Follows Keep a Changelog format** with proper categorization
- **Generates detailed release notes** for every version bump

## How It Works

### Automatic Generation (Default)

When the release workflow runs and the `[Unreleased]` section is empty or minimal, the system:

1. **Analyzes conventional commits** since the last release
2. **Categorizes them** into appropriate sections:
   - `feat:` → **Added**
   - `fix:` → **Fixed** 
   - `docs:` → **Documentation**
   - `style:`, `refactor:`, `perf:`, `chore:` → **Changed**
   - `security:` → **Security**
3. **Generates proper changelog sections** following Keep a Changelog format
4. **Creates the new version entry** with categorized changes

### Manual Enhancement (Recommended)

For better release notes, developers can manually update the `[Unreleased]` section:

```markdown
## [Unreleased]

### Added
- Complete checkout sidebar widget area with WordPress admin integration
- Comprehensive responsive design controls for all viewport sizes
- Professional admin interface with tabbed navigation

### Changed
- Enhanced security with XSS prevention and input sanitization
- Improved performance with conditional asset loading

### Fixed
- jQuery availability check to prevent JavaScript errors
- Safe file inclusion to prevent fatal errors

### Documentation
- Added comprehensive usage instructions and troubleshooting guide
- Updated compatibility matrix for different checkout systems
```

## Conventional Commit Mapping

The system maps conventional commit types to changelog categories:

| Commit Type | Changelog Section | Example |
|-------------|------------------|---------|
| `feat:` | **Added** | `feat: add checkout sidebar widget area` |
| `fix:` | **Fixed** | `fix: resolve jQuery dependency issue` |
| `docs:` | **Documentation** | `docs: update installation guide` |
| `style:` | **Changed** | `style: improve CSS formatting` |
| `refactor:` | **Changed** | `refactor: optimize database queries` |
| `perf:` | **Changed** | `perf: improve page load times` |
| `test:` | **Changed** | `test: add unit tests for new features` |
| `chore:` | **Changed** | `chore: update dependencies` |
| `ci:` | **Changed** | `ci: improve GitHub Actions workflow` |
| `security:` | **Security** | `security: fix XSS vulnerability` |

### Breaking Changes

Breaking changes are automatically detected and marked:
- `feat!:` or `fix!:` → **Changed** section with `**BREAKING:**` prefix
- Commits with `BREAKING CHANGE:` in body → **Changed** section

## Developer Workflow

### Option 1: Automatic Generation (Minimal Effort)

1. **Use conventional commits** for all changes
2. **Let the system generate** changelog entries automatically
3. **Review the generated changelog** after release

### Option 2: Manual Enhancement (Recommended)

1. **Use conventional commits** for all changes
2. **Before merging major features**, update the `[Unreleased]` section with detailed descriptions
3. **Add context and details** that automatic generation can't provide
4. **The system preserves** your manual entries during release

### Option 3: Hybrid Approach (Best Practice)

1. **Use conventional commits** for all changes
2. **Add manual entries** for major features and important changes
3. **Let automatic generation** handle minor updates and fixes
4. **Review and refine** the final changelog after release

## Best Practices

### Writing Good Manual Changelog Entries

```markdown
### Added
- **Feature Name**: Brief description of what was added and why it's valuable
- **Integration**: Specific integration details that users need to know
- **Compatibility**: New compatibility or system requirements

### Changed
- **Breaking Changes**: Clear description of what changed and migration steps
- **Improvements**: Performance, security, or usability improvements
- **Updates**: Dependency updates or configuration changes

### Fixed
- **Bug Fixes**: Description of the issue and how it was resolved
- **Security Fixes**: Security vulnerabilities that were addressed
- **Compatibility**: Fixes for specific environments or configurations
```

### Conventional Commit Best Practices

```bash
# Good examples
feat(checkout): add sidebar widget area with admin controls
fix(security): prevent XSS in widget content output
docs(readme): update installation instructions
perf(css): optimize stylesheet loading performance

# Breaking changes
feat(api)!: change authentication method to JWT
fix(database)!: update schema for better performance

# With detailed body
feat(checkout): add responsive design controls

Add comprehensive responsive design controls for checkout
customization with separate viewport configurations for
desktop, tablet, and mobile breakpoints.

BREAKING CHANGE: Old CSS classes have been renamed to
follow new naming convention.
```

## Using the Changelog Generation Script

The repository includes a helper script for manual changelog generation with enhanced error handling and validation:

```bash
# Generate changelog from recent commits
python3 scripts/generate-changelog.py --update-unreleased

# Generate changelog from specific range
python3 scripts/generate-changelog.py --from-commits v1.0.0..HEAD

# Generate changelog since specific tag
python3 scripts/generate-changelog.py --since-tag v1.0.0

# Output to file instead of stdout
python3 scripts/generate-changelog.py --from-commits v1.0.0..HEAD --output changelog-preview.md
```

### Script Features

- **Input Validation**: Validates git command format and tag ranges for security
- **Error Handling**: Comprehensive error handling with informative logging
- **Timeout Protection**: Commands timeout after 30 seconds to prevent hanging
- **Type Safety**: Full type annotations for better code maintainability
- **Shared Utilities**: Uses common functions to ensure consistency across tools

## Troubleshooting

### Empty Changelog Sections

**Problem**: New version has empty sections
**Solution**: 
1. Check if commits follow conventional format
2. Manually add entries to `[Unreleased]` section before release
3. Use the generation script to preview what will be generated

### Missing Categories

**Problem**: Some changes don't appear in expected categories
**Solution**:
1. Review conventional commit type mapping above
2. Use manual entries for complex changes
3. Ensure commit messages follow conventional format

### Duplicate Entries

**Problem**: Manual and automatic entries create duplicates
**Solution**:
1. The system preserves manual entries and skips automatic generation
2. Keep `[Unreleased]` section updated to prevent automatic generation
3. Review and clean up after release if needed

## Release Workflow Integration

The changelog generation is integrated into the release workflow:

1. **PR Merged** → Release workflow triggers
2. **Commits Analyzed** → System checks for conventional commits
3. **Unreleased Section Checked** → Manual entries take precedence
4. **Changelog Generated** → Automatic or manual content used
5. **Version Created** → New version section added to CHANGELOG.md
6. **Release Published** → GitHub release created with changelog content

## Migration from Old System

If you're migrating from the old system where changelog entries were missing:

1. **Review recent releases** and add missing information manually
2. **Update the `[Unreleased]` section** with current development changes
3. **Start using conventional commits** for all new changes
4. **The next release** will have properly formatted changelog entries

## Examples

### Before (Empty Sections)
```markdown
## [1.13.0] - 2025-08-28

## [1.12.0] - 2025-08-28
```

### After (Automatic Generation)
```markdown
## [1.13.0] - 2025-08-28

### Added
- Comprehensive testing framework implementation with code review enhancements

### Changed
- Enhanced error handling with logging and graceful degradation
- Improved input validation with proxy support

### Fixed
- Memory usage optimization for image processing
- Enhanced file upload security with content scanning

## [1.12.0] - 2025-08-28

### Added
- Checkout sidebar widget area with security improvements
- Professional styling with customization options

### Changed
- Enhanced security with XSS prevention and input sanitization
- Improved performance with conditional asset loading
```

This system ensures that every release has meaningful, categorized changelog entries that help users understand what changed and why it matters.
