#!/bin/bash
# =======================================================================
# Manual Cleanup Script for Outdated Version Bump PRs
# =======================================================================
# This script helps maintainers manually clean up outdated version bump
# PRs created by the BlazeCommerce Automation Bot.
#
# Usage: 
#   ./scripts/cleanup-version-bump-prs.sh [current_version]
#   ./scripts/cleanup-version-bump-prs.sh --dry-run [current_version]
#   ./scripts/cleanup-version-bump-prs.sh --list
#
# Examples:
#   ./scripts/cleanup-version-bump-prs.sh 1.8.0
#   ./scripts/cleanup-version-bump-prs.sh --dry-run 1.8.0
#   ./scripts/cleanup-version-bump-prs.sh --list
# =======================================================================

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to show usage
show_usage() {
    echo "Usage: $0 [OPTIONS] [CURRENT_VERSION]"
    echo ""
    echo "Options:"
    echo "  --dry-run    Show what would be done without actually doing it"
    echo "  --list       List all open version bump PRs without taking action"
    echo "  --help       Show this help message"
    echo ""
    echo "Arguments:"
    echo "  CURRENT_VERSION  The current/latest version (e.g., 1.8.0)"
    echo ""
    echo "Examples:"
    echo "  $0 1.8.0                    # Clean up PRs older than v1.8.0"
    echo "  $0 --dry-run 1.8.0          # Show what would be cleaned up"
    echo "  $0 --list                   # List all open version bump PRs"
}

# Function to validate version format
validate_version() {
    local version="$1"
    if [[ ! "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        print_error "Invalid version format: $version"
        print_status "Version must be in format X.Y.Z (e.g., 1.8.0)"
        exit 1
    fi
}

# Function to compare versions
version_compare() {
    local version1="$1"
    local version2="$2"
    
    # Use sort -V for version comparison
    local older_version
    older_version=$(printf '%s\n%s\n' "$version1" "$version2" | sort -V | head -n1)
    
    if [ "$older_version" = "$version1" ]; then
        if [ "$version1" = "$version2" ]; then
            echo "equal"
        else
            echo "older"
        fi
    else
        echo "newer"
    fi
}

# Function to list open version bump PRs
list_version_bump_prs() {
    print_status "Fetching open version bump PRs created by blazecommerce-automation-bot[bot]..."
    
    # Check if gh CLI is available
    if ! command -v gh >/dev/null 2>&1; then
        print_error "GitHub CLI (gh) is not installed or not in PATH"
        print_status "Please install GitHub CLI: https://cli.github.com/"
        exit 1
    fi
    
    # Get open PRs created by the bot
    local open_prs
    open_prs=$(gh pr list \
        --author "blazecommerce-automation-bot[bot]" \
        --state open \
        --json number,title,headRefName,url,createdAt \
        --jq '.[] | select(.title | test("^chore(\\(release\\))?: bump( theme)? version to [0-9]+\\.[0-9]+\\.[0-9]+"))') || {
        print_error "Failed to fetch PRs. Make sure you're authenticated with GitHub CLI."
        exit 1
    }
    
    if [ -z "$open_prs" ]; then
        print_success "No open version bump PRs found"
        return 0
    fi
    
    echo
    print_status "Open version bump PRs:"
    echo "$open_prs" | jq -r '"  🔄 PR #" + (.number | tostring) + ": " + .title + " (created: " + (.createdAt | split("T")[0]) + ")"'
    echo
    
    local count
    count=$(echo "$open_prs" | jq -s 'length')
    print_status "Total: $count open version bump PR(s)"
    
    return 0
}

# Function to cleanup outdated PRs
cleanup_outdated_prs() {
    local current_version="$1"
    local dry_run="$2"
    
    validate_version "$current_version"
    
    print_status "Current version: $current_version"
    print_status "Looking for outdated version bump PRs..."
    
    # Get open PRs created by the bot
    local open_prs
    open_prs=$(gh pr list \
        --author "blazecommerce-automation-bot[bot]" \
        --state open \
        --json number,title,headRefName,url \
        --jq '.[] | select(.title | test("^chore(\\(release\\))?: bump( theme)? version to [0-9]+\\.[0-9]+\\.[0-9]+"))') || {
        print_error "Failed to fetch PRs"
        exit 1
    }
    
    if [ -z "$open_prs" ]; then
        print_success "No open version bump PRs found"
        return 0
    fi
    
    # Analyze each PR
    local outdated_count=0
    local temp_file
    temp_file=$(mktemp)
    
    echo "$open_prs" | jq -c '.' | while read -r pr; do
        local pr_number pr_title pr_branch pr_version comparison
        pr_number=$(echo "$pr" | jq -r '.number')
        pr_title=$(echo "$pr" | jq -r '.title')
        pr_branch=$(echo "$pr" | jq -r '.headRefName')
        
        # Extract version from PR title
        if [[ "$pr_title" =~ chore(\(release\))?\:\ bump(\ theme)?\ version\ to\ ([0-9]+\.[0-9]+\.[0-9]+) ]]; then
            pr_version="${BASH_REMATCH[3]}"
            
            comparison=$(version_compare "$pr_version" "$current_version")
            
            case "$comparison" in
                "older"|"equal")
                    echo "❌ PR #$pr_number (v$pr_version) is outdated (current: v$current_version)"
                    echo "$pr" >> "$temp_file"
                    ;;
                "newer")
                    echo "✅ PR #$pr_number (v$pr_version) is newer than current version (v$current_version)"
                    ;;
            esac
        else
            print_warning "Could not extract version from PR title: $pr_title"
        fi
    done
    
    # Process outdated PRs
    if [ -f "$temp_file" ] && [ -s "$temp_file" ]; then
        outdated_count=$(wc -l < "$temp_file")
        echo
        print_warning "Found $outdated_count outdated version bump PR(s)"
        
        if [ "$dry_run" = "true" ]; then
            print_status "DRY RUN - Would close the following PRs:"
            cat "$temp_file" | jq -r '"  🗑️  PR #" + (.number | tostring) + ": " + .title'
        else
            print_status "Closing outdated PRs..."
            
            cat "$temp_file" | jq -c '.' | while read -r pr; do
                local pr_number pr_title pr_branch pr_version
                pr_number=$(echo "$pr" | jq -r '.number')
                pr_title=$(echo "$pr" | jq -r '.title')
                pr_branch=$(echo "$pr" | jq -r '.headRefName')
                
                # Extract version for comment
                if [[ "$pr_title" =~ chore(\(release\))?\:\ bump(\ theme)?\ version\ to\ ([0-9]+\.[0-9]+\.[0-9]+) ]]; then
                    pr_version="${BASH_REMATCH[3]}"
                else
                    pr_version="unknown"
                fi
                
                print_status "Closing PR #$pr_number (v$pr_version)..."
                
                # Add comment and close PR
                local comment="🤖 **Manual Cleanup**

This version bump PR is being closed because version **$current_version** is now the current version.

**Details:**
- 📦 **Target version**: $pr_version
- ✅ **Current version**: $current_version
- 🕒 **Closed at**: $(date -u '+%Y-%m-%d %H:%M:%S UTC')

This version bump is no longer needed.

---
*This action was performed manually using the cleanup script.*"
                
                gh pr comment "$pr_number" --body "$comment"
                gh pr close "$pr_number" --comment "Manually closed - version $current_version is current"
                
                # Delete branch if it follows the standard pattern
                if [[ "$pr_branch" =~ ^release/bump-v[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
                    print_status "Deleting branch: $pr_branch"
                    git push origin --delete "$pr_branch" 2>/dev/null || print_warning "Could not delete branch $pr_branch"
                fi
                
                print_success "Closed PR #$pr_number"
            done
        fi
    else
        print_success "No outdated version bump PRs found"
    fi
    
    rm -f "$temp_file"
}

# Main function
main() {
    local dry_run="false"
    local list_only="false"
    local current_version=""
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --dry-run)
                dry_run="true"
                shift
                ;;
            --list)
                list_only="true"
                shift
                ;;
            --help)
                show_usage
                exit 0
                ;;
            -*)
                print_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
            *)
                if [ -z "$current_version" ]; then
                    current_version="$1"
                else
                    print_error "Too many arguments"
                    show_usage
                    exit 1
                fi
                shift
                ;;
        esac
    done
    
    # Check if we're in a git repository
    if [ ! -d ".git" ]; then
        print_error "This script must be run from the root of a git repository"
        exit 1
    fi
    
    print_status "BlazeCommerce Version Bump PR Cleanup Tool"
    echo
    
    if [ "$list_only" = "true" ]; then
        list_version_bump_prs
    elif [ -n "$current_version" ]; then
        cleanup_outdated_prs "$current_version" "$dry_run"
    else
        print_error "Missing required argument: CURRENT_VERSION"
        echo
        show_usage
        exit 1
    fi
}

# Run main function
main "$@"
