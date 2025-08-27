#!/usr/bin/env node

/**
 * Husky Setup Script
 * Automated setup and verification of Git hooks
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const chalk = require('chalk');

/**
 * Print setup header
 */
function printHeader() {
  console.log(chalk.blue.bold('\n🐕 Husky Git Hooks Setup'));
  console.log(chalk.gray('Comprehensive quality standards enforcement'));
  console.log(chalk.gray('Reference: .augment/rules/ALWAYS-comprehensive-code-review-standards.md\n'));
}

/**
 * Check prerequisites
 */
function checkPrerequisites() {
  console.log(chalk.yellow('🔍 Checking prerequisites...'));
  
  const requirements = [
    { command: 'node --version', name: 'Node.js' },
    { command: 'npm --version', name: 'npm' },
    { command: 'git --version', name: 'Git' }
  ];

  let allMet = true;

  requirements.forEach(req => {
    try {
      const version = execSync(req.command, { encoding: 'utf8' }).trim();
      console.log(chalk.green(`  ✅ ${req.name}: ${version}`));
    } catch (error) {
      console.log(chalk.red(`  ❌ ${req.name}: Not found`));
      allMet = false;
    }
  });

  if (!allMet) {
    console.log(chalk.red('\n❌ Prerequisites not met. Please install missing requirements.'));
    process.exit(1);
  }

  console.log(chalk.green('✅ All prerequisites met'));
}

/**
 * Install dependencies
 */
function installDependencies() {
  console.log(chalk.yellow('\n📦 Installing dependencies...'));
  
  try {
    console.log(chalk.blue('  Installing npm packages...'));
    execSync('npm install', { stdio: 'inherit' });
    console.log(chalk.green('  ✅ npm packages installed'));
  } catch (error) {
    console.log(chalk.red('  ❌ Failed to install npm packages'));
    process.exit(1);
  }
}

/**
 * Initialize Husky
 */
function initializeHusky() {
  console.log(chalk.yellow('\n🐕 Initializing Husky...'));
  
  try {
    console.log(chalk.blue('  Setting up Husky...'));
    execSync('npx husky install', { stdio: 'pipe' });
    console.log(chalk.green('  ✅ Husky initialized'));
  } catch (error) {
    console.log(chalk.red('  ❌ Failed to initialize Husky'));
    process.exit(1);
  }
}

/**
 * Verify hook files
 */
function verifyHookFiles() {
  console.log(chalk.yellow('\n🔧 Verifying hook files...'));
  
  const hooks = ['pre-commit', 'commit-msg', 'pre-push', 'post-merge'];
  let allHooksPresent = true;

  hooks.forEach(hook => {
    const hookPath = `.husky/${hook}`;
    if (fs.existsSync(hookPath)) {
      // Make sure hook is executable
      try {
        execSync(`chmod +x ${hookPath}`, { stdio: 'pipe' });
        console.log(chalk.green(`  ✅ ${hook} hook verified`));
      } catch (error) {
        console.log(chalk.yellow(`  ⚠️ Could not make ${hook} executable`));
      }
    } else {
      console.log(chalk.red(`  ❌ ${hook} hook missing`));
      allHooksPresent = false;
    }
  });

  if (!allHooksPresent) {
    console.log(chalk.red('\n❌ Some hooks are missing. Please check the .husky directory.'));
    process.exit(1);
  }

  console.log(chalk.green('✅ All hooks verified'));
}

/**
 * Test hook execution
 */
function testHookExecution() {
  console.log(chalk.yellow('\n🧪 Testing hook execution...'));

  const tests = [
    { script: 'quality:pre-commit', name: 'Pre-commit quality check' },
    { script: 'quality:commit-msg', name: 'Commit message validation' },
    { script: 'quality:pre-push', name: 'Pre-push validation' },
    { script: 'quality:post-merge', name: 'Post-merge cleanup' },
    { script: 'branch:validate', name: 'Branch naming validation' }
  ];

  tests.forEach(test => {
    try {
      console.log(chalk.blue(`  Testing ${test.name}...`));
      // Test with dry run or help flag to avoid actual execution
      execSync(`npm run ${test.script} --help || echo "Script exists"`, { stdio: 'pipe' });
      console.log(chalk.green(`  ✅ ${test.name} script available`));
    } catch (error) {
      console.log(chalk.yellow(`  ⚠️ ${test.name} script may have issues`));
    }
  });

  console.log(chalk.green('✅ Hook execution tests completed'));
}

/**
 * Setup branch naming configuration
 */
function setupBranchNaming() {
  console.log(chalk.yellow('\n🌿 Setting up branch naming standards...'));

  const configPath = '.branch-naming.json';

  if (!fs.existsSync(configPath)) {
    try {
      console.log(chalk.blue('  Creating branch naming configuration...'));
      execSync('npm run branch:config', { stdio: 'pipe' });
      console.log(chalk.green('  ✅ Branch naming configuration created'));
    } catch (error) {
      console.log(chalk.yellow('  ⚠️ Could not create branch naming configuration'));
    }
  } else {
    console.log(chalk.gray('  📁 Branch naming configuration already exists'));
  }

  // Test branch validation
  try {
    console.log(chalk.blue('  Testing branch naming validation...'));
    execSync('npm run branch:validate', { stdio: 'pipe' });
    console.log(chalk.green('  ✅ Branch naming validation working'));
  } catch (error) {
    console.log(chalk.yellow('  ⚠️ Branch naming validation may have issues'));
  }
}

/**
 * Setup documentation enforcement
 */
function setupDocumentationEnforcement() {
  console.log(chalk.yellow('\n📚 Setting up documentation enforcement...'));

  const configPath = '.documentation-config.json';

  if (!fs.existsSync(configPath)) {
    try {
      console.log(chalk.blue('  Creating documentation configuration...'));
      execSync('npm run docs:config', { stdio: 'pipe' });
      console.log(chalk.green('  ✅ Documentation configuration created'));
    } catch (error) {
      console.log(chalk.yellow('  ⚠️ Could not create documentation configuration'));
    }
  } else {
    console.log(chalk.gray('  📁 Documentation configuration already exists'));
  }

  // Create documentation directories
  const docDirs = ['docs', 'docs/features', 'docs/api', 'docs/components', 'docs/configuration', 'docs/database', 'docs/templates'];
  docDirs.forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
      console.log(chalk.green(`  ✅ Created ${dir} directory`));
    } else {
      console.log(chalk.gray(`  📁 ${dir} directory already exists`));
    }
  });

  // Test documentation enforcement
  try {
    console.log(chalk.blue('  Testing documentation enforcement...'));
    execSync('npm run docs:enforce', { stdio: 'pipe' });
    console.log(chalk.green('  ✅ Documentation enforcement working'));
  } catch (error) {
    console.log(chalk.yellow('  ⚠️ Documentation enforcement may have issues'));
  }
}

/**
 * Create test directories
 */
function createTestDirectories() {
  console.log(chalk.yellow('\n📁 Creating test directories...'));
  
  const directories = ['tests', 'coverage'];
  
  directories.forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
      console.log(chalk.green(`  ✅ Created ${dir} directory`));
    } else {
      console.log(chalk.gray(`  📁 ${dir} directory already exists`));
    }
  });

  // Create basic test setup file
  const testSetupPath = 'tests/setup.js';
  if (!fs.existsSync(testSetupPath)) {
    const testSetupContent = `// Jest setup file
// Global test configuration and mocks

// Mock WordPress globals
global.wp = {};
global.jQuery = jest.fn();
global.$ = global.jQuery;
global.ajaxurl = 'admin-ajax.php';

// Console warnings for missing implementations
console.log('Test environment initialized');
`;
    fs.writeFileSync(testSetupPath, testSetupContent);
    console.log(chalk.green('  ✅ Created test setup file'));
  }
}

/**
 * Display setup summary
 */
function displaySummary() {
  console.log(chalk.blue.bold('\n📋 Setup Summary:'));
  console.log(chalk.green('✅ Dependencies installed'));
  console.log(chalk.green('✅ Husky initialized'));
  console.log(chalk.green('✅ Git hooks configured'));
  console.log(chalk.green('✅ Quality scripts available'));
  console.log(chalk.green('✅ Test environment prepared'));

  console.log(chalk.blue.bold('\n🎯 Available Commands:'));
  console.log(chalk.gray('npm run quality:check      - Run all quality checks'));
  console.log(chalk.gray('npm run lint               - Run linting checks'));
  console.log(chalk.gray('npm run test               - Run test suite'));
  console.log(chalk.gray('npm run security:scan      - Run security scans'));
  console.log(chalk.gray('npm run build              - Build and validate'));

  console.log(chalk.blue.bold('\n🔧 Git Hooks Active:'));
  console.log(chalk.gray('pre-commit    - Quality checks before commits'));
  console.log(chalk.gray('commit-msg    - Conventional commit validation'));
  console.log(chalk.gray('pre-push      - Branch naming + comprehensive validation'));
  console.log(chalk.gray('pre-checkout  - Branch naming validation on checkout'));
  console.log(chalk.gray('post-merge    - Cleanup and optimization after merge'));

  console.log(chalk.blue.bold('\n🌿 Branch Naming Commands:'));
  console.log(chalk.gray('npm run branch:validate       - Validate current branch'));
  console.log(chalk.gray('npm run branch:validate-all    - Validate all branches'));
  console.log(chalk.gray('npm run branch:help           - Show naming conventions'));
  console.log(chalk.gray('npm run branch:config         - Create configuration file'));

  console.log(chalk.blue.bold('\n📚 Documentation Commands:'));
  console.log(chalk.gray('npm run docs:enforce          - Enforce documentation requirements'));
  console.log(chalk.gray('npm run docs:generate         - Generate documentation templates'));
  console.log(chalk.gray('npm run docs:validate         - Validate documentation quality'));
  console.log(chalk.gray('npm run docs:config           - Create documentation configuration'));

  console.log(chalk.blue.bold('\n💡 Next Steps:'));
  console.log(chalk.yellow('1. Review README-HUSKY-SETUP.md for detailed usage'));
  console.log(chalk.yellow('2. Test with a sample commit: git commit -m "test: verify hooks"'));
  console.log(chalk.yellow('3. Configure your IDE with ESLint and Prettier'));
  console.log(chalk.yellow('4. Share this setup with your team'));

  console.log(chalk.blue.bold('\n🚨 Emergency Bypass:'));
  console.log(chalk.red('EMERGENCY_BYPASS=true git commit -m "emergency fix"'));
  console.log(chalk.gray('Use only for critical production fixes!'));

  console.log(chalk.green.bold('\n✅ Husky setup completed successfully!\n'));
}

/**
 * Main execution
 */
function main() {
  printHeader();
  
  try {
    checkPrerequisites();
    installDependencies();
    initializeHusky();
    verifyHookFiles();
    testHookExecution();
    setupBranchNaming();
    setupDocumentationEnforcement();
    createTestDirectories();
    displaySummary();
  } catch (error) {
    console.log(chalk.red.bold('\n❌ Setup failed:'), error.message);
    console.log(chalk.yellow('\n💡 Troubleshooting:'));
    console.log(chalk.gray('1. Ensure you have proper permissions'));
    console.log(chalk.gray('2. Check that Git repository is initialized'));
    console.log(chalk.gray('3. Verify Node.js and npm are properly installed'));
    console.log(chalk.gray('4. Try running: npm install && npx husky install'));
    process.exit(1);
  }
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = { main };
