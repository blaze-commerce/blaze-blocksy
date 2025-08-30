# Auto-Merge Security Configuration

This document outlines the comprehensive security measures implemented to ensure auto-merge functionality is only used for authorized version bump PRs while maintaining proper code review processes for all other changes.

## 🛡️ Security Architecture

### Multi-Layer Security Approach

Since GitHub's repository-level auto-merge setting is binary (enabled/disabled for entire repository), we implement a **multi-layered security approach**:

1. **Repository Level**: Auto-merge enabled (required for automation bot)
2. **Workflow Level**: Security guard prevents unauthorized usage
3. **Branch Protection**: Bot configured as bypass actor for version bumps only
4. **Monitoring**: Continuous security monitoring and alerting

## 🔒 Security Workflows

### 1. Auto-Merge Security Guard (`auto-merge-guard.yml`)

**Purpose**: Prevents unauthorized auto-merge usage on regular development PRs

**Triggers**: All PRs (except authorized version bump PRs)

**Actions**:
- Detects if auto-merge is enabled on non-version-bump PRs
- Automatically disables auto-merge if found
- Adds security warning comment
- Ensures proper review processes are followed

**Exclusions**: Skips validation for:
- PRs created by `blazecommerce-automation-bot[bot]`
- Titles starting with `chore(release): bump theme version to` or `chore: bump version to`

### 2. Enhanced Auto-Merge Workflow (`auto-merge-version-bumps.yml`)

**Purpose**: Securely handles version bump PR automation

**Security Features**:
- Strict author validation (only automation bot)
- Title format validation (version bump patterns only)
- Automated approval with security context
- Co-authored commit attribution
- Comprehensive logging

**Process**:
1. Validates PR meets strict criteria
2. Auto-approves using bot's bypass permissions
3. Enables auto-merge with security context
4. Provides detailed audit trail

### 3. Auto-Merge Security Monitor (`auto-merge-monitor.yml`)

**Purpose**: Monitors all merged PRs for security compliance

**Triggers**: When PRs are merged

**Actions**:
- Analyzes merge patterns
- Validates expected automation vs manual patterns
- Creates security alerts for unusual patterns
- Maintains audit logs

## 🎯 Authorized Auto-Merge Criteria

Auto-merge is **ONLY** allowed for PRs that meet **ALL** of the following criteria:

### ✅ Required Conditions

1. **Author**: `blazecommerce-automation-bot[bot]`
2. **Title Format**: Must start with:
   - `chore(release): bump theme version to X.Y.Z`
   - `chore: bump version to X.Y.Z`
3. **Target Branch**: `main` or `master`
4. **Validation**: Passes all security checks
5. **Status**: All required CI/CD checks pass
6. **Conflicts**: No merge conflicts

### ❌ Blocked Scenarios

- PRs created by human developers
- PRs with non-version-bump titles
- PRs targeting other branches
- PRs with failing checks or conflicts
- Any PR that doesn't meet ALL criteria above

## 🔧 Branch Protection Configuration

### Current Settings

- **Required Reviews**: 1 approval required
- **Dismiss Stale Reviews**: Enabled
- **Require Last Push Approval**: Enabled
- **Required Status Checks**: Strict mode enabled
- **Bypass Actors**: BlazeCommerce Automation Bot (for version bumps only)

### Security Benefits

- **Human PRs**: Must follow standard review process
- **Bot PRs**: Can bypass reviews for version bumps only
- **Status Checks**: Always enforced for all PRs
- **Audit Trail**: All actions logged and monitored

## 🚨 Security Monitoring

### Automated Alerts

The system creates security alerts for:

- Unusual merge patterns
- Unauthorized auto-merge attempts
- Security workflow failures
- Unexpected bot behavior

### Alert Recipients

- **Primary**: `lanz-2024` (repository maintainer)
- **Labels**: `security`, `auto-merge`, `alert`
- **Location**: GitHub Issues

## 📋 Security Checklist

### For Repository Maintainers

- [ ] Verify auto-merge guard workflow is active
- [ ] Confirm branch protection rules are properly configured
- [ ] Check that bot has appropriate permissions
- [ ] Monitor security alerts regularly
- [ ] Review merge patterns in security monitor

### For Developers

- [ ] **Never manually enable auto-merge** on development PRs
- [ ] Follow standard review processes for all non-version-bump PRs
- [ ] Report any suspicious auto-merge behavior
- [ ] Understand that auto-merge is reserved for automation only

## 🔍 Troubleshooting

### Common Issues

1. **Auto-merge disabled on version bump PR**
   - Check if PR title matches exact format
   - Verify author is `blazecommerce-automation-bot[bot]`
   - Ensure all status checks pass

2. **Security alert triggered**
   - Review the specific alert details
   - Verify the merge followed proper protocols
   - Update security rules if needed

3. **Bot permissions issues**
   - Check GitHub App configuration
   - Verify bot is configured as bypass actor
   - Ensure required secrets are properly set

### Support

For security-related issues or questions:
1. Check this documentation first
2. Review recent security alerts
3. Contact repository maintainers
4. Create an issue with `security` label

## 📚 Related Documentation

- [GitHub Branch Protection Rules](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/defining-the-mergeability-of-pull-requests/about-protected-branches)
- [GitHub Auto-merge](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/incorporating-changes-from-a-pull-request/automatically-merging-a-pull-request)
- [BlazeCommerce Automation Bot Documentation](../README.md)

---

**Last Updated**: 2025-08-30  
**Security Level**: High  
**Review Frequency**: Monthly
