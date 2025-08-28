#!/bin/bash
# =======================================================================
# Pre-commit Setup Script for Blaze Commerce Blocksy Child Theme
# =======================================================================
# This script sets up pre-commit hooks for conventional commit validation
# and code quality checks.
#
# Usage: ./scripts/setup-pre-commit.sh
# =======================================================================

set -euo pipefail  # Exit on any error, undefined variables, or pipe failures

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

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Main setup function
main() {
    print_status "Setting up pre-commit hooks for Blaze Commerce Blocksy Child Theme..."
    echo

    # Check if we're in a git repository
    if [ ! -d ".git" ]; then
        print_error "This script must be run from the root of a git repository"
        exit 1
    fi

    # Check if .pre-commit-config.yaml exists
    if [ ! -f ".pre-commit-config.yaml" ]; then
        print_error ".pre-commit-config.yaml not found in current directory"
        exit 1
    fi

    # Check if we have write permissions in the current directory
    if [ ! -w "." ]; then
        print_error "No write permissions in current directory"
        print_status "Please run from a directory where you have write access"
        exit 1
    fi

    # Check if git is properly configured
    if ! git config user.name >/dev/null 2>&1 || ! git config user.email >/dev/null 2>&1; then
        print_warning "Git user.name or user.email not configured"
        print_status "Please configure git with: git config user.name 'Your Name'"
        print_status "                         git config user.email 'your@email.com'"
    fi

    # Check Python installation
    print_status "Checking Python installation..."
    if ! command_exists python3; then
        print_error "Python 3 is required but not installed"
        print_status "Please install Python 3 and try again"
        exit 1
    fi
    # Safely get Python version
    PYTHON_VERSION=$(python3 --version 2>&1) || {
        print_error "Failed to get Python version"
        exit 1
    }
    print_success "Python 3 found: $PYTHON_VERSION"

    # Check pip installation
    print_status "Checking pip installation..."
    if ! command_exists pip3; then
        print_error "pip3 is required but not installed"
        print_status "Please install pip3 and try again"
        exit 1
    fi
    # Safely get pip version
    PIP_VERSION=$(pip3 --version 2>&1) || {
        print_error "Failed to get pip version"
        exit 1
    }
    print_success "pip3 found: $PIP_VERSION"

    # Install pre-commit
    print_status "Installing pre-commit..."
    if command_exists pre-commit; then
        # Safely get pre-commit version
        PRECOMMIT_VERSION=$(pre-commit --version 2>&1) || {
            print_warning "pre-commit is installed but version check failed"
            PRECOMMIT_VERSION="unknown version"
        }
        print_warning "pre-commit is already installed: $PRECOMMIT_VERSION"
    else
        print_status "Installing pre-commit via pip3..."
        if pip3 install pre-commit; then
            print_success "pre-commit installed successfully"
        else
            print_error "Failed to install pre-commit"
            print_status "Try running with sudo or check your pip3 permissions"
            exit 1
        fi
    fi

    # Install pre-commit hooks
    print_status "Installing pre-commit hooks..."
    if pre-commit install; then
        print_success "Pre-commit hooks installed for commit stage"
    else
        print_error "Failed to install pre-commit hooks"
        exit 1
    fi

    # Install commit-msg hooks
    print_status "Installing commit-msg hooks..."
    if pre-commit install --hook-type commit-msg; then
        print_success "Commit message validation hooks installed"
    else
        print_error "Failed to install commit-msg hooks"
        exit 1
    fi

    # Install pre-push hooks (optional)
    print_status "Installing pre-push hooks..."
    if pre-commit install --hook-type pre-push; then
        print_success "Pre-push hooks installed"
    else
        print_warning "Failed to install pre-push hooks (optional)"
        print_status "Continuing without pre-push hooks..."
    fi

    # Run hooks on all files to test installation
    print_status "Testing pre-commit hooks on all files..."
    if pre-commit run --all-files; then
        print_success "All pre-commit hooks passed!"
    else
        print_warning "Some pre-commit hooks failed. This is normal for the first run."
        print_status "The hooks will automatically fix issues where possible."
    fi

    echo
    print_success "Pre-commit setup completed successfully!"
    echo
    print_status "Next steps:"
    echo "  1. Commit any changes made by the hooks"
    echo "  2. Your commits will now be automatically validated"
    echo "  3. Use conventional commit format: feat:, fix:, docs:, etc."
    echo
    print_status "Example conventional commit messages:"
    echo "  feat: add new checkout customization feature"
    echo "  fix: resolve mobile menu alignment issue"
    echo "  docs: update installation instructions"
    echo "  style: format CSS according to standards"
    echo "  refactor: optimize thank you page performance"
    echo
    print_status "To bypass hooks (not recommended): git commit --no-verify"
    print_status "To run hooks manually: pre-commit run --all-files"
}

# Run main function
main "$@"
