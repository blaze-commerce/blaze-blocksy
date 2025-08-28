# Branch Naming Standards and Enforcement

## Overview

This document outlines the comprehensive branch naming standards enforced through Git hooks and automated validation. These standards promote clear identification, easy sorting, team collaboration, and integration with issue tracking systems.

## 🌿 Branch Naming Conventions

### Standard Branch Types

#### 1. Feature Branches
**Pattern:** `feature/[ticket-id]-[description]` or `feature/[description]`

**Purpose:** Development of new functionality or enhancements

**Examples:**
```
feature/user-authentication
feature/PROJ-123-oauth-integration
feature/checkout/express-payment
feature/api/user-management
feature/ui/mobile-responsive-design
```

**Lifecycle:**
- Created from: `develop` or `main`
- Merged to: `develop` or `main`
- Deleted after: Successful merge and deployment

#### 2. Bug Fix Branches
**Pattern:** `bugfix/[ticket-id]-[description]` or `fix/[description]`

**Purpose:** Resolution of non-critical bugs and issues

**Examples:**
```
bugfix/login-error
fix/PROJ-456-cart-calculation
bugfix/checkout/payment-validation
fix/api/response-timeout
bugfix/ui/mobile-layout-issue
```

**Lifecycle:**
- Created from: `develop` or `main`
- Merged to: `develop` or `main`
- Priority: Normal development cycle

#### 3. Hotfix Branches
**Pattern:** `hotfix/[ticket-id]-[description]` or `hotfix/[description]`

**Purpose:** Critical production issues requiring immediate attention

**Examples:**
```
hotfix/security-patch
hotfix/PROJ-789-critical-bug
hotfix/prod/database-connection
hotfix/payment/gateway-timeout
hotfix/auth/session-expiry
```

**Lifecycle:**
- Created from: `main` or `production`
- Merged to: Both `main` and `develop`
- Priority: Immediate deployment required

#### 4. Release Branches
**Pattern:** `release/[version-number]`

**Purpose:** Release preparation, stabilization, and final testing

**Examples:**
```
release/1.2.0
release/2.0.0-beta
release/1.5.3-rc1
release/3.1.0-alpha
release/2.4.1-hotfix
```

**Lifecycle:**
- Created from: `develop`
- Merged to: `main` and `develop`
- Tagged: With version number after merge

#### 5. Maintenance Branches

##### Chore Branches
**Pattern:** `chore/[description]`
**Purpose:** Maintenance tasks, dependency updates, cleanup

**Examples:**
```
chore/update-dependencies
chore/cleanup-logs
chore/ci/improve-pipeline
chore/config/environment-setup
```

##### Documentation Branches
**Pattern:** `docs/[description]`
**Purpose:** Documentation-only changes

**Examples:**
```
docs/api-documentation
docs/setup-guide
docs/contributing/code-standards
docs/deployment/production-guide
```

##### Testing Branches
**Pattern:** `test/[description]`
**Purpose:** Testing improvements and additions

**Examples:**
```
test/unit-tests
test/e2e-checkout-flow
test/performance/load-testing
test/security/penetration-testing
```

##### Refactoring Branches
**Pattern:** `refactor/[description]`
**Purpose:** Code refactoring without functional changes

**Examples:**
```
refactor/user-service
refactor/api/response-format
refactor/database/query-optimization
refactor/frontend/component-structure
```

#### 6. Protected Branches

**Patterns:** `main`, `master`, `develop`, `dev`, `staging`, `production`, `prod`

**Purpose:** Long-lived branches with special protection rules

**Characteristics:**
- Direct commits blocked or discouraged
- Require pull request approval
- Automated deployment targets
- Branch protection rules enforced

## 🔧 Configuration and Customization

### Configuration File: `.branch-naming.json`

The branch naming patterns are configurable through the `.branch-naming.json` file:

```json
{
  "patterns": {
    "feature": {
      "regex": "^feature\/([a-z0-9-]+\/)?[a-z0-9-]+$",
      "description": "Feature branches for new functionality",
      "examples": ["feature/user-auth", "feature/PROJ-123-oauth"]
    }
  },
  "validation": {
    "strictMode": false,
    "requireTicketId": false,
    "maxBranchNameLength": 100
  }
}
```

### Customization Options

#### Team-Specific Patterns
Add custom patterns for your team's workflow:

```json
{
  "customPatterns": {
    "wordpress": {
      "regex": "^(wp|wordpress)\/([a-z0-9-]+\/)?[a-z0-9-]+$",
      "description": "WordPress-specific branches"
    },
    "security": {
      "regex": "^security\/([a-z0-9-]+\/)?[a-z0-9-]+$",
      "description": "Security-focused branches"
    }
  }
}
```

#### Integration Settings
Configure integration with issue tracking systems:

```json
{
  "integration": {
    "jira": {
      "enabled": true,
      "ticketPattern": "[A-Z]+-\\d+",
      "projectKeys": ["PROJ", "DEV", "BUG"]
    },
    "github": {
      "enabled": true,
      "issuePattern": "#\\d+",
      "requireIssueReference": false
    }
  }
}
```

## 🚀 Enforcement and Validation

### Git Hook Integration

#### Pre-Push Hook
Validates branch names before pushing to remote repository:

```bash
# Automatically runs during git push
git push origin feature/new-functionality
# → Validates branch name against patterns
# → Blocks push if validation fails
```

#### Pre-Checkout Hook
Validates branch names when switching or creating branches:

```bash
# Automatically runs during git checkout
git checkout -b feature/invalid@name
# → Validates new branch name
# → Blocks checkout if validation fails
```

### Manual Validation Commands

```bash
# Validate current branch
npm run branch:validate

# Validate all branches
npm run branch:validate-all

# Get help and examples
npm run branch:help

# Create configuration file
npm run branch:config
```

### Validation Rules

#### Format Requirements
- **Character Set:** Lowercase letters, numbers, hyphens, forward slashes only
- **Length:** 5-100 characters
- **Structure:** `type/[scope/]description` format
- **Separators:** Hyphens for words, slashes for hierarchy

#### Content Guidelines
- **Descriptive:** Clear indication of branch purpose
- **Consistent:** Follow established patterns
- **Traceable:** Include ticket/issue ID when applicable
- **Professional:** Avoid temporary or placeholder names

## 🔄 Workflow Integration

### Development Workflow

#### 1. Feature Development
```bash
# Create feature branch
git checkout -b feature/user-authentication
# → Branch name validated automatically

# Work on feature
git add .
git commit -m "feat(auth): implement OAuth2 integration"

# Push to remote
git push origin feature/user-authentication
# → Branch name re-validated before push
```

#### 2. Bug Fix Workflow
```bash
# Create bug fix branch
git checkout -b bugfix/PROJ-456-cart-calculation

# Fix the bug
git add .
git commit -m "fix(cart): resolve quantity calculation error"

# Push and create PR
git push origin bugfix/PROJ-456-cart-calculation
```

#### 3. Hotfix Workflow
```bash
# Create hotfix from main
git checkout main
git checkout -b hotfix/security-patch

# Apply critical fix
git add .
git commit -m "fix(security): patch authentication vulnerability"

# Push for immediate deployment
git push origin hotfix/security-patch
```

### CI/CD Integration

Branch naming patterns integrate with CI/CD pipelines:

```yaml
# GitHub Actions example
name: CI/CD Pipeline
on:
  push:
    branches:
      - 'feature/**'
      - 'bugfix/**'
      - 'hotfix/**'
      - 'release/**'
      - main
      - develop
```

## 🚨 Emergency Procedures

### Emergency Bypass

For critical production fixes that cannot wait for naming validation:

```bash
# Bypass branch naming validation
EMERGENCY_BYPASS=true git checkout -b emergency-fix
EMERGENCY_BYPASS=true git push origin emergency-fix
```

**Important:** Emergency bypass should only be used for critical production issues and must be followed by proper branch renaming.

### Recovery Procedures

#### Rename Existing Branch
```bash
# Rename current branch
git branch -m old-branch-name feature/new-proper-name

# Update remote
git push origin -u feature/new-proper-name
git push origin --delete old-branch-name
```

#### Fix Invalid Branch Names
```bash
# Get suggestions for current branch
npm run branch:validate
# → Shows validation errors and suggestions

# Apply suggested name
git branch -m feature/suggested-name
```

## 📊 Best Practices

### Naming Guidelines

#### DO:
- Use descriptive, meaningful names
- Include ticket/issue IDs when available
- Follow consistent patterns across the team
- Use lowercase letters and hyphens
- Keep names concise but clear

#### DON'T:
- Use special characters or spaces
- Create overly long branch names
- Use temporary or placeholder names
- Mix naming conventions within a project
- Ignore validation errors

### Team Collaboration

#### Onboarding
1. Review branch naming standards
2. Install and configure Git hooks
3. Practice with sample branches
4. Understand emergency procedures

#### Code Reviews
- Verify branch names follow conventions
- Check for proper ticket/issue references
- Ensure branch purpose matches naming pattern
- Validate merge target is appropriate

#### Project Management
- Configure issue tracking integration
- Set up automated branch creation from tickets
- Establish branch lifecycle policies
- Monitor compliance and provide feedback

## 🔍 Troubleshooting

### Common Issues

#### Validation Failures
```bash
# Issue: Branch name doesn't match pattern
# Solution: Rename branch or create new one
git branch -m feature/proper-name

# Issue: Special characters in name
# Solution: Use only allowed characters
git checkout -b feature/user-auth-system
```

#### Hook Not Running
```bash
# Ensure hooks are executable
chmod +x .husky/pre-push .husky/pre-checkout

# Reinstall hooks
npm run husky:install
```

#### Configuration Issues
```bash
# Validate configuration file
npm run branch:config

# Reset to defaults
rm .branch-naming.json
npm run branch:config
```

### Support Resources

- **Documentation:** This file and comprehensive code review standards
- **Help Command:** `npm run branch:help`
- **Team Guidelines:** Project-specific naming conventions
- **Issue Tracking:** Report problems through standard channels

## 📈 Metrics and Monitoring

### Compliance Tracking
- Monitor branch naming compliance rates
- Track validation failures and patterns
- Identify common naming issues
- Measure team adoption and adherence

### Continuous Improvement
- Regular review of naming patterns
- Team feedback on validation rules
- Updates based on workflow changes
- Integration with new tools and systems

---

**Remember:** Consistent branch naming is a team effort that improves collaboration, automation, and project management. When in doubt, follow the established patterns and seek team guidance.
