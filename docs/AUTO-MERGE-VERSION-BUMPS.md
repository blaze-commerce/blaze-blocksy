# Auto-Merge Version Bump PRs System

This document describes the automated system for merging version bump PRs created by the BlazeCommerce Automation Bot, eliminating the need for manual intervention in the release pipeline.

## 🎯 Purpose

The auto-merge system streamlines the release process by automatically:
- Detecting version bump PRs created by the automation bot
- Waiting for all required CI/CD checks to pass
- Automatically merging approved version bump PRs
- Maintaining audit trail of automated merges
- Cleaning up outdated version bump PRs

## 🔧 Implementation Components

### 1. **Auto-Merge Workflow** (`.github/workflows/auto-merge-version-bumps.yml`)

**Trigger**: Runs when version bump PRs are opened, synchronized, or reopened

**Process**:
1. **PR Validation**: Verifies the PR is created by `blazecommerce-automation-bot[bot]` with correct title format
2. **Check Waiting**: Waits for all required CI/CD checks to complete (max 30 minutes)
3. **Conflict Detection**: Ensures the PR has no merge conflicts
4. **Auto-Merge**: Automatically merges the PR using squash merge
5. **Cleanup Trigger**: Triggers cleanup of any outdated version bump PRs

**Safety Features**:
- Only processes PRs created by the automation bot
- Validates PR title format with regex
- Waits for all CI checks to pass before merging
- Checks for merge conflicts before proceeding
- Provides detailed logging and audit trail
- Falls back to GITHUB_TOKEN if GitHub App token fails

### 2. **Enhanced Cleanup Workflow** (`.github/workflows/cleanup-outdated-version-bumps.yml`)

**Updated Triggers**:
- When version bump PRs are auto-merged
- When regular PRs are merged (to clean up stale version bump PRs)

**Enhanced Logic**:
- Handles both auto-merged and manually merged scenarios
- Detects latest version from Git tags for regular PR merges
- Provides context-aware cleanup messages

### 3. **GitHub App Permissions**

The `blazecommerce-automation-bot` requires these permissions:
- **Contents**: `write` (for merging PRs)
- **Pull Requests**: `write` (for merging and commenting)
- **Checks**: `read` (for checking CI status)
- **Statuses**: `read` (for checking status checks)

## 🚀 Complete Workflow

### **Standard Release Flow**:
1. **Developer** creates and merges a regular PR to `main`
2. **release.yml** workflow triggers and creates a version bump PR
3. **auto-merge-version-bumps.yml** detects the new version bump PR
4. **CI/CD checks** run on the version bump PR
5. **Auto-merge** occurs once all checks pass
6. **cleanup-outdated-version-bumps.yml** cleans up any outdated version bump PRs

### **Edge Case Handling**:
- **CI Failures**: Auto-merge fails, manual intervention required
- **Merge Conflicts**: Auto-merge fails, manual resolution needed
- **Timeout**: If checks don't complete within 30 minutes, auto-merge fails
- **Multiple Version Bumps**: Cleanup workflow handles outdated PRs

## 🛡️ Safety Mechanisms

### **Strict Validation**:
- Only processes PRs created by `blazecommerce-automation-bot[bot]`
- Validates PR title format: `chore(release): bump theme version to X.Y.Z`
- Ensures all required CI/CD checks pass
- Verifies no merge conflicts exist

### **Timeout Protection**:
- Maximum wait time of 30 minutes for CI checks
- Regular status polling every 30 seconds
- Graceful failure with detailed error messages

### **Audit Trail**:
- Comprehensive logging of all actions
- Comments added to PRs explaining auto-merge
- Failure notifications with troubleshooting guidance
- Integration with existing cleanup documentation

### **Fallback Mechanisms**:
- GitHub App token with GITHUB_TOKEN fallback
- Manual merge capability if auto-merge fails
- Detailed failure notifications for debugging

## 📊 Monitoring & Troubleshooting

### **Success Indicators**:
- Version bump PR automatically merged within expected timeframe
- Cleanup workflow successfully removes outdated PRs
- No manual intervention required for standard releases

### **Failure Scenarios**:

| Scenario | Cause | Resolution |
|----------|-------|------------|
| **CI Checks Fail** | Test failures, linting errors | Fix issues and push updates |
| **Merge Conflicts** | Concurrent changes to same files | Manually resolve conflicts |
| **Timeout** | Slow CI, external service issues | Check CI logs, retry if needed |
| **Permission Issues** | GitHub App misconfiguration | Verify app permissions and secrets |
| **Invalid PR Format** | Incorrect title or author | Verify automation bot configuration |

### **Debugging Steps**:
1. **Check Workflow Logs**: Review GitHub Actions logs for detailed error messages
2. **Verify Bot Permissions**: Ensure GitHub App has required permissions
3. **Check CI Status**: Verify all required checks are configured and passing
4. **Review PR Details**: Confirm PR author and title format are correct
5. **Manual Merge**: If auto-merge fails, merge manually and investigate

## 🔧 Configuration

### **Required Secrets**:
- `BLAZECOMMERCE_BOT_APP_ID`: GitHub App ID for the automation bot
- `BLAZECOMMERCE_BOT_PRIVATE_KEY`: Private key for the GitHub App

### **Workflow Customization**:

**Timeout Settings** (in `auto-merge-version-bumps.yml`):
```yaml
MAX_WAIT_TIME=1800  # 30 minutes
CHECK_INTERVAL=30   # 30 seconds
```

**Required Checks**: The workflow automatically detects and waits for all configured status checks. No manual configuration needed.

**Merge Strategy**: Currently uses squash merge. Can be changed to:
```bash
gh pr merge "$PR_NUMBER" --merge --delete-branch    # Regular merge
gh pr merge "$PR_NUMBER" --rebase --delete-branch   # Rebase merge
```

## 🧪 Testing

### **Test Scenarios**:

1. **Standard Flow Test**:
   - Create and merge a regular PR
   - Verify version bump PR is created
   - Confirm auto-merge occurs after CI passes

2. **CI Failure Test**:
   - Create version bump PR with failing CI
   - Verify auto-merge does not occur
   - Check failure notification is added

3. **Merge Conflict Test**:
   - Create conflicting changes
   - Verify auto-merge detects conflicts
   - Confirm manual resolution is required

4. **Cleanup Test**:
   - Create multiple version bump PRs
   - Merge one version bump PR
   - Verify outdated PRs are cleaned up

### **Manual Testing Commands**:

```bash
# List current version bump PRs
gh pr list --author "blazecommerce-automation-bot[bot]" --state open

# Check PR status
gh pr view [PR_NUMBER] --json statusCheckRollup

# Test auto-merge workflow
gh workflow run auto-merge-version-bumps.yml

# Monitor workflow execution
gh run list --workflow=auto-merge-version-bumps.yml
```

## 📈 Benefits

### **Efficiency Gains**:
- **Eliminates Manual Steps**: No need to manually merge version bump PRs
- **Faster Releases**: Automated process reduces release cycle time
- **Reduced Errors**: Automated validation prevents human mistakes
- **24/7 Operation**: Works outside business hours

### **Reliability Improvements**:
- **Consistent Process**: Same steps executed every time
- **Comprehensive Checks**: All CI requirements validated before merge
- **Audit Trail**: Complete record of all automated actions
- **Rollback Capability**: Failed merges don't break the pipeline

### **Developer Experience**:
- **Hands-Off Releases**: Developers can focus on feature development
- **Clear Notifications**: Detailed status updates and failure explanations
- **Easy Debugging**: Comprehensive logs for troubleshooting
- **Flexible Fallback**: Manual override available when needed

## 🔄 Migration from Manual Process

### **Before (Manual Process)**:
1. Regular PR merged → Version bump PR created
2. **Manual step**: Developer reviews and merges version bump PR
3. Cleanup workflow removes outdated PRs

### **After (Automated Process)**:
1. Regular PR merged → Version bump PR created
2. **Automated**: Auto-merge workflow merges version bump PR
3. Cleanup workflow removes outdated PRs

### **Rollback Plan**:
If issues arise, disable auto-merge by:
1. Disabling the `auto-merge-version-bumps.yml` workflow
2. Reverting to manual merge process
3. Investigating and fixing issues
4. Re-enabling auto-merge workflow

## 📚 Related Documentation

- [Version Bump PR Cleanup System](./VERSION-BUMP-CLEANUP.md)
- [Release Workflow Documentation](./RELEASE-WORKFLOW.md)
- [GitHub Actions Troubleshooting](./GITHUB-ACTIONS-TROUBLESHOOTING.md)
- [BlazeCommerce Automation Bot Setup](./AUTOMATION-BOT-SETUP.md)
