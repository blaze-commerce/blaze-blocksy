# Auto-Merge Workflow Verification Test

This file was created to test the new auto-merge functionality for version bump PRs.

## Test Details

- **Date**: 2025-08-28
- **Purpose**: Verify that the auto-merge workflow correctly handles version bump PRs
- **Expected Flow**:
  1. This PR gets merged to main
  2. Release workflow triggers and creates a version bump PR
  3. Auto-merge workflow detects the version bump PR
  4. Auto-merge workflow waits for CI checks to pass
  5. Auto-merge workflow automatically merges the version bump PR
  6. Cleanup workflow removes any outdated version bump PRs

## Verification Points

- [ ] Release workflow creates version bump PR
- [ ] Auto-merge workflow triggers on version bump PR
- [ ] CI checks pass on version bump PR
- [ ] Version bump PR is automatically merged
- [ ] Cleanup workflow executes successfully
- [ ] No manual intervention required

## Test Status

This test will validate that the BlazeCommerce automation bot can successfully auto-merge version bump PRs without manual intervention, streamlining the release process.
