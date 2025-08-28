#!/usr/bin/env node

/**
 * Post-merge Cleanup Script
 * Automated quality assessment and cleanup after merge operations
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const chalk = require('chalk');

// Configuration
const CONFIG = {
  emergencyBypass: process.env.EMERGENCY_BYPASS === 'true',
  skipCleanup: process.env.SKIP_CLEANUP === 'true',
  skipDependencyUpdate: process.env.SKIP_DEPENDENCY_UPDATE === 'true'
};

// Cleanup results
let cleanupResults = {
  completed: 0,
  skipped: 0,
  warnings: 0,
  actions: []
};

/**
 * Print header
 */
function printHeader() {
  console.log(chalk.blue.bold('\n🔄 Post-Merge Cleanup'));
  console.log(chalk.gray('Automated quality assessment and repository optimization'));
  console.log(chalk.gray('Reference: ALWAYS-comprehensive-code-review-standards.md\n'));
}

/**
 * Check if merge was successful
 */
function checkMergeStatus() {
  try {
    const status = execSync('git status --porcelain', { encoding: 'utf8' });
    const conflictMarkers = status.split('\n').filter(line => 
      line.startsWith('UU ') || line.startsWith('AA ') || line.startsWith('DD ')
    );

    if (conflictMarkers.length > 0) {
      console.log(chalk.red('❌ Merge conflicts still present - cleanup aborted'));
      process.exit(1);
    }

    console.log(chalk.green('✅ Merge completed successfully'));
    return true;
  } catch (error) {
    console.warn(chalk.yellow('⚠️ Could not verify merge status'));
    return false;
  }
}

/**
 * Update dependencies if package files were modified
 */
function updateDependencies() {
  if (CONFIG.skipDependencyUpdate) {
    console.log(chalk.yellow('⏭️ Skipping dependency update (SKIP_DEPENDENCY_UPDATE=true)'));
    return;
  }

  console.log(chalk.yellow('📦 Checking for dependency updates...'));

  try {
    // Check if package.json was modified in the merge
    const changedFiles = execSync('git diff --name-only HEAD~1 HEAD', { encoding: 'utf8' });
    const packageFilesChanged = changedFiles.split('\n').some(file => 
      file === 'package.json' || file === 'package-lock.json' || 
      file === 'composer.json' || file === 'composer.lock'
    );

    if (packageFilesChanged) {
      console.log(chalk.blue('  Package files modified - updating dependencies...'));

      // Update Node.js dependencies
      if (fs.existsSync('package.json')) {
        console.log(chalk.blue('  Updating Node.js dependencies...'));
        execSync('npm install', { stdio: 'pipe' });
        cleanupResults.actions.push('Updated Node.js dependencies');
        console.log(chalk.green('  ✅ Node.js dependencies updated'));
      }

      // Update PHP dependencies
      if (fs.existsSync('composer.json')) {
        console.log(chalk.blue('  Updating PHP dependencies...'));
        execSync('composer install --no-dev --optimize-autoloader', { stdio: 'pipe' });
        cleanupResults.actions.push('Updated PHP dependencies');
        console.log(chalk.green('  ✅ PHP dependencies updated'));
      }

      cleanupResults.completed++;
    } else {
      console.log(chalk.gray('  No package files modified - skipping dependency update'));
      cleanupResults.skipped++;
    }
  } catch (error) {
    cleanupResults.warnings++;
    console.log(chalk.yellow('⚠️ Dependency update failed:'), error.message);
  }
}

/**
 * Clean up temporary files and optimize repository
 */
function cleanupTemporaryFiles() {
  if (CONFIG.skipCleanup) {
    console.log(chalk.yellow('⏭️ Skipping file cleanup (SKIP_CLEANUP=true)'));
    return;
  }

  console.log(chalk.yellow('🧹 Cleaning up temporary files...'));

  const tempPatterns = [
    '**/*.tmp',
    '**/*.temp',
    '**/.DS_Store',
    '**/Thumbs.db',
    '**/*.log',
    '**/npm-debug.log*',
    '**/yarn-debug.log*',
    '**/yarn-error.log*',
    '**/.nyc_output',
    '**/coverage',
    '**/.sass-cache',
    '**/.cache'
  ];

  let filesRemoved = 0;

  tempPatterns.forEach(pattern => {
    try {
      const command = process.platform === 'win32' 
        ? `del /s /q "${pattern}"` 
        : `find . -name "${pattern.replace('**/', '')}" -type f -delete`;
      
      execSync(command, { stdio: 'pipe' });
      filesRemoved++;
    } catch (error) {
      // File pattern not found (expected)
    }
  });

  if (filesRemoved > 0) {
    cleanupResults.actions.push(`Removed ${filesRemoved} temporary file patterns`);
    console.log(chalk.green(`  ✅ Cleaned up ${filesRemoved} temporary file patterns`));
  } else {
    console.log(chalk.gray('  No temporary files found'));
  }

  cleanupResults.completed++;
}

/**
 * Optimize Git repository
 */
function optimizeGitRepository() {
  console.log(chalk.yellow('⚡ Optimizing Git repository...'));

  try {
    // Run git garbage collection
    console.log(chalk.blue('  Running garbage collection...'));
    execSync('git gc --auto', { stdio: 'pipe' });
    
    // Prune remote tracking branches
    console.log(chalk.blue('  Pruning remote tracking branches...'));
    execSync('git remote prune origin', { stdio: 'pipe' });
    
    cleanupResults.actions.push('Optimized Git repository');
    cleanupResults.completed++;
    console.log(chalk.green('  ✅ Git repository optimized'));
  } catch (error) {
    cleanupResults.warnings++;
    console.log(chalk.yellow('⚠️ Git optimization failed:'), error.message);
  }
}

/**
 * Run post-merge quality assessment
 */
function runQualityAssessment() {
  console.log(chalk.yellow('🔍 Running post-merge quality assessment...'));

  try {
    // Check for any obvious issues in the merged code
    const changedFiles = execSync('git diff --name-only HEAD~1 HEAD', { encoding: 'utf8' });
    const files = changedFiles.split('\n').filter(f => f.length > 0);

    console.log(chalk.blue(`  Assessing ${files.length} changed files...`));

    // Basic syntax check for PHP files
    const phpFiles = files.filter(f => f.endsWith('.php') && fs.existsSync(f));
    if (phpFiles.length > 0) {
      console.log(chalk.blue('  Checking PHP syntax...'));
      phpFiles.forEach(file => {
        execSync(`php -l ${file}`, { stdio: 'pipe' });
      });
      console.log(chalk.green(`  ✅ PHP syntax valid for ${phpFiles.length} files`));
    }

    // Check for JavaScript syntax if files exist
    const jsFiles = files.filter(f => f.endsWith('.js') && fs.existsSync(f));
    if (jsFiles.length > 0 && fs.existsSync('node_modules/.bin/eslint')) {
      console.log(chalk.blue('  Checking JavaScript syntax...'));
      execSync(`npx eslint ${jsFiles.join(' ')} --quiet`, { stdio: 'pipe' });
      console.log(chalk.green(`  ✅ JavaScript syntax valid for ${jsFiles.length} files`));
    }

    cleanupResults.actions.push(`Quality assessment completed for ${files.length} files`);
    cleanupResults.completed++;
    console.log(chalk.green('  ✅ Post-merge quality assessment passed'));
  } catch (error) {
    cleanupResults.warnings++;
    console.log(chalk.yellow('⚠️ Quality assessment issues detected:'), error.message);
    console.log(chalk.gray('  Consider running full quality checks manually'));
  }
}

/**
 * Update local branch information
 */
function updateBranchInformation() {
  console.log(chalk.yellow('🌿 Updating branch information...'));

  try {
    // Fetch latest remote information
    console.log(chalk.blue('  Fetching remote updates...'));
    execSync('git fetch --prune', { stdio: 'pipe' });

    // Get current branch
    const currentBranch = execSync('git branch --show-current', { encoding: 'utf8' }).trim();
    
    // Check if we're ahead/behind remote
    try {
      const status = execSync(`git status -b --porcelain`, { encoding: 'utf8' });
      const statusLine = status.split('\n')[0];
      
      if (statusLine.includes('ahead')) {
        console.log(chalk.yellow('  ⚠️ Local branch is ahead of remote'));
      } else if (statusLine.includes('behind')) {
        console.log(chalk.yellow('  ⚠️ Local branch is behind remote'));
      } else {
        console.log(chalk.green('  ✅ Branch is up to date with remote'));
      }
    } catch (error) {
      // Status check failed, continue
    }

    cleanupResults.actions.push('Updated branch information');
    cleanupResults.completed++;
    console.log(chalk.green(`  ✅ Branch information updated for ${currentBranch}`));
  } catch (error) {
    cleanupResults.warnings++;
    console.log(chalk.yellow('⚠️ Branch update failed:'), error.message);
  }
}

/**
 * Generate merge summary report
 */
function generateMergeSummary() {
  console.log(chalk.yellow('📋 Generating merge summary...'));

  try {
    // Get merge commit information
    const mergeCommit = execSync('git log -1 --pretty=format:"%H %s"', { encoding: 'utf8' });
    const changedFiles = execSync('git diff --name-only HEAD~1 HEAD', { encoding: 'utf8' });
    const stats = execSync('git diff --stat HEAD~1 HEAD', { encoding: 'utf8' });

    console.log(chalk.blue('\n📊 Merge Summary:'));
    console.log(chalk.gray(`Commit: ${mergeCommit}`));
    console.log(chalk.gray(`Files changed: ${changedFiles.split('\n').filter(f => f.length > 0).length}`));
    console.log(chalk.gray('Statistics:'));
    stats.split('\n').forEach(line => {
      if (line.trim()) {
        console.log(chalk.gray(`  ${line}`));
      }
    });

    cleanupResults.actions.push('Generated merge summary');
    cleanupResults.completed++;
  } catch (error) {
    cleanupResults.warnings++;
    console.log(chalk.yellow('⚠️ Could not generate merge summary'));
  }
}

/**
 * Print cleanup results
 */
function printResults() {
  console.log(chalk.blue.bold('\n📊 Post-Merge Cleanup Results:'));
  console.log(chalk.green(`✅ Completed: ${cleanupResults.completed}`));
  console.log(chalk.gray(`⏭️ Skipped: ${cleanupResults.skipped}`));
  console.log(chalk.yellow(`⚠️ Warnings: ${cleanupResults.warnings}`));

  if (cleanupResults.actions.length > 0) {
    console.log(chalk.blue.bold('\n🔧 Actions Performed:'));
    cleanupResults.actions.forEach((action, index) => {
      console.log(chalk.blue(`${index + 1}. ${action}`));
    });
  }

  console.log(chalk.green.bold('\n✅ Post-merge cleanup completed'));
  console.log(chalk.gray('Repository is optimized and ready for continued development\n'));
}

/**
 * Handle emergency bypass
 */
function handleEmergencyBypass() {
  if (CONFIG.emergencyBypass) {
    console.log(chalk.red.bold('\n🚨 EMERGENCY BYPASS ACTIVATED'));
    console.log(chalk.yellow('⚠️ Post-merge cleanup bypassed'));
    console.log(chalk.gray('Set EMERGENCY_BYPASS=false to re-enable cleanup\n'));
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

  // Verify merge was successful
  if (!checkMergeStatus()) {
    process.exit(1);
  }

  console.log(chalk.blue('🚀 Starting post-merge cleanup process...\n'));

  // Run all cleanup operations
  updateDependencies();
  cleanupTemporaryFiles();
  optimizeGitRepository();
  runQualityAssessment();
  updateBranchInformation();
  generateMergeSummary();

  printResults();
  process.exit(0);
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = { main, CONFIG };
