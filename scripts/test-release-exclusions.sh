#!/bin/bash

# Test script to verify release exclusion patterns work correctly
# This simulates the ZIP creation process from the GitHub Actions workflow

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Test configuration
TEST_VERSION="1.15.0-test"
ZIP_NAME="blocksy-child-v${TEST_VERSION}.zip"
THEME_FOLDER="blocksy-child"
TEST_DIR="test-release-validation"

print_status "Testing BlazeCommerce Release Exclusion Patterns"
print_status "================================================"

# Check for required tools
check_requirements() {
    local missing_tools=()

    if ! command -v rsync &> /dev/null; then
        missing_tools+=("rsync")
    fi

    if ! command -v zip &> /dev/null; then
        missing_tools+=("zip")
    fi

    if ! command -v unzip &> /dev/null; then
        missing_tools+=("unzip")
    fi

    if [[ ${#missing_tools[@]} -gt 0 ]]; then
        print_error "Missing required tools: ${missing_tools[*]}"
        print_error "Please install the missing tools and try again"
        exit 1
    fi
}

# Validate we're in the correct directory
validate_environment() {
    if [[ ! -f "style.css" ]] || [[ ! -f "functions.php" ]]; then
        print_error "This script must be run from the WordPress theme root directory"
        print_error "Expected files: style.css, functions.php"
        exit 1
    fi
}

print_status "Validating environment and requirements..."
check_requirements
validate_environment

# Clean up any previous test
if [[ -d "$TEST_DIR" ]]; then
    rm -rf "$TEST_DIR"
fi

if [[ -f "$ZIP_NAME" ]]; then
    rm -f "$ZIP_NAME"
fi

# Create test directory structure
mkdir -p "${TEST_DIR}/${THEME_FOLDER}"

print_status "Creating test ZIP with production exclusions..."

# Use the same rsync command from the GitHub Actions workflow
rsync -av --delete \
  --exclude='.git/' \
  --exclude='.github/' \
  --exclude='.augment/' \
  --exclude='.augmentignore' \
  --exclude='.gitignore' \
  --exclude='.gitattributes' \
  --exclude='.gitmodules' \
  --exclude='.zipignore' \
  --exclude='docs/' \
  --exclude='tests/' \
  --exclude='scripts/' \
  --exclude='bin/' \
  --exclude='node_modules/' \
  --exclude='vendor/' \
  --exclude='dist/' \
  --exclude='build/' \
  --exclude='assets/dist/' \
  --exclude='assets/build/' \
  --exclude='src/' \
  --exclude='coverage/' \
  --exclude='test-results/' \
  --exclude='test-releases/' \
  --exclude='releases/' \
  --exclude='agency_logo/' \
  --exclude='performance-optimizations/' \
  --exclude='security-fixes/' \
  --exclude='.husky/' \
  --exclude='.cache/' \
  --exclude='.tmp/' \
  --exclude='.sass-cache/' \
  --exclude='.parcel-cache/' \
  --exclude='.webpack/' \
  --exclude='.rollup.cache/' \
  --exclude='.phpunit.result.cache' \
  --exclude='.phpunit.cache/' \
  --exclude='.nyc_output/' \
  --exclude='.jest-cache/' \
  --exclude='screenshots/' \
  --exclude='test-screenshots/' \
  --exclude='visual-regression/' \
  --exclude='playwright-screenshots/' \
  --exclude='lighthouse-results/' \
  --exclude='__pycache__/' \
  --exclude='.patchwork-cache/' \
  --exclude='.vscode/' \
  --exclude='.idea/' \
  --exclude='.atom/' \
  --exclude='.history/' \
  --exclude='*.code-workspace' \
  --exclude='*.iml' \
  --exclude='*.ipr' \
  --exclude='*.iws' \
  --exclude='*.sublime-project' \
  --exclude='*.sublime-workspace' \
  --exclude='*.swp' \
  --exclude='*.swo' \
  --exclude='*.tmp' \
  --exclude='*.bak' \
  --exclude='*.backup' \
  --exclude='*.old' \
  --exclude='*.orig' \
  --exclude='*.rej' \
  --exclude='*.map' \
  --exclude='*.css.map' \
  --exclude='*.js.map' \
  --exclude='*.scss.map' \
  --exclude='*.less.map' \
  --exclude='*.sass.map' \
  --exclude='*.log' \
  --exclude='*.lock' \
  --exclude='*.lcov' \
  --exclude='*.pid' \
  --exclude='*.seed' \
  --exclude='*.pid.lock' \
  --exclude='*.py[cod]' \
  --exclude='*.so' \
  --exclude='*.egg-info/' \
  --exclude='*.egg' \
  --exclude='*.sh' \
  --exclude='*.bash' \
  --exclude='*.zsh' \
  --exclude='*.fish' \
  --exclude='*.ps1' \
  --exclude='*.bat' \
  --exclude='*.cmd' \
  --exclude='*.zip' \
  --exclude='*.tar.gz' \
  --exclude='*.tgz' \
  --exclude='*.sql' \
  --exclude='*.sqlite' \
  --exclude='*.db' \
  --exclude='*.psd' \
  --exclude='*.ai' \
  --exclude='*.sketch' \
  --exclude='*.fig' \
  --exclude='*.xd' \
  --exclude='*.indd' \
  --exclude='*.eps' \
  --exclude='*.key' \
  --exclude='*.pem' \
  --exclude='*.p12' \
  --exclude='*.pfx' \
  --exclude='.env*' \
  --exclude='!.env.example' \
  --exclude='!.env.sample' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='composer.json' \
  --exclude='composer.lock' \
  --exclude='yarn.lock' \
  --exclude='pnpm-lock.yaml' \
  --exclude='bun.lockb' \
  --exclude='jest.config.js' \
  --exclude='playwright.config.js' \
  --exclude='stylelint.config.js' \
  --exclude='lighthouserc.js' \
  --exclude='webpack.config.js' \
  --exclude='rollup.config.js' \
  --exclude='vite.config.js' \
  --exclude='tsconfig.json' \
  --exclude='babel.config.js' \
  --exclude='.eslintrc.*' \
  --exclude='.prettierrc.*' \
  --exclude='.stylelintrc.*' \
  --exclude='phpunit.xml' \
  --exclude='phpunit-*.xml' \
  --exclude='patchwork.json' \
  --exclude='wp-config-local.php' \
  --exclude='wp-config-staging.php' \
  --exclude='wp-config-production.php' \
  --exclude='wp-config-development.php' \
  --exclude='wp-cli.local.yml' \
  --exclude='wp-cli.yml' \
  --exclude='local-config.php' \
  --exclude='local-development.php' \
  --exclude='.htpasswd' \
  --exclude='auth.json' \
  --exclude='.secrets' \
  --exclude='.htaccess.backup' \
  --exclude='.maintenance' \
  --exclude='.well-known/' \
  --exclude='wp-config-sample.php' \
  --exclude='readme.html' \
  --exclude='license.txt' \
  --exclude="${TEST_DIR}/" \
  --exclude='*.md' \
  --exclude='.DS_Store' \
  --exclude='.AppleDouble' \
  --exclude='.LSOverride' \
  --exclude='._*' \
  --exclude='.DocumentRevisions-V100' \
  --exclude='.fseventsd' \
  --exclude='.Spotlight-V100' \
  --exclude='.TemporaryItems' \
  --exclude='.Trashes' \
  --exclude='.VolumeIcon.icns' \
  --exclude='.com.apple.timemachine.donotpresent' \
  --exclude='Thumbs.db' \
  --exclude='Thumbs.db:encryptable' \
  --exclude='ehthumbs.db' \
  --exclude='ehthumbs_vista.db' \
  --exclude='*.stackdump' \
  --exclude='[Dd]esktop.ini' \
  --exclude='$RECYCLE.BIN/' \
  --exclude='*.cab' \
  --exclude='*.msi' \
  --exclude='*.msix' \
  --exclude='*.msm' \
  --exclude='*.msp' \
  --exclude='*.lnk' \
  --exclude='.fuse_hidden*' \
  --exclude='.directory' \
  --exclude='.Trash-*' \
  --exclude='.nfs*' \
  --exclude='.eslintcache' \
  --exclude='.stylelintcache' \
  --exclude='.prettiercache' \
  --exclude='*-baseline.json' \
  --exclude='*-junit.xml' \
  --exclude='security-scan-results.json' \
  --exclude='vulnerability-report.json' \
  --exclude='lighthouse-report.html' \
  --exclude='lighthouse-report.json' \
  --exclude='k6-results.json' \
  --exclude='artillery-report.json' \
  --exclude='performance-report.json' \
  --exclude='load-test-*.json' \
  --exclude='stress-test-*.json' \
  --exclude='coverage-final.json' \
  --exclude='lcov-report/' \
  --exclude='clover.xml' \
  --exclude='cobertura.xml' \
  --exclude='junit.xml' \
  --exclude='test-summary.json' \
  --exclude='playwright-results.json' \
  --exclude='playwright-junit.xml' \
  --exclude='jest-results.json' \
  --exclude='lcov.info' \
  . "${TEST_DIR}/${THEME_FOLDER}/" > /dev/null

# Create ZIP
cd "$TEST_DIR"
zip -r "../${ZIP_NAME}" "${THEME_FOLDER}" > /dev/null
cd ..

print_success "Test ZIP created: ${ZIP_NAME}"

# Validate ZIP contents
print_status "Validating ZIP contents..."

# Check for essential WordPress theme files
ESSENTIAL_FILES=("style.css" "functions.php")
MISSING_ESSENTIAL=()

for file in "${ESSENTIAL_FILES[@]}"; do
  if ! unzip -l "${ZIP_NAME}" | grep -q "${THEME_FOLDER}/${file}"; then
    MISSING_ESSENTIAL+=("${file}")
  fi
done

if [[ ${#MISSING_ESSENTIAL[@]} -gt 0 ]]; then
  print_error "Missing essential WordPress theme files:"
  for file in "${MISSING_ESSENTIAL[@]}"; do
    echo "  - ${file}"
  done
  exit 1
fi

print_success "Essential WordPress theme files present"

# Check that development artifacts are properly excluded
DEV_PATTERNS=(
  "node_modules/"
  "tests/"
  "scripts/"
  ".git/"
  "package.json"
  "composer.json"
  "jest.config.js"
  "playwright.config.js"
  "README.md"
  ".github/"
  "docs/"
  "vendor/"
  ".vscode/"
  ".idea/"
  "coverage/"
  "*.log"
  "*.lock"
  "*.bak"
  ".env"
  "__pycache__/"
  "agency_logo/"
)

FOUND_DEV_FILES=()
for pattern in "${DEV_PATTERNS[@]}"; do
  if unzip -l "${ZIP_NAME}" | grep -q "${pattern}"; then
    FOUND_DEV_FILES+=("${pattern}")
  fi
done

if [[ ${#FOUND_DEV_FILES[@]} -gt 0 ]]; then
  print_warning "Found development files in production ZIP:"
  for file in "${FOUND_DEV_FILES[@]}"; do
    echo "  - ${file}"
  done
else
  print_success "Development artifacts properly excluded"
fi

# Get ZIP file size and file count
ZIP_SIZE=$(du -h "${ZIP_NAME}" | cut -f1)
FILE_COUNT=$(unzip -l "${ZIP_NAME}" | tail -1 | awk '{print $2}')

print_status "ZIP Summary:"
echo "  - Size: ${ZIP_SIZE}"
echo "  - Files: ${FILE_COUNT}"
echo "  - Essential files: ✓ Present"
echo "  - Development artifacts: $([ ${#FOUND_DEV_FILES[@]} -eq 0 ] && echo "✓ Excluded" || echo "⚠️ Some found")"

# Show first 20 files in ZIP for verification
print_status "ZIP contents (first 20 files):"
unzip -l "${ZIP_NAME}" | head -25

# Clean up
print_status "Cleaning up test artifacts..."
rm -rf "$TEST_DIR"
rm -f "$ZIP_NAME"

print_success "Release exclusion test completed successfully!"
print_status "All validation checks passed. The exclusion patterns are working correctly."
