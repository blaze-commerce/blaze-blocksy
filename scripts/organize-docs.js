#!/usr/bin/env node

/**
 * Automated Markdown Documentation Organizer
 * Intelligently categorizes and moves .md files to appropriate /docs subdirectories
 * Integrates with existing BlazeCommerce documentation workflow
 */

const fs = require('fs');
const path = require('path');
const chalk = require('chalk');
const { execSync } = require('child_process');

/**
 * Validate configuration object structure
 */
function validateConfig(config) {
  if (!config || typeof config !== 'object') {
    return {};
  }

  // Validate manualCategorization structure
  if (config.manualCategorization && typeof config.manualCategorization !== 'object') {
    console.warn(chalk.yellow('⚠️ Invalid manualCategorization format, using defaults'));
    config.manualCategorization = {};
  }

  // Validate arrays
  ['rootExceptions', 'skipFiles', 'excludeDirectories'].forEach(key => {
    if (config[key] && !Array.isArray(config[key])) {
      console.warn(chalk.yellow(`⚠️ Invalid ${key} format, using defaults`));
      config[key] = [];
    }
  });

  return config;
}

// Load manual categorization configuration
let MANUAL_CONFIG = {};
try {
  if (fs.existsSync('.docs-organization-config.json')) {
    const configContent = fs.readFileSync('.docs-organization-config.json', 'utf8').trim();
    if (configContent) {
      MANUAL_CONFIG = validateConfig(JSON.parse(configContent));
    }
  }
} catch (error) {
  console.warn(chalk.yellow('⚠️ Could not load manual configuration, using defaults'));
  MANUAL_CONFIG = {};
}

// Configuration for documentation organization
const DOC_CONFIG = {
  // Files that should NEVER be moved from root
  rootExceptions: MANUAL_CONFIG.rootExceptions || [
    'README.md',
    'CHANGELOG.md',
    'LICENSE.md',
    'CONTRIBUTING.md',
    'CODE_OF_CONDUCT.md'
  ],
  
  // Directory structure for organized documentation
  categories: {
    'development': {
      path: 'docs/development',
      patterns: [
        /^dev/i,
        /development/i,
        /contributing/i,
        /coding/i,
        /standards/i,
        /review/i,
        /quality/i,
        /git[-_]/i,
        /commit/i,
        /push/i,
        /branch/i,
        /workflow/i,
        /husky/i,
        /hooks/i
      ],
      keywords: ['development', 'coding', 'standards', 'review', 'quality', 'contributing', 'workflow', 'git', 'commit', 'branch', 'husky', 'hooks']
    },
    'guides': {
      path: 'docs/guides',
      patterns: [
        /^guide[-_]/i,
        /[-_]guide[-_]/i,
        /tutorial/i,
        /how[-_]to/i,
        /setup/i,
        /installation/i,
        /comprehensive/i,
        /implementation/i,
        /customization/i,
        /pickup/i,
        /account/i
      ],
      keywords: ['tutorial', 'guide', 'how to', 'setup', 'installation', 'getting started', 'comprehensive', 'implementation', 'customization']
    },
    'api': {
      path: 'docs/api',
      patterns: [
        /^api[-_]/i,
        /[-_]api[-_]/i,
        /endpoint/i,
        /rest[-_]/i,
        /graphql/i,
        /credentials/i,
        /database/i
      ],
      keywords: ['endpoint', 'api', 'rest', 'graphql', 'swagger', 'openapi', 'postman', 'credentials', 'database']
    },
    'architecture': {
      path: 'docs/architecture',
      patterns: [
        /architecture/i,
        /design/i,
        /structure/i,
        /system/i,
        /technical/i,
        /analysis/i
      ],
      keywords: ['architecture', 'design', 'structure', 'system', 'technical', 'diagram', 'analysis']
    },
    'deployment': {
      path: 'docs/deployment',
      patterns: [
        /deploy/i,
        /release/i,
        /build/i,
        /ci[-_]cd/i,
        /pipeline/i,
        /automation/i,
        /merge/i,
        /version/i,
        /bump/i,
        /changelog/i
      ],
      keywords: ['deployment', 'release', 'build', 'ci/cd', 'pipeline', 'automation', 'docker', 'merge', 'version', 'changelog']
    },
    'testing': {
      path: 'docs/testing',
      patterns: [
        /^test/i,
        /testing/i,
        /spec/i,
        /qa/i,
        /coverage/i
      ],
      keywords: ['testing', 'test', 'spec', 'qa', 'coverage', 'unit', 'integration', 'e2e']
    },
    'security': {
      path: 'docs/security',
      patterns: [
        /security/i,
        /auth/i,
        /permission/i,
        /vulnerability/i
      ],
      keywords: ['security', 'authentication', 'authorization', 'vulnerability', 'encryption']
    },
    'features': {
      path: 'docs/features',
      patterns: [
        /thank[-_]you/i,
        /checkout/i,
        /blaze[-_]commerce/i,
        /customization/i,
        /widget/i,
        /sidebar/i
      ],
      keywords: ['thank you', 'checkout', 'blaze commerce', 'customization', 'widget', 'sidebar', 'feature']
    },
    'performance': {
      path: 'docs/performance',
      patterns: [
        /performance/i,
        /optimization/i,
        /speed/i,
        /benchmark/i
      ],
      keywords: ['performance', 'optimization', 'speed', 'benchmark', 'lighthouse']
    }
  },
  
  // Default category for uncategorized files
  defaultCategory: 'docs/general',
  
  // Directories to scan for .md files
  scanDirectories: [
    '.',
    'performance-optimizations',
    'scripts',
    'security-fixes',
    'tests'
  ],

  // Directories to exclude from scanning
  excludeDirectories: [
    'node_modules',
    '.git',
    'vendor',
    'coverage',
    'dist',
    'build',
    '.augment'
  ]
};

/**
 * Print header
 */
function printHeader() {
  console.log(chalk.blue.bold('\n📁 Markdown Documentation Organizer'));
  console.log(chalk.gray('Intelligently organizing .md files into structured directories'));
  console.log(chalk.gray('BlazeCommerce Documentation Management System\n'));
}

/**
 * Ensure all documentation directories exist
 */
function ensureDirectoriesExist() {
  const directories = [
    ...Object.values(DOC_CONFIG.categories).map(cat => cat.path),
    DOC_CONFIG.defaultCategory
  ];

  directories.forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
      console.log(chalk.green(`✅ Created directory: ${dir}`));
    }
  });
}

/**
 * Find all .md files in the repository
 */
function findMarkdownFiles() {
  const markdownFiles = [];
  
  function scanDirectory(dir, relativePath = '') {
    if (DOC_CONFIG.excludeDirectories.some(exclude => dir.includes(exclude))) {
      return;
    }

    // Additional check to exclude .augment directory and subdirectories
    if (relativePath.startsWith('.augment') || dir.includes('.augment')) {
      return;
    }

    try {
      const items = fs.readdirSync(dir);

      items.forEach(item => {
        const fullPath = path.join(dir, item);
        const relativeFilePath = path.join(relativePath, item);

        // Skip .augment directory entirely
        if (item === '.augment' || relativeFilePath.startsWith('.augment')) {
          return;
        }

        if (fs.statSync(fullPath).isDirectory()) {
          scanDirectory(fullPath, relativeFilePath);
        } else if (item.endsWith('.md')) {
          markdownFiles.push({
            fullPath,
            relativePath: relativeFilePath,
            fileName: item,
            directory: relativePath || '.'
          });
        }
      });
    } catch (error) {
      console.warn(chalk.yellow(`⚠️ Could not scan directory: ${dir}`));
    }
  }
  
  DOC_CONFIG.scanDirectories.forEach(dir => {
    if (fs.existsSync(dir)) {
      scanDirectory(dir, dir === '.' ? '' : dir);
    }
  });
  
  return markdownFiles;
}

/**
 * Analyze file content to determine category
 */
function analyzeFileContent(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf8').toLowerCase();
    const fileName = path.basename(filePath).toLowerCase();

    // Priority-based categorization - check specific categories first
    const categoryPriority = [
      'features',
      'api',
      'testing',
      'security',
      'performance',
      'deployment',
      'architecture',
      'guides',
      'development'
    ];

    for (const categoryName of categoryPriority) {
      const category = DOC_CONFIG.categories[categoryName];

      // Check filename patterns first (higher priority)
      if (category.patterns.some(pattern => pattern.test(fileName))) {
        return categoryName;
      }

      // Check content keywords (lower priority)
      const keywordMatches = category.keywords.filter(keyword =>
        content.includes(keyword.toLowerCase())
      ).length;

      // Require multiple keyword matches for development category to avoid over-categorization
      if (categoryName === 'development' && keywordMatches < 2) {
        continue;
      }

      if (keywordMatches > 0) {
        return categoryName;
      }
    }

    return null; // No category match found
  } catch (error) {
    console.warn(chalk.yellow(`⚠️ Could not analyze content of: ${filePath}`));
    return null;
  }
}

/**
 * Categorize a markdown file
 */
function categorizeFile(file) {
  // Check if file should stay in root
  if (DOC_CONFIG.rootExceptions.includes(file.fileName)) {
    return { category: 'root', reason: 'Root exception' };
  }

  // Check manual skip list
  if (MANUAL_CONFIG.skipFiles && MANUAL_CONFIG.skipFiles.includes(file.relativePath)) {
    return { category: 'skip', reason: 'Manual skip configuration' };
  }

  // Skip files already in docs subdirectories
  if (file.relativePath.startsWith('docs/') && file.directory !== 'docs') {
    return { category: 'skip', reason: 'Already in docs subdirectory' };
  }

  // Check manual categorization first
  if (MANUAL_CONFIG.manualCategorization) {
    for (const [categoryName, fileList] of Object.entries(MANUAL_CONFIG.manualCategorization)) {
      if (fileList.includes(file.fileName)) {
        const targetPath = DOC_CONFIG.categories[categoryName]?.path || `docs/${categoryName}`;
        return {
          category: categoryName,
          reason: 'Manual categorization',
          targetPath: targetPath
        };
      }
    }
  }

  // Analyze content for categorization
  const contentCategory = analyzeFileContent(file.fullPath);
  if (contentCategory) {
    return {
      category: contentCategory,
      reason: 'Content analysis match',
      targetPath: DOC_CONFIG.categories[contentCategory].path
    };
  }

  // Default category for uncategorized files
  if (file.directory === '.' || file.directory === 'docs') {
    return {
      category: 'general',
      reason: 'Default categorization',
      targetPath: DOC_CONFIG.defaultCategory
    };
  }

  return { category: 'skip', reason: 'No categorization needed' };
}

/**
 * Move file to target directory with enhanced error handling
 */
function moveFile(file, targetPath) {
  try {
    // Validate inputs
    if (!file || !file.fullPath || !file.fileName) {
      console.error(chalk.red(`❌ Invalid file object provided`));
      return false;
    }

    if (!targetPath || typeof targetPath !== 'string') {
      console.error(chalk.red(`❌ Invalid target path provided`));
      return false;
    }

    const targetFile = path.join(targetPath, file.fileName);

    // Check if source file still exists
    if (!fs.existsSync(file.fullPath)) {
      console.error(chalk.red(`❌ Source file no longer exists: ${file.fullPath}`));
      return false;
    }

    // Check if target file already exists
    if (fs.existsSync(targetFile)) {
      console.log(chalk.yellow(`⚠️ Target file already exists: ${targetFile}`));
      return false;
    }

    // Create target directory if it doesn't exist
    if (!fs.existsSync(targetPath)) {
      fs.mkdirSync(targetPath, { recursive: true });
    }

    // Verify we can write to target directory
    try {
      fs.accessSync(targetPath, fs.constants.W_OK);
    } catch (accessError) {
      console.error(chalk.red(`❌ Cannot write to target directory: ${targetPath}`));
      return false;
    }

    // Move the file
    fs.renameSync(file.fullPath, targetFile);
    console.log(chalk.green(`✅ Moved: ${file.relativePath} → ${targetFile}`));

    return true;
  } catch (error) {
    console.error(chalk.red(`❌ Failed to move ${file.relativePath}: ${error.message}`));

    // Provide specific error guidance
    if (error.code === 'ENOENT') {
      console.error(chalk.red(`   Source file not found or target directory cannot be created`));
    } else if (error.code === 'EACCES') {
      console.error(chalk.red(`   Permission denied - check file/directory permissions`));
    } else if (error.code === 'EEXIST') {
      console.error(chalk.red(`   Target file already exists`));
    }

    return false;
  }
}

/**
 * Organize all markdown files with performance optimization
 */
function organizeMarkdownFiles(dryRun = false) {
  console.log(chalk.blue('🔍 Scanning for markdown files...'));

  const markdownFiles = findMarkdownFiles();
  console.log(chalk.blue(`📄 Found ${markdownFiles.length} markdown files`));

  if (markdownFiles.length === 0) {
    console.log(chalk.gray('No markdown files found to organize'));
    return { moved: 0, skipped: 0, errors: 0 };
  }

  // Early optimization: check if any files actually need organization
  const filesToOrganize = markdownFiles.filter(file => {
    const categorization = categorizeFile(file);
    return categorization.category !== 'root' && categorization.category !== 'skip';
  });

  if (filesToOrganize.length === 0) {
    console.log(chalk.green('✅ All files already properly organized'));
    return { moved: 0, skipped: markdownFiles.length, errors: 0, categories: {} };
  }

  console.log(chalk.blue(`📋 ${filesToOrganize.length} files need organization`));
  
  const results = {
    moved: 0,
    skipped: 0,
    errors: 0,
    categories: {}
  };
  
  console.log(chalk.blue('\n📋 Categorizing files...'));
  
  markdownFiles.forEach(file => {
    const categorization = categorizeFile(file);
    
    if (categorization.category === 'root' || categorization.category === 'skip') {
      console.log(chalk.gray(`⏭️ Skipping: ${file.relativePath} (${categorization.reason})`));
      results.skipped++;
      return;
    }
    
    if (!results.categories[categorization.category]) {
      results.categories[categorization.category] = [];
    }
    results.categories[categorization.category].push(file);
    
    if (!dryRun && categorization.targetPath) {
      if (moveFile(file, categorization.targetPath)) {
        results.moved++;
      } else {
        results.errors++;
      }
    } else if (dryRun) {
      console.log(chalk.cyan(`📋 Would move: ${file.relativePath} → ${categorization.targetPath}/${file.fileName}`));
    }
  });
  
  return results;
}

/**
 * Print organization results
 */
function printResults(results, dryRun = false) {
  console.log(chalk.blue.bold('\n📊 Organization Results:'));
  
  if (dryRun) {
    console.log(chalk.cyan('🔍 DRY RUN - No files were actually moved'));
  }
  
  console.log(chalk.green(`✅ Files ${dryRun ? 'to be moved' : 'moved'}: ${results.moved || Object.values(results.categories).reduce((sum, files) => sum + files.length, 0)}`));
  console.log(chalk.gray(`⏭️ Files skipped: ${results.skipped}`));
  
  if (results.errors > 0) {
    console.log(chalk.red(`❌ Errors: ${results.errors}`));
  }
  
  if (Object.keys(results.categories).length > 0) {
    console.log(chalk.blue.bold('\n📁 Files by Category:'));
    Object.entries(results.categories).forEach(([category, files]) => {
      console.log(chalk.blue(`\n${category.toUpperCase()}:`));
      files.forEach(file => {
        console.log(chalk.blue(`  • ${file.relativePath}`));
      });
    });
  }
}

/**
 * Main execution function
 */
function main() {
  const args = process.argv.slice(2);
  const dryRun = args.includes('--dry-run') || args.includes('-d');
  const help = args.includes('--help') || args.includes('-h');
  
  if (help) {
    console.log(chalk.blue.bold('📁 Markdown Documentation Organizer'));
    console.log(chalk.gray('\nUsage: npm run docs:organize [options]'));
    console.log(chalk.gray('\nOptions:'));
    console.log(chalk.gray('  --dry-run, -d    Show what would be moved without actually moving files'));
    console.log(chalk.gray('  --help, -h       Show this help message'));
    console.log(chalk.gray('\nExamples:'));
    console.log(chalk.gray('  npm run docs:organize              # Organize all markdown files'));
    console.log(chalk.gray('  npm run docs:organize -- --dry-run # Preview organization without moving files'));
    return;
  }
  
  printHeader();
  
  if (dryRun) {
    console.log(chalk.cyan('🔍 Running in DRY RUN mode - no files will be moved\n'));
  }
  
  try {
    ensureDirectoriesExist();
    const results = organizeMarkdownFiles(dryRun);
    printResults(results, dryRun);
    
    if (!dryRun && results.moved > 0) {
      console.log(chalk.green.bold('\n✅ Documentation organization completed successfully!'));
      console.log(chalk.yellow('💡 Consider running "git add ." to stage the organized files'));
    } else if (dryRun) {
      console.log(chalk.cyan.bold('\n🔍 Dry run completed. Use "npm run docs:organize" to actually move files.'));
    }
    
  } catch (error) {
    console.error(chalk.red.bold('\n❌ Documentation organization failed:'), error.message);
    process.exit(1);
  }
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = {
  organizeMarkdownFiles,
  categorizeFile,
  findMarkdownFiles,
  DOC_CONFIG
};
