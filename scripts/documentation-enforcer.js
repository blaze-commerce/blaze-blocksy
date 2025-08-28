#!/usr/bin/env node

/**
 * Documentation Enforcement and Generation Script
 * Automatically enforces documentation requirements and generates templates
 * Integrates with ALWAYS-comprehensive-code-review-standards.md
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const chalk = require('chalk');

// Default documentation configuration
const DEFAULT_CONFIG = {
  enforcement: {
    enabled: true,
    blockCommitsWithoutDocs: true,
    autoGenerateTemplates: true,
    priority: 2, // Priority 2 violation
    strictMode: false
  },
  directories: {
    source: ['src/', 'lib/', 'components/', 'modules/', 'services/', 'utils/'],
    api: ['routes/', 'controllers/', 'api/', 'endpoints/'],
    config: ['config/', 'settings/', '.env*', 'docker*', 'package.json', 'composer.json'],
    database: ['migrations/', 'models/', 'schemas/', 'database/'],
    docs: 'docs/',
    templates: 'docs/templates/'
  },
  fileTypes: {
    requireDocs: ['.js', '.ts', '.php', '.py', '.rb', '.go', '.rs', '.java', '.kt'],
    configFiles: ['.json', '.yml', '.yaml', '.toml', '.ini', '.env'],
    ignore: ['.test.js', '.spec.js', '.min.js', '.d.ts']
  },
  documentation: {
    minWordCount: 50,
    requiredSections: ['Overview', 'Usage', 'Examples'],
    optionalSections: ['Configuration', 'API Reference', 'Troubleshooting'],
    autoGenerate: {
      extractComments: true,
      extractFunctionSignatures: true,
      extractClassDefinitions: true,
      extractApiEndpoints: true
    }
  },
  templates: {
    feature: 'feature-template.md',
    api: 'api-template.md',
    component: 'component-template.md',
    configuration: 'configuration-template.md',
    database: 'database-template.md'
  },
  emergencyBypass: process.env.EMERGENCY_BYPASS === 'true'
};

// Load custom configuration
function loadConfiguration() {
  const configPath = '.documentation-config.json';
  
  if (fs.existsSync(configPath)) {
    try {
      const customConfig = JSON.parse(fs.readFileSync(configPath, 'utf8'));
      return mergeDeep(DEFAULT_CONFIG, customConfig);
    } catch (error) {
      console.warn(chalk.yellow('⚠️ Invalid documentation configuration, using defaults'));
    }
  }
  
  return DEFAULT_CONFIG;
}

// Deep merge configuration objects
function mergeDeep(target, source) {
  const output = Object.assign({}, target);
  if (isObject(target) && isObject(source)) {
    Object.keys(source).forEach(key => {
      if (isObject(source[key])) {
        if (!(key in target))
          Object.assign(output, { [key]: source[key] });
        else
          output[key] = mergeDeep(target[key], source[key]);
      } else {
        Object.assign(output, { [key]: source[key] });
      }
    });
  }
  return output;
}

function isObject(item) {
  return item && typeof item === 'object' && !Array.isArray(item);
}

/**
 * Print header
 */
function printHeader() {
  console.log(chalk.blue.bold('\n📚 Documentation Enforcement & Generation'));
  console.log(chalk.gray('Ensuring code changes are properly documented'));
  console.log(chalk.gray('Reference: ALWAYS-comprehensive-code-review-standards.md\n'));
}

/**
 * Get staged files for documentation analysis
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
 * Analyze files to determine documentation requirements
 */
function analyzeDocumentationRequirements(files, config) {
  const requirements = {
    features: [],
    apis: [],
    configurations: [],
    databases: [],
    components: [],
    needsDocumentation: false
  };

  files.forEach(file => {
    // Skip ignored file types
    if (config.fileTypes.ignore.some(ignore => file.includes(ignore))) {
      return;
    }

    // Check if file requires documentation
    const requiresDoc = config.fileTypes.requireDocs.some(ext => file.endsWith(ext));
    if (!requiresDoc) return;

    // Categorize files by type
    if (config.directories.source.some(dir => file.startsWith(dir))) {
      if (isNewFeature(file)) {
        requirements.features.push(file);
        requirements.needsDocumentation = true;
      } else if (isComponent(file)) {
        requirements.components.push(file);
        requirements.needsDocumentation = true;
      }
    }

    if (config.directories.api.some(dir => file.startsWith(dir))) {
      requirements.apis.push(file);
      requirements.needsDocumentation = true;
    }

    if (config.directories.config.some(pattern => 
      file.startsWith(pattern) || file.includes(pattern))) {
      requirements.configurations.push(file);
      requirements.needsDocumentation = true;
    }

    if (config.directories.database.some(dir => file.startsWith(dir))) {
      requirements.databases.push(file);
      requirements.needsDocumentation = true;
    }
  });

  return requirements;
}

/**
 * Check if file represents a new feature
 */
function isNewFeature(file) {
  try {
    // Check if file is new (not in previous commit)
    execSync(`git show HEAD:${file}`, { stdio: 'pipe' });
    return false; // File exists in previous commit
  } catch (error) {
    return true; // File is new
  }
}

/**
 * Check if file is a component
 */
function isComponent(file) {
  const componentPatterns = [
    /components?\//i,
    /widgets?\//i,
    /blocks?\//i,
    /modules?\//i
  ];
  
  return componentPatterns.some(pattern => pattern.test(file));
}

/**
 * Check existing documentation for files
 */
function checkExistingDocumentation(requirements, config) {
  const missing = {
    features: [],
    apis: [],
    configurations: [],
    databases: [],
    components: []
  };

  // Check for feature documentation
  requirements.features.forEach(file => {
    const docPath = generateDocumentationPath(file, 'feature', config);
    if (!fs.existsSync(docPath)) {
      missing.features.push({ file, expectedDoc: docPath });
    }
  });

  // Check for API documentation
  requirements.apis.forEach(file => {
    const docPath = generateDocumentationPath(file, 'api', config);
    if (!fs.existsSync(docPath)) {
      missing.apis.push({ file, expectedDoc: docPath });
    }
  });

  // Check for configuration documentation
  requirements.configurations.forEach(file => {
    const docPath = generateDocumentationPath(file, 'configuration', config);
    if (!fs.existsSync(docPath)) {
      missing.configurations.push({ file, expectedDoc: docPath });
    }
  });

  // Check for database documentation
  requirements.databases.forEach(file => {
    const docPath = generateDocumentationPath(file, 'database', config);
    if (!fs.existsSync(docPath)) {
      missing.databases.push({ file, expectedDoc: docPath });
    }
  });

  // Check for component documentation
  requirements.components.forEach(file => {
    const docPath = generateDocumentationPath(file, 'component', config);
    if (!fs.existsSync(docPath)) {
      missing.components.push({ file, expectedDoc: docPath });
    }
  });

  return missing;
}

/**
 * Generate documentation file path based on source file
 */
function generateDocumentationPath(sourceFile, type, config) {
  const baseName = path.basename(sourceFile, path.extname(sourceFile));
  const dirName = path.dirname(sourceFile);
  
  // Create documentation path based on type
  switch (type) {
    case 'feature':
      return path.join(config.directories.docs, 'features', `${baseName}.md`);
    case 'api':
      return path.join(config.directories.docs, 'api', `${baseName}.md`);
    case 'component':
      return path.join(config.directories.docs, 'components', `${baseName}.md`);
    case 'configuration':
      return path.join(config.directories.docs, 'configuration', `${baseName}.md`);
    case 'database':
      return path.join(config.directories.docs, 'database', `${baseName}.md`);
    default:
      return path.join(config.directories.docs, `${baseName}.md`);
  }
}

/**
 * Extract information from source files for documentation generation
 */
function extractFileInformation(filePath, config) {
  if (!fs.existsSync(filePath)) return null;

  try {
    const content = fs.readFileSync(filePath, 'utf8');
    const info = {
      fileName: path.basename(filePath),
      filePath: filePath,
      functions: [],
      classes: [],
      comments: [],
      exports: [],
      imports: []
    };

    if (config.documentation.autoGenerate.extractComments) {
      info.comments = extractComments(content);
    }

    if (config.documentation.autoGenerate.extractFunctionSignatures) {
      info.functions = extractFunctions(content, filePath);
    }

    if (config.documentation.autoGenerate.extractClassDefinitions) {
      info.classes = extractClasses(content, filePath);
    }

    if (config.documentation.autoGenerate.extractApiEndpoints) {
      info.endpoints = extractApiEndpoints(content, filePath);
    }

    return info;
  } catch (error) {
    console.warn(chalk.yellow(`⚠️ Could not extract information from ${filePath}`));
    return null;
  }
}

/**
 * Extract comments from source code
 */
function extractComments(content) {
  const comments = [];
  
  // Extract single-line comments
  const singleLineComments = content.match(/\/\/.*$/gm) || [];
  comments.push(...singleLineComments.map(c => c.replace('//', '').trim()));
  
  // Extract multi-line comments
  const multiLineComments = content.match(/\/\*[\s\S]*?\*\//g) || [];
  comments.push(...multiLineComments.map(c => 
    c.replace(/\/\*|\*\//g, '').replace(/^\s*\*/gm, '').trim()
  ));
  
  // Extract JSDoc comments
  const jsDocComments = content.match(/\/\*\*[\s\S]*?\*\//g) || [];
  comments.push(...jsDocComments.map(c => 
    c.replace(/\/\*\*|\*\//g, '').replace(/^\s*\*/gm, '').trim()
  ));

  return comments.filter(c => c.length > 10); // Filter out short comments
}

/**
 * Extract function signatures
 */
function extractFunctions(content, filePath) {
  const functions = [];
  const ext = path.extname(filePath);
  
  let patterns = [];
  
  if (['.js', '.ts'].includes(ext)) {
    patterns = [
      /function\s+(\w+)\s*\([^)]*\)/g,
      /const\s+(\w+)\s*=\s*\([^)]*\)\s*=>/g,
      /(\w+)\s*:\s*\([^)]*\)\s*=>/g,
      /export\s+function\s+(\w+)\s*\([^)]*\)/g
    ];
  } else if (ext === '.php') {
    patterns = [
      /function\s+(\w+)\s*\([^)]*\)/g,
      /public\s+function\s+(\w+)\s*\([^)]*\)/g,
      /private\s+function\s+(\w+)\s*\([^)]*\)/g,
      /protected\s+function\s+(\w+)\s*\([^)]*\)/g
    ];
  }
  
  patterns.forEach(pattern => {
    let match;
    while ((match = pattern.exec(content)) !== null) {
      functions.push({
        name: match[1],
        signature: match[0],
        line: content.substring(0, match.index).split('\n').length
      });
    }
  });
  
  return functions;
}

/**
 * Extract class definitions
 */
function extractClasses(content, filePath) {
  const classes = [];
  const ext = path.extname(filePath);
  
  let patterns = [];
  
  if (['.js', '.ts'].includes(ext)) {
    patterns = [
      /class\s+(\w+)(?:\s+extends\s+\w+)?\s*{/g,
      /export\s+class\s+(\w+)(?:\s+extends\s+\w+)?\s*{/g
    ];
  } else if (ext === '.php') {
    patterns = [
      /class\s+(\w+)(?:\s+extends\s+\w+)?(?:\s+implements\s+[\w,\s]+)?\s*{/g
    ];
  }
  
  patterns.forEach(pattern => {
    let match;
    while ((match = pattern.exec(content)) !== null) {
      classes.push({
        name: match[1],
        signature: match[0],
        line: content.substring(0, match.index).split('\n').length
      });
    }
  });
  
  return classes;
}

/**
 * Extract API endpoints
 */
function extractApiEndpoints(content, filePath) {
  const endpoints = [];
  
  // Common API patterns
  const patterns = [
    /app\.(get|post|put|patch|delete)\s*\(\s*['"`]([^'"`]+)['"`]/g,
    /router\.(get|post|put|patch|delete)\s*\(\s*['"`]([^'"`]+)['"`]/g,
    /Route::(get|post|put|patch|delete)\s*\(\s*['"`]([^'"`]+)['"`]/g,
    /@(Get|Post|Put|Patch|Delete)\s*\(\s*['"`]([^'"`]+)['"`]/g
  ];
  
  patterns.forEach(pattern => {
    let match;
    while ((match = pattern.exec(content)) !== null) {
      endpoints.push({
        method: match[1].toUpperCase(),
        path: match[2],
        line: content.substring(0, match.index).split('\n').length
      });
    }
  });
  
  return endpoints;
}

/**
 * Generate documentation templates for missing documentation
 */
function generateDocumentationTemplates(missing, config) {
  const generated = [];

  // Ensure docs directories exist
  ensureDocsDirectories(config);

  // Generate feature documentation
  missing.features.forEach(({ file, expectedDoc }) => {
    const info = extractFileInformation(file, config);
    const template = generateFeatureTemplate(file, info, config);
    if (writeDocumentationFile(expectedDoc, template)) {
      generated.push(expectedDoc);
    }
  });

  // Generate API documentation
  missing.apis.forEach(({ file, expectedDoc }) => {
    const info = extractFileInformation(file, config);
    const template = generateApiTemplate(file, info, config);
    if (writeDocumentationFile(expectedDoc, template)) {
      generated.push(expectedDoc);
    }
  });

  // Generate component documentation
  missing.components.forEach(({ file, expectedDoc }) => {
    const info = extractFileInformation(file, config);
    const template = generateComponentTemplate(file, info, config);
    if (writeDocumentationFile(expectedDoc, template)) {
      generated.push(expectedDoc);
    }
  });

  // Generate configuration documentation
  missing.configurations.forEach(({ file, expectedDoc }) => {
    const info = extractFileInformation(file, config);
    const template = generateConfigurationTemplate(file, info, config);
    if (writeDocumentationFile(expectedDoc, template)) {
      generated.push(expectedDoc);
    }
  });

  // Generate database documentation
  missing.databases.forEach(({ file, expectedDoc }) => {
    const info = extractFileInformation(file, config);
    const template = generateDatabaseTemplate(file, info, config);
    if (writeDocumentationFile(expectedDoc, template)) {
      generated.push(expectedDoc);
    }
  });

  return generated;
}

/**
 * Ensure documentation directories exist
 */
function ensureDocsDirectories(config) {
  const directories = [
    config.directories.docs,
    path.join(config.directories.docs, 'features'),
    path.join(config.directories.docs, 'api'),
    path.join(config.directories.docs, 'components'),
    path.join(config.directories.docs, 'configuration'),
    path.join(config.directories.docs, 'database')
  ];

  directories.forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
  });
}

/**
 * Write documentation file
 */
function writeDocumentationFile(filePath, content) {
  try {
    fs.writeFileSync(filePath, content, 'utf8');
    return true;
  } catch (error) {
    console.error(chalk.red(`❌ Failed to write documentation file: ${filePath}`));
    return false;
  }
}

/**
 * Generate feature documentation template
 */
function generateFeatureTemplate(sourceFile, info, config) {
  const fileName = path.basename(sourceFile, path.extname(sourceFile));
  const featureName = fileName.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

  let template = `# ${featureName}

## Overview

<!-- TODO: Provide a comprehensive overview of the ${featureName} feature -->
This document describes the ${featureName} feature implementation.

**Source File:** \`${sourceFile}\`
**Last Updated:** ${new Date().toISOString().split('T')[0]}

## Purpose

<!-- TODO: Explain the purpose and goals of this feature -->

## Usage

<!-- TODO: Provide usage examples and instructions -->

### Basic Usage

\`\`\`javascript
// TODO: Add basic usage example
\`\`\`

### Advanced Usage

\`\`\`javascript
// TODO: Add advanced usage examples
\`\`\`

## API Reference

`;

  // Add function documentation if available
  if (info && info.functions.length > 0) {
    template += `### Functions

`;
    info.functions.forEach(func => {
      template += `#### \`${func.name}\`

\`\`\`javascript
${func.signature}
\`\`\`

<!-- TODO: Document the ${func.name} function -->
- **Purpose:**
- **Parameters:**
- **Returns:**
- **Example:**

`;
    });
  }

  // Add class documentation if available
  if (info && info.classes.length > 0) {
    template += `### Classes

`;
    info.classes.forEach(cls => {
      template += `#### \`${cls.name}\`

<!-- TODO: Document the ${cls.name} class -->
- **Purpose:**
- **Properties:**
- **Methods:**
- **Example:**

`;
    });
  }

  template += `## Configuration

<!-- TODO: Document any configuration options -->

## Examples

<!-- TODO: Provide comprehensive examples -->

## Testing

<!-- TODO: Document testing approach and examples -->

## Troubleshooting

<!-- TODO: Document common issues and solutions -->

## Related Documentation

<!-- TODO: Link to related documentation -->

---

*This documentation was auto-generated and requires completion by the developer.*
`;

  return template;
}

/**
 * Generate API documentation template
 */
function generateApiTemplate(sourceFile, info, config) {
  const fileName = path.basename(sourceFile, path.extname(sourceFile));
  const apiName = fileName.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

  let template = `# ${apiName} API

## Overview

<!-- TODO: Provide a comprehensive overview of the ${apiName} API -->
This document describes the ${apiName} API endpoints and usage.

**Source File:** \`${sourceFile}\`
**Last Updated:** ${new Date().toISOString().split('T')[0]}

## Base URL

\`\`\`
<!-- TODO: Add base URL -->
\`\`\`

## Authentication

<!-- TODO: Document authentication requirements -->

## Endpoints

`;

  // Add endpoint documentation if available
  if (info && info.endpoints && info.endpoints.length > 0) {
    info.endpoints.forEach(endpoint => {
      template += `### ${endpoint.method} ${endpoint.path}

<!-- TODO: Document the ${endpoint.method} ${endpoint.path} endpoint -->

**Description:**

**Parameters:**
- <!-- TODO: Document parameters -->

**Request Body:**
\`\`\`json
{
  // TODO: Add request body example
}
\`\`\`

**Response:**
\`\`\`json
{
  // TODO: Add response example
}
\`\`\`

**Status Codes:**
- \`200\` - Success
- \`400\` - Bad Request
- \`401\` - Unauthorized
- \`404\` - Not Found
- \`500\` - Internal Server Error

**Example:**
\`\`\`bash
curl -X ${endpoint.method} \\
  -H "Content-Type: application/json" \\
  -d '{}' \\
  "http://localhost:3000${endpoint.path}"
\`\`\`

`;
    });
  } else {
    template += `### Endpoint Name

<!-- TODO: Document API endpoints -->

**Method:** \`GET|POST|PUT|DELETE\`
**Path:** \`/api/endpoint\`
**Description:**

**Parameters:**
- \`param1\` (string, required) - Description

**Request Body:**
\`\`\`json
{
  "example": "value"
}
\`\`\`

**Response:**
\`\`\`json
{
  "success": true,
  "data": {}
}
\`\`\`

`;
  }

  template += `## Error Handling

<!-- TODO: Document error handling -->

## Rate Limiting

<!-- TODO: Document rate limiting if applicable -->

## Examples

<!-- TODO: Provide comprehensive API usage examples -->

## Testing

<!-- TODO: Document API testing approach -->

---

*This documentation was auto-generated and requires completion by the developer.*
`;

  return template;
}

/**
 * Generate component documentation template
 */
function generateComponentTemplate(sourceFile, info, config) {
  const fileName = path.basename(sourceFile, path.extname(sourceFile));
  const componentName = fileName.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

  let template = `# ${componentName} Component

## Overview

<!-- TODO: Provide a comprehensive overview of the ${componentName} component -->
This document describes the ${componentName} component implementation and usage.

**Source File:** \`${sourceFile}\`
**Last Updated:** ${new Date().toISOString().split('T')[0]}

## Purpose

<!-- TODO: Explain the purpose and functionality of this component -->

## Props/Parameters

<!-- TODO: Document component props or parameters -->

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | <!-- TODO --> |

## Usage

### Basic Usage

\`\`\`javascript
// TODO: Add basic usage example
\`\`\`

### Advanced Usage

\`\`\`javascript
// TODO: Add advanced usage examples with different props
\`\`\`

## Styling

<!-- TODO: Document styling options and CSS classes -->

## Events

<!-- TODO: Document any events emitted by the component -->

## Examples

<!-- TODO: Provide comprehensive examples -->

## Accessibility

<!-- TODO: Document accessibility features and considerations -->

## Browser Support

<!-- TODO: Document browser compatibility -->

## Related Components

<!-- TODO: Link to related components -->

---

*This documentation was auto-generated and requires completion by the developer.*
`;

  return template;
}

/**
 * Generate configuration documentation template
 */
function generateConfigurationTemplate(sourceFile, info, config) {
  const fileName = path.basename(sourceFile, path.extname(sourceFile));
  const configName = fileName.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

  let template = `# ${configName} Configuration

## Overview

<!-- TODO: Provide a comprehensive overview of the ${configName} configuration -->
This document describes the ${configName} configuration options and setup.

**Source File:** \`${sourceFile}\`
**Last Updated:** ${new Date().toISOString().split('T')[0]}

## Configuration Options

<!-- TODO: Document all configuration options -->

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | <!-- TODO --> |

## Environment Variables

<!-- TODO: Document environment variables -->

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | <!-- TODO --> |

## Setup Instructions

### Development Environment

\`\`\`bash
# TODO: Add development setup instructions
\`\`\`

### Production Environment

\`\`\`bash
# TODO: Add production setup instructions
\`\`\`

## Examples

### Basic Configuration

\`\`\`json
{
  // TODO: Add basic configuration example
}
\`\`\`

### Advanced Configuration

\`\`\`json
{
  // TODO: Add advanced configuration example
}
\`\`\`

## Validation

<!-- TODO: Document configuration validation -->

## Troubleshooting

<!-- TODO: Document common configuration issues -->

---

*This documentation was auto-generated and requires completion by the developer.*
`;

  return template;
}

/**
 * Generate database documentation template
 */
function generateDatabaseTemplate(sourceFile, info, config) {
  const fileName = path.basename(sourceFile, path.extname(sourceFile));
  const dbName = fileName.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

  let template = `# ${dbName} Database

## Overview

<!-- TODO: Provide a comprehensive overview of the ${dbName} database changes -->
This document describes the ${dbName} database schema, migrations, or model changes.

**Source File:** \`${sourceFile}\`
**Last Updated:** ${new Date().toISOString().split('T')[0]}

## Schema Changes

<!-- TODO: Document schema changes -->

### Tables

<!-- TODO: Document table structures -->

#### Table Name

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| <!-- TODO --> | <!-- TODO --> | <!-- TODO --> | <!-- TODO --> |

### Indexes

<!-- TODO: Document indexes -->

### Relationships

<!-- TODO: Document table relationships -->

## Migration Instructions

### Up Migration

\`\`\`sql
-- TODO: Add up migration SQL
\`\`\`

### Down Migration

\`\`\`sql
-- TODO: Add down migration SQL
\`\`\`

## Model Changes

<!-- TODO: Document model/ORM changes -->

## Data Migration

<!-- TODO: Document any data migration requirements -->

## Performance Considerations

<!-- TODO: Document performance implications -->

## Backup Considerations

<!-- TODO: Document backup requirements -->

---

*This documentation was auto-generated and requires completion by the developer.*
`;

  return template;
}

/**
 * Stage generated documentation files
 */
function stageGeneratedDocumentation(generatedFiles) {
  if (generatedFiles.length === 0) return;

  try {
    generatedFiles.forEach(file => {
      execSync(`git add "${file}"`, { stdio: 'pipe' });
    });
    console.log(chalk.green(`✅ Staged ${generatedFiles.length} generated documentation files`));
  } catch (error) {
    console.error(chalk.red('❌ Failed to stage generated documentation files'));
  }
}

/**
 * Validate existing documentation quality
 */
function validateDocumentationQuality(docFiles, config) {
  const issues = [];

  docFiles.forEach(file => {
    if (!fs.existsSync(file)) return;

    try {
      const content = fs.readFileSync(file, 'utf8');
      const wordCount = content.split(/\s+/).length;

      // Check minimum word count
      if (wordCount < config.documentation.minWordCount) {
        issues.push({
          file,
          issue: `Documentation too short (${wordCount} words, minimum ${config.documentation.minWordCount})`
        });
      }

      // Check for required sections
      config.documentation.requiredSections.forEach(section => {
        if (!content.includes(`## ${section}`) && !content.includes(`# ${section}`)) {
          issues.push({
            file,
            issue: `Missing required section: ${section}`
          });
        }
      });

      // Check for TODO placeholders
      const todoCount = (content.match(/TODO:/g) || []).length;
      if (todoCount > 5) {
        issues.push({
          file,
          issue: `Too many TODO placeholders (${todoCount}), documentation incomplete`
        });
      }

    } catch (error) {
      issues.push({
        file,
        issue: 'Could not read documentation file'
      });
    }
  });

  return issues;
}

/**
 * Handle emergency bypass
 */
function handleEmergencyBypass() {
  if (DEFAULT_CONFIG.emergencyBypass) {
    console.log(chalk.red.bold('\n🚨 EMERGENCY BYPASS ACTIVATED'));
    console.log(chalk.yellow('⚠️ Documentation enforcement bypassed'));
    console.log(chalk.gray('Set EMERGENCY_BYPASS=false to re-enable documentation checks\n'));
    return true;
  }
  return false;
}

/**
 * Main documentation enforcement function
 */
function enforceDocumentation() {
  const config = loadConfiguration();

  if (!config.enforcement.enabled) {
    console.log(chalk.gray('📚 Documentation enforcement disabled'));
    return { success: true, generated: [] };
  }

  const stagedFiles = getStagedFiles();

  if (stagedFiles.length === 0) {
    console.log(chalk.gray('📚 No staged files to analyze'));
    return { success: true, generated: [] };
  }

  console.log(chalk.blue(`📚 Analyzing ${stagedFiles.length} staged files for documentation requirements...`));

  // Analyze documentation requirements
  const requirements = analyzeDocumentationRequirements(stagedFiles, config);

  if (!requirements.needsDocumentation) {
    console.log(chalk.green('✅ No documentation requirements detected'));
    return { success: true, generated: [] };
  }

  // Check existing documentation
  const missing = checkExistingDocumentation(requirements, config);
  const totalMissing = Object.values(missing).reduce((sum, arr) => sum + arr.length, 0);

  if (totalMissing === 0) {
    console.log(chalk.green('✅ All required documentation exists'));
    return { success: true, generated: [] };
  }

  console.log(chalk.yellow(`⚠️ Found ${totalMissing} files requiring documentation`));

  let generatedFiles = [];

  // Generate documentation templates if enabled
  if (config.enforcement.autoGenerateTemplates) {
    console.log(chalk.blue('📝 Generating documentation templates...'));
    generatedFiles = generateDocumentationTemplates(missing, config);

    if (generatedFiles.length > 0) {
      console.log(chalk.green(`✅ Generated ${generatedFiles.length} documentation templates`));
      stageGeneratedDocumentation(generatedFiles);
    }
  }

  // Check if we should block the commit
  if (config.enforcement.blockCommitsWithoutDocs && totalMissing > generatedFiles.length) {
    return {
      success: false,
      generated: generatedFiles,
      missing: missing,
      error: 'Documentation requirements not met'
    };
  }

  return {
    success: true,
    generated: generatedFiles,
    missing: missing
  };
}

/**
 * Print documentation enforcement results
 */
function printResults(result) {
  if (result.generated && result.generated.length > 0) {
    console.log(chalk.blue.bold('\n📝 Generated Documentation:'));
    result.generated.forEach(file => {
      console.log(chalk.blue(`  • ${file}`));
    });
    console.log(chalk.yellow('\n💡 Please review and complete the generated documentation templates'));
  }

  if (result.missing) {
    const totalMissing = Object.values(result.missing).reduce((sum, arr) => sum + arr.length, 0);
    if (totalMissing > 0) {
      console.log(chalk.yellow.bold('\n⚠️ Missing Documentation:'));
      Object.entries(result.missing).forEach(([type, files]) => {
        if (files.length > 0) {
          console.log(chalk.yellow(`\n${type.toUpperCase()}:`));
          files.forEach(({ file, expectedDoc }) => {
            console.log(chalk.yellow(`  • ${file} → ${expectedDoc}`));
          });
        }
      });
    }
  }
}

/**
 * Main execution
 */
function main() {
  printHeader();

  if (handleEmergencyBypass()) {
    process.exit(0);
  }

  try {
    const result = enforceDocumentation();
    printResults(result);

    if (!result.success) {
      console.log(chalk.red.bold('\n🚫 Commit blocked: Documentation requirements not met'));
      console.log(chalk.yellow('💡 Complete the required documentation or use EMERGENCY_BYPASS=true'));
      console.log(chalk.gray('📖 See .augment/rules/ALWAYS-comprehensive-code-review-standards.md for details\n'));
      process.exit(1);
    } else {
      console.log(chalk.green.bold('\n✅ Documentation enforcement passed\n'));
      process.exit(0);
    }
  } catch (error) {
    console.error(chalk.red.bold('\n❌ Documentation enforcement failed:'), error.message);
    process.exit(1);
  }
}

// Execute if run directly
if (require.main === module) {
  main();
}

module.exports = {
  enforceDocumentation,
  generateDocumentationTemplates,
  analyzeDocumentationRequirements,
  loadConfiguration,
  DEFAULT_CONFIG
};
