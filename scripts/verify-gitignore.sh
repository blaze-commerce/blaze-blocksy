#!/bin/bash

# WordPress/WooCommerce .gitignore Verification Script
# This script verifies that the .gitignore file is working correctly

set -e

echo "🔍 WordPress/WooCommerce .gitignore Verification"
echo "=============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to test if a pattern is ignored
test_ignore_pattern() {
    local test_file="$1"
    local description="$2"
    
    # Create a temporary test file
    mkdir -p "$(dirname "$test_file")"
    touch "$test_file"
    
    # Check if git ignores it
    if git check-ignore "$test_file" >/dev/null 2>&1; then
        echo -e "${GREEN}✅ $description${NC}"
        rm -f "$test_file"
        return 0
    else
        echo -e "${RED}❌ $description${NC}"
        echo -e "${YELLOW}   File: $test_file${NC}"
        rm -f "$test_file"
        return 1
    fi
}

# Function to test if a pattern is NOT ignored (should be tracked)
test_not_ignored() {
    local test_file="$1"
    local description="$2"
    
    # Create a temporary test file
    mkdir -p "$(dirname "$test_file")"
    touch "$test_file"
    
    # Check if git does NOT ignore it
    if ! git check-ignore "$test_file" >/dev/null 2>&1; then
        echo -e "${GREEN}✅ $description${NC}"
        rm -f "$test_file"
        return 0
    else
        echo -e "${RED}❌ $description${NC}"
        echo -e "${YELLOW}   File: $test_file (should NOT be ignored)${NC}"
        rm -f "$test_file"
        return 1
    fi
}

echo "🧪 Testing .gitignore patterns..."
echo ""

# Test dependency directories
echo -e "${BLUE}Testing Dependency Directories:${NC}"
test_ignore_pattern "node_modules/test.js" "Node.js dependencies ignored"
test_ignore_pattern "vendor/test.php" "Composer dependencies ignored"
test_ignore_pattern ".npm/test" "NPM cache ignored"
test_ignore_pattern ".yarn/cache/test" "Yarn cache ignored"
echo ""

# Test build artifacts
echo -e "${BLUE}Testing Build Artifacts:${NC}"
test_ignore_pattern "dist/app.js" "Build directory ignored"
test_ignore_pattern "build/styles.css" "Build directory ignored"
test_ignore_pattern "assets/js/script.min.js" "Minified JS ignored"
test_ignore_pattern "assets/css/style.min.css" "Minified CSS ignored"
test_ignore_pattern "assets/js/app.js.map" "Source maps ignored"
echo ""

# Test testing artifacts
echo -e "${BLUE}Testing Testing Artifacts:${NC}"
test_ignore_pattern "coverage/index.html" "Coverage reports ignored"
test_ignore_pattern "test-results/report.json" "Test results ignored"
test_ignore_pattern ".phpunit.result.cache" "PHPUnit cache ignored"
test_ignore_pattern ".jest-cache/test" "Jest cache ignored"
echo ""

# Test cache files
echo -e "${BLUE}Testing Cache Files:${NC}"
test_ignore_pattern ".eslintcache" "ESLint cache ignored"
test_ignore_pattern ".stylelintcache" "Stylelint cache ignored"
test_ignore_pattern ".cache/test" "General cache ignored"
test_ignore_pattern ".parcel-cache/test" "Parcel cache ignored"
echo ""

# Test IDE files
echo -e "${BLUE}Testing IDE Files:${NC}"
test_ignore_pattern ".vscode/settings.json" "VS Code settings ignored"
test_ignore_pattern ".idea/workspace.xml" "JetBrains IDE files ignored"
test_ignore_pattern "test.swp" "Vim swap files ignored"
test_ignore_pattern "test.code-workspace" "VS Code workspace ignored"
echo ""

# Test OS files
echo -e "${BLUE}Testing OS Files:${NC}"
test_ignore_pattern ".DS_Store" "macOS system files ignored"
test_ignore_pattern "Thumbs.db" "Windows thumbnail files ignored"
test_ignore_pattern ".directory" "Linux directory files ignored"
echo ""

# Test environment files
echo -e "${BLUE}Testing Environment Files:${NC}"
test_ignore_pattern ".env" "Environment files ignored"
test_ignore_pattern ".env.local" "Local environment files ignored"
test_ignore_pattern ".env.production" "Production environment files ignored"
test_not_ignored ".env.example" "Example environment files tracked"
echo ""

# Test WordPress specific
echo -e "${BLUE}Testing WordPress Specific:${NC}"
test_ignore_pattern "wp-config-local.php" "Local WordPress config ignored"
test_ignore_pattern "wp-content/uploads/test.jpg" "WordPress uploads ignored"
test_ignore_pattern "error_log" "Error logs ignored"
test_ignore_pattern "debug.log" "Debug logs ignored"
echo ""

# Test backup files
echo -e "${BLUE}Testing Backup Files:${NC}"
test_ignore_pattern "test.bak" "Backup files ignored"
test_ignore_pattern "config.backup" "Backup files ignored"
test_ignore_pattern "old-file.old" "Old files ignored"
echo ""

# Test log files
echo -e "${BLUE}Testing Log Files:${NC}"
test_ignore_pattern "app.log" "Log files ignored"
test_ignore_pattern "npm-debug.log" "NPM debug logs ignored"
test_ignore_pattern "yarn-error.log" "Yarn error logs ignored"
echo ""

# Test that important files are NOT ignored
echo -e "${BLUE}Testing Important Files (Should NOT be ignored):${NC}"
test_not_ignored "functions.php" "Theme functions tracked"
test_not_ignored "style.css" "Theme stylesheet tracked"
test_not_ignored "assets/css/main.css" "Theme CSS tracked"
test_not_ignored "assets/js/main.js" "Theme JS tracked"
test_not_ignored "screenshot.png" "Theme screenshot tracked"
test_not_ignored "assets/images/logo.svg" "Theme images tracked"
test_not_ignored "composer.json" "Composer config tracked"
test_not_ignored "package.json" "Package config tracked"
echo ""

# Clean up any remaining test directories
rmdir node_modules 2>/dev/null || true
rmdir vendor 2>/dev/null || true
rmdir dist 2>/dev/null || true
rmdir build 2>/dev/null || true
rmdir coverage 2>/dev/null || true
rmdir test-results 2>/dev/null || true
rmdir .vscode 2>/dev/null || true
rmdir .idea 2>/dev/null || true
rmdir wp-content/uploads 2>/dev/null || true
rmdir wp-content 2>/dev/null || true
rmdir assets/js 2>/dev/null || true
rmdir assets/css 2>/dev/null || true
rmdir assets/images 2>/dev/null || true
rmdir assets 2>/dev/null || true

echo "📊 Verification Summary:"
echo "======================="
echo ""

# Check current git status for any issues
echo -e "${BLUE}Current Git Status:${NC}"
if git status --porcelain | grep -E "^\?\?" | grep -E "(node_modules|vendor|coverage|test-results|\.cache|\.log)" >/dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Some files that should be ignored are showing as untracked${NC}"
    echo "   Run: git status --porcelain | grep '^??'"
else
    echo -e "${GREEN}✅ No untracked files that should be ignored${NC}"
fi
echo ""

# Check for large files
echo -e "${BLUE}Large Files Check:${NC}"
large_files=$(git ls-files | xargs ls -la 2>/dev/null | awk '$5 > 1048576 {print $9 " (" int($5/1024/1024) "MB)"}' || true)
if [ -n "$large_files" ]; then
    echo -e "${YELLOW}⚠️  Large files found:${NC}"
    echo "$large_files" | sed 's/^/   /'
    echo -e "${YELLOW}   Consider using Git LFS or optimizing these files${NC}"
else
    echo -e "${GREEN}✅ No large files detected${NC}"
fi
echo ""

echo "🎉 .gitignore verification complete!"
echo ""
echo "📋 Next Steps:"
echo "1. Review any warnings above"
echo "2. Test your build process: npm run build"
echo "3. Test your test process: npm test"
echo "4. Commit the .gitignore changes"
echo ""
