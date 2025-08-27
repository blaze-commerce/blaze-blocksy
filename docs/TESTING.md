# Testing the Semantic Release Workflow

This document provides comprehensive testing instructions to verify the automated semantic versioning and release management workflow works correctly.

## Prerequisites

Before testing, ensure:
- You have access to the GitHub repository
- GitHub Actions are enabled for the repository
- The workflow file is present at `.github/workflows/release.yml`
- You have a local clone of the repository
- Branch protection rules are enabled on `main`:
  - Pull Request approval is required before merging
  - Status checks must pass before merging
  - Direct pushes to `main` are blocked

## Test Scenarios

### Test 1: Patch Release (Bug Fix)

**Objective**: Verify that `fix:` commits trigger a PATCH version bump.

**Steps**:
1. Create a new branch:
   ```bash
   git checkout -b test/patch-release
   ```

2. Make a small CSS change (e.g., add a comment):
   ```bash
   echo "/* Test patch fix */" >> style.css
   ```

3. Commit with conventional format:
   ```bash
   git add style.css
   git commit -m "fix: resolve minor CSS formatting issue in style.css"
   ```

4. Push and create PR:
   ```bash
   git push origin test/patch-release
   ```
   Create a PR via GitHub UI with title: `fix: resolve minor CSS formatting issue in style.css`.

5. Request review and get PR approval:
   - Assign at least one reviewer
   - Wait for all required status checks to pass
   - Ensure all review comments are addressed

6. Merge the PR after approval (squash or merge strategy per repo policy)

**Expected Results**:
- Release workflow runs automatically after the PR is approved and merged
- Version bumps from `X.Y.Z` to `X.Y.(Z+1)`
- A follow-up PR is automatically opened to update `style.css` on `main` with the new version (branch: `release/bump-vX.Y.Z`)
- Git tag created (e.g., `v1.0.1`)
- GitHub release created with changelog
- ZIP file attached to release

### Test 2: Minor Release (New Feature)

**Objective**: Verify that `feat:` commits trigger a MINOR version bump.

**Steps**:
1. Create a new branch:
   ```bash
   git checkout -b test/minor-release
   ```

2. Add a new CSS class:
   ```css
   /* Add to style.css */
   .test-feature {
       display: block;
       color: #333;
   }
   ```

3. Commit with conventional format:
   ```bash
   git add style.css
   git commit -m "feat: add new test-feature CSS class for enhanced styling"
   ```

4. Push and create PR, request review, wait for status checks to pass, then merge after approval

**Expected Results**:
- Version bumps from `X.Y.Z` to `X.(Y+1).0`
- A follow-up PR is created to bump the version in `style.css`
- All other expected results from Test 1

### Test 3: Major Release (Breaking Change)

**Objective**: Verify that breaking change commits trigger a MAJOR version bump.

**Steps**:
1. Create a new branch:
   ```bash
   git checkout -b test/major-release
   ```

2. Remove or significantly change existing CSS:
   ```css
   /* Comment out or remove existing classes */
   /*
   .jdgm-carousel-item {
       width: 33.33% !important;
   }
   */
   ```

3. Commit with breaking change format:
   ```bash
   git add style.css
   git commit -m "feat!: restructure carousel CSS classes

   BREAKING CHANGE: Removed .jdgm-carousel-item width styling.
   Sites using this class will need to update their CSS."
   ```

4. Push, create PR, request review, wait for status checks to pass, and merge after approval

**Expected Results**:
- Version bumps from `X.Y.Z` to `(X+1).0.0`
- A follow-up PR is created to bump the version in `style.css`
- Release notes include breaking change information

### Test 4: Multiple Commits in Single PR

**Objective**: Verify the workflow handles multiple commits correctly.

**Steps**:
1. Create a new branch:
   ```bash
   git checkout -b test/multiple-commits
   ```

2. Make multiple commits:
   ```bash
   # First commit (patch)
   echo "/* Fix 1 */" >> style.css
   git add style.css
   git commit -m "fix: correct mobile responsive issue"

   # Second commit (feature)
   echo "/* Feature 1 */" >> style.css
   git add style.css
   git commit -m "feat: add new responsive utility classes"

   # Third commit (patch)
   echo "/* Fix 2 */" >> style.css
   git add style.css
   git commit -m "fix: resolve z-index conflict"
   ```

3. Push, create PR, and merge

**Expected Results**:
- Version should bump by MINOR (highest priority change)
- Changelog includes all commits
- Single release created for the PR

### Test 5: Non-Conventional Commits

**Objective**: Verify that non-conventional commits don't trigger releases.

**Steps**:
1. Create a new branch:
   ```bash
   git checkout -b test/no-release
   ```

2. Make commits without conventional format:
   ```bash
   echo "/* Documentation update */" >> style.css
   git add style.css
   git commit -m "docs: update CSS comments for better documentation"
   ```

3. Push, create PR, request review, wait for status checks to pass, and merge after approval

**Expected Results**:
- Workflow runs but determines no version bump needed
- No new release created
- No version update in `style.css`

### Test 6: First Release (No Previous Tags)

**Objective**: Test workflow behavior when no previous releases exist.

**Steps**:
1. If testing on a fresh repository, ensure no tags exist:
   ```bash
   git tag -l  # Should show no tags
   ```

2. Follow Test 1 steps

**Expected Results**:
- Version starts from `0.0.1` (or `1.0.0` depending on commit type)
- Changelog includes all commits from repository history
- First release created successfully

## Verification Checklist

After each test, verify:

### GitHub Actions
- [ ] Workflow appears in Actions tab
- [ ] All steps completed successfully (green checkmarks)
- [ ] No error messages in logs
- [ ] Execution time is reasonable (< 5 minutes)

### Version Updates
- [ ] `style.css` header contains correct new version
- [ ] Version follows semantic versioning rules
- [ ] Git commit created for version update
- [ ] Commit message follows format: `chore(release): bump version to X.Y.Z`

### Git Tags
- [ ] New tag created with format `vX.Y.Z`
- [ ] Tag points to correct commit
- [ ] Tag pushed to remote repository

### GitHub Releases
- [ ] New release appears on Releases page
- [ ] Release title matches `Release vX.Y.Z`
- [ ] Changelog is auto-generated and accurate
- [ ] Release is not marked as draft or prerelease

### ZIP Distribution
- [ ] ZIP file attached to release
- [ ] ZIP filename follows format `blocksy-child-vX.Y.Z.zip`
- [ ] ZIP contains all theme files in `blocksy-child/` folder
- [ ] ZIP excludes `.git`, `docs/`, `.github/`, and `*.md` files
- [ ] ZIP is downloadable and extractable
- [ ] Extracted folder is consistently named `blocksy-child` (without version suffix)

### WordPress Compatibility
- [ ] ZIP file installs correctly in WordPress
- [ ] Theme header shows correct version in WordPress admin
- [ ] Theme functions work as expected after installation

## Troubleshooting Common Issues

### Workflow Doesn't Trigger
**Symptoms**: No workflow run after merging PR
**Solutions**:
- Verify workflow file is in `.github/workflows/` directory
- Check that PR was actually merged (not just closed)
- Ensure target branch is `main` or `master`
- Confirm the PR was approved and all required status checks passed before merging

### Version Not Updated
**Symptoms**: Workflow runs but version in `style.css` unchanged
**Solutions**:
- Check commit messages follow conventional format
- Verify `style.css` file exists and is readable
- Review workflow logs for parsing errors

### Release Creation Fails
**Symptoms**: Workflow runs but no GitHub release created
**Solutions**:
- Verify `GITHUB_TOKEN` has correct permissions
- Check for existing tag with same version
- Review GitHub API rate limits

### ZIP File Issues
**Symptoms**: ZIP file missing or corrupted
**Solutions**:
- Check file permissions in repository
- Verify `rsync` and `zip` commands work correctly
- Review excluded file patterns

### Rollback Activated
**Symptoms**: Rollback job runs after main job failure
**Solutions**:
- Review main job logs for failure cause
- Check if partial changes were reverted correctly
- Manually clean up any remaining artifacts

## Manual Testing Commands

### Check Current Version
```bash
grep "Version:" style.css || echo "No version found"
```

### List All Tags
```bash
git tag -l --sort=-version:refname
```

### View Recent Commits
```bash
git log --oneline -10
```

### Check Workflow Status
```bash
# Via GitHub CLI (if installed)
gh run list --workflow=release.yml --limit=5
```

### Validate ZIP Contents
```bash
# After downloading ZIP from release
unzip -l blocksy-child-vX.Y.Z.zip

# Verify folder structure (should show blocksy-child/ as root folder)
# Expected output should show:
#   blocksy-child/style.css
#   blocksy-child/functions.php
#   blocksy-child/assets/
#   etc.
```

## Performance Benchmarks

Expected workflow performance:
- **Total Runtime**: 2-4 minutes
- **Checkout**: < 30 seconds
- **Version Calculation**: < 10 seconds
- **File Updates**: < 5 seconds
- **ZIP Creation**: < 30 seconds
- **Release Creation**: < 30 seconds

If workflow consistently exceeds these times, investigate:
- Repository size and checkout performance
- Network connectivity issues
- GitHub Actions runner performance

## Test Environment Setup

For comprehensive testing, consider setting up:

1. **Test Repository**: Fork or create a test copy
2. **Test WordPress Site**: Local installation for ZIP testing
3. **Multiple Branches**: Various scenarios ready for testing
4. **Mock Commits**: Pre-written commit messages for different scenarios

## Automated Testing

Consider creating additional automated tests:
- Unit tests for version calculation logic
- Integration tests for file updates
- End-to-end tests for complete workflow
- Performance tests for large repositories

This ensures the workflow remains reliable as the repository grows and changes.
