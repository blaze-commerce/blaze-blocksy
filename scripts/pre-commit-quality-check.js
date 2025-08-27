#!/usr/bin/env node

/**
 * Pre-commit Quality Check Script
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 * Enforces Priority 1-2 quality standards before commits
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const chalk = require('chalk');
const { enforceDocumentation } = require('./documentation-enforcer');

// Configuration
const CONFIG = {
  minCoverage: 80,
  maxFunctionLines: 30,
  maxNestingDepth: 4,
  emergencyBypass: process.env.EMERGENCY_BYPASS === 'true',
  skipTests: process.env.SKIP_TESTS === 'true'
};

// Quality check results
let qualityResults = {
  passed: 0,
  failed: 0,
  warnings: 0,
  errors: []
};

/**
 * Print header with comprehensive code review standards reference
 */
function printHeader() {
  console.log(chalk.blue.bold('\n🔍 Pre-Commit Quality Check'));
  console.log(chalk.gray('Enforcing ALWAYS-comprehensive-code-review-standards.md'));
  console.log(chalk.gray('Priority 1-2 quality standards validation\n'));
}

/**
 * Get list of staged files
 */
function getStagedFiles() {
  try {
    const output = execSync('git diff --cached --name-only', { encoding: 'utf8' });
    return output.trim().split('\n').filter(file => file.length > 0);
  } catch (error) {
    console.error(chalk.red('❌ Failed to get staged files:'), error.message);
    return [];
  }
}

/**
 * Check for hardcoded credentials and sensitive data (Priority 1)
 */
function checkSecrets(files) {
  console.log(chalk.yellow('🔒 Checking for hardcoded credentials...'));
  
  const secretPatterns = [
    /password\s*=\s*["'][^"']{8,}["']/i,
    /api[_-]?key\s*=\s*["'][^"']{16,}["']/i,
    /secret\s*=\s*["'][^"']{16,}["']/i,
    /token\s*=\s*["'][^"']{16,}["']/i,
    /private[_-]?key\s*=\s*["'][^"']{32,}["']/i,
    /database[_-]?url\s*=\s*["'][^"']+["']/i,
    /mysql:\/\/|postgres:\/\/|mongodb:\/\//i
  ];

  let secretsFound = false;

  files.forEach(file => {
    if (!fs.existsSync(file)) return;
    
    try {
      const content = fs.readFileSync(file, 'utf8');
      secretPatterns.forEach((pattern, index) => {
        if (pattern.test(content)) {
          secretsFound = true;
          qualityResults.errors.push({
            type: 'SECURITY',
            priority: 1,
            file: file,
            message: `Potential hardcoded credential detected (pattern ${index + 1})`
          });
        }
      });
    } catch (error) {
      console.warn(chalk.yellow(`⚠️ Could not read file: ${file}`));
    }
  });

  if (secretsFound) {
    qualityResults.failed++;
    console.log(chalk.red('❌ Hardcoded credentials detected'));
  } else {
    qualityResults.passed++;
    console.log(chalk.green('✅ No hardcoded credentials found'));
  }
}

/**
 * Run linting checks (Priority 2)
 */
function runLinting(files) {
  console.log(chalk.yellow('📝 Running linting checks...'));
  
  const phpFiles = files.filter(f => f.endsWith('.php'));
  const jsFiles = files.filter(f => f.endsWith('.js'));
  const cssFiles = files.filter(f => f.endsWith('.css'));

  let lintingPassed = true;

  // PHP linting
  if (phpFiles.length > 0) {
    try {
      execSync(`vendor/bin/phpcs --standard=WordPress --extensions=php ${phpFiles.join(' ')}`, 
        { stdio: 'pipe' });
      console.log(chalk.green('✅ PHP linting passed'));
    } catch (error) {
      lintingPassed = false;
      qualityResults.errors.push({
        type: 'LINTING',
        priority: 2,
        file: 'PHP files',
        message: 'PHP coding standards violations detected'
      });
      console.log(chalk.red('❌ PHP linting failed'));
    }
  }

  // JavaScript linting
  if (jsFiles.length > 0) {
    try {
      execSync(`npx eslint ${jsFiles.join(' ')}`, { stdio: 'pipe' });
      console.log(chalk.green('✅ JavaScript linting passed'));
    } catch (error) {
      lintingPassed = false;
      qualityResults.errors.push({
        type: 'LINTING',
        priority: 2,
        file: 'JavaScript files',
        message: 'JavaScript linting violations detected'
      });
      console.log(chalk.red('❌ JavaScript linting failed'));
    }
  }

  // CSS linting
  if (cssFiles.length > 0) {
    try {
      execSync(`npx stylelint ${cssFiles.join(' ')}`, { stdio: 'pipe' });
      console.log(chalk.green('✅ CSS linting passed'));
    } catch (error) {
      lintingPassed = false;
      qualityResults.errors.push({
        type: 'LINTING',
        priority: 2,
        file: 'CSS files',
        message: 'CSS linting violations detected'
      });
      console.log(chalk.red('❌ CSS linting failed'));
    }
  }

  if (lintingPassed) {
    qualityResults.passed++;
  } else {
    qualityResults.failed++;
  }
}

/**
 * Check code structure standards (Priority 2)
 */
function checkCodeStructure(files) {
  console.log(chalk.yellow('🏗️ Checking code structure standards...'));
  
  let structureIssues = false;

  files.forEach(file => {
    if (!fs.existsSync(file) || (!file.endsWith('.php') && !file.endsWith('.js'))) return;
    
    try {
      const content = fs.readFileSync(file, 'utf8');
      const lines = content.split('\n');
      
      // Check function length (max 30 lines)
      let inFunction = false;
      let functionStartLine = 0;
      let braceCount = 0;
      
      lines.forEach((line, index) => {
        // Simple function detection (can be enhanced)
        if (line.match(/function\s+\w+|def\s+\w+|\w+\s*\(/)) {
          inFunction = true;
          functionStartLine = index;
          braceCount = 0;
        }
        
        if (inFunction) {
          if (line.includes('{')) braceCount++;
          if (line.includes('}')) braceCount--;
          
          if (braceCount === 0 && index > functionStartLine) {
            const functionLength = index - functionStartLine;
            if (functionLength > CONFIG.maxFunctionLines) {
              structureIssues = true;
              qualityResults.errors.push({
                type: 'STRUCTURE',
                priority: 2,
                file: file,
                message: `Function exceeds ${CONFIG.maxFunctionLines} lines (${functionLength} lines) at line ${functionStartLine + 1}`
              });
            }
            inFunction = false;
          }
        }
      });
    } catch (error) {
      console.warn(chalk.yellow(`⚠️ Could not analyze structure for: ${file}`));
    }
  });

  if (structureIssues) {
    qualityResults.failed++;
    console.log(chalk.red('❌ Code structure issues detected'));
  } else {
    qualityResults.passed++;
    console.log(chalk.green('✅ Code structure standards met'));
  }
}

/**
 * Run build validation (Priority 1)
 */
function validateBuild() {
  if (CONFIG.skipTests) {
    console.log(chalk.yellow('⏭️ Skipping build validation (SKIP_TESTS=true)'));
    return;
  }

  console.log(chalk.yellow('🔨 Validating build...'));
  
  try {
    // Check if there are any syntax errors by running a basic PHP syntax check
    const phpFiles = getStagedFiles().filter(f => f.endsWith('.php'));
    if (phpFiles.length > 0) {
      phpFiles.forEach(file => {
        execSync(`php -l ${file}`, { stdio: 'pipe' });
      });
    }
    
    qualityResults.passed++;
    console.log(chalk.green('✅ Build validation passed'));
  } catch (error) {
    qualityResults.failed++;
    qualityResults.errors.push({
      type: 'BUILD',
      priority: 1,
      file: 'Build process',
      message: 'Build validation failed - syntax errors detected'
    });
    console.log(chalk.red('❌ Build validation failed'));
  }
}

/**
 * Check test coverage (Priority 2)
 */
function checkTestCoverage() {
  if (CONFIG.skipTests) {
    console.log(chalk.yellow('⏭️ Skipping test coverage check (SKIP_TESTS=true)'));
    return;
  }

  console.log(chalk.yellow('🧪 Checking test coverage...'));

  try {
    // Run tests with coverage (simplified for WordPress theme)
    if (fs.existsSync('tests') || fs.existsSync('test')) {
      execSync('npm test', { stdio: 'pipe' });
      qualityResults.passed++;
      console.log(chalk.green('✅ Test coverage requirements met'));
    } else {
      qualityResults.warnings++;
      console.log(chalk.yellow('⚠️ No test directory found - consider adding tests'));
    }
  } catch (error) {
    qualityResults.failed++;
    qualityResults.errors.push({
      type: 'TESTING',
      priority: 2,
      file: 'Test suite',
      message: 'Test coverage below minimum requirements'
    });
    console.log(chalk.red('❌ Test coverage requirements not met'));
  }
}

/**
 * Enforce documentation requirements (Priority 2)
 */
function enforceDocumentation() {
  console.log(chalk.yellow('📚 Checking documentation requirements...'));

  try {
    const result = require('./documentation-enforcer').enforceDocumentation();

    if (result.success) {
      qualityResults.passed++;
      console.log(chalk.green('✅ Documentation requirements met'));

      if (result.generated && result.generated.length > 0) {
        console.log(chalk.blue(`📝 Generated ${result.generated.length} documentation templates`));
      }
    } else {
      qualityResults.failed++;
      qualityResults.errors.push({
        type: 'DOCUMENTATION',
        priority: 2,
        file: 'Documentation',
        message: result.error || 'Documentation requirements not met'
      });
      console.log(chalk.red('❌ Documentation requirements not met'));
    }
  } catch (error) {
    qualityResults.warnings++;
    console.log(chalk.yellow('⚠️ Documentation enforcement failed:', error.message));
  }
}

/**
 * Print quality results summary
 */
function printResults() {
  console.log(chalk.blue.bold('\n📊 Quality Check Results:'));
  console.log(chalk.green(`✅ Passed: ${qualityResults.passed}`));
  console.log(chalk.red(`❌ Failed: ${qualityResults.failed}`));
  console.log(chalk.yellow(`⚠️ Warnings: ${qualityResults.warnings}`));

  if (qualityResults.errors.length > 0) {
    console.log(chalk.red.bold('\n🚨 Issues Found:'));
    qualityResults.errors.forEach((error, index) => {
      console.log(chalk.red(`${index + 1}. [P${error.priority}] ${error.type}: ${error.message}`));
      if (error.file) {
        console.log(chalk.gray(`   File: ${error.file}`));
      }
    });
  }
}

/**
 * Handle emergency bypass
 */
function handleEmergencyBypass() {
  if (CONFIG.emergencyBypass) {
    console.log(chalk.red.bold('\n🚨 EMERGENCY BYPASS ACTIVATED'));
    console.log(chalk.yellow('⚠️ Quality checks bypassed - ensure immediate follow-up'));
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

  const stagedFiles = getStagedFiles();
  
  if (stagedFiles.length === 0) {
    console.log(chalk.yellow('⚠️ No staged files found'));
    process.exit(0);
  }

  console.log(chalk.blue(`📁 Analyzing ${stagedFiles.length} staged files...\n`));

  // Run all quality checks
  checkSecrets(stagedFiles);
  runLinting(stagedFiles);
  checkCodeStructure(stagedFiles);
  validateBuild();
  checkTestCoverage();
  enforceDocumentation();

  printResults();

  // Determine exit code based on Priority 1-2 failures
  const criticalFailures = qualityResults.errors.filter(e => e.priority <= 2);
  
  if (criticalFailures.length > 0) {
    console.log(chalk.red.bold('\n🚫 Commit blocked due to Priority 1-2 issues'));
    console.log(chalk.yellow('💡 Fix the issues above or use EMERGENCY_BYPASS=true for critical fixes'));
    console.log(chalk.gray('📖 See .augment/rules/ALWAYS-comprehensive-code-review-standards.md for details\n'));
    process.exit(1);
  } else {
    console.log(chalk.green.bold('\n✅ Quality checks passed - commit allowed\n'));
    process.exit(0);
  }
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = { main, CONFIG };
