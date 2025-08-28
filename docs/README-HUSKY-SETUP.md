# Husky Git Hooks Setup Guide

This document provides comprehensive instructions for setting up and using the Husky Git hooks that integrate with our comprehensive code review standards.

## Overview

The Husky hooks enforce quality standards at multiple Git workflow stages:
- **Pre-commit**: Priority 1-2 quality checks before commits
- **Commit-msg**: Conventional commit message format validation
- **Pre-push**: Comprehensive validation before remote push
- **Post-merge**: Automated cleanup and optimization

## Quick Setup

### 1. Install Dependencies
```bash
# Install all development dependencies
npm install

# Initialize Husky hooks
npm run husky:install
```

### 2. Verify Installation
```bash
# Check that hooks are installed
ls -la .husky/
# Should show: pre-commit, commit-msg, pre-push, post-merge

# Test hook execution
npm run quality:check
```

## Hook Details

### Pre-commit Hook
**Triggers**: Before each `git commit`
**Purpose**: Enforce Priority 1-2 quality standards

**Checks Performed**:
- ✅ Hardcoded credentials detection (Priority 1)
- ✅ Code linting (PHP, JavaScript, CSS)
- ✅ Code structure standards (function size, nesting depth)
- ✅ Build validation (syntax checks)
- ✅ Test coverage requirements (80% minimum)
- ✅ Documentation enforcement (Priority 2)
- ✅ Automatic documentation template generation

**Example Usage**:
```bash
# Normal commit (will run quality checks)
git add .
git commit -m "feat(auth): add OAuth2 integration"

# Emergency bypass (use sparingly)
EMERGENCY_BYPASS=true git commit -m "hotfix: critical security patch"
```

### Commit Message Hook
**Triggers**: During `git commit` message validation
**Purpose**: Enforce conventional commit format

**Format Required**:
```
type(scope): description

[optional body]

[optional footer]
```

**Valid Types**:
- `feat`: New features
- `fix`: Bug fixes
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Test additions/modifications
- `chore`: Build/tool changes

**Examples**:
```bash
# ✅ Good examples
git commit -m "feat(checkout): add express checkout option"
git commit -m "fix(cart): resolve quantity calculation error"
git commit -m "docs(readme): update installation instructions"

# ❌ Bad examples
git commit -m "update stuff"
git commit -m "fixed bug"
git commit -m "WIP"
```

### Pre-push Hook
**Triggers**: Before `git push` to remote
**Purpose**: Branch naming validation + comprehensive quality checks

**Checks Performed**:
- ✅ Branch naming convention validation
- ✅ All commits follow quality standards
- ✅ Comprehensive test suite execution
- ✅ Merge conflict detection
- ✅ CI/CD pipeline compatibility
- ✅ Integration requirements
- ✅ Large file and secret detection

### Pre-checkout Hook
**Triggers**: When switching or creating branches
**Purpose**: Enforce branch naming conventions

**Validation Performed**:
- ✅ Branch name follows standardized patterns
- ✅ Provides suggestions for invalid names
- ✅ Blocks checkout of invalid branch names
- ✅ Supports emergency bypass for critical situations

**Example Usage**:
```bash
# Normal push (will run comprehensive checks)
git push origin feature-branch

# Emergency bypass for critical fixes
EMERGENCY_BYPASS=true git push origin hotfix-branch
```

### Post-merge Hook
**Triggers**: After successful `git merge`
**Purpose**: Automated cleanup and optimization

**Actions Performed**:
- 🔄 Update dependencies if package files changed
- 🧹 Clean temporary files
- ⚡ Optimize Git repository
- 🔍 Run quality assessment
- 🌿 Update branch information
- 📋 Generate merge summary

## Configuration Options

### Environment Variables

```bash
# Emergency bypass (use only for critical production fixes)
export EMERGENCY_BYPASS=true

# Skip specific checks during development
export SKIP_TESTS=true
export SKIP_INTEGRATION=true
export SKIP_CLEANUP=true
export SKIP_DEPENDENCY_UPDATE=true
```

### Quality Standards Configuration

Edit `scripts/pre-commit-quality-check.js` to adjust:
```javascript
const CONFIG = {
  minCoverage: 80,           // Minimum test coverage percentage
  maxFunctionLines: 30,      // Maximum lines per function
  maxNestingDepth: 4,        // Maximum nesting depth
  emergencyBypass: false     // Emergency bypass flag
};
```

## Troubleshooting

### Common Issues

#### 1. Hook Not Executing
```bash
# Ensure hooks are executable
chmod +x .husky/pre-commit .husky/commit-msg .husky/pre-push .husky/post-merge

# Reinstall Husky
npm run husky:install
```

#### 2. Quality Checks Failing
```bash
# Run individual checks to identify issues
npm run lint
npm run test
npm run security:scan

# Fix issues or use emergency bypass
EMERGENCY_BYPASS=true git commit -m "emergency fix"
```

#### 3. Dependency Issues
```bash
# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Update dependencies
npm update
```

#### 4. PHP Tool Issues
```bash
# Install PHP development tools
composer install --dev

# Verify PHP tools
vendor/bin/phpcs --version
vendor/bin/phpunit --version
```

## Integration with IDE

### VS Code
Add to `.vscode/settings.json`:
```json
{
  "eslint.enable": true,
  "prettier.enable": true,
  "php.validate.enable": true,
  "git.enableCommitSigning": true
}
```

### PhpStorm
1. Enable ESLint: Settings → Languages → JavaScript → Code Quality Tools → ESLint
2. Enable Prettier: Settings → Languages → JavaScript → Prettier
3. Enable PHP CS: Settings → Languages → PHP → Code Sniffer

## Best Practices

### 1. Commit Frequently
- Make small, focused commits
- Use descriptive commit messages
- Follow conventional commit format

### 2. Test Before Committing
```bash
# Run quality checks manually
npm run quality:check

# Run specific checks
npm run lint
npm run test
npm run security:scan
```

### 3. Handle Emergencies Properly
```bash
# Only use emergency bypass for critical production fixes
EMERGENCY_BYPASS=true git commit -m "hotfix: critical security vulnerability"

# Always follow up with proper fix
git commit -m "fix: proper implementation of security patch"
```

### 4. Keep Dependencies Updated
```bash
# Regular dependency updates
npm update
composer update

# Security audits
npm audit
composer audit
```

## Team Adoption

### Onboarding New Developers
1. Clone repository
2. Run `npm install`
3. Run `npm run husky:install`
4. Review this documentation
5. Test with a sample commit

### Enforcing Standards
- All team members must use the hooks
- No bypassing without proper justification
- Regular review of quality metrics
- Continuous improvement of standards

## Support

For issues or questions:
1. Check this documentation
2. Review `.augment/rules/ALWAYS-comprehensive-code-review-standards.md`
3. Contact the development team
4. Create an issue in the repository

## References

- [Husky Documentation](https://typicode.github.io/husky/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [ESLint Rules](https://eslint.org/docs/rules/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
