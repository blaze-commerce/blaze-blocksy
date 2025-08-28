#!/bin/bash
# =======================================================================
# Test Script for Auto-Merge Version Bump Workflow
# =======================================================================
# This script helps test the auto-merge functionality for version bump PRs
# created by the BlazeCommerce Automation Bot.
#
# Usage: 
#   ./scripts/test-auto-merge-workflow.sh [command] [options]
#
# Commands:
#   test-flow       - Test the complete auto-merge flow
#   check-bot       - Verify bot permissions and configuration
#   list-prs        - List current version bump PRs
#   monitor         - Monitor auto-merge workflow execution
#   simulate        - Simulate version bump PR creation (dry-run)
#
# Examples:
#   ./scripts/test-auto-merge-workflow.sh test-flow
#   ./scripts/test-auto-merge-workflow.sh check-bot
#   ./scripts/test-auto-merge-workflow.sh monitor --watch
#   ./scripts/test-auto-merge-workflow.sh simulate --version 1.9.0

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
print_status() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}"
}

# Check if required tools are available
check_dependencies() {
    local missing_tools=()
    
    if ! command -v gh >/dev/null 2>&1; then
        missing_tools+=("gh (GitHub CLI)")
    fi
    
    if ! command -v git >/dev/null 2>&1; then
        missing_tools+=("git")
    fi
    
    if ! command -v jq >/dev/null 2>&1; then
        missing_tools+=("jq")
    fi
    
    if [ ${#missing_tools[@]} -gt 0 ]; then
        print_error "Missing required tools:"
        for tool in "${missing_tools[@]}"; do
            echo "  - $tool"
        done
        echo
        print_status "Please install missing tools and try again."
        exit 1
    fi
}

# Verify bot permissions and configuration
check_bot_permissions() {
    print_header "Checking Bot Permissions and Configuration"
    
    # Check if we can access the repository
    if ! gh repo view >/dev/null 2>&1; then
        print_error "Cannot access repository. Make sure you're authenticated with GitHub CLI."
        return 1
    fi
    
    print_success "Repository access confirmed"
    
    # Check for automation bot PRs
    print_status "Checking for automation bot activity..."
    local bot_prs
    bot_prs=$(gh pr list --author "blazecommerce-automation-bot[bot]" --state all --limit 5 --json number,title,state,createdAt 2>/dev/null || echo "[]")
    
    if [ "$bot_prs" = "[]" ]; then
        print_warning "No PRs found from blazecommerce-automation-bot[bot]"
        print_status "This could mean:"
        echo "  - Bot hasn't created any PRs yet"
        echo "  - Bot configuration issues"
        echo "  - Repository access problems"
    else
        print_success "Found automation bot activity:"
        echo "$bot_prs" | jq -r '.[] | "  - PR #" + (.number | tostring) + ": " + .title + " (" + .state + ")"'
    fi
    
    # Check workflow files
    print_status "Checking workflow files..."
    local workflows=("auto-merge-version-bumps.yml" "cleanup-outdated-version-bumps.yml" "release.yml")
    
    for workflow in "${workflows[@]}"; do
        if [ -f ".github/workflows/$workflow" ]; then
            print_success "Found workflow: $workflow"
        else
            print_error "Missing workflow: $workflow"
        fi
    done
    
    # Check for required secrets (we can't read them, but we can check if workflows reference them)
    print_status "Checking for required secret references..."
    if grep -q "BLAZECOMMERCE_BOT_APP_ID" .github/workflows/auto-merge-version-bumps.yml 2>/dev/null; then
        print_success "Found BLAZECOMMERCE_BOT_APP_ID reference"
    else
        print_error "Missing BLAZECOMMERCE_BOT_APP_ID reference"
    fi
    
    if grep -q "BLAZECOMMERCE_BOT_PRIVATE_KEY" .github/workflows/auto-merge-version-bumps.yml 2>/dev/null; then
        print_success "Found BLAZECOMMERCE_BOT_PRIVATE_KEY reference"
    else
        print_error "Missing BLAZECOMMERCE_BOT_PRIVATE_KEY reference"
    fi
}

# List current version bump PRs
list_version_bump_prs() {
    print_header "Current Version Bump PRs"
    
    local open_prs
    open_prs=$(gh pr list \
        --author "blazecommerce-automation-bot[bot]" \
        --state open \
        --json number,title,headRefName,url,createdAt,statusCheckRollup \
        --jq '.[] | select(.title | test("^chore(\\(release\\))?: bump( theme)? version to [0-9]+\\.[0-9]+\\.[0-9]+"))') || {
        print_error "Failed to fetch PRs"
        return 1
    }
    
    if [ -z "$open_prs" ]; then
        print_success "No open version bump PRs found"
        return 0
    fi
    
    print_status "Found open version bump PRs:"
    echo "$open_prs" | jq -c '.' | while read -r pr; do
        local pr_number pr_title pr_branch pr_url pr_created
        pr_number=$(echo "$pr" | jq -r '.number')
        pr_title=$(echo "$pr" | jq -r '.title')
        pr_branch=$(echo "$pr" | jq -r '.headRefName')
        pr_url=$(echo "$pr" | jq -r '.url')
        pr_created=$(echo "$pr" | jq -r '.createdAt')
        
        echo
        echo "  📋 PR #$pr_number: $pr_title"
        echo "     Branch: $pr_branch"
        echo "     Created: $pr_created"
        echo "     URL: $pr_url"
        
        # Check status
        local status_checks
        status_checks=$(echo "$pr" | jq -r '.statusCheckRollup[]? | .status' 2>/dev/null || echo "")
        if [ -n "$status_checks" ]; then
            local pending failed success
            pending=$(echo "$status_checks" | grep -c "PENDING\|IN_PROGRESS" || echo "0")
            failed=$(echo "$status_checks" | grep -c "FAILURE\|ERROR" || echo "0")
            success=$(echo "$status_checks" | grep -c "SUCCESS" || echo "0")
            
            echo "     Status: ✅ $success passed, ⏳ $pending pending, ❌ $failed failed"
        else
            echo "     Status: No checks found"
        fi
    done
}

# Monitor auto-merge workflow execution
monitor_workflow() {
    local watch_mode="$1"
    
    print_header "Monitoring Auto-Merge Workflow"
    
    if [ "$watch_mode" = "--watch" ]; then
        print_status "Watching for workflow runs (press Ctrl+C to stop)..."
        while true; do
            local recent_runs
            recent_runs=$(gh run list --workflow=auto-merge-version-bumps.yml --limit 5 --json status,conclusion,createdAt,url 2>/dev/null || echo "[]")
            
            clear
            echo -e "${BLUE}=== Auto-Merge Workflow Monitor ===${NC}"
            echo "Last updated: $(date)"
            echo
            
            if [ "$recent_runs" = "[]" ]; then
                print_status "No recent workflow runs found"
            else
                echo "$recent_runs" | jq -r '.[] | "  " + .status + " | " + (.conclusion // "running") + " | " + .createdAt + " | " + .url'
            fi
            
            sleep 10
        done
    else
        print_status "Recent auto-merge workflow runs:"
        local recent_runs
        recent_runs=$(gh run list --workflow=auto-merge-version-bumps.yml --limit 10 --json status,conclusion,createdAt,url 2>/dev/null || echo "[]")
        
        if [ "$recent_runs" = "[]" ]; then
            print_status "No workflow runs found"
        else
            echo "$recent_runs" | jq -r '.[] | "  " + .status + " | " + (.conclusion // "running") + " | " + .createdAt + " | " + .url'
        fi
    fi
}

# Simulate version bump PR creation (dry-run)
simulate_version_bump() {
    local version="$1"
    
    print_header "Simulating Version Bump PR Creation"
    
    if [ -z "$version" ]; then
        print_error "Version not specified"
        echo "Usage: $0 simulate --version X.Y.Z"
        return 1
    fi
    
    # Validate version format
    if ! echo "$version" | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+$'; then
        print_error "Invalid version format: $version"
        echo "Expected format: X.Y.Z (e.g., 1.9.0)"
        return 1
    fi
    
    print_status "Simulating creation of version bump PR for version $version"
    
    # Check if branch already exists
    local branch="release/bump-v$version"
    if git ls-remote --heads origin "$branch" | grep -q "$branch"; then
        print_warning "Branch $branch already exists"
    else
        print_success "Branch $branch is available"
    fi
    
    # Check current version in style.css
    if [ -f "style.css" ]; then
        local current_version
        current_version=$(grep "Version:" style.css | sed 's/.*Version: *\([0-9\.]*\).*/\1/' || echo "unknown")
        print_status "Current version in style.css: $current_version"
        
        if [ "$current_version" = "$version" ]; then
            print_warning "Target version $version matches current version"
        fi
    else
        print_warning "style.css not found"
    fi
    
    # Simulate PR title and check if it would trigger auto-merge
    local pr_title="chore(release): bump theme version to $version"
    print_status "Simulated PR title: $pr_title"
    
    if [[ "$pr_title" =~ chore(\(release\))?\:\ bump(\ theme)?\ version\ to\ ([0-9]+\.[0-9]+\.[0-9]+) ]]; then
        print_success "PR title would trigger auto-merge workflow"
    else
        print_error "PR title would NOT trigger auto-merge workflow"
    fi
    
    print_status "Simulation complete. No actual changes made."
}

# Test the complete auto-merge flow
test_complete_flow() {
    print_header "Testing Complete Auto-Merge Flow"
    
    print_status "This test will:"
    echo "  1. Check bot permissions and configuration"
    echo "  2. List current version bump PRs"
    echo "  3. Check recent workflow runs"
    echo "  4. Verify workflow file syntax"
    echo
    
    # Step 1: Check bot permissions
    if ! check_bot_permissions; then
        print_error "Bot permission check failed"
        return 1
    fi
    
    echo
    
    # Step 2: List current PRs
    list_version_bump_prs
    
    echo
    
    # Step 3: Check workflow runs
    monitor_workflow ""
    
    echo
    
    # Step 4: Verify workflow syntax
    print_status "Checking workflow file syntax..."
    if command -v yamllint >/dev/null 2>&1; then
        if yamllint .github/workflows/auto-merge-version-bumps.yml >/dev/null 2>&1; then
            print_success "Workflow YAML syntax is valid"
        else
            print_error "Workflow YAML syntax errors found"
            yamllint .github/workflows/auto-merge-version-bumps.yml
        fi
    else
        print_warning "yamllint not available, skipping syntax check"
    fi
    
    print_success "Complete flow test finished"
}

# Main script logic
main() {
    local command="${1:-help}"
    
    # Check dependencies first
    check_dependencies
    
    case "$command" in
        "test-flow")
            test_complete_flow
            ;;
        "check-bot")
            check_bot_permissions
            ;;
        "list-prs")
            list_version_bump_prs
            ;;
        "monitor")
            local watch_flag="${2:-}"
            monitor_workflow "$watch_flag"
            ;;
        "simulate")
            local version_flag="${2:-}"
            local version="${3:-}"
            if [ "$version_flag" = "--version" ] && [ -n "$version" ]; then
                simulate_version_bump "$version"
            else
                print_error "Usage: $0 simulate --version X.Y.Z"
                exit 1
            fi
            ;;
        "help"|*)
            echo "Auto-Merge Version Bump Workflow Test Script"
            echo
            echo "Usage: $0 [command] [options]"
            echo
            echo "Commands:"
            echo "  test-flow       - Test the complete auto-merge flow"
            echo "  check-bot       - Verify bot permissions and configuration"
            echo "  list-prs        - List current version bump PRs"
            echo "  monitor         - Monitor auto-merge workflow execution"
            echo "  monitor --watch - Monitor with live updates"
            echo "  simulate        - Simulate version bump PR creation"
            echo "  help            - Show this help message"
            echo
            echo "Examples:"
            echo "  $0 test-flow"
            echo "  $0 check-bot"
            echo "  $0 monitor --watch"
            echo "  $0 simulate --version 1.9.0"
            ;;
    esac
}

# Run main function with all arguments
main "$@"
