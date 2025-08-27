#!/usr/bin/env node

/**
 * Branch Naming Convention Validator
 * Enforces standardized branch naming patterns
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 */

const { execSync } = require('child_process');
const fs = require('fs');
const chalk = require('chalk');

// Default branch naming configuration
const DEFAULT_CONFIG = {
  patterns: {
    feature: {
      regex: /^feature\/([a-z0-9-]+\/)?[a-z0-9-]+$/,
      description: 'Feature branches',
      examples: ['feature/user-authentication', 'feature/PROJ-123-user-auth', 'feature/auth/oauth-integration']
    },
    bugfix: {
      regex: /^(bugfix|fix)\/([a-z0-9-]+\/)?[a-z0-9-]+$/,
      description: 'Bug fix branches',
      examples: ['bugfix/login-error', 'fix/PROJ-456-cart-calculation', 'bugfix/checkout/payment-validation']
    },
    hotfix: {
      regex: /^hotfix\/([a-z0-9-]+\/)?[a-z0-9-]+$/,
      description: 'Hotfix branches',
      examples: ['hotfix/security-patch', 'hotfix/PROJ-789-critical-bug', 'hotfix/prod/database-connection']
    },
    release: {
      regex: /^release\/\d+\.\d+\.\d+(-[a-z0-9-]+)?$/,
      description: 'Release branches',
      examples: ['release/1.2.0', 'release/2.0.0-beta', 'release/1.5.3-rc1']
    },
    chore: {
      regex: /^chore\/([a-z0-9-]+\/)?[a-z0-9-]+$/,
      description: 'Maintenance and chore branches',
      examples: ['chore/update-dependencies', 'chore/PROJ-101-cleanup', 'chore/docs/update-readme']
    },
    docs: {
      regex: /^docs\/([a-z0-9-]+\/)?[a-z0-9-]+$/,
      description: 'Documentation branches',
      examples: ['docs/api-documentation', 'docs/PROJ-202-user-guide', 'docs/setup/installation-guide']
    },
    test: {
      regex: /^test\/([a-z0-9-]+\/)?[a-z0-9-]+$/,
      description: 'Testing branches',
      examples: ['test/unit-tests', 'test/PROJ-303-integration', 'test/e2e/checkout-flow']
    },
    refactor: {
      regex: /^refactor\/([a-z0-9-]+\/)?[a-z0-9-]+$/,
      description: 'Refactoring branches',
      examples: ['refactor/user-service', 'refactor/PROJ-404-auth-module', 'refactor/api/response-format']
    }
  },
  protected: {
    regex: /^(main|master|develop|dev|staging|production|prod)$/,
    description: 'Protected branches',
    examples: ['main', 'master', 'develop', 'dev', 'staging', 'production']
  },
  allowCustomPatterns: true,
  emergencyBypass: process.env.EMERGENCY_BYPASS === 'true',
  strictMode: process.env.STRICT_BRANCH_NAMING === 'true'
};

// Load custom configuration if available
function loadConfiguration() {
  const configPath = '.branch-naming.json';
  
  if (fs.existsSync(configPath)) {
    try {
      const customConfig = JSON.parse(fs.readFileSync(configPath, 'utf8'));
      return { ...DEFAULT_CONFIG, ...customConfig };
    } catch (error) {
      console.warn(chalk.yellow('⚠️ Invalid branch naming configuration, using defaults'));
    }
  }
  
  return DEFAULT_CONFIG;
}

/**
 * Print header
 */
function printHeader() {
  console.log(chalk.blue.bold('\n🌿 Branch Naming Convention Validator'));
  console.log(chalk.gray('Enforcing standardized branch naming patterns'));
  console.log(chalk.gray('Reference: ALWAYS-comprehensive-code-review-standards.md\n'));
}

/**
 * Get current branch name
 */
function getCurrentBranch() {
  try {
    const branch = execSync('git branch --show-current', { encoding: 'utf8' }).trim();
    return branch;
  } catch (error) {
    console.error(chalk.red('❌ Failed to get current branch:'), error.message);
    return null;
  }
}

/**
 * Get all local branches
 */
function getAllBranches() {
  try {
    const output = execSync('git branch --format="%(refname:short)"', { encoding: 'utf8' });
    return output.trim().split('\n').filter(branch => branch.length > 0);
  } catch (error) {
    console.error(chalk.red('❌ Failed to get branches:'), error.message);
    return [];
  }
}

/**
 * Validate branch name against patterns
 */
function validateBranchName(branchName, config) {
  const results = {
    valid: false,
    pattern: null,
    suggestions: [],
    errors: []
  };

  // Check if it's a protected branch (always valid)
  if (config.protected.regex.test(branchName)) {
    results.valid = true;
    results.pattern = 'protected';
    return results;
  }

  // Check against defined patterns
  for (const [patternName, pattern] of Object.entries(config.patterns)) {
    if (pattern.regex.test(branchName)) {
      results.valid = true;
      results.pattern = patternName;
      return results;
    }
  }

  // If no pattern matches, generate suggestions
  results.errors.push(`Branch name "${branchName}" does not follow naming conventions`);
  
  // Generate suggestions based on branch name content
  const suggestions = generateSuggestions(branchName, config);
  results.suggestions = suggestions;

  return results;
}

/**
 * Generate naming suggestions
 */
function generateSuggestions(branchName, config) {
  const suggestions = [];
  const cleanName = branchName.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-');
  
  // Common prefixes to suggest
  const commonPrefixes = ['feature', 'bugfix', 'hotfix', 'chore', 'docs'];
  
  commonPrefixes.forEach(prefix => {
    suggestions.push(`${prefix}/${cleanName}`);
  });

  // If it looks like a ticket number, suggest with ticket format
  const ticketMatch = branchName.match(/([A-Z]+-\d+)/i);
  if (ticketMatch) {
    const ticket = ticketMatch[1].toUpperCase();
    const description = cleanName.replace(ticket.toLowerCase(), '').replace(/^-+|-+$/g, '');
    if (description) {
      suggestions.push(`feature/${ticket.toLowerCase()}-${description}`);
      suggestions.push(`bugfix/${ticket.toLowerCase()}-${description}`);
    }
  }

  return suggestions.slice(0, 5); // Limit to 5 suggestions
}

/**
 * Validate current branch
 */
function validateCurrentBranch() {
  const config = loadConfiguration();
  const currentBranch = getCurrentBranch();
  
  if (!currentBranch) {
    console.log(chalk.yellow('⚠️ Could not determine current branch'));
    return true; // Don't block if we can't determine branch
  }

  console.log(chalk.blue(`🔍 Validating current branch: ${currentBranch}`));
  
  const validation = validateBranchName(currentBranch, config);
  
  if (validation.valid) {
    console.log(chalk.green(`✅ Branch name follows ${validation.pattern} pattern`));
    return true;
  } else {
    console.log(chalk.red('❌ Branch name validation failed'));
    printValidationErrors(validation);
    return false;
  }
}

/**
 * Validate all branches
 */
function validateAllBranches() {
  const config = loadConfiguration();
  const branches = getAllBranches();
  
  console.log(chalk.blue(`🔍 Validating ${branches.length} branches...\n`));
  
  let validBranches = 0;
  let invalidBranches = 0;
  const issues = [];

  branches.forEach(branch => {
    const validation = validateBranchName(branch, config);
    
    if (validation.valid) {
      validBranches++;
      console.log(chalk.green(`✅ ${branch} (${validation.pattern})`));
    } else {
      invalidBranches++;
      console.log(chalk.red(`❌ ${branch}`));
      issues.push({ branch, validation });
    }
  });

  console.log(chalk.blue.bold('\n📊 Validation Summary:'));
  console.log(chalk.green(`✅ Valid branches: ${validBranches}`));
  console.log(chalk.red(`❌ Invalid branches: ${invalidBranches}`));

  if (issues.length > 0) {
    console.log(chalk.red.bold('\n🚨 Issues Found:'));
    issues.forEach(({ branch, validation }) => {
      console.log(chalk.red(`\n${branch}:`));
      printValidationErrors(validation);
    });
  }

  return invalidBranches === 0;
}

/**
 * Print validation errors and suggestions
 */
function printValidationErrors(validation) {
  validation.errors.forEach(error => {
    console.log(chalk.red(`  • ${error}`));
  });

  if (validation.suggestions.length > 0) {
    console.log(chalk.yellow('\n💡 Suggested names:'));
    validation.suggestions.forEach(suggestion => {
      console.log(chalk.yellow(`  • ${suggestion}`));
    });
  }
}

/**
 * Print naming conventions help
 */
function printNamingConventions() {
  const config = loadConfiguration();
  
  console.log(chalk.blue.bold('\n📖 Branch Naming Conventions:'));
  
  Object.entries(config.patterns).forEach(([name, pattern]) => {
    console.log(chalk.blue(`\n${pattern.description}:`));
    pattern.examples.forEach(example => {
      console.log(chalk.gray(`  • ${example}`));
    });
  });

  console.log(chalk.blue(`\n${config.protected.description}:`));
  config.protected.examples.forEach(example => {
    console.log(chalk.gray(`  • ${example}`));
  });

  console.log(chalk.blue.bold('\n📝 Naming Guidelines:'));
  console.log(chalk.gray('• Use lowercase letters, numbers, and hyphens only'));
  console.log(chalk.gray('• Start with a descriptive prefix (feature/, bugfix/, etc.)'));
  console.log(chalk.gray('• Include ticket/issue ID when applicable'));
  console.log(chalk.gray('• Use brief but descriptive names'));
  console.log(chalk.gray('• Separate words with hyphens'));
  console.log(chalk.gray('• Avoid special characters and spaces'));

  console.log(chalk.blue.bold('\n🔧 Configuration:'));
  console.log(chalk.gray('• Create .branch-naming.json to customize patterns'));
  console.log(chalk.gray('• Set STRICT_BRANCH_NAMING=true for strict enforcement'));
  console.log(chalk.gray('• Use EMERGENCY_BYPASS=true for emergency situations'));
}

/**
 * Create default configuration file
 */
function createConfigFile() {
  const configPath = '.branch-naming.json';
  
  if (fs.existsSync(configPath)) {
    console.log(chalk.yellow('⚠️ Configuration file already exists'));
    return;
  }

  const configContent = JSON.stringify(DEFAULT_CONFIG, null, 2);
  fs.writeFileSync(configPath, configContent);
  
  console.log(chalk.green(`✅ Created configuration file: ${configPath}`));
  console.log(chalk.gray('Edit this file to customize branch naming patterns'));
}

/**
 * Handle emergency bypass
 */
function handleEmergencyBypass() {
  if (DEFAULT_CONFIG.emergencyBypass) {
    console.log(chalk.red.bold('\n🚨 EMERGENCY BYPASS ACTIVATED'));
    console.log(chalk.yellow('⚠️ Branch naming validation bypassed'));
    console.log(chalk.gray('Set EMERGENCY_BYPASS=false to re-enable validation\n'));
    return true;
  }
  return false;
}

/**
 * Main execution
 */
function main() {
  const args = process.argv.slice(2);
  const command = args[0];

  printHeader();

  if (handleEmergencyBypass()) {
    process.exit(0);
  }

  switch (command) {
    case 'current':
    case 'validate':
      const isValid = validateCurrentBranch();
      process.exit(isValid ? 0 : 1);
      
    case 'all':
      const allValid = validateAllBranches();
      process.exit(allValid ? 0 : 1);
      
    case 'help':
    case '--help':
    case '-h':
      printNamingConventions();
      process.exit(0);
      
    case 'config':
      createConfigFile();
      process.exit(0);
      
    default:
      // Default: validate current branch
      const defaultValid = validateCurrentBranch();
      process.exit(defaultValid ? 0 : 1);
  }
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = {
  validateBranchName,
  validateCurrentBranch,
  validateAllBranches,
  loadConfiguration,
  DEFAULT_CONFIG
};
