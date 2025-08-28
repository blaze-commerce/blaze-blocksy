# GitHub Actions Troubleshooting Guide

## Overview
This document provides troubleshooting guidance for common GitHub Actions workflow failures in the blaze-commerce/blaze-blocksy repository.

## Common Workflow Failures

### 1. Conventional Commit Validation Failure

**Workflow:** `Pull Request Validation` (`.github/workflows/pr-validation.yml`)
**Job:** `Validate Conventional Commits`

#### Symptoms
- ❌ GitHub Actions workflow fails with exit code 1
- Error message: "Conventional commit validation failed!"
- Lists invalid commit messages that don't follow conventional format

#### Root Cause
Commit messages in the PR don't follow the conventional commit specification:
```
<type>[optional scope]: <description>
```

#### Common Invalid Examples
```bash
# ❌ Invalid - missing type
"search customization for infinity targets"
"update footer styles"
"bug fix"

# ❌ Invalid - wrong format
"Feature/footer component styling"
"Fix: bug in checkout" (capitalized type)
"added new feature" (wrong tense)
```

#### Valid Examples
```bash
# ✅ Valid
"feat: add search customization for infinity targets"
"feat(footer): add component styling system"
"fix: resolve mobile menu alignment issue"
"docs: update installation instructions"
"style: format CSS according to standards"
```

#### Solution Steps

##### Option 1: Fix Individual Commit Messages (Recommended)
```bash
# 1. Start interactive rebase
git rebase -i HEAD~N  # N = number of commits to review

# 2. Change 'pick' to 'reword' for problematic commits
# 3. Save and exit editor
# 4. Update each commit message when prompted
# 5. Force push changes
git push --force-with-lease origin <branch-name>
```

##### Option 2: Squash All Commits
```bash
# 1. Soft reset to combine commits
git reset --soft HEAD~N  # N = number of commits

# 2. Create new commit with proper format
git commit -m "feat(scope): descriptive message

- Detail 1
- Detail 2
- Detail 3"

# 3. Force push
git push --force-with-lease origin <branch-name>
```

#### Prevention
1. **Use conventional commit messages** from the start
2. **Set up git hooks** for local validation
3. **Use tools** like `commitizen` or `conventional-changelog`
4. **Review commit history** before creating PRs

### 2. PR Title Validation Failure

#### Symptoms
- PR title doesn't follow conventional commit format
- Workflow fails on "Validate PR title" step

#### Solution
Update the PR title to follow conventional commit format:
```
feat(scope): add new feature
fix(scope): resolve specific issue
docs: update documentation
```

### 3. Breaking Changes Detection

#### Symptoms
- Workflow detects breaking changes but they're not properly documented
- Warning messages about MAJOR version bumps

#### Solution
1. **Document breaking changes** in PR description
2. **Use proper format** for breaking changes:
   ```
   feat!: remove deprecated API
   
   BREAKING CHANGE: The old API has been removed. Use newAPI() instead.
   ```
3. **Provide migration guide** when necessary

## Workflow Configuration

### Conventional Commit Types
The workflow accepts these commit types:
- `feat`: New features
- `fix`: Bug fixes  
- `docs`: Documentation changes
- `style`: Code formatting (no logic changes)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `ci`: CI/CD changes
- `build`: Build system changes
- `perf`: Performance improvements
- `revert`: Reverting previous commits

### Scope Examples
- `feat(checkout): add payment validation`
- `fix(footer): resolve styling conflicts`
- `docs(api): update endpoint documentation`
- `test(utils): add unit tests for helpers`

## Best Practices

### For Contributors
1. **Write descriptive commit messages** that explain the "what" and "why"
2. **Use present tense** ("add feature" not "added feature")
3. **Keep first line under 50 characters** when possible
4. **Use body for detailed explanations** when needed
5. **Reference issues** when applicable: `fixes #123`

### For Maintainers
1. **Review commit history** before merging PRs
2. **Provide guidance** to contributors on commit message format
3. **Use squash and merge** when commit history is messy
4. **Maintain consistent standards** across the project

## Troubleshooting Commands

### Check Recent Commits
```bash
# View recent commit messages
git log --oneline -10

# View detailed commit info
git log --pretty=format:"%h - %s (%an, %ar)" -10
```

### Validate Commit Messages Locally
```bash
# Check if commit message follows conventional format
echo "feat: add new feature" | grep -E "^(feat|fix|docs|style|refactor|test|chore|ci|build|perf|revert)(\(.+\))?: .+"
```

### Fix Common Issues
```bash
# Amend last commit message
git commit --amend -m "feat: corrected commit message"

# Interactive rebase for multiple commits
git rebase -i HEAD~3

# Reset and recommit (destructive)
git reset --soft HEAD~1
git commit -m "feat: proper commit message"
```

## Related Documentation
- [Conventional Commits Specification](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [Git Rebase Documentation](https://git-scm.com/docs/git-rebase)

## Support
If you need help with GitHub Actions workflows or conventional commits:
1. Check this troubleshooting guide first
2. Review the workflow logs for specific error messages
3. Ask for help in the PR comments
4. Contact the maintainers for complex issues

---
*Last updated: 2025-08-28*
