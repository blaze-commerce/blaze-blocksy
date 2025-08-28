#!/bin/bash

# WordPress/WooCommerce Project .gitignore Cleanup Script
# This script helps identify and remove files that should be ignored from version control

set -e

echo "🔍 WordPress/WooCommerce .gitignore Cleanup Analysis"
echo "=================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to check if file exists and is tracked
check_tracked_file() {
    local file="$1"
    if git ls-files --error-unmatch "$file" >/dev/null 2>&1; then
        return 0  # File is tracked
    else
        return 1  # File is not tracked
    fi
}

# Function to safely remove files from git tracking
remove_from_tracking() {
    local pattern="$1"
    local description="$2"
    
    echo -e "${BLUE}Checking for: $description${NC}"
    
    # Find tracked files matching the pattern
    local files=$(git ls-files | grep -E "$pattern" 2>/dev/null || true)
    
    if [ -n "$files" ]; then
        echo -e "${YELLOW}Found tracked files that should be ignored:${NC}"
        echo "$files" | sed 's/^/  - /'
        echo ""
        
        read -p "Remove these files from git tracking? (y/N): " -n 1 -r
        echo ""
        
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            echo "$files" | xargs git rm --cached
            echo -e "${GREEN}✅ Files removed from tracking${NC}"
        else
            echo -e "${YELLOW}⏭️  Skipped${NC}"
        fi
    else
        echo -e "${GREEN}✅ No problematic files found${NC}"
    fi
    echo ""
}

# Function to check for large files
check_large_files() {
    echo -e "${BLUE}Checking for large files (>1MB)...${NC}"
    
    local large_files=$(git ls-files | xargs ls -la 2>/dev/null | awk '$5 > 1048576 {print $9 " (" $5 " bytes)"}' || true)
    
    if [ -n "$large_files" ]; then
        echo -e "${YELLOW}Found large files in repository:${NC}"
        echo "$large_files" | sed 's/^/  - /'
        echo ""
        echo -e "${YELLOW}Consider using Git LFS for these files or adding them to .gitignore${NC}"
    else
        echo -e "${GREEN}✅ No large files found${NC}"
    fi
    echo ""
}

echo "🧹 Starting cleanup analysis..."
echo ""

# 1. Check for test artifacts and coverage reports
remove_from_tracking "^coverage/" "Test coverage reports"
remove_from_tracking "^test-results/" "Test result files"
remove_from_tracking "\.phpunit\.result\.cache$" "PHPUnit cache files"

# 2. Check for dependency directories (should not be tracked)
remove_from_tracking "^node_modules/" "Node.js dependencies"
remove_from_tracking "^vendor/" "Composer dependencies"

# 3. Check for build artifacts
remove_from_tracking "\.min\.(css|js)$" "Minified CSS/JS files"
remove_from_tracking "\.map$" "Source map files"
remove_from_tracking "^(dist|build)/" "Build directories"

# 4. Check for log files
remove_from_tracking "\.log$" "Log files"
remove_from_tracking "debug\.log$" "Debug log files"
remove_from_tracking "error_log$" "Error log files"

# 5. Check for cache files
remove_from_tracking "\.cache/" "Cache directories"
remove_from_tracking "\.eslintcache$" "ESLint cache files"
remove_from_tracking "\.stylelintcache$" "Stylelint cache files"

# 6. Check for IDE files
remove_from_tracking "^\.vscode/" "VS Code settings"
remove_from_tracking "^\.idea/" "JetBrains IDE files"
remove_from_tracking "\.swp$" "Vim swap files"

# 7. Check for OS files
remove_from_tracking "\.DS_Store$" "macOS system files"
remove_from_tracking "Thumbs\.db$" "Windows thumbnail files"

# 8. Check for environment files
remove_from_tracking "^\.env" "Environment files"

# 9. Check for backup files
remove_from_tracking "\.(bak|backup|old|orig)$" "Backup files"

# 10. Check for large files
check_large_files

echo "🔍 Additional Manual Checks Needed:"
echo "=================================="
echo ""

echo -e "${BLUE}1. WordPress Core Files:${NC}"
if git ls-files | grep -E "^wp-(admin|includes)/" >/dev/null 2>&1; then
    echo -e "${YELLOW}   ⚠️  WordPress core files detected in repository${NC}"
    echo "   These should typically not be tracked in a theme repository"
else
    echo -e "${GREEN}   ✅ No WordPress core files found${NC}"
fi
echo ""

echo -e "${BLUE}2. Configuration Files:${NC}"
if git ls-files | grep -E "wp-config.*\.php$" >/dev/null 2>&1; then
    echo -e "${YELLOW}   ⚠️  WordPress configuration files detected${NC}"
    echo "   Review these files for sensitive information"
else
    echo -e "${GREEN}   ✅ No WordPress config files found${NC}"
fi
echo ""

echo -e "${BLUE}3. Upload Directories:${NC}"
if git ls-files | grep -E "^(wp-content/)?uploads/" >/dev/null 2>&1; then
    echo -e "${YELLOW}   ⚠️  Upload directory files detected${NC}"
    echo "   Consider if these should be tracked or ignored"
else
    echo -e "${GREEN}   ✅ No upload directory files found${NC}"
fi
echo ""

echo "📋 Recommendations:"
echo "==================="
echo ""
echo "1. 🔄 Run 'git status' to see current changes"
echo "2. 📝 Review the updated .gitignore file"
echo "3. 🧪 Test your build process to ensure nothing important was removed"
echo "4. 📦 Consider using Git LFS for large binary assets"
echo "5. 🔒 Ensure no sensitive data (passwords, keys) is tracked"
echo "6. 📚 Update your team documentation about the new .gitignore rules"
echo ""

echo "✨ Cleanup analysis complete!"
echo ""
echo "Next steps:"
echo "1. Review any changes made above"
echo "2. Run: git add .gitignore"
echo "3. Run: git commit -m 'Update .gitignore for WordPress/WooCommerce best practices'"
echo "4. Run: git push"
echo ""
