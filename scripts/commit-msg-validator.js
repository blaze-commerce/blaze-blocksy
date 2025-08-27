#!/usr/bin/env node

/**
 * Commit Message Validator Script
 * Enforces conventional commit format and quality standards
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 */

const fs = require('fs');
const chalk = require('chalk');

// Configuration
const CONFIG = {
  emergencyBypass: process.env.EMERGENCY_BYPASS === 'true',
  maxSubjectLength: 50,
  maxBodyLineLength: 72,
  minDescriptionLength: 10
};

// Conventional commit types
const COMMIT_TYPES = {
  feat: 'A new feature',
  fix: 'A bug fix',
  docs: 'Documentation only changes',
  style: 'Changes that do not affect the meaning of the code (white-space, formatting, etc)',
  refactor: 'A code change that neither fixes a bug nor adds a feature',
  perf: 'A code change that improves performance',
  test: 'Adding missing tests or correcting existing tests',
  chore: 'Changes to the build process or auxiliary tools and libraries',
  ci: 'Changes to CI configuration files and scripts',
  build: 'Changes that affect the build system or external dependencies',
  revert: 'Reverts a previous commit'
};

// Breaking change indicators
const BREAKING_CHANGE_INDICATORS = [
  'BREAKING CHANGE:',
  'BREAKING-CHANGE:',
  '!'
];

/**
 * Print header
 */
function printHeader() {
  console.log(chalk.blue.bold('\n📝 Commit Message Validation'));
  console.log(chalk.gray('Enforcing conventional commit format standards'));
  console.log(chalk.gray('Reference: ALWAYS-comprehensive-code-review-standards.md\n'));
}

/**
 * Read commit message from file
 */
function readCommitMessage() {
  const commitMsgFile = process.argv[2] || '.git/COMMIT_EDITMSG';
  
  if (!fs.existsSync(commitMsgFile)) {
    console.error(chalk.red('❌ Commit message file not found'));
    process.exit(1);
  }

  try {
    const message = fs.readFileSync(commitMsgFile, 'utf8').trim();
    return message;
  } catch (error) {
    console.error(chalk.red('❌ Failed to read commit message:'), error.message);
    process.exit(1);
  }
}

/**
 * Parse conventional commit format
 */
function parseCommitMessage(message) {
  const lines = message.split('\n');
  const subject = lines[0];
  const body = lines.slice(2).join('\n').trim(); // Skip empty line after subject

  // Parse subject line: type(scope): description
  const conventionalRegex = /^(\w+)(\(.+\))?(!)?: (.+)$/;
  const match = subject.match(conventionalRegex);

  if (!match) {
    return {
      valid: false,
      subject,
      body,
      error: 'Subject line does not follow conventional commit format'
    };
  }

  const [, type, scope, breakingIndicator, description] = match;

  return {
    valid: true,
    subject,
    body,
    type,
    scope: scope ? scope.slice(1, -1) : null, // Remove parentheses
    description,
    isBreaking: !!breakingIndicator || body.includes('BREAKING CHANGE:'),
    hasBreakingChangeFooter: body.includes('BREAKING CHANGE:')
  };
}

/**
 * Validate commit type
 */
function validateCommitType(parsed) {
  const errors = [];

  if (!COMMIT_TYPES[parsed.type]) {
    errors.push({
      type: 'INVALID_TYPE',
      message: `Invalid commit type: "${parsed.type}"`,
      suggestion: `Valid types: ${Object.keys(COMMIT_TYPES).join(', ')}`
    });
  }

  return errors;
}

/**
 * Validate subject line
 */
function validateSubject(parsed) {
  const errors = [];

  // Check length
  if (parsed.subject.length > CONFIG.maxSubjectLength) {
    errors.push({
      type: 'SUBJECT_TOO_LONG',
      message: `Subject line too long (${parsed.subject.length}/${CONFIG.maxSubjectLength} chars)`,
      suggestion: 'Keep subject line under 50 characters'
    });
  }

  // Check description length
  if (parsed.description && parsed.description.length < CONFIG.minDescriptionLength) {
    errors.push({
      type: 'DESCRIPTION_TOO_SHORT',
      message: `Description too short (${parsed.description.length}/${CONFIG.minDescriptionLength} chars)`,
      suggestion: 'Provide a meaningful description of the change'
    });
  }

  // Check capitalization
  if (parsed.description && parsed.description[0] !== parsed.description[0].toLowerCase()) {
    errors.push({
      type: 'DESCRIPTION_CAPITALIZATION',
      message: 'Description should start with lowercase letter',
      suggestion: 'Use lowercase for the first letter of description'
    });
  }

  // Check for period at end
  if (parsed.description && parsed.description.endsWith('.')) {
    errors.push({
      type: 'DESCRIPTION_PERIOD',
      message: 'Description should not end with a period',
      suggestion: 'Remove the period at the end of description'
    });
  }

  return errors;
}

/**
 * Validate body format
 */
function validateBody(parsed) {
  const errors = [];

  if (!parsed.body) {
    return errors; // Body is optional
  }

  const bodyLines = parsed.body.split('\n');

  // Check line length
  bodyLines.forEach((line, index) => {
    if (line.length > CONFIG.maxBodyLineLength) {
      errors.push({
        type: 'BODY_LINE_TOO_LONG',
        message: `Body line ${index + 1} too long (${line.length}/${CONFIG.maxBodyLineLength} chars)`,
        suggestion: 'Wrap body lines at 72 characters'
      });
    }
  });

  return errors;
}

/**
 * Validate breaking changes
 */
function validateBreakingChanges(parsed) {
  const errors = [];

  // If breaking change indicator (!) is used, must have BREAKING CHANGE footer
  if (parsed.isBreaking && !parsed.hasBreakingChangeFooter) {
    errors.push({
      type: 'MISSING_BREAKING_CHANGE_FOOTER',
      message: 'Breaking change indicator used but no BREAKING CHANGE footer found',
      suggestion: 'Add "BREAKING CHANGE: <description>" in the commit body'
    });
  }

  return errors;
}

/**
 * Validate scope appropriateness
 */
function validateScope(parsed) {
  const errors = [];
  const warnings = [];

  // Common WordPress theme scopes
  const validScopes = [
    'auth', 'api', 'ui', 'css', 'js', 'php', 'theme', 'woocommerce', 
    'checkout', 'cart', 'product', 'user', 'admin', 'frontend', 
    'backend', 'database', 'config', 'docs', 'test', 'build', 'ci'
  ];

  if (parsed.scope && !validScopes.includes(parsed.scope.toLowerCase())) {
    warnings.push({
      type: 'UNCOMMON_SCOPE',
      message: `Uncommon scope: "${parsed.scope}"`,
      suggestion: `Consider using common scopes: ${validScopes.slice(0, 10).join(', ')}, etc.`
    });
  }

  return { errors, warnings };
}

/**
 * Check for issue references
 */
function validateIssueReferences(parsed) {
  const warnings = [];

  // Check for issue/ticket references
  const issuePatterns = [
    /#\d+/,           // GitHub issues
    /fixes?\s+#\d+/i, // Fixes #123
    /closes?\s+#\d+/i, // Closes #123
    /resolves?\s+#\d+/i // Resolves #123
  ];

  const hasIssueReference = issuePatterns.some(pattern => 
    pattern.test(parsed.subject) || pattern.test(parsed.body)
  );

  if (!hasIssueReference && parsed.type === 'fix') {
    warnings.push({
      type: 'MISSING_ISSUE_REFERENCE',
      message: 'Bug fix commits should reference the related issue',
      suggestion: 'Add "fixes #123" or similar issue reference'
    });
  }

  return warnings;
}

/**
 * Print validation results
 */
function printResults(parsed, errors, warnings) {
  console.log(chalk.blue.bold('📋 Commit Message Analysis:'));
  console.log(chalk.gray(`Type: ${parsed.type || 'unknown'}`));
  console.log(chalk.gray(`Scope: ${parsed.scope || 'none'}`));
  console.log(chalk.gray(`Breaking: ${parsed.isBreaking ? 'yes' : 'no'}`));
  console.log(chalk.gray(`Subject: ${parsed.subject}`));

  if (errors.length > 0) {
    console.log(chalk.red.bold('\n❌ Validation Errors:'));
    errors.forEach((error, index) => {
      console.log(chalk.red(`${index + 1}. ${error.message}`));
      if (error.suggestion) {
        console.log(chalk.yellow(`   💡 ${error.suggestion}`));
      }
    });
  }

  if (warnings.length > 0) {
    console.log(chalk.yellow.bold('\n⚠️ Warnings:'));
    warnings.forEach((warning, index) => {
      console.log(chalk.yellow(`${index + 1}. ${warning.message}`));
      if (warning.suggestion) {
        console.log(chalk.gray(`   💡 ${warning.suggestion}`));
      }
    });
  }

  if (errors.length === 0) {
    console.log(chalk.green.bold('\n✅ Commit message validation passed'));
  }
}

/**
 * Print commit format help
 */
function printHelp() {
  console.log(chalk.blue.bold('\n📖 Conventional Commit Format:'));
  console.log(chalk.gray('type(scope): description\n'));
  console.log(chalk.gray('[optional body]\n'));
  console.log(chalk.gray('[optional footer(s)]'));

  console.log(chalk.blue.bold('\n📝 Valid Types:'));
  Object.entries(COMMIT_TYPES).forEach(([type, description]) => {
    console.log(chalk.gray(`${type.padEnd(10)} - ${description}`));
  });

  console.log(chalk.blue.bold('\n💡 Examples:'));
  console.log(chalk.green('feat(auth): add OAuth2 integration'));
  console.log(chalk.green('fix(cart): resolve checkout calculation error'));
  console.log(chalk.green('docs(readme): update installation instructions'));
  console.log(chalk.green('feat(api)!: change user authentication method'));
}

/**
 * Handle emergency bypass
 */
function handleEmergencyBypass() {
  if (CONFIG.emergencyBypass) {
    console.log(chalk.red.bold('\n🚨 EMERGENCY BYPASS ACTIVATED'));
    console.log(chalk.yellow('⚠️ Commit message validation bypassed'));
    console.log(chalk.gray('Set EMERGENCY_BYPASS=false to re-enable validation\n'));
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

  const commitMessage = readCommitMessage();
  
  if (!commitMessage) {
    console.error(chalk.red('❌ Empty commit message'));
    printHelp();
    process.exit(1);
  }

  const parsed = parseCommitMessage(commitMessage);
  
  if (!parsed.valid) {
    console.error(chalk.red(`❌ ${parsed.error}`));
    printHelp();
    process.exit(1);
  }

  // Run all validations
  const errors = [
    ...validateCommitType(parsed),
    ...validateSubject(parsed),
    ...validateBody(parsed),
    ...validateBreakingChanges(parsed)
  ];

  const scopeValidation = validateScope(parsed);
  const warnings = [
    ...scopeValidation.warnings,
    ...validateIssueReferences(parsed)
  ];

  errors.push(...scopeValidation.errors);

  printResults(parsed, errors, warnings);

  if (errors.length > 0) {
    console.log(chalk.red.bold('\n🚫 Commit message validation failed'));
    console.log(chalk.yellow('💡 Fix the errors above or use EMERGENCY_BYPASS=true for critical fixes'));
    printHelp();
    process.exit(1);
  } else {
    console.log(chalk.green.bold('\n✅ Commit message validation passed\n'));
    process.exit(0);
  }
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = { main, parseCommitMessage, CONFIG };
