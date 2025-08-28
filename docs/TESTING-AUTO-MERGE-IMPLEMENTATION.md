# Testing Auto-Merge Implementation

This document provides step-by-step instructions for testing the new auto-merge functionality for version bump PRs.

## 🧪 Pre-Testing Checklist

### **1. Verify Workflow Files**
Ensure all required workflow files are present:
- [ ] `.github/workflows/auto-merge-version-bumps.yml`
- [ ] `.github/workflows/cleanup-outdated-version-bumps.yml` (updated)
- [ ] `.github/workflows/release.yml`
- [ ] `.github/workflows/pr-validation.yml`

### **2. Check GitHub App Configuration**
Verify the blazecommerce-automation-bot has required permissions:
- [ ] Contents: Write
- [ ] Pull Requests: Write  
- [ ] Checks: Read
- [ ] Statuses: Read

### **3. Validate Secrets**
Confirm repository secrets are configured:
- [ ] `BLAZECOMMERCE_BOT_APP_ID`
- [ ] `BLAZECOMMERCE_BOT_PRIVATE_KEY`

## 🚀 Test Scenarios

### **Test 1: Complete Auto-Merge Flow**

**Objective**: Verify the complete flow from regular PR merge to auto-merged version bump PR.

**Steps**:
1. Create a test branch with a small change:
   ```bash
   git checkout -b test/auto-merge-flow
   echo "# Test auto-merge flow" >> test-file.md
   git add test-file.md
   git commit -m "feat: add test file for auto-merge flow"
   git push origin test/auto-merge-flow
   ```

2. Create and merge a PR:
   ```bash
   gh pr create --title "feat: test auto-merge functionality" --body "Testing the new auto-merge system for version bump PRs"
   gh pr merge --squash --delete-branch
   ```

3. Monitor the automation:
   ```bash
   # Watch for version bump PR creation
   ./scripts/test-auto-merge-workflow.sh monitor --watch
   
   # Or check manually
   gh pr list --author "blazecommerce-automation-bot[bot]"
   ```

**Expected Results**:
- [ ] Version bump PR created by automation bot
- [ ] Auto-merge workflow triggers
- [ ] CI checks run and pass
- [ ] Version bump PR automatically merged
- [ ] Cleanup workflow removes any outdated PRs

### **Test 2: CI Failure Handling**

**Objective**: Verify auto-merge fails gracefully when CI checks fail.

**Steps**:
1. Create a version bump PR manually (simulating bot):
   ```bash
   git checkout -b release/bump-v1.9.0
   # Modify style.css to introduce a syntax error
   echo "/* Syntax error: unclosed comment" >> style.css
   git add style.css
   git commit -m "chore(release): bump theme version to 1.9.0"
   git push origin release/bump-v1.9.0
   ```

2. Create PR as if from automation bot:
   ```bash
   gh pr create --title "chore(release): bump theme version to 1.9.0" --body "Test PR with failing CI"
   ```

3. Monitor auto-merge behavior:
   ```bash
   gh run list --workflow=auto-merge-version-bumps.yml
   ```

**Expected Results**:
- [ ] Auto-merge workflow triggers
- [ ] CI checks fail
- [ ] Auto-merge workflow fails gracefully
- [ ] Failure comment added to PR
- [ ] PR remains open for manual intervention

### **Test 3: Merge Conflict Detection**

**Objective**: Verify auto-merge detects and handles merge conflicts.

**Steps**:
1. Create conflicting changes in main branch:
   ```bash
   git checkout main
   echo "Conflicting change" >> style.css
   git add style.css
   git commit -m "feat: add conflicting change"
   git push origin main
   ```

2. Create version bump PR with conflicting changes:
   ```bash
   git checkout -b release/bump-v1.9.1
   echo "Different conflicting change" >> style.css
   git add style.css
   git commit -m "chore(release): bump theme version to 1.9.1"
   git push origin release/bump-v1.9.1
   gh pr create --title "chore(release): bump theme version to 1.9.1" --body "Test PR with merge conflicts"
   ```

**Expected Results**:
- [ ] Auto-merge workflow triggers
- [ ] Merge conflict detected
- [ ] Auto-merge workflow fails
- [ ] Conflict notification added to PR
- [ ] PR remains open for manual resolution

### **Test 4: Permission Validation**

**Objective**: Verify only automation bot PRs trigger auto-merge.

**Steps**:
1. Create version bump PR from regular user account:
   ```bash
   git checkout -b test/fake-version-bump
   echo "Version: 1.9.2" >> style.css
   git add style.css
   git commit -m "chore(release): bump theme version to 1.9.2"
   git push origin test/fake-version-bump
   gh pr create --title "chore(release): bump theme version to 1.9.2" --body "Test PR from regular user"
   ```

**Expected Results**:
- [ ] Auto-merge workflow does NOT trigger
- [ ] PR remains open
- [ ] No auto-merge activity in workflow logs

### **Test 5: Cleanup Workflow Integration**

**Objective**: Verify cleanup workflow works with auto-merged PRs.

**Steps**:
1. Create multiple version bump PRs:
   ```bash
   # Create older version PR
   git checkout -b release/bump-v1.8.0
   git commit --allow-empty -m "chore(release): bump theme version to 1.8.0"
   git push origin release/bump-v1.8.0
   gh pr create --title "chore(release): bump theme version to 1.8.0" --body "Older version PR"
   
   # Create newer version PR
   git checkout -b release/bump-v1.9.0
   git commit --allow-empty -m "chore(release): bump theme version to 1.9.0"
   git push origin release/bump-v1.9.0
   gh pr create --title "chore(release): bump theme version to 1.9.0" --body "Newer version PR"
   ```

2. Simulate auto-merge of newer version:
   ```bash
   gh pr merge [NEWER_PR_NUMBER] --squash --delete-branch
   ```

3. Check cleanup workflow:
   ```bash
   gh run list --workflow=cleanup-outdated-version-bumps.yml
   ```

**Expected Results**:
- [ ] Cleanup workflow triggers after merge
- [ ] Older version PR automatically closed
- [ ] Explanatory comment added to closed PR
- [ ] Associated branch deleted

## 🔍 Monitoring & Validation

### **Real-Time Monitoring**

Use the test script for continuous monitoring:
```bash
# Monitor all workflows
./scripts/test-auto-merge-workflow.sh monitor --watch

# Check bot permissions
./scripts/test-auto-merge-workflow.sh check-bot

# List current version bump PRs
./scripts/test-auto-merge-workflow.sh list-prs
```

### **Workflow Status Checks**

```bash
# Check recent auto-merge runs
gh run list --workflow=auto-merge-version-bumps.yml --limit 10

# Check cleanup workflow runs
gh run list --workflow=cleanup-outdated-version-bumps.yml --limit 10

# Check release workflow runs
gh run list --workflow=release.yml --limit 10
```

### **Manual Verification**

```bash
# Check for automation bot PRs
gh pr list --author "blazecommerce-automation-bot[bot]" --state all

# Check recent tags
git tag -l "v*" | sort -V | tail -5

# Check recent releases
gh release list --limit 5
```

## 📊 Test Results Documentation

### **Test Results Template**

| Test | Status | Duration | Notes |
|------|--------|----------|-------|
| Complete Auto-Merge Flow | ✅/❌ | X minutes | |
| CI Failure Handling | ✅/❌ | X minutes | |
| Merge Conflict Detection | ✅/❌ | X minutes | |
| Permission Validation | ✅/❌ | X minutes | |
| Cleanup Integration | ✅/❌ | X minutes | |

### **Performance Metrics**

| Metric | Target | Actual | Status |
|--------|--------|--------|--------|
| Auto-merge trigger time | < 1 minute | X minutes | ✅/❌ |
| CI check wait time | < 30 minutes | X minutes | ✅/❌ |
| Merge completion time | < 1 minute | X minutes | ✅/❌ |
| Cleanup execution time | < 2 minutes | X minutes | ✅/❌ |

## 🚨 Troubleshooting

### **Common Issues**

1. **Workflow not triggering**:
   - Check PR author is `blazecommerce-automation-bot[bot]`
   - Verify PR title matches expected pattern
   - Check workflow file syntax

2. **CI checks timeout**:
   - Review CI configuration
   - Check external service dependencies
   - Verify check requirements

3. **Permission errors**:
   - Validate GitHub App configuration
   - Check repository secrets
   - Verify bot permissions

### **Debug Commands**

```bash
# Check workflow syntax
yamllint .github/workflows/auto-merge-version-bumps.yml

# View workflow logs
gh run view [RUN_ID] --log

# Check PR status
gh pr view [PR_NUMBER] --json statusCheckRollup

# Test GitHub CLI authentication
gh auth status
```

## ✅ Sign-off Checklist

Before considering the auto-merge implementation complete:

- [ ] All test scenarios pass
- [ ] Performance metrics meet targets
- [ ] Documentation is complete and accurate
- [ ] Team training completed
- [ ] Monitoring and alerting configured
- [ ] Rollback procedure tested
- [ ] Stakeholder approval obtained

## 📞 Support

If issues arise during testing:

1. **Check workflow logs** in GitHub Actions
2. **Review this testing guide** for troubleshooting steps
3. **Use the test script** for automated diagnostics
4. **Create GitHub issue** with detailed error information
5. **Contact development team** for complex issues

---

*This testing guide ensures the auto-merge system works reliably before production deployment.*
