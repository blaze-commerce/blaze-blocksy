# Automated Documentation Organization System

## Overview

This document describes the automated system for organizing Markdown files in the BlazeCommerce repository. The system intelligently categorizes and moves `.md` files to appropriate subdirectories within `/docs`, maintaining a clean and organized documentation structure.

**Last Updated:** 2025-08-28

## System Components

### 1. Core Organization Script
- **File:** `scripts/organize-docs.js`
- **Purpose:** Main automation script for categorizing and moving markdown files
- **Integration:** Integrated with Git hooks and npm scripts

### 2. Configuration File
- **File:** `.docs-organization-config.json`
- **Purpose:** Manual categorization rules and exceptions
- **Customizable:** Allows fine-tuning of file categorization

### 3. Git Hook Integration
- **File:** `scripts/pre-commit-quality-check.js`
- **Purpose:** Automatically organizes new markdown files during commits
- **Behavior:** Runs before documentation enforcement

## Directory Structure

The system organizes documentation into the following categories:

```
docs/
├── api/                    # API documentation and credentials
├── architecture/           # System architecture and analysis
├── deployment/             # Release, versioning, and CI/CD
├── development/            # Development workflow and standards
├── features/               # Feature implementations and customizations
├── guides/                 # User guides and tutorials
├── performance/            # Performance optimization reports
├── security/               # Security implementations and guides
├── testing/                # Testing documentation and infrastructure
└── general/                # Uncategorized documentation (fallback)
```

## Categorization Rules

### Priority-Based System

The system uses a priority-based categorization approach:

1. **Manual Configuration** (Highest Priority)
   - Files listed in `.docs-organization-config.json`
   - Exact filename matches

2. **Filename Pattern Matching**
   - Regex patterns for common naming conventions
   - Category-specific patterns

3. **Content Analysis**
   - Keyword scanning within file content
   - Multiple keyword matches required for broad categories

4. **Default Fallback**
   - Uncategorized files go to `docs/general/`

### Category Definitions

#### Development (`docs/development/`)
- **Patterns:** `git-*`, `commit*`, `branch*`, `workflow*`, `husky*`, `hooks*`
- **Keywords:** development, coding, standards, review, quality, workflow, git, commit, branch
- **Examples:** Git workflows, code review standards, development setup

#### Features (`docs/features/`)
- **Patterns:** `thank-you*`, `checkout*`, `blaze-commerce*`, `customization*`
- **Keywords:** thank you, checkout, blaze commerce, customization, widget, sidebar
- **Examples:** Feature implementations, customizations, widget documentation

#### Testing (`docs/testing/`)
- **Patterns:** `test*`, `testing*`, `spec*`, `qa*`, `coverage*`
- **Keywords:** testing, test, spec, qa, coverage, unit, integration, e2e
- **Examples:** Test guides, testing infrastructure, QA documentation

#### Security (`docs/security/`)
- **Patterns:** `security*`, `auth*`, `permission*`, `vulnerability*`
- **Keywords:** security, authentication, authorization, vulnerability, encryption
- **Examples:** Security implementations, authentication guides

#### Performance (`docs/performance/`)
- **Patterns:** `performance*`, `optimization*`, `speed*`, `benchmark*`
- **Keywords:** performance, optimization, speed, benchmark, lighthouse
- **Examples:** Performance reports, optimization guides

#### Deployment (`docs/deployment/`)
- **Patterns:** `deploy*`, `release*`, `build*`, `ci-cd*`, `merge*`, `version*`, `changelog*`
- **Keywords:** deployment, release, build, ci/cd, pipeline, automation, merge, version
- **Examples:** Release automation, versioning, CI/CD pipelines

#### API (`docs/api/`)
- **Patterns:** `api-*`, `endpoint*`, `rest-*`, `credentials*`, `database*`
- **Keywords:** endpoint, api, rest, graphql, credentials, database
- **Examples:** API documentation, credentials setup, database schemas

#### Architecture (`docs/architecture/`)
- **Patterns:** `architecture*`, `design*`, `structure*`, `system*`, `analysis*`
- **Keywords:** architecture, design, structure, system, technical, analysis
- **Examples:** System architecture, technical analysis, design documents

## Protected Files and Directories

### Root-Protected Files
The following files are **NEVER** moved from the repository root:

- `README.md`
- `CHANGELOG.md`
- `LICENSE.md`
- `CONTRIBUTING.md`
- `CODE_OF_CONDUCT.md`

### Excluded Directories
The following directories are **COMPLETELY EXCLUDED** from organization:

- `.augment/` - Augment configuration and rules directory
- `node_modules/` - Node.js dependencies
- `.git/` - Git repository data
- `vendor/` - PHP dependencies
- `coverage/` - Test coverage files
- `dist/` - Distribution/build files
- `build/` - Build output

### Configuration
Additional files can be protected by adding them to the `rootExceptions` array in `.docs-organization-config.json`.
Additional directories can be excluded by adding them to the `excludeDirectories` array.

## Usage

### Manual Organization

```bash
# Organize all markdown files
npm run docs:organize

# Preview organization without moving files
npm run docs:organize:dry-run

# Show help and options
npm run docs:organize:help
```

### Automatic Organization

The system automatically runs during Git commits via the pre-commit hook:

1. **Pre-commit Hook Execution**
   - Detects new/modified `.md` files
   - Runs organization automatically
   - Stages organized files

2. **Integration with Quality Checks**
   - Runs before documentation enforcement
   - Non-blocking (warnings only)
   - Maintains commit workflow

### Configuration Customization

Edit `.docs-organization-config.json` to customize categorization:

```json
{
  "manualCategorization": {
    "development": [
      "custom-workflow.md",
      "team-standards.md"
    ],
    "features": [
      "new-feature-docs.md"
    ]
  },
  "rootExceptions": [
    "README.md",
    "CHANGELOG.md",
    "CUSTOM-ROOT-FILE.md"
  ],
  "skipFiles": [
    "scripts/README.md",
    "temp/draft.md"
  ]
}
```

## Error Handling

### Common Issues

1. **Duplicate Files**
   - System prevents overwriting existing files
   - Shows warning and skips conflicting files

2. **Permission Errors**
   - Logs errors but continues processing
   - Non-blocking for commit workflow

3. **Missing Dependencies**
   - Graceful degradation
   - Clear error messages

### Troubleshooting

```bash
# Check for issues with dry run
npm run docs:organize:dry-run

# Manually organize if automatic fails
npm run docs:organize

# Check Git hook status
npm run husky:setup
```

## Integration with Existing Workflow

### Documentation Enforcement
- Runs **after** organization
- Uses organized file locations
- Maintains existing quality standards

### Git Hooks
- Integrated with existing pre-commit checks
- Non-blocking warnings
- Automatic staging of organized files

### npm Scripts
- Extends existing documentation scripts
- Consistent with project conventions
- Maintains backward compatibility

## Benefits

1. **Automatic Organization**
   - No manual file management required
   - Consistent structure across team

2. **Intelligent Categorization**
   - Content-aware organization
   - Customizable rules

3. **Workflow Integration**
   - Seamless Git hook integration
   - Non-disruptive to existing processes

4. **Maintainable Structure**
   - Clear category definitions
   - Easy to find documentation

5. **Scalable System**
   - Handles growing documentation
   - Configurable for future needs

## Future Enhancements

1. **Content Analysis Improvements**
   - Machine learning categorization
   - Better keyword detection

2. **Integration Enhancements**
   - GitHub Actions integration
   - Automated README generation

3. **Reporting Features**
   - Documentation coverage reports
   - Organization statistics

4. **Advanced Configuration**
   - Per-directory rules
   - Custom category creation

---

*This documentation is automatically maintained as part of the BlazeCommerce documentation organization system.*
