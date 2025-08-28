# BlazeCommerce Documentation

Welcome to the BlazeCommerce documentation repository. This documentation is automatically organized using an intelligent categorization system.

## 📁 Documentation Structure

Our documentation is organized into the following categories:

### 🔧 [Development](./development/)
Development workflow, coding standards, and team processes
- Git workflows and branch naming standards
- Code review processes and quality standards
- Development setup and environment configuration
- Husky hooks and automation tools

### 🚀 [Features](./features/)
Feature implementations and customizations
- Blaze Commerce specific features
- Checkout and thank you page customizations
- Widget and sidebar implementations
- Feature analysis and implementation guides

### 🧪 [Testing](./testing/)
Testing documentation and infrastructure
- Comprehensive testing guides
- Test infrastructure setup
- Testing automation and CI/CD
- Quality assurance processes

### 🔒 [Security](./security/)
Security implementations and guidelines
- Security performance improvements
- Implementation guides and best practices
- Authentication and authorization
- Vulnerability assessments

### ⚡ [Performance](./performance/)
Performance optimization and monitoring
- Performance optimization reports
- Benchmarking and analysis
- Speed improvements and monitoring
- Lighthouse audits and recommendations

### 🚢 [Deployment](./deployment/)
Release management and CI/CD
- Release automation and versioning
- Changelog generation and management
- Version bump processes
- CI/CD pipeline documentation

### 🔌 [API](./api/)
API documentation and credentials
- API endpoint documentation
- Credentials setup and management
- Database configuration
- REST API guides

### 🏗️ [Architecture](./architecture/)
System architecture and technical analysis
- Implementation summaries
- System analysis and recommendations
- Technical architecture decisions
- Design patterns and structures

### 📚 [Guides](./guides/)
User guides and tutorials
- Step-by-step tutorials
- How-to guides
- Setup instructions
- Best practices

## 🤖 Automated Organization

This documentation is automatically organized using our intelligent categorization system:

### How It Works
1. **Automatic Detection**: New `.md` files are automatically detected
2. **Smart Categorization**: Files are categorized based on filename patterns and content analysis
3. **Git Hook Integration**: Organization happens automatically during commits
4. **Manual Override**: Custom categorization rules can be configured

### Usage Commands

```bash
# Organize all markdown files
npm run docs:organize

# Preview organization without moving files
npm run docs:organize:dry-run

# Show help and options
npm run docs:organize:help
```

### Protected Files
The following files remain in the repository root:
- `README.md`
- `CHANGELOG.md`
- `LICENSE.md`
- `CONTRIBUTING.md`
- `CODE_OF_CONDUCT.md`

## 📖 Finding Documentation

### By Category
Browse the folders above to find documentation by category.

### By Feature
- **Checkout System**: [Features > Checkout](./features/)
- **Thank You Pages**: [Features > Thank You Page](./features/)
- **API Integration**: [API Documentation](./api/)
- **Testing Setup**: [Testing Guides](./testing/)

### By Development Phase
- **Getting Started**: [Development > Setup](./development/)
- **Contributing**: [Development > Standards](./development/)
- **Deployment**: [Deployment > Automation](./deployment/)
- **Monitoring**: [Performance > Optimization](./performance/)

## 🔧 Configuration

The organization system can be customized via `.docs-organization-config.json`:

```json
{
  "manualCategorization": {
    "development": ["custom-workflow.md"],
    "features": ["new-feature.md"]
  },
  "rootExceptions": ["CUSTOM-ROOT-FILE.md"],
  "skipFiles": ["temp/draft.md"]
}
```

## 📝 Contributing to Documentation

1. **Create Documentation**: Write `.md` files anywhere in the repository
2. **Automatic Organization**: Files are automatically categorized and moved
3. **Manual Categorization**: Add specific files to `.docs-organization-config.json` if needed
4. **Review and Commit**: The system integrates with our Git workflow

### Documentation Standards
- Use clear, descriptive filenames
- Include proper headings and structure
- Add relevant keywords for better categorization
- Follow markdown best practices

## 🔍 Search and Navigation

### Quick Links
- [Development Setup](./development/DEVELOPER-SETUP.md)
- [Testing Guide](./testing/COMPREHENSIVE-TESTING-GUIDE.md)
- [Security Implementation](./security/SECURITY_IMPLEMENTATION_GUIDE.md)
- [Performance Optimization](./performance/PERFORMANCE_OPTIMIZATION_REPORT.md)
- [API Credentials](./api/API_CREDENTIALS_SETUP.md)

### Documentation System
- [Automated Organization Guide](./development/AUTOMATED-DOCUMENTATION-ORGANIZATION.md)
- [Documentation Enforcement](./development/DOCUMENTATION-ENFORCEMENT.md)

## 🆘 Support

If you can't find what you're looking for:

1. **Check the appropriate category folder**
2. **Use the repository search function**
3. **Review the [Development Setup](./development/DEVELOPER-SETUP.md) guide**
4. **Contact the development team**

## 📊 Documentation Statistics

This documentation system automatically maintains:
- ✅ **Organized Structure**: All files properly categorized
- 🔄 **Automatic Updates**: New files automatically organized
- 📈 **Growing Coverage**: Documentation expands with the project
- 🎯 **Easy Navigation**: Clear category-based organization

---

*This documentation structure is maintained by the BlazeCommerce automated documentation organization system.*
