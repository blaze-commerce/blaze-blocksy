#!/usr/bin/env node

/**
 * Pre-push Quality Check Script
 * Comprehensive validation before pushing to remote repository
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 */

const { execSync } = require('child_process');
const fs = require('fs');
const chalk = require('chalk');

// Import branch naming validator with error handling
let branchValidator;
try {
  branchValidator = require('./branch-naming-validator');
} catch (error) {
  console.warn(chalk.yellow('⚠️ Branch naming validator not available, using fallback validation'));
}

// Configuration
const CONFIG = {
  emergencyBypass: process.env.EMERGENCY_BYPASS === 'true',
  skipTests: process.env.SKIP_TESTS === 'true',
  skipIntegration: process.env.SKIP_INTEGRATION === 'true',
  maxCommitsToCheck: 10
};

// Quality check results
let qualityResults = {
  passed: 0,
  failed: 0,
  warnings: 0,
  errors: []
};

/**
 * Print header
 */
function printHeader() {
  console.log(chalk.blue.bold('\n🚀 Pre-Push Quality Check'));
  console.log(chalk.gray('Comprehensive validation before remote push'));
  console.log(chalk.gray('Reference: ALWAYS-comprehensive-code-review-standards.md\n'));
}

/**
 * Get commits to be pushed
 */
function getCommitsToPush() {
  try {
    // Get commits that will be pushed
    const output = execSync('git log --oneline @{u}..HEAD', { encoding: 'utf8' });
    const commits = output.trim().split('\n').filter(line => line.length > 0);
    return commits;
  } catch (error) {
    // If no upstream branch, get recent commits
    try {
      const output = execSync(`git log --oneline -${CONFIG.maxCommitsToCheck}`, { encoding: 'utf8' });
      const commits = output.trim().split('\n').filter(line => line.length > 0);
      return commits;
    } catch (fallbackError) {
      console.warn(chalk.yellow('⚠️ Could not determine commits to push'));
      return [];
    }
  }
}

/**
 * Validate all commits meet quality standards
 */
function validateCommitQuality(commits) {
  console.log(chalk.yellow(`📝 Validating ${commits.length} commits...`));
  
  let allCommitsValid = true;

  commits.forEach((commit, index) => {
    const [hash, ...messageParts] = commit.split(' ');
    const message = messageParts.join(' ');
    
    // Basic conventional commit validation
    const conventionalRegex = /^(\w+)(\(.+\))?(!)?: (.+)$/;
    if (!conventionalRegex.test(message)) {
      allCommitsValid = false;
      qualityResults.errors.push({
        type: 'COMMIT_FORMAT',
        priority: 2,
        commit: hash,
        message: `Commit ${hash} does not follow conventional format: "${message}"`
      });
    }
  });

  if (allCommitsValid) {
    qualityResults.passed++;
    console.log(chalk.green('✅ All commits follow quality standards'));
  } else {
    qualityResults.failed++;
    console.log(chalk.red('❌ Some commits do not meet quality standards'));
  }
}

/**
 * Run comprehensive test suite
 */
function runTestSuite() {
  if (CONFIG.skipTests) {
    console.log(chalk.yellow('⏭️ Skipping test suite (SKIP_TESTS=true)'));
    return;
  }

  console.log(chalk.yellow('🧪 Running comprehensive test suite...'));
  
  try {
    // Run PHP tests if available
    if (fs.existsSync('vendor/bin/phpunit')) {
      console.log(chalk.blue('  Running PHP tests...'));
      execSync('vendor/bin/phpunit', { stdio: 'pipe' });
      console.log(chalk.green('  ✅ PHP tests passed'));
    }

    // Run JavaScript tests if available
    if (fs.existsSync('package.json')) {
      const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
      if (packageJson.scripts && packageJson.scripts.test) {
        console.log(chalk.blue('  Running JavaScript tests...'));
        execSync('npm test', { stdio: 'pipe' });
        console.log(chalk.green('  ✅ JavaScript tests passed'));
      }
    }

    qualityResults.passed++;
    console.log(chalk.green('✅ Test suite completed successfully'));
  } catch (error) {
    qualityResults.failed++;
    qualityResults.errors.push({
      type: 'TESTING',
      priority: 1,
      message: 'Test suite failed - critical issues detected'
    });
    console.log(chalk.red('❌ Test suite failed'));
  }
}

/**
 * Check for merge conflicts
 */
function checkMergeConflicts() {
  console.log(chalk.yellow('🔀 Checking for merge conflicts...'));
  
  try {
    // Check if there are any unresolved merge conflicts
    const status = execSync('git status --porcelain', { encoding: 'utf8' });
    const conflictMarkers = status.split('\n').filter(line => 
      line.startsWith('UU ') || line.startsWith('AA ') || line.startsWith('DD ')
    );

    if (conflictMarkers.length > 0) {
      qualityResults.failed++;
      qualityResults.errors.push({
        type: 'MERGE_CONFLICT',
        priority: 1,
        message: `Unresolved merge conflicts detected in ${conflictMarkers.length} files`
      });
      console.log(chalk.red('❌ Unresolved merge conflicts detected'));
    } else {
      qualityResults.passed++;
      console.log(chalk.green('✅ No merge conflicts detected'));
    }
  } catch (error) {
    console.warn(chalk.yellow('⚠️ Could not check merge conflicts'));
  }
}

/**
 * Validate CI/CD pipeline compatibility
 */
function validateCIPipeline() {
  console.log(chalk.yellow('⚙️ Validating CI/CD pipeline compatibility...'));
  
  let ciConfigFound = false;
  const ciFiles = [
    '.github/workflows',
    '.gitlab-ci.yml',
    'Jenkinsfile',
    '.travis.yml',
    'circle.yml',
    '.circleci/config.yml'
  ];

  ciFiles.forEach(file => {
    if (fs.existsSync(file)) {
      ciConfigFound = true;
    }
  });

  if (ciConfigFound) {
    try {
      // Basic validation - ensure no syntax errors in common CI files
      if (fs.existsSync('.github/workflows')) {
        const workflowFiles = fs.readdirSync('.github/workflows')
          .filter(f => f.endsWith('.yml') || f.endsWith('.yaml'));
        
        workflowFiles.forEach(file => {
          const content = fs.readFileSync(`.github/workflows/${file}`, 'utf8');
          // Basic YAML syntax check (simplified)
          if (!content.includes('on:') || !content.includes('jobs:')) {
            throw new Error(`Invalid workflow file: ${file}`);
          }
        });
      }

      qualityResults.passed++;
      console.log(chalk.green('✅ CI/CD pipeline configuration valid'));
    } catch (error) {
      qualityResults.failed++;
      qualityResults.errors.push({
        type: 'CI_PIPELINE',
        priority: 2,
        message: `CI/CD pipeline configuration error: ${error.message}`
      });
      console.log(chalk.red('❌ CI/CD pipeline configuration issues detected'));
    }
  } else {
    qualityResults.warnings++;
    console.log(chalk.yellow('⚠️ No CI/CD pipeline configuration found'));
  }
}

/**
 * Check integration requirements
 */
function checkIntegrationRequirements() {
  if (CONFIG.skipIntegration) {
    console.log(chalk.yellow('⏭️ Skipping integration checks (SKIP_INTEGRATION=true)'));
    return;
  }

  console.log(chalk.yellow('🔗 Checking integration requirements...'));
  
  try {
    // Check if all dependencies are properly installed
    if (fs.existsSync('package.json')) {
      console.log(chalk.blue('  Checking Node.js dependencies...'));
      execSync('npm ls --depth=0', { stdio: 'pipe' });
      console.log(chalk.green('  ✅ Node.js dependencies satisfied'));
    }

    if (fs.existsSync('composer.json')) {
      console.log(chalk.blue('  Checking PHP dependencies...'));
      execSync('composer validate', { stdio: 'pipe' });
      console.log(chalk.green('  ✅ PHP dependencies valid'));
    }

    qualityResults.passed++;
    console.log(chalk.green('✅ Integration requirements met'));
  } catch (error) {
    qualityResults.failed++;
    qualityResults.errors.push({
      type: 'INTEGRATION',
      priority: 2,
      message: 'Integration requirements not met - dependency issues detected'
    });
    console.log(chalk.red('❌ Integration requirements not met'));
  }
}

/**
 * Validate branch protection requirements
 */
function validateBranchProtection() {
  console.log(chalk.yellow('🛡️ Validating branch protection requirements...'));
  
  try {
    const currentBranch = execSync('git branch --show-current', { encoding: 'utf8' }).trim();
    const protectedBranches = ['main', 'master', 'develop', 'production'];
    
    if (protectedBranches.includes(currentBranch)) {
      qualityResults.warnings++;
      console.log(chalk.yellow(`⚠️ Pushing to protected branch: ${currentBranch}`));
      console.log(chalk.gray('  Ensure you have proper authorization for this action'));
    } else {
      qualityResults.passed++;
      console.log(chalk.green(`✅ Pushing to feature branch: ${currentBranch}`));
    }
  } catch (error) {
    console.warn(chalk.yellow('⚠️ Could not determine current branch'));
  }
}

/**
 * Check for large files or sensitive data
 */
function checkLargeFilesAndSecrets() {
  console.log(chalk.yellow('📦 Checking for large files and sensitive data...'));
  
  try {
    // Check for large files (>10MB)
    const largeFiles = execSync('find . -type f -size +10M -not -path "./.git/*" -not -path "./node_modules/*" -not -path "./vendor/*"', 
      { encoding: 'utf8' }).trim();
    
    if (largeFiles) {
      qualityResults.warnings++;
      console.log(chalk.yellow('⚠️ Large files detected:'));
      largeFiles.split('\n').forEach(file => {
        console.log(chalk.gray(`  ${file}`));
      });
      console.log(chalk.gray('  Consider using Git LFS for large files'));
    }

    // Basic secret detection
    const secretPatterns = [
      'password\\s*=',
      'api[_-]?key\\s*=',
      'secret\\s*=',
      'token\\s*='
    ];

    let secretsFound = false;
    secretPatterns.forEach(pattern => {
      try {
        const result = execSync(`git grep -i "${pattern}" HEAD`, { encoding: 'utf8' });
        if (result.trim()) {
          secretsFound = true;
        }
      } catch (error) {
        // No matches found (expected)
      }
    });

    if (secretsFound) {
      qualityResults.failed++;
      qualityResults.errors.push({
        type: 'SECURITY',
        priority: 1,
        message: 'Potential secrets detected in repository'
      });
      console.log(chalk.red('❌ Potential secrets detected'));
    } else {
      qualityResults.passed++;
      console.log(chalk.green('✅ No large files or secrets detected'));
    }
  } catch (error) {
    console.warn(chalk.yellow('⚠️ Could not complete file and secret check'));
  }
}

/**
 * Print quality results summary
 */
function printResults() {
  console.log(chalk.blue.bold('\n📊 Pre-Push Quality Results:'));
  console.log(chalk.green(`✅ Passed: ${qualityResults.passed}`));
  console.log(chalk.red(`❌ Failed: ${qualityResults.failed}`));
  console.log(chalk.yellow(`⚠️ Warnings: ${qualityResults.warnings}`));

  if (qualityResults.errors.length > 0) {
    console.log(chalk.red.bold('\n🚨 Issues Found:'));
    qualityResults.errors.forEach((error, index) => {
      console.log(chalk.red(`${index + 1}. [P${error.priority}] ${error.type}: ${error.message}`));
      if (error.commit) {
        console.log(chalk.gray(`   Commit: ${error.commit}`));
      }
    });
  }
}

/**
 * Validate branch naming with robust error handling
 */
function validateBranchNaming() {
  console.log(chalk.yellow('🌿 Validating branch naming convention...'));

  try {
    const currentBranch = execSync('git branch --show-current', { encoding: 'utf8' }).trim();

    if (!currentBranch) {
      qualityResults.warnings++;
      console.log(chalk.yellow('⚠️ Could not determine current branch name'));
      return;
    }

    // Try to use the branch naming validator if available
    if (branchValidator && typeof branchValidator.validateBranchName === 'function') {
      try {
        const config = branchValidator.loadConfiguration ? branchValidator.loadConfiguration() : {};
        const validation = branchValidator.validateBranchName(currentBranch, config);

        if (validation.valid) {
          qualityResults.passed++;
          console.log(chalk.green(`✅ Branch name "${currentBranch}" follows ${validation.pattern} pattern`));
        } else {
          qualityResults.failed++;
          qualityResults.errors.push({
            type: 'BRANCH_NAMING',
            priority: 2,
            branch: currentBranch,
            message: `Branch name "${currentBranch}" does not follow naming conventions`,
            suggestions: validation.suggestions || []
          });
          console.log(chalk.red(`❌ Branch name "${currentBranch}" violates naming conventions`));

          if (validation.suggestions && validation.suggestions.length > 0) {
            console.log(chalk.yellow('💡 Suggested alternatives:'));
            validation.suggestions.slice(0, 3).forEach(suggestion => {
              console.log(chalk.gray(`  • ${suggestion}`));
            });
          }
        }
      } catch (validatorError) {
        console.warn(chalk.yellow('⚠️ Branch validator error, using fallback validation'));
        fallbackBranchValidation(currentBranch);
      }
    } else {
      // Fallback validation
      fallbackBranchValidation(currentBranch);
    }
  } catch (error) {
    qualityResults.warnings++;
    console.log(chalk.yellow('⚠️ Branch naming validation failed:', error.message));
  }
}

/**
 * Fallback branch naming validation
 */
function fallbackBranchValidation(branchName) {
  const validPatterns = [
    /^feature\/[a-z0-9-]+$/,
    /^fix\/[a-z0-9-]+$/,
    /^hotfix\/[a-z0-9-]+$/,
    /^bugfix\/[a-z0-9-]+$/,
    /^chore\/[a-z0-9-]+$/,
    /^docs\/[a-z0-9-]+$/,
    /^refactor\/[a-z0-9-]+$/,
    /^(main|master|develop|dev|staging|production|prod)$/
  ];

  const isValid = validPatterns.some(pattern => pattern.test(branchName));

  if (isValid) {
    qualityResults.passed++;
    console.log(chalk.green(`✅ Branch name "${branchName}" follows basic naming conventions`));
  } else {
    qualityResults.failed++;
    qualityResults.errors.push({
      type: 'BRANCH_NAMING',
      priority: 2,
      branch: branchName,
      message: `Branch name "${branchName}" should follow pattern: type/description (e.g., feature/user-auth)`
    });
    console.log(chalk.red(`❌ Branch name "${branchName}" should follow pattern: type/description`));
    console.log(chalk.yellow('💡 Examples: feature/user-auth, fix/login-bug, hotfix/security-patch'));
  }
}

/**
 * Handle emergency bypass
 */
function handleEmergencyBypass() {
  if (CONFIG.emergencyBypass) {
    console.log(chalk.red.bold('\n🚨 EMERGENCY BYPASS ACTIVATED'));
    console.log(chalk.yellow('⚠️ Pre-push quality checks bypassed'));
    console.log(chalk.gray('Set EMERGENCY_BYPASS=false to re-enable quality gates\n'));
    return true;
  }
  return false;
}

/**
 * Main execution
 */
function main() {
  printHeader();

  if (handleEmergencyBypass()) {
    process.exit(0);
  }

  const commits = getCommitsToPush();
  
  if (commits.length === 0) {
    console.log(chalk.yellow('⚠️ No commits to push'));
    process.exit(0);
  }

  console.log(chalk.blue(`📁 Analyzing ${commits.length} commits for push...\n`));

  // Run all quality checks with branch naming validation first
  validateBranchNaming();
  validateCommitQuality(commits);
  runTestSuite();
  checkMergeConflicts();
  validateCIPipeline();
  checkIntegrationRequirements();
  validateBranchProtection();
  checkLargeFilesAndSecrets();

  printResults();

  // Determine exit code based on Priority 1-2 failures
  const criticalFailures = qualityResults.errors.filter(e => e.priority <= 2);
  
  if (criticalFailures.length > 0) {
    console.log(chalk.red.bold('\n🚫 Push blocked due to critical issues'));
    console.log(chalk.yellow('💡 Fix the issues above or use EMERGENCY_BYPASS=true for critical fixes'));
    console.log(chalk.gray('📖 See .augment/rules/ALWAYS-comprehensive-code-review-standards.md for details\n'));
    process.exit(1);
  } else {
    console.log(chalk.green.bold('\n✅ Pre-push quality checks passed - push allowed\n'));
    process.exit(0);
  }
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = { main, CONFIG };
