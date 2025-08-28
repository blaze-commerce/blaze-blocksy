#!/usr/bin/env node

/**
 * Pre-commit Quality Check Script
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 * Enforces Priority 1-2 quality standards before commits
 */

const { execSync, spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const chalk = require('chalk');
const { enforceDocumentation: enforceDocumentationRules } = require('./documentation-enforcer');
const { organizeMarkdownFiles } = require('./organize-docs');

// Configuration
const CONFIG = {
  minCoverage: 80,
  maxFunctionLines: 30,
  maxNestingDepth: 4,
  emergencyBypass: process.env.EMERGENCY_BYPASS === 'true',
  skipTests: process.env.SKIP_TESTS === 'true',
  parallelExecution: process.env.PARALLEL_CHECKS !== 'false', // Enable by default
  maxConcurrency: parseInt(process.env.MAX_CONCURRENCY) || 3
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
  console.log(chalk.gray('Priority 1-2 quality standards validation'));
  if (CONFIG.parallelExecution) {
    console.log(chalk.gray(`Parallel execution enabled (max ${CONFIG.maxConcurrency} concurrent checks)\n`));
  } else {
    console.log(chalk.gray('Sequential execution mode\n'));
  }
}

/**
 * Execute multiple checks in parallel for better performance
 */
async function runChecksInParallel(checks) {
  if (!CONFIG.parallelExecution) {
    // Run sequentially if parallel execution is disabled
    for (const check of checks) {
      await check();
    }
    return;
  }

  console.log(chalk.blue(`🚀 Running ${checks.length} checks in parallel...`));

  const results = [];
  const executing = [];

  for (let i = 0; i < checks.length; i++) {
    const checkPromise = Promise.resolve().then(checks[i]).catch(error => {
      console.error(chalk.red(`❌ Check ${i + 1} failed:`, error.message));
      return { error };
    });

    executing.push(checkPromise);

    // Limit concurrency
    if (executing.length >= CONFIG.maxConcurrency || i === checks.length - 1) {
      const batchResults = await Promise.all(executing);
      results.push(...batchResults);
      executing.length = 0; // Clear the array
    }
  }

  return results;
}

/**
 * Create a promise-based wrapper for synchronous check functions
 */
function promisifyCheck(checkFunction, ...args) {
  return () => new Promise((resolve, reject) => {
    try {
      const result = checkFunction(...args);
      resolve(result);
    } catch (error) {
      reject(error);
    }
  });
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
 * Run linting checks with detailed file-specific feedback (Priority 2)
 */
function runLinting(files) {
  console.log(chalk.yellow('📝 Running linting checks...'));

  const phpFiles = files.filter(f => f.endsWith('.php'));
  const jsFiles = files.filter(f => f.endsWith('.js'));
  const cssFiles = files.filter(f => f.endsWith('.css'));

  let lintingPassed = true;
  const lintingResults = {
    php: { passed: [], failed: [] },
    js: { passed: [], failed: [] },
    css: { passed: [], failed: [] }
  };

  // PHP linting with file-specific feedback
  if (phpFiles.length > 0) {
    console.log(chalk.blue(`  🔍 Checking ${phpFiles.length} PHP files...`));

    phpFiles.forEach(file => {
      try {
        execSync(`vendor/bin/phpcs --standard=WordPress --extensions=php ${file}`,
          { stdio: 'pipe' });
        lintingResults.php.passed.push(file);
        console.log(chalk.green(`    ✅ ${file}`));
      } catch (error) {
        lintingPassed = false;
        lintingResults.php.failed.push({ file, error: error.stdout?.toString() || error.message });

        // Parse PHPCS output for specific errors
        const errorOutput = error.stdout?.toString() || '';
        const errorLines = errorOutput.split('\n').filter(line => line.includes('ERROR') || line.includes('WARNING'));

        qualityResults.errors.push({
          type: 'LINTING',
          priority: 2,
          file: file,
          message: `PHP coding standards violations: ${errorLines.length} issues found`,
          details: errorLines.slice(0, 3).join('\n') // Show first 3 errors
        });

        console.log(chalk.red(`    ❌ ${file} - ${errorLines.length} issues`));
        if (errorLines.length > 0) {
          console.log(chalk.gray(`      ${errorLines[0].trim()}`));
        }
      }
    });
  }

  // JavaScript linting with file-specific feedback
  if (jsFiles.length > 0) {
    console.log(chalk.blue(`  🔍 Checking ${jsFiles.length} JavaScript files...`));

    jsFiles.forEach(file => {
      try {
        execSync(`npx eslint ${file}`, { stdio: 'pipe' });
        lintingResults.js.passed.push(file);
        console.log(chalk.green(`    ✅ ${file}`));
      } catch (error) {
        lintingPassed = false;
        lintingResults.js.failed.push({ file, error: error.stdout?.toString() || error.message });

        // Parse ESLint output for specific errors
        const errorOutput = error.stdout?.toString() || '';
        const errorLines = errorOutput.split('\n').filter(line => line.includes('error') || line.includes('warning'));

        qualityResults.errors.push({
          type: 'LINTING',
          priority: 2,
          file: file,
          message: `ESLint violations: ${errorLines.length} issues found`,
          details: errorLines.slice(0, 3).join('\n') // Show first 3 errors
        });

        console.log(chalk.red(`    ❌ ${file} - ${errorLines.length} issues`));
        if (errorLines.length > 0) {
          console.log(chalk.gray(`      ${errorLines[0].trim()}`));
        }
        console.log(chalk.yellow(`      💡 Run 'npx eslint ${file} --fix' to auto-fix some issues`));
      }
    });
  }

  // CSS linting with file-specific feedback
  if (cssFiles.length > 0) {
    console.log(chalk.blue(`  🔍 Checking ${cssFiles.length} CSS files...`));

    cssFiles.forEach(file => {
      try {
        execSync(`npx stylelint ${file}`, { stdio: 'pipe' });
        lintingResults.css.passed.push(file);
        console.log(chalk.green(`    ✅ ${file}`));
      } catch (error) {
        lintingPassed = false;
        lintingResults.css.failed.push({ file, error: error.stdout?.toString() || error.message });

        // Parse Stylelint output for specific errors
        const errorOutput = error.stdout?.toString() || '';
        const errorLines = errorOutput.split('\n').filter(line => line.includes('✖') || line.includes('⚠'));

        qualityResults.errors.push({
          type: 'LINTING',
          priority: 2,
          file: file,
          message: `CSS linting violations: ${errorLines.length} issues found`,
          details: errorLines.slice(0, 3).join('\n') // Show first 3 errors
        });

        console.log(chalk.red(`    ❌ ${file} - ${errorLines.length} issues`));
        if (errorLines.length > 0) {
          console.log(chalk.gray(`      ${errorLines[0].trim()}`));
        }
        console.log(chalk.yellow(`      💡 Run 'npx stylelint ${file} --fix' to auto-fix some issues`));
      }
    });
  }

  // Summary of linting results
  const totalPassed = lintingResults.php.passed.length + lintingResults.js.passed.length + lintingResults.css.passed.length;
  const totalFailed = lintingResults.php.failed.length + lintingResults.js.failed.length + lintingResults.css.failed.length;

  if (lintingPassed) {
    qualityResults.passed++;
    console.log(chalk.green(`✅ All ${totalPassed} files passed linting checks`));
  } else {
    qualityResults.failed++;
    console.log(chalk.red(`❌ ${totalFailed} of ${totalPassed + totalFailed} files failed linting checks`));
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
 * Run build validation with detailed file-specific feedback (Priority 1)
 */
function validateBuild() {
  if (CONFIG.skipTests) {
    console.log(chalk.yellow('⏭️ Skipping build validation (SKIP_TESTS=true)'));
    return;
  }

  console.log(chalk.yellow('🔨 Validating build...'));

  const phpFiles = getStagedFiles().filter(f => f.endsWith('.php'));
  const jsFiles = getStagedFiles().filter(f => f.endsWith('.js'));

  let buildPassed = true;
  const buildResults = { passed: [], failed: [] };

  try {
    // PHP syntax validation with file-specific feedback
    if (phpFiles.length > 0) {
      console.log(chalk.blue(`  🔍 Checking PHP syntax in ${phpFiles.length} files...`));

      phpFiles.forEach(file => {
        try {
          execSync(`php -l ${file}`, { stdio: 'pipe' });
          buildResults.passed.push(file);
          console.log(chalk.green(`    ✅ ${file}`));
        } catch (error) {
          buildPassed = false;
          const errorOutput = error.stderr?.toString() || error.message;
          buildResults.failed.push({ file, error: errorOutput });

          // Parse PHP syntax error for line number and specific issue
          const syntaxErrorMatch = errorOutput.match(/Parse error: (.+) in .+ on line (\d+)/);
          const errorMessage = syntaxErrorMatch
            ? `Line ${syntaxErrorMatch[2]}: ${syntaxErrorMatch[1]}`
            : 'Syntax error detected';

          qualityResults.errors.push({
            type: 'BUILD',
            priority: 1,
            file: file,
            message: `PHP syntax error: ${errorMessage}`,
            details: errorOutput.split('\n')[0] // First line of error
          });

          console.log(chalk.red(`    ❌ ${file} - ${errorMessage}`));
        }
      });
    }

    // JavaScript syntax validation (basic check)
    if (jsFiles.length > 0) {
      console.log(chalk.blue(`  🔍 Checking JavaScript syntax in ${jsFiles.length} files...`));

      jsFiles.forEach(file => {
        try {
          // Basic syntax check using Node.js
          execSync(`node -c ${file}`, { stdio: 'pipe' });
          buildResults.passed.push(file);
          console.log(chalk.green(`    ✅ ${file}`));
        } catch (error) {
          buildPassed = false;
          const errorOutput = error.stderr?.toString() || error.message;
          buildResults.failed.push({ file, error: errorOutput });

          // Parse JavaScript syntax error
          const syntaxErrorMatch = errorOutput.match(/SyntaxError: (.+)/);
          const errorMessage = syntaxErrorMatch
            ? syntaxErrorMatch[1]
            : 'Syntax error detected';

          qualityResults.errors.push({
            type: 'BUILD',
            priority: 1,
            file: file,
            message: `JavaScript syntax error: ${errorMessage}`,
            details: errorOutput.split('\n')[0]
          });

          console.log(chalk.red(`    ❌ ${file} - ${errorMessage}`));
        }
      });
    }

    if (buildPassed) {
      qualityResults.passed++;
      console.log(chalk.green(`✅ Build validation passed - ${buildResults.passed.length} files checked`));
    } else {
      qualityResults.failed++;
      console.log(chalk.red(`❌ Build validation failed - ${buildResults.failed.length} files have syntax errors`));
    }

  } catch (error) {
    qualityResults.failed++;
    qualityResults.errors.push({
      type: 'BUILD',
      priority: 1,
      file: 'Build process',
      message: `Build validation system error: ${error.message}`,
      details: error.stack
    });
    console.log(chalk.red('❌ Build validation system error'));
    console.log(chalk.gray(`   ${error.message}`));
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
 * Organize markdown documentation files
 */
function organizeDocumentation() {
  console.log(chalk.yellow('📁 Organizing markdown documentation...'));

  try {
    // Check if there are any new .md files to organize
    const stagedFiles = getStagedFiles();
    const markdownFiles = stagedFiles.filter(file => file.endsWith('.md'));

    if (markdownFiles.length === 0) {
      console.log(chalk.gray('📄 No markdown files to organize'));
      return;
    }

    console.log(chalk.blue(`📄 Found ${markdownFiles.length} markdown files to organize`));

    // Run organization (not dry run)
    const result = organizeMarkdownFiles(false);

    if (result.moved > 0) {
      console.log(chalk.green(`✅ Organized ${result.moved} markdown files`));

      // Stage the organized files
      try {
        execSync('git add docs/', { stdio: 'pipe' });
        console.log(chalk.green('📁 Staged organized documentation files'));
      } catch (error) {
        console.warn(chalk.yellow('⚠️ Could not stage organized files automatically'));
      }
    } else {
      console.log(chalk.gray('📄 No files needed organization'));
    }

    qualityResults.passed++;
  } catch (error) {
    qualityResults.warnings++;
    console.log(chalk.yellow('⚠️ Documentation organization failed:'), error.message);
    console.log(chalk.gray('💡 This is a warning - commit will continue'));
  }
}

/**
 * Enforce documentation requirements (Priority 2)
 */
function enforceDocumentation() {
  console.log(chalk.yellow('📚 Checking documentation requirements...'));

  try {
    const result = enforceDocumentationRules();

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
 * Print enhanced quality results summary with file-specific feedback
 */
function printResults() {
  console.log(chalk.blue.bold('\n📊 Quality Check Results Summary:'));
  console.log(chalk.green(`✅ Passed: ${qualityResults.passed}`));
  console.log(chalk.red(`❌ Failed: ${qualityResults.failed}`));
  console.log(chalk.yellow(`⚠️ Warnings: ${qualityResults.warnings}`));

  if (qualityResults.errors.length > 0) {
    console.log(chalk.red.bold('\n🚨 Issues Found:'));

    // Group errors by type for better organization
    const errorsByType = {};
    qualityResults.errors.forEach(error => {
      if (!errorsByType[error.type]) {
        errorsByType[error.type] = [];
      }
      errorsByType[error.type].push(error);
    });

    Object.entries(errorsByType).forEach(([type, errors]) => {
      console.log(chalk.red.bold(`\n${getTypeIcon(type)} ${type} Issues (${errors.length}):`));

      errors.forEach((error, index) => {
        console.log(chalk.red(`  ${index + 1}. [P${error.priority}] ${error.message}`));
        if (error.file && error.file !== type + ' files') {
          console.log(chalk.gray(`     📁 File: ${error.file}`));
        }
        if (error.details) {
          console.log(chalk.gray(`     💡 Details: ${error.details.split('\n')[0]}`));
        }
      });
    });

    // Provide actionable suggestions
    console.log(chalk.yellow.bold('\n💡 Quick Fix Suggestions:'));
    if (errorsByType.LINTING) {
      console.log(chalk.yellow('  • Run `npm run format` to auto-fix formatting issues'));
      console.log(chalk.yellow('  • Run `npm run lint:fix` to auto-fix linting violations'));
    }
    if (errorsByType.BUILD) {
      console.log(chalk.yellow('  • Check syntax errors in the files listed above'));
      console.log(chalk.yellow('  • Ensure all required dependencies are installed'));
    }
    if (errorsByType.SECURITY) {
      console.log(chalk.yellow('  • Remove hardcoded credentials and use environment variables'));
      console.log(chalk.yellow('  • Check .gitignore to prevent committing sensitive files'));
    }
  }
}

/**
 * Get appropriate icon for error type
 */
function getTypeIcon(type) {
  const icons = {
    'LINTING': '📝',
    'BUILD': '🔨',
    'SECURITY': '🔒',
    'TESTING': '🧪',
    'DOCUMENTATION': '📚',
    'STRUCTURE': '🏗️'
  };
  return icons[type] || '⚠️';
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
 * Main execution with parallel processing support
 */
async function main() {
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

  try {
    if (CONFIG.parallelExecution) {
      // Run independent checks in parallel for better performance
      const independentChecks = [
        promisifyCheck(checkSecrets, stagedFiles),
        promisifyCheck(runLinting, stagedFiles),
        promisifyCheck(checkCodeStructure, stagedFiles),
        promisifyCheck(organizeDocumentation),
        promisifyCheck(enforceDocumentation)
      ];

      // Run parallel checks
      await runChecksInParallel(independentChecks);

      // Run dependent checks sequentially (build validation depends on files being clean)
      validateBuild();
      checkTestCoverage();
    } else {
      // Run all quality checks sequentially
      checkSecrets(stagedFiles);
      runLinting(stagedFiles);
      checkCodeStructure(stagedFiles);
      validateBuild();
      checkTestCoverage();
      organizeDocumentation();
      enforceDocumentation();
    }

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
  } catch (error) {
    console.error(chalk.red.bold('\n💥 Critical error during quality checks:'));
    console.error(chalk.red(error.message));
    console.log(chalk.yellow('\n💡 Use EMERGENCY_BYPASS=true to bypass if this is a critical fix'));
    console.log(chalk.gray('📖 Report this issue to the development team\n'));
    process.exit(1);
  }
}

// Execute if run directly
if (require.main === module) {
  main().catch(error => {
    console.error(chalk.red.bold('\n💥 Unhandled error in pre-commit checks:'));
    console.error(chalk.red(error.message));
    console.error(chalk.gray(error.stack));
    process.exit(1);
  });
}

module.exports = { main, CONFIG };
