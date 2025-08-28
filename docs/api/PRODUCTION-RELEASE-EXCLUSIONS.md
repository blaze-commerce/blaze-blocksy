# BlazeCommerce Production Release Exclusions

## Overview

This document describes the comprehensive exclusion patterns implemented in the GitHub Actions release workflow to ensure clean production releases of the BlazeCommerce WordPress child theme.

## Implementation Summary

### Updated Files

1. **`.github/workflows/release.yml`** - Updated the "Create theme ZIP" step with comprehensive exclusion patterns
2. **`.zipignore`** - Created comprehensive exclusion file for manual releases
3. **`scripts/test-release-exclusions.sh`** - Test script to validate exclusion patterns
4. **`scripts/create-production-release.sh`** - Manual release script (alternative)
5. **`scripts/create-production-release.js`** - Node.js release script (alternative)
6. **`package.json`** - Added release-related npm scripts for easy access

### Key Changes to GitHub Actions Workflow

The release workflow now excludes **170+ patterns** covering:

#### Development Dependencies
- `node_modules/`, `vendor/`
- `package.json`, `composer.json`, `*.lock` files
- `yarn.lock`, `pnpm-lock.yaml`, `bun.lockb`

#### Build Artifacts & Compiled Files
- `dist/`, `build/`, `assets/dist/`, `assets/build/`
- `*.map`, `*.css.map`, `*.js.map`, `*.scss.map`
- `.cache/`, `.tmp/`, `.sass-cache/`, `.webpack/`

#### Testing Infrastructure
- `tests/`, `coverage/`, `test-results/`
- `screenshots/`, `playwright-screenshots/`
- `*.log`, `*-baseline.json`, `*-junit.xml`
- `.phpunit.cache/`, `.jest-cache/`

#### Configuration Files
- `jest.config.js`, `playwright.config.js`, `stylelint.config.js`
- `webpack.config.js`, `tsconfig.json`, `babel.config.js`
- `.eslintrc.*`, `.prettierrc.*`, `.stylelintrc.*`
- `phpunit.xml`, `phpunit-*.xml`

#### Documentation & Development Files
- `docs/`, `scripts/`, `bin/`
- `*.md` files (README, CHANGELOG, etc.)
- `agency_logo/`, `performance-optimizations/`

#### Version Control & CI/CD
- `.git/`, `.github/`, `.gitignore`, `.gitattributes`
- `.husky/`, `.augment/`, `.augmentignore`

#### IDE & Editor Files
- `.vscode/`, `.idea/`, `.atom/`, `.history/`
- `*.code-workspace`, `*.iml`, `*.sublime-*`
- `*.swp`, `*.swo`, `*.tmp`

#### Operating System Files
- `.DS_Store`, `Thumbs.db`, `._*`
- `.AppleDouble`, `.LSOverride`, `.fseventsd`
- `.directory`, `.Trash-*`, `$RECYCLE.BIN/`

#### Security & Sensitive Files
- `.env*` (except `.env.example`, `.env.sample`)
- `*.key`, `*.pem`, `*.p12`, `*.pfx`
- `.htpasswd`, `auth.json`, `.secrets`
- `*.sql`, `*.sqlite`, `*.db`

#### Design & Media Files
- `*.psd`, `*.ai`, `*.sketch`, `*.fig`, `*.xd`
- `*.indd`, `*.eps`

#### Python & Other Language Artifacts
- `__pycache__/`, `*.py[cod]`, `*.so`
- `*.egg-info/`, `*.egg`

#### WordPress Specific
- `wp-config-local.php`, `wp-config-staging.php`
- `wp-cli.local.yml`, `wp-cli.yml`
- `.htaccess.backup`, `.maintenance`
- `wp-config-sample.php`, `readme.html`

## Essential Files Preserved

The following essential WordPress theme files are **always included**:

### Required WordPress Theme Files
- `style.css` - Theme stylesheet with header information
- `functions.php` - Theme functionality
- `screenshot.jpg` - Theme screenshot

### Theme Assets
- `assets/css/*.css` - Compiled stylesheets
- `assets/js/*.js` - Compiled JavaScript
- `includes/` - PHP source code
- `woocommerce/` - WooCommerce template overrides

### WordPress Core Files
- Any other PHP files required for theme functionality

## Validation Process

The updated workflow includes a validation step that:

1. **Checks for essential files** - Ensures `style.css` and `functions.php` are present
2. **Warns about development artifacts** - Reports any development files found in the ZIP
3. **Reports ZIP statistics** - Shows file count and size
4. **Displays contents summary** - Lists first 20 files for verification

## Testing the Implementation

### Automated Testing (GitHub Actions)

The validation runs automatically during the release process and will:
- ✅ Pass if all essential files are present and development artifacts are excluded
- ⚠️ Warn if some development files are found but continue the release
- ❌ Fail if essential WordPress theme files are missing

### Manual Testing

Use the test script to validate exclusion patterns locally:

```bash
# Test the exclusion patterns (recommended)
npm run release:test-exclusions

# Alternative: Run script directly
./scripts/test-release-exclusions.sh

# Create a manual release (requires zip command)
npm run release:test

# Alternative: Run script directly
./scripts/create-production-release.sh --skip-build --output-dir test-releases

# Or use the Node.js version
node scripts/create-production-release.js --skip-build --output-dir test-releases

# Clean up test files
npm run release:clean
```

### Expected Results

A properly created production ZIP should:
- Be significantly smaller than a full repository archive
- Contain only essential WordPress theme files
- Exclude all development dependencies and build artifacts
- Include compiled assets but not source files
- Maintain the correct folder structure for WordPress installation

## File Size Comparison

| Type | Approximate Size | Files |
|------|------------------|-------|
| Full Repository | 50-100+ MB | 2000+ files |
| Production Release | 1-5 MB | 50-200 files |

## Troubleshooting

### Common Issues

1. **Missing essential files** - Check that `style.css` and `functions.php` exist in the repository
2. **Large ZIP size** - Review exclusion patterns for missed development artifacts
3. **Development files included** - Update exclusion patterns in the workflow

### Debugging

To debug exclusion issues:

1. Check the workflow logs for the ZIP contents summary
2. Use the validation step output to identify problematic files
3. Test exclusion patterns locally with the test script
4. Review the rsync command output for excluded files

## Maintenance

### Adding New Exclusions

To add new exclusion patterns:

1. Update the rsync command in `.github/workflows/release.yml`
2. Add patterns to `.zipignore` for manual releases
3. Update the validation patterns if needed
4. Test with the test script

### Removing Exclusions

To include previously excluded files:

1. Remove the pattern from the rsync command
2. Update `.zipignore` accordingly
3. Ensure the file is actually needed in production
4. Test the release to verify functionality

## Security Considerations

The exclusion patterns help maintain security by:
- Removing sensitive configuration files (`.env*`, `*.key`)
- Excluding development credentials and secrets
- Preventing exposure of build tools and dependencies
- Removing database dumps and backup files

## Performance Benefits

Clean production releases provide:
- Faster download times for users
- Reduced server storage requirements
- Quicker WordPress theme installation
- Improved security through reduced attack surface

## Compliance

This implementation follows WordPress theme development best practices and ensures releases contain only files necessary for theme functionality in production environments.
