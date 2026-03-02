---
type: "always_apply"
---

# Augment Rules Configuration: Enforce Documentation for All Code Changes

This configuration is applied automatically with ALWAYS priority and requires documentation to be created/updated in `/docs` for all qualifying changes.

## Configuration Metadata

- **Version:** 1
- **Name:** Documentation Enforcement Rules
- **Owner:** Engineering
- **Purpose:** Make documentation a mandatory artifact of every code/config change
- **Applies Automatically:** Yes

## Priority Settings

- **Description:** CRITICAL: Enforce documentation for all code/config changes and validate before merge
- **Priority:** 1 (Highest)
- **Scope:** Global
- **Type:** Always Apply

## File Patterns

### Included File Types

Files that are considered "code or config" and thus require documentation:

#### Source Code Languages
- `**/*.ts` - TypeScript files
- `**/*.tsx` - TypeScript React files
- `**/*.js` - JavaScript files
- `**/*.jsx` - JavaScript React files
- `**/*.mjs` - ES Module JavaScript
- `**/*.cjs` - CommonJS JavaScript
- `**/*.py` - Python files
- `**/*.php` - PHP files
- `**/*.rb` - Ruby files
- `**/*.go` - Go files
- `**/*.rs` - Rust files
- `**/*.java` - Java files
- `**/*.kt` - Kotlin files
- `**/*.swift` - Swift files
- `**/*.[ch]` - C/C++ header files
- `**/*.[ch]pp` - C++ files
- `**/*.cs` - C# files

#### Markup/Templates/Styles
Files that define UI behavior or components:
- `**/*.html` - HTML files
- `**/*.twig` - Twig template files
- `**/*.liquid` - Liquid template files
- `**/*.vue` - Vue.js files
- `**/*.svelte` - Svelte files
- `**/*.css` - CSS files
- `**/*.scss` - SCSS files

#### Infrastructure/Configuration
- `**/*.yml` - YAML files
- `**/*.yaml` - YAML files
- `**/*.json` - JSON files
- `**/*.toml` - TOML files
- `**/*.ini` - INI files
- `**/*.conf` - Configuration files
- `**/Dockerfile` - Docker files
- `**/docker-compose*.yml` - Docker Compose files
- `**/nginx*.conf` - Nginx configuration
- `**/*.env.example` - Environment examples
- `**/*.env.template` - Environment templates
- `**/package.json` - Node.js package files
- `**/composer.json` - PHP Composer files
- `**/requirements*.txt` - Python requirements
- `**/pyproject.toml` - Python project files
- `**/go.mod` - Go module files
- `**/Cargo.toml` - Rust Cargo files
- `**/*.sh` - Shell scripts
- `**/*.ps1` - PowerShell scripts

### Excluded File Types

Files that do NOT trigger documentation requirements:
- `docs/**` - Documentation files (but must follow structure rules)
- `.augment/**` - Augment configuration files
- `.claude/**` - Claude Code configuration files
- `**/node_modules/**` - Node.js dependencies
- `**/dist/**` - Distribution/build files
- `**/build/**` - Build output
- `**/.next/**` - Next.js build files
- `**/out/**` - Output directories
- `**/.vercel/**` - Vercel deployment files
- `**/coverage/**` - Test coverage files
- `**/*.map` - Source map files
- `**/*.lock` - Lock files

### Documentation File Validation

**ENFORCEMENT RULES:**
- All new `.md` files MUST be placed within the `/docs` directory structure
- Files placed in repository root or other locations will trigger CI/CD failures
- Proper categorization into appropriate subfolders is mandatory
- Naming conventions (kebab-case, lowercase) are strictly enforced

## Documentation Requirements

### Mandatory Documentation Structure

**CRITICAL REQUIREMENT:** All new Markdown (.md) files MUST be created within the `/docs` directory structure. Files placed outside this structure will be rejected during CI/CD validation.

### Directory Structure
- **Root:** `docs` (MANDATORY - all documentation must reside here)
- **API Documentation:** `docs/api/` - API endpoint documentation
- **Components:** `docs/components/` - UI component documentation
- **Modules:** `docs/modules/` - Code module and function documentation
- **Guides:** `docs/guides/` - User guides and tutorials
- **Configuration:** `docs/guides/config/` - Configuration documentation
- **Migrations:** `docs/guides/migrations/` - Migration procedures and guides

### File Naming Conventions
- **Case:** kebab-case (e.g., `order-service-create.md`)
- **Extension:** `.md`
- **Index Files:** Not allowed
- **Spaces:** Disallowed
- **Case Enforcement:** Lowercase required
- **Location Enforcement:** Must be within appropriate `/docs` subfolder

### Markdown Requirements

#### Style
- **Format:** CommonMark + Tables
- **Frontmatter:** Required

#### Required Frontmatter Keys
- `title` - Document title
- `description` - Brief description
- `category` - Document category
- `last_updated` - Last update date

#### Optional Frontmatter Keys
- `framework` - Technology framework
- `domain` - Business domain
- `layer` - Architecture layer
- `tags` - Document tags
- `related` - Related documents
- `owner` - Document owner

#### Minimum Word Counts
- **Overview:** 50 words
- **Usage:** 30 words
- **Dependencies:** 20 words
- **Testing:** 30 words

### Valid Categories
- `api` - API documentation
- `module` - Module documentation
- `component` - Component documentation
- `guide` - User guides
- `config` - Configuration documentation
- `migrations` - Migration documentation

## Project Taxonomy

### Frameworks
- `react` - React framework
- `nextjs` - Next.js framework
- `wordpress-plugin` - WordPress plugin
- `wordpress-theme` - WordPress theme
- `node-express` - Node.js Express
- `nestjs` - NestJS framework
- `php` - PHP
- `python` - Python
- `go` - Go language

### Domains
- `authentication` - User authentication
- `payment-processing` - Payment systems
- `user-management` - User management
- `catalog` - Product catalog
- `cart` - Shopping cart
- `checkout` - Checkout process
- `orders` - Order management
- `notifications` - Notification systems
- `reporting` - Reporting and analytics

### Layers
- `frontend` - Frontend/UI layer
- `backend` - Backend services
- `api` - API layer
- `database` - Database layer
- `infrastructure` - Infrastructure
- `ci-cd` - CI/CD pipeline

### Custom Tags
#### Recommended Tags
- `accessibility` - Accessibility features
- `performance` - Performance optimizations
- `security` - Security implementations
- `migration` - Migration procedures
- `deprecation` - Deprecated features

#### Usage Guidance
Use these values in frontmatter fields (`framework`, `domain`, `layer`) and `tags` for consistent categorization.

## CI Integration

### Pre-commit Hooks
- **Enabled:** Yes
- **Hook Name:** `docs-validate`
- **Description:** Blocks commits if required documentation is missing, incomplete, or placed outside /docs structure
- **Command:** `augment validate --rules validate-documentation,enforce-docs-structure --changed`

### CI Workflows
- **Required:** Yes
- **Fail On:**
  - `validation.error`
  - `docs_missing`
  - `docs_outside_structure` - .md files outside /docs directory
  - `docs_miscategorized` - .md files in wrong subfolder
  - `insufficient_content`
  - `invalid_code_snippet`
  - `invalid_file_naming` - non-kebab-case or uppercase filenames

#### GitHub Actions Workflow
```yaml
name: Docs Check
on:
  pull_request:
    types: [opened, synchronize, reopened]
jobs:
  docs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
      - name: Install Augment CLI
        run: |
          npm i -g @augmentcode/cli || true
      - name: Validate Documentation
        run: |
          augment validate --rules validate-documentation,enforce-docs-structure --diff origin/${{ github.base_ref }}...HEAD
```

#### GitLab CI Workflow
```yaml
docs_check:
  image: node:20
  stage: test
  script:
    - npm i -g @augmentcode/cli || true
    - augment validate --rules validate-documentation,enforce-docs-structure --changed
  only:
    - merge_requests
```

#### Jenkins Pipeline
```groovy
stage('Docs Check') {
  agent { label 'linux' }
  steps {
    sh '''
      npm i -g @augmentcode/cli || true
      augment validate --rules validate-documentation,enforce-docs-structure --changed
    '''
  }
}
```

### Build Failure Policy
- **Missing Docs:** Error
- **Incomplete Docs:** Error
- **Priority:** 1 (Critical)
- **Scope:** Global
- **Type:** Always Apply
- **Warnings as Errors:** No

## Rules

### Rule 1: Require Documentation for Code Changes

**ID:** `require-docs-for-code-changes`
**Description:** Require docs for new/modified files, functions/methods, API endpoints, and configuration changes.

#### Triggers

**File Changes:**
- **Include:** All file patterns listed above
- **Exclude:** Documentation and generated files
- **Detection Types:**
  - New file creation
  - File modification
  - Function/method addition
  - Function/method modification
  - API endpoint creation
  - API endpoint modification
  - Configuration changes

#### Detection Heuristics

**Function/Method Changes:**
- `^\+.*(function\s+|def\s+|class\s+.*\{|=>\s*\(|:\s*\(|fn\s+|method\s+)`

**API Endpoint Changes:**
- `^\+.*(GET|POST|PUT|PATCH|DELETE)\s+(/[^\s\"]+)`
- `^\+.*(route|router\.|app\.|server\.)\s*(get|post|put|patch|delete)\s*\(`
- `^\+.*(@Get|@Post|@Put|@Patch|@Delete)\b`

**Configuration Changes:**
- `^\+.*(config|settings|options|environment|env|credentials|policy|rules)`

#### Documentation Destinations (MANDATORY STRUCTURE)

**API Documentation:**
- **Path:** `docs/api/` (MANDATORY)
- **File Name:** `<service-or-area>/<endpoint-kebab>.md`
- **Enforcement:** All API endpoint documentation MUST be placed here

**Function/Method Documentation:**
- **Path:** `docs/modules/` (MANDATORY)
- **File Name:** `<module-path-kebab>/<symbol-kebab>.md`
- **Enforcement:** All code module and function documentation MUST be placed here

**Component/UI Documentation:**
- **Path:** `docs/components/` (MANDATORY)
- **File Name:** `<component-path-kebab>.md`
- **Enforcement:** All UI component documentation MUST be placed here

**Configuration Documentation:**
- **Path:** `docs/guides/config/` (MANDATORY)
- **File Name:** `<config-area-kebab>/<file-kebab>.md`
- **Enforcement:** All configuration documentation MUST be placed here

**Migration Documentation:**
- **Path:** `docs/guides/migrations/` (MANDATORY)
- **File Name:** `<migration-name-kebab>.md`
- **Enforcement:** All migration procedures MUST be placed here

**Generic Changes:**
- **Path:** `docs/guides/` (MANDATORY)
- **File Name:** `change-logs/<short-summary-kebab>.md`
- **Enforcement:** All general guides and tutorials MUST be placed here

#### Required Content

**Required Sections:**
- Overview
- Usage
- Parameters
- Returns
- Dependencies
- Testing
- Changelog

**Code Examples:**
- **Minimum Blocks:** 1
- **Supported Languages:** TypeScript, JavaScript, TSX, JSX, JSON, YAML, Bash, PHP, Python

### Rule 2: Validate Documentation

**ID:** `validate-documentation`
**Priority:** 1 (Critical)
**Description:** Validate the presence, placement, naming, and quality of documentation for changed code.

#### Validation Checks

**Documentation Existence:**
- **Type:** `docs_exist_for_changed_files`
- **Severity:** Error
- **Message:** "Documentation is required in /docs for all code/config changes."

**Mandatory Documentation Location:**
- **Type:** `docs_location_enforcement`
- **Root:** `docs` (MANDATORY)
- **Allowed Subdirectories:** api, components, modules, guides, guides/config, guides/migrations
- **Severity:** Error
- **Message:** "All .md files MUST reside under the /docs directory structure. Files outside /docs are prohibited."
- **Enforcement:** Block commits/PRs with .md files outside /docs

**Documentation Categorization:**
- **Type:** `docs_categorization`
- **Required:** Proper subfolder placement based on content type
- **Severity:** Error
- **Message:** "Documentation must be categorized into the appropriate /docs subfolder."

**File Naming Conventions:**
- **Type:** `file_name_conventions`
- **Requirements:** kebab-case, lowercase, .md extension
- **Severity:** Error
- **Message:** "Doc file names must be kebab-case, lowercase, and .md."

**Markdown Frontmatter:**
- **Type:** `markdown_frontmatter`
- **Required:** Yes
- **Required Keys:** title, description, category, last_updated
- **Severity:** Error
- **Message:** "Docs must include required frontmatter keys."

**Required Sections:**
- **Type:** `required_sections`
- **Sections:** Overview, Usage, Parameters, Returns, Dependencies, Testing, Changelog
- **Severity:** Error
- **Message:** "Docs must include all required sections."

**Code Examples:**
- **Type:** `code_examples`
- **Minimum Blocks:** 1
- **Severity:** Error
- **Message:** "At least one fenced code block example is required."

**API-Specific Requirements:**
- **Type:** `api_specific`
- **Applies When:** API endpoint created/modified
- **Required Sections:** Request, Response, Security
- **Severity:** Error
- **Message:** "API docs must include Request, Response, and Security sections."

**Testing Commands:**
- **Type:** `testing_commands`
- **Require Executable:** Yes
- **Minimum Commands:** 1
- **Allowed Prefixes:** npm, yarn, pnpm, composer, php, python, pytest, go, cargo, dotnet, make
- **Severity:** Error
- **Message:** "Testing section must include at least one executable command."

**Section Word Counts:**
- **Type:** `section_min_word_count`
- **Thresholds:** Overview (50), Usage (30), Dependencies (20), Testing (30)
- **Severity:** Error
- **Message:** "Critical sections must meet minimum word counts."

**Code Snippet Validation:**
- **Type:** `code_snippet_validation`
- **Languages:** TypeScript, JavaScript, TSX, JSX, JSON, YAML, Bash, PHP, Python
- **Runnable:** Yes
- **Severity:** Error
- **Message:** "Code examples must be syntactically valid."

**Dependency Versions:**
- **Type:** `dependency_versions`
- **Require Versions:** Yes
- **Severity:** Error
- **Message:** "All external dependencies must include version requirements."

**API Request/Response Examples:**
- **Type:** `api_request_response_examples`
- **Applies When:** API endpoint created/modified
- **Require Examples:** Yes
- **Severity:** Error
- **Message:** "API docs must include concrete request and response examples."

**Function-Specific Requirements:**
- **Type:** `function_specific`
- **Applies When:** Function/method added/modified
- **Require Parameters Table:** Yes
- **Require Returns:** Yes
- **Severity:** Error
- **Message:** "Function/method docs must include Parameters and Returns."

**Links and Images:**
- **Type:** `links_and_images`
- **Check Internal Links:** Yes
- **Severity:** Warning
- **Message:** "Broken links/images found in documentation."

**Frontmatter Taxonomy:**
- **Type:** `frontmatter_taxonomy`
- **Fields:** framework, domain, layer
- **Allowed Values:** See taxonomy section above
- **Severity:** Warning
- **Message:** "Frontmatter taxonomy values should use the predefined taxonomy."

**Markdown Style:**
- **Type:** `markdown_style`
- **Style:** CommonMark + Tables
- **Severity:** Warning
- **Message:** "Markdown style issues detected."

### Rule 3: Enforce Mandatory Documentation Structure

**ID:** `enforce-docs-structure`
**Priority:** 1 (Critical)
**Description:** Enforce that all .md files are created within the mandatory /docs directory structure with proper categorization.

#### Validation Checks

**Documentation Structure Enforcement:**
- **Type:** `docs_structure_enforcement`
- **Severity:** Error
- **Message:** "All .md files MUST be placed within the /docs directory structure."
- **Action:** Block commits containing .md files outside /docs

**Subfolder Categorization:**
- **Type:** `subfolder_categorization`
- **Required Categories:**
  - `docs/api/` - API endpoint documentation
  - `docs/components/` - UI component documentation
  - `docs/modules/` - Code module and function documentation
  - `docs/guides/` - User guides and tutorials
  - `docs/guides/config/` - Configuration documentation
  - `docs/guides/migrations/` - Migration procedures and guides
- **Severity:** Error
- **Message:** "Documentation must be categorized into the appropriate /docs subfolder."

**Naming Convention Validation:**
- **Type:** `naming_convention_validation`
- **Requirements:**
  - kebab-case only
  - lowercase only
  - .md extension required
  - no spaces allowed
- **Severity:** Error
- **Message:** "Documentation filenames must follow kebab-case, lowercase naming conventions."

**Repository Root Protection:**
- **Type:** `root_protection`
- **Prohibited Locations:** Repository root, any directory outside /docs
- **Severity:** Error
- **Message:** "Markdown files are prohibited outside the /docs directory structure."

#### On Failure Actions
- **Block Merge:** Yes
- **Block Commits:** Yes (for .md files outside /docs)
- **Suggestions:**
  - Generate a doc using the appropriate template in the MANDATORY /docs structure
  - Ensure all required sections and frontmatter keys are present
  - Place docs under the correct /docs subdirectory and use kebab-case filenames
  - Move any existing .md files from repository root to appropriate /docs subfolder
  - Validate proper categorization based on content type

## Documentation Templates

### Generic Code Change Template

```markdown
---
title: "<Title>"
description: "<1-2 sentence summary of the change>"
category: "guide"
last_updated: "<YYYY-MM-DD>"
tags: [change, docs]
---

# Overview
Explain the purpose, the problem solved, and scope of the change.

# Usage
Provide example usage and context.

```ts
// Example usage/code snippet
```

# Parameters
List inputs/arguments if applicable.

# Returns
Describe outputs/return values if applicable.

# Dependencies
Note upstream/downstream dependencies, integrations, or side-effects.

# Testing
- How to run tests
- What to verify

# Changelog
- Related PR/Commit: <link>
```

### Function/Method Template

```markdown
---
title: "<Module>.<FunctionOrMethod>()"
description: "Purpose and behavior of the function/method."
category: "module"
last_updated: "<YYYY-MM-DD>"
tags: [function, api]
---

# Overview
High-level description and when to use it.

# Usage
```ts
// Example invocation
```

# Parameters
| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
|      |      |          |         |             |

# Returns
Describe return type(s) and semantics.

# Dependencies
Internal/external dependencies and assumptions.

# Testing
Unit/integration test instructions and cases.

# Changelog
- Introduced/Updated in: <PR/commit link>
```

### API Endpoint Template

```markdown
---
title: "<METHOD> <PATH>"
description: "Purpose of the endpoint and the business capability."
category: "api"
last_updated: "<YYYY-MM-DD>"
tags: [api]
---

# Overview
What the endpoint does, auth requirements, rate limits.

# Request
- Method: <GET|POST|PUT|PATCH|DELETE>
- Path: `<path>`
- Headers: `{ ... }`
- Query Params:
| Name | Type | Required | Description |
|------|------|----------|-------------|

- Path Params:
| Name | Type | Required | Description |
|------|------|----------|-------------|

- Body (schema and example):
```json
{

}
```

# Response
- Status Codes: 200, 400, 401, 403, 404, 500
- Body (schema and example):
```json
{

}
```

# Security
Auth, scopes, roles and permissions.

# Dependencies
Upstream/downstream services, database tables, queues, etc.

# Testing
cURL/Postman examples, contract tests.

# Changelog
- Introduced/Updated in: <PR/commit link>
```

### Configuration Change Template

```markdown
---
title: "Config: <Area/File>"
description: "What changed and why."
category: "config"
last_updated: "<YYYY-MM-DD>"
tags: [config]
---

# Overview
Summary of configuration change and intended effect.

# Settings
| Key | Old | New | Reason |
|-----|-----|-----|--------|

# Dependencies
Related services, environment variables, deployment notes.

# Rollback Plan
How to revert safely if needed.

# Testing
Verification steps and health checks.

# Changelog
- Introduced/Updated in: <PR/commit link>
```

### New File/Module Template

```markdown
---
title: "<New File or Module Name>"
description: "Purpose and scope of the new file/module."
category: "module"
last_updated: "<YYYY-MM-DD>"
tags: [new]
---

# Overview
Why this was added and what it provides.

# Public API
Document exported functions/classes and their usage.

# Usage
```ts
// Example code using the new module
```

# Dependencies
Internal/external integrations.

# Testing
How to test and expected results.

# Changelog
- Introduced in: <PR/commit link>
```

## Automation

### On Rule Violation Actions

When `require-docs-for-code-changes` or `enforce-docs-structure` rules are violated:

1. **Scaffold Document in Mandatory /docs Structure**
   - **API Changes:** Use `api_endpoint` template in `docs/api/`
   - **Function/Method Changes:** Use `function_method` template in `docs/modules/`
   - **Configuration Changes:** Use `configuration_change` template in `docs/guides/config/`
   - **New File/Module:** Use `new_file_or_module` template in `docs/modules/`
   - **Migration Changes:** Use migration template in `docs/guides/migrations/`
   - **Default:** Use `generic_code_change` template in `docs/guides/`

2. **Enforce Structure Compliance**
   - **Reject:** Any .md files placed outside /docs directory
   - **Validate:** Proper categorization into appropriate subfolders
   - **Check:** kebab-case, lowercase naming conventions

3. **Create TODO**
   - **Title:** "Complete required documentation in /docs structure"
   - **Body:** "A documentation skeleton was created in the mandatory /docs structure. Please complete all sections and ensure proper categorization before merging."
   - **Assignees:** Code author

---

## Summary of Mandatory Documentation Structure Requirements

### Critical Enforcement Points

1. **MANDATORY /docs Directory**: All .md files MUST be created within the `/docs` directory structure
2. **Categorized Subfolders**: Documentation must be properly categorized into appropriate subfolders:
   - `/docs/api/` - API endpoint documentation
   - `/docs/components/` - UI component documentation
   - `/docs/modules/` - Code module and function documentation
   - `/docs/guides/` - User guides and tutorials
   - `/docs/guides/config/` - Configuration documentation
   - `/docs/guides/migrations/` - Migration procedures and guides
3. **Naming Conventions**: kebab-case, lowercase, .md extension required
4. **CI/CD Blocking**: Commits/PRs with .md files outside /docs will be automatically rejected
5. **Template Integration**: All documentation templates updated to use mandatory /docs structure

### Validation Rules Integration

- **Rule 1**: `require-docs-for-code-changes` - Requires documentation for code changes
- **Rule 2**: `validate-documentation` - Validates documentation quality and placement
- **Rule 3**: `enforce-docs-structure` - Enforces mandatory /docs directory structure

*This configuration ensures comprehensive documentation coverage for all code changes while enforcing a consistent, organized documentation structure that prevents scattered .md files throughout the repository.*
