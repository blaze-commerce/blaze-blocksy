# Developer Setup Guide

This guide helps new developers set up their environment to work with the Blaze Commerce Blocksy Child Theme while following our semantic versioning and code quality standards.

## Prerequisites

- **Git** installed and configured
- **Python 3** (for pre-commit hooks)
- **PHP** (for WordPress development)
- **Node.js** (optional, for additional tooling)

## Quick Setup

### 1. Clone the Repository

```bash
git clone https://github.com/blaze-commerce/blaze-blocksy.git
cd blaze-blocksy
```

### 2. Install Pre-commit Hooks (Required)

Run our automated setup script:

```bash
./scripts/setup-pre-commit.sh
```

Or manually install:

```bash
# Install pre-commit
pip3 install pre-commit

# Install hooks
pre-commit install
pre-commit install --hook-type commit-msg
pre-commit install --hook-type pre-push

# Test installation
pre-commit run --all-files
```

### 3. Configure Git (If not already done)

```bash
git config user.name "Your Name"
git config user.email "your.email@example.com"
```

## Development Workflow

### Branch Protection Rules

The `main` branch is protected with the following rules:
- ✅ Pull request reviews required (minimum 1 approval)
- ✅ Status checks must pass before merging
- ✅ Conversation resolution required
- ✅ Direct pushes blocked
- ✅ Force pushes prevented

### Conventional Commits (Enforced)

All commit messages must follow the conventional commit format:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

#### Valid Types:
- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation changes
- `style:` - Code formatting (no logic changes)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks
- `ci:` - CI/CD changes
- `build:` - Build system changes
- `perf:` - Performance improvements
- `revert:` - Reverting previous commits

#### Examples:
```bash
git commit -m "feat: add new checkout customization feature"
git commit -m "fix: resolve mobile menu alignment issue"
git commit -m "docs: update installation instructions"
git commit -m "feat(checkout): add payment method validation"
git commit -m "fix!: remove deprecated API (breaking change)"
```

### Pull Request Process

1. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Changes and Commit**
   ```bash
   # Pre-commit hooks will automatically validate your commits
   git add .
   git commit -m "feat: add your feature description"
   ```

3. **Push Branch**
   ```bash
   git push origin feature/your-feature-name
   ```

4. **Create Pull Request**
   - Use conventional commit format for PR title
   - Provide detailed description
   - Include testing instructions
   - Document any breaking changes

5. **Address Review Feedback**
   - Resolve all conversations
   - Ensure all status checks pass
   - Get required approvals

6. **Merge**
   - PR will be automatically merged after approval
   - Semantic release will handle versioning

## Code Quality Standards

### Automated Checks

Pre-commit hooks automatically check:
- ✅ Conventional commit message format
- ✅ PHP syntax validation
- ✅ CSS/SCSS linting and formatting
- ✅ Trailing whitespace removal
- ✅ File size limits (1MB max)
- ✅ Security checks (private keys, merge conflicts)
- ✅ YAML/JSON validation

### Manual Testing

Before submitting a PR:
1. Test your changes locally
2. Verify WordPress functionality
3. Check responsive design
4. Validate accessibility
5. Test across different browsers

## Troubleshooting

### Pre-commit Hook Issues

**Hook fails to run:**
```bash
# Reinstall hooks
pre-commit uninstall
pre-commit install --install-hooks
```

**Commit message validation fails:**
```bash
# Check your commit message format
git log --oneline -1

# Amend last commit message
git commit --amend -m "feat: correct conventional format"
```

**Skip hooks (not recommended):**
```bash
git commit --no-verify -m "emergency fix"
```

### Branch Protection Issues

**Cannot push to main:**
```bash
# Create a feature branch instead
git checkout -b feature/your-changes
git push origin feature/your-changes
# Then create a PR
```

**PR checks failing:**
- Check the Actions tab for detailed error messages
- Ensure all commits follow conventional format
- Resolve any code quality issues

### Common Errors

**"conventional-pre-commit not found":**
```bash
pip3 install conventional-pre-commit
```

**"stylelint command not found":**
```bash
npm install -g stylelint stylelint-config-standard
```

**"PHP syntax error":**
- Fix PHP syntax issues before committing
- Use a PHP linter in your IDE

## IDE Configuration

### VS Code

Recommended extensions:
- PHP Intelephense
- Prettier - Code formatter
- ESLint
- GitLens
- Conventional Commits

### PhpStorm

Enable:
- PHP Code Sniffer
- WordPress Coding Standards
- Git integration
- Code formatting on save

## Security Guidelines

### Commit Message Security
- **Never include sensitive information** in commit messages
- **Avoid personal information** (emails, names, phone numbers)
- **No credentials or tokens** in commit messages or code
- **Use placeholders** for examples: `[REPLACE_WITH_ACTUAL_VALUE]`

### Pre-commit Security Checks
The pre-commit hooks automatically check for:
- ✅ Private key detection
- ✅ Merge conflict markers
- ✅ Large file prevention (>1MB)
- ✅ Case conflict detection

### GitHub Actions Security
- All workflows use minimal required permissions
- Secrets are properly managed through GitHub Secrets
- No hardcoded credentials in workflow files
- Input validation for all user-provided data

## Getting Help

- **Documentation**: Check `docs/` directory
- **Issues**: Create GitHub issue for bugs
- **Questions**: Ask in team chat or PR comments
- **Code Review**: Request review from team members
- **Security Issues**: Report privately to maintainers

## Resources

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Pre-commit Documentation](https://pre-commit.com/)
- [GitHub Actions Security](https://docs.github.com/en/actions/security-guides)
