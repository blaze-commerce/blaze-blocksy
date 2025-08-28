# WordPress/WooCommerce .gitignore Best Practices

## Overview

This document explains the comprehensive .gitignore configuration implemented for this WordPress/WooCommerce project, following industry best practices for version control hygiene.

## What Was Updated

### 🧹 Files Removed from Tracking

The following types of files were automatically removed from version control:

1. **Test Coverage Reports** (29 files removed)
   - `coverage/` directory contents
   - PHPUnit coverage reports
   - Playwright test reports
   - Jest coverage files

2. **Test Result Files** (2 files removed)
   - `test-results/` directory contents
   - Performance test results

3. **Cache Files** (1 file removed)
   - `.phpunit.result.cache`
   - ESLint and Stylelint cache files

### 📁 New .gitignore Categories

The updated .gitignore file is organized into clear sections:

#### 1. Dependency Directories
```gitignore
# Node.js and Package Managers
node_modules/
.npm/
.yarn/
.pnp.*

# Composer Dependencies
vendor/
```

#### 2. Build Artifacts & Compiled Files
```gitignore
# Build outputs
dist/
build/
*.min.css
*.min.js
*.map

# CSS Preprocessors
.sass-cache/
```

#### 3. Testing & Coverage
```gitignore
# Test artifacts
coverage/
test-results/
.phpunit.result.cache
.jest-cache/
```

#### 4. Development Tools & Caches
```gitignore
# Linting caches
.eslintcache
.stylelintcache
.prettiercache

# Build tool caches
.parcel-cache/
.webpack/
```

#### 5. Environment & Configuration Files
```gitignore
# Environment files
.env
.env.*
!.env.example

# WordPress configuration
wp-config-local.php
wp-config-staging.php
wp-cli.local.yml
```

#### 6. IDE & Editor Files
```gitignore
# Visual Studio Code
.vscode/
*.code-workspace

# JetBrains IDEs
.idea/

# Vim/Neovim
*.swp
*.swo
```

#### 7. Operating System Files
```gitignore
# macOS
.DS_Store
._*

# Windows
Thumbs.db
desktop.ini

# Linux
.directory
.Trash-*
```

#### 8. WordPress Specific
```gitignore
# WordPress core (if accidentally added)
wp-admin/
wp-includes/
wp-content/uploads/

# WordPress logs
error_log
debug.log
```

#### 9. Security & Sensitive Files
```gitignore
# Authentication
.htpasswd
*.key
*.pem

# Database dumps
*.sql
*.sqlite
```

## Lock File Strategy

### Current Approach
- ✅ **Keep `package-lock.json`** - Ensures consistent Node.js dependency versions
- ✅ **Keep `composer.lock`** - Ensures consistent PHP dependency versions
- ❌ **Ignore alternative lock files** - `yarn.lock`, `pnpm-lock.yaml`, `bun.lockb`

### Rationale
Lock files ensure reproducible builds across different environments. They should be committed to maintain consistency between development, staging, and production environments.

## Large Files Detected

⚠️ **Large files found in repository:**
- `agency_logo/220802-M-YE553-1001.png` (1.8MB)
- `agency_logo/US_Army_Marksmanship_Unit_DUI.png` (1.8MB)
- `agency_logo/fbi-seal-logo-png-transparent.png` (1.4MB)

### Recommendations for Large Files:
1. **Consider Git LFS** for files >1MB
2. **Optimize images** using tools like ImageOptim or TinyPNG
3. **Use CDN** for serving large assets in production

## Protected Theme Assets

The .gitignore includes specific rules to ensure important theme assets are preserved:

```gitignore
# Keep Important Theme Files
!screenshot.jpg
!screenshot.png
!assets/**/*.jpg
!assets/**/*.jpeg
!assets/**/*.png
!assets/**/*.gif
!assets/**/*.svg
!assets/**/*.ico
!assets/**/*.webp
!assets/**/*.woff
!assets/**/*.woff2

# Keep documentation images
!docs/**/*.jpg
!docs/**/*.png
!docs/**/*.svg
```

## Development Workflow Impact

### What Developers Need to Know

1. **Coverage Reports**: No longer tracked - generated locally during testing
2. **Test Results**: No longer tracked - generated during CI/CD
3. **Cache Files**: No longer tracked - improves repository performance
4. **Build Artifacts**: Must be generated locally or in CI/CD

### Commands Still Work
All existing npm/composer scripts continue to work:
```bash
npm run test           # Generates coverage locally
npm run build          # Creates build artifacts locally
composer test          # Runs PHP tests with coverage
```

## CI/CD Considerations

### What CI/CD Should Handle
1. **Install dependencies** (`npm install`, `composer install`)
2. **Run tests** and generate coverage reports
3. **Build assets** for production
4. **Deploy built assets** to production environment

### Artifacts Storage
- Use CI/CD artifact storage for coverage reports
- Use deployment pipelines for built assets
- Consider using GitHub Actions artifacts or similar

## Security Improvements

### Sensitive Files Now Ignored
- Environment files (`.env*`)
- WordPress configuration files
- Authentication files (`.htpasswd`, `*.key`)
- Database dumps (`*.sql`)

### Best Practices Enforced
- No accidental commit of secrets
- No WordPress core files in theme repository
- No sensitive configuration in version control

## Migration Guide

### For Existing Developers

1. **Pull latest changes**:
   ```bash
   git pull origin main
   ```

2. **Clean local repository**:
   ```bash
   git clean -fd  # Remove untracked files
   ```

3. **Reinstall dependencies**:
   ```bash
   npm install
   composer install
   ```

4. **Regenerate coverage** (if needed):
   ```bash
   npm run test
   composer test
   ```

### For New Developers

The updated .gitignore ensures a clean setup experience:
1. Clone repository
2. Run `npm install` and `composer install`
3. Start developing - no cleanup needed

## Maintenance

### Regular Tasks
1. **Review .gitignore** quarterly for new patterns
2. **Check for large files** using `git ls-files | xargs ls -la | sort -k5 -n`
3. **Update documentation** when adding new build tools

### Monitoring
- Use pre-commit hooks to prevent large files
- Monitor repository size growth
- Regular cleanup of old branches and tags

## Troubleshooting

### Common Issues

**Q: My coverage reports disappeared**
A: They're now generated locally. Run `npm run test` to regenerate.

**Q: Build files are missing**
A: Run `npm run build` to generate them locally.

**Q: I accidentally committed a large file**
A: Use `git filter-branch` or BFG Repo-Cleaner to remove it from history.

### Getting Help

If you encounter issues with the new .gitignore configuration:
1. Check this documentation
2. Run the cleanup script: `./scripts/cleanup-gitignore.sh`
3. Contact the development team

## References

- [Git Documentation - gitignore](https://git-scm.com/docs/gitignore)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Node.js .gitignore Best Practices](https://github.com/github/gitignore/blob/main/Node.gitignore)
- [PHP .gitignore Best Practices](https://github.com/github/gitignore/blob/main/Composer.gitignore)
