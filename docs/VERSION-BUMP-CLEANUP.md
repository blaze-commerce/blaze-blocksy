# Version Bump PR Cleanup System

This document describes the automated cleanup system for outdated version bump PRs created by the BlazeCommerce Automation Bot.

## 🎯 Purpose

The cleanup system prevents accumulation of stale version bump PRs by automatically:
- Identifying outdated version bump PRs after a new version is released
- Closing obsolete PRs with explanatory comments
- Deleting associated feature branches to keep the repository clean

## 🔧 Implementation Components

### 1. **Automated Cleanup Workflow** (`.github/workflows/cleanup-outdated-version-bumps.yml`)

**Trigger**: Runs when a version bump PR is merged to the main branch

**Process**:
1. **Version Extraction**: Extracts the version number from the merged PR title
2. **PR Discovery**: Finds all open PRs created by `blazecommerce-automation-bot[bot]`
3. **Version Comparison**: Compares versions to identify outdated PRs
4. **Cleanup Actions**: Closes outdated PRs and deletes branches

**Safety Features**:
- Only targets PRs created by the automation bot
- Only processes PRs with specific title patterns
- Uses semantic version comparison for accuracy
- Adds explanatory comments before closing PRs

### 2. **Manual Cleanup Script** (`scripts/cleanup-version-bump-prs.sh`)

**Usage**:
```bash
# Clean up PRs older than v1.8.0
./scripts/cleanup-version-bump-prs.sh 1.8.0

# Dry run to see what would be cleaned up
./scripts/cleanup-version-bump-prs.sh --dry-run 1.8.0

# List all open version bump PRs
./scripts/cleanup-version-bump-prs.sh --list
```

**Features**:
- Interactive dry-run mode for testing
- Colored output for better readability
- Comprehensive error handling and validation
- Manual override capabilities for edge cases

## 📋 Supported PR Title Patterns

The cleanup system recognizes these version bump PR title patterns:

```bash
✅ "chore(release): bump theme version to 1.8.0"
✅ "chore: bump version to 1.8.0"
✅ "chore(release): bump theme version to 2.0.0-beta.1"
✅ "chore: bump version to 1.7.5"
```

## 🔍 Version Comparison Logic

The system uses semantic version comparison to determine outdated PRs:

| Merged Version | PR Version | Action | Reason |
|----------------|------------|--------|---------|
| 1.8.0 | 1.7.0 | ❌ Close | Older version |
| 1.8.0 | 1.8.0 | ❌ Close | Duplicate version |
| 1.8.0 | 1.9.0 | ✅ Keep | Newer version |
| 1.8.0 | 2.0.0 | ✅ Keep | Future major version |

## 🛡️ Safety Mechanisms

### **Strict Targeting**:
- Only processes PRs created by `blazecommerce-automation-bot[bot]`
- Only matches specific title patterns with regex validation
- Validates version format before processing

### **Branch Protection**:
- Only deletes branches following the pattern `release/bump-vX.Y.Z`
- Gracefully handles cases where branches are already deleted
- Logs all actions for audit trail

### **Error Handling**:
- Continues processing even if individual operations fail
- Provides detailed logging for troubleshooting
- Uses `set -euo pipefail` for strict error handling

## 🚀 Integration Options

### **Option 1: Automatic Cleanup (Recommended)**
The workflow automatically runs when version bump PRs are merged:

```yaml
# Triggers on PR merge events
on:
  pull_request:
    types: [closed]
    branches: [main]

# Only runs for merged version bump PRs
if: >
  github.event.pull_request.merged == true &&
  github.event.pull_request.user.login == 'blazecommerce-automation-bot[bot]' &&
  startsWith(github.event.pull_request.title, 'chore(release): bump theme version to')
```

### **Option 2: Manual Cleanup**
Use the script for one-off cleanups or testing:

```bash
# Check what would be cleaned up
./scripts/cleanup-version-bump-prs.sh --dry-run 1.8.0

# Perform actual cleanup
./scripts/cleanup-version-bump-prs.sh 1.8.0
```

### **Option 3: Integration with Auto-Approval Workflow**
Add cleanup step to auto-approval workflow:

```yaml
- name: Cleanup after auto-merge
  if: steps.auto-merge.outputs.merged == 'true'
  run: ./scripts/cleanup-version-bump-prs.sh "$MERGED_VERSION"
```

## 📊 Workflow Example

### **Scenario**: Version 1.8.0 is released

1. **Before Cleanup**:
   ```
   Open PRs:
   - PR #25: "chore(release): bump theme version to 1.6.0" ❌ (outdated)
   - PR #28: "chore(release): bump theme version to 1.7.0" ❌ (outdated)  
   - PR #32: "chore(release): bump theme version to 1.8.0" ❌ (duplicate)
   - PR #35: "chore(release): bump theme version to 1.9.0" ✅ (future)
   ```

2. **After Cleanup**:
   ```
   Open PRs:
   - PR #35: "chore(release): bump theme version to 1.9.0" ✅ (kept)
   
   Closed PRs:
   - PR #25: Closed with explanation ❌
   - PR #28: Closed with explanation ❌
   - PR #32: Closed with explanation ❌
   
   Deleted Branches:
   - release/bump-v1.6.0 🗑️
   - release/bump-v1.7.0 🗑️
   - release/bump-v1.8.0 🗑️
   ```

## 🔧 Configuration

### **Required Permissions**:
```yaml
permissions:
  contents: write      # For branch deletion
  pull-requests: write # For closing PRs and adding comments
```

### **Required Tools**:
- GitHub CLI (`gh`) for PR operations
- `jq` for JSON processing
- `sort -V` for version comparison

### **Environment Variables**:
- `GH_TOKEN`: GitHub token with appropriate permissions
- `GITHUB_TOKEN`: Standard GitHub Actions token

## 🐛 Troubleshooting

### **Common Issues**:

1. **"GitHub CLI not found"**
   ```bash
   # Install GitHub CLI
   curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
   echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
   sudo apt update && sudo apt install gh
   ```

2. **"Permission denied"**
   - Ensure the GitHub token has `contents: write` and `pull-requests: write` permissions
   - Check that the bot has access to the repository

3. **"Version comparison failed"**
   - Verify version format is X.Y.Z (semantic versioning)
   - Check that `sort -V` is available on the system

### **Debug Mode**:
```bash
# Enable debug output
export DEBUG=1
./scripts/cleanup-version-bump-prs.sh --dry-run 1.8.0
```

## 📈 Monitoring

### **Success Indicators**:
- ✅ Workflow completes without errors
- ✅ Outdated PRs are closed with explanatory comments
- ✅ Associated branches are deleted
- ✅ Future version PRs remain open

### **Failure Indicators**:
- ❌ Workflow fails with permission errors
- ❌ PRs are not closed or comments not added
- ❌ Branches are not deleted
- ❌ Wrong PRs are targeted for cleanup

### **Monitoring Commands**:
```bash
# Check recent workflow runs
gh run list --workflow="cleanup-outdated-version-bumps.yml"

# View specific run details
gh run view <run-id> --log

# List current open version bump PRs
./scripts/cleanup-version-bump-prs.sh --list
```

---

**The cleanup system ensures a clean and organized repository by automatically managing outdated version bump PRs while maintaining safety and providing manual override capabilities.**
