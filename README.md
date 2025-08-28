# Blocksy Child Theme - Blaze Commerce Edition

A WordPress child theme for Blocksy by BlazeCommerce with automated semantic versioning, release management, and comprehensive WooCommerce customizations.

## Recent Updates

- **Fixed GitHub Actions Workflow**: Resolved "fatal: tag already exists" error with intelligent tag handling
- **Improved Release Automation**: Enhanced rollback functionality and pre-release cleanup

## Features

- **Blaze Commerce Thank You Page**: Complete custom thank you page implementation with responsive design
- **Responsive visibility classes**: Hide/show elements on mobile, tablet, desktop
- **Judge.me review carousel customizations**: Enhanced product review displays
- **Sticky header z-index optimizations**: Improved navigation behavior
- **Mobile-responsive design improvements**: Optimized for all viewport sizes
- **WooCommerce integration**: Enhanced checkout and order confirmation experience

## 🧪 **Advanced Testing Framework**

[![Security Tests](https://img.shields.io/badge/Security-95%2F100-brightgreen)](tests/security/)
[![API Tests](https://img.shields.io/badge/API-34%20Tests-blue)](tests/api/)
[![Performance](https://img.shields.io/badge/Performance-A%2B%20Grade-brightgreen)](tests/performance/)
[![Database](https://img.shields.io/badge/Database-8%20Tests-blue)](tests/database/)
[![CI/CD](https://img.shields.io/badge/CI%2FCD-Ready-brightgreen)](.github/workflows/)

### **World-Class Testing Infrastructure:**
- 🔒 **Security Testing** - Vulnerability scanning and penetration testing (8 tests)
- 🌐 **API Testing** - WooCommerce REST API comprehensive validation (34 tests)
- 🗄️ **Database Testing** - Database integrity and transaction validation (8 tests)
- ⚡ **Performance Testing** - Load testing and Core Web Vitals monitoring
- 🔗 **Integration Testing** - End-to-end workflow validation with Playwright
- 🤖 **CI/CD Automation** - GitHub Actions with parallel test execution

### **Current Performance Metrics:**
- **Performance Grade**: A+ (95/100)
- **First Contentful Paint**: 541.8ms (✅ 70% better than threshold)
- **Largest Contentful Paint**: 1264.2ms (✅ 49% better than threshold)
- **Cumulative Layout Shift**: 0.038 (✅ 62% better than threshold)
- **Security Score**: 95/100 (after implementing security fixes)

### **Quick Testing Commands:**
```bash
# Run all tests
npm test

# Security tests
npm run security:test

# API tests (requires WooCommerce credentials)
npm run test:api:rest

# Performance baseline
npm run performance:baseline

# Database tests (requires MySQL)
npm run test:database
```

### **Complete Documentation:**
- **[📚 Comprehensive Testing Guide](docs/COMPREHENSIVE_TESTING_GUIDE.md)** - Complete team onboarding
- **[🔒 Security Implementation Guide](security-fixes/SECURITY_IMPLEMENTATION_GUIDE.md)** - Security hardening
- **[🌐 API Credentials Setup](tests/api/API_CREDENTIALS_SETUP.md)** - API testing configuration
- **[⚡ Performance Optimization Report](performance-optimizations/PERFORMANCE_OPTIMIZATION_REPORT.md)** - Performance enhancements

## Automated Releases

This repository uses automated semantic versioning with GitHub Actions. Version numbers are automatically calculated based on conventional commit messages and releases are created with downloadable ZIP files.

### Quick Start for Developers

1. Use conventional commit messages:
   - `fix:` for bug fixes (PATCH version bump)
   - `feat:` for new features (MINOR version bump)
   - `feat!:` or `BREAKING CHANGE:` for breaking changes (MAJOR version bump)

2. Create pull requests with descriptive titles
3. Merge PRs to trigger automatic releases

### Documentation

- [Versioning Strategy](docs/VERSIONING.md) - Complete guide to semantic versioning and conventional commits
- [Testing Instructions](docs/TESTING.md) - How to test the automated release workflow
- [Blaze Commerce Thank You Page](docs/THANK-YOU-PAGE-CUSTOMIZATION.md) - Complete customization guide
- [Thank You Page Analysis](docs/thank-you-page-analysis.md) - Technical analysis and implementation details

## Installation

### From GitHub Release (Recommended)
1. Go to the [Releases page](../../releases)
2. Download the latest `blocksy-child-vX.Y.Z.zip` file
3. Upload via WordPress Admin → Appearance → Themes → Add New → Upload Theme
4. The extracted theme folder will be consistently named `blocksy-child` regardless of version

### Manual Installation
1. Clone this repository to your WordPress themes directory
2. Activate the theme in WordPress Admin

## Development

The theme follows WordPress coding standards and includes:
- Semantic versioning in `style.css` header
- Automated changelog generation
- ZIP distribution for easy installation
- Rollback mechanisms for failed releases

## Version History

All releases and changes are tracked in the [GitHub Releases](../../releases) section with auto-generated changelogs.
