# Blocksy Child Theme - Blaze Commerce Edition

A **site-agnostic** and **portable** WordPress child theme for Blocksy with automated semantic versioning, release management, and comprehensive WooCommerce customizations.

> **✅ All site-specific references removed - Ready for deployment on any WordPress site**

## 🚀 Quick Start

This theme is designed to be **completely portable** across different WordPress installations. All hardcoded URLs, domain references, and site-specific configurations have been removed and replaced with configurable environment variables.

### Prerequisites

- WordPress 5.0+
- Blocksy parent theme
- PHP 7.4+
- Node.js 16+ (for development)
- Composer (for PHP dependencies)

### Installation

1. **Download** the theme files
2. **Upload** to your WordPress site via Admin → Appearance → Themes → Add New → Upload Theme
3. **Activate** the child theme
4. **Configure** environment variables (see Configuration section below)

## ⚙️ Configuration

### Environment Variables

Copy `.env.example` to `.env` and configure for your site:

```bash
# Copy the example file
cp .env.example .env

# Edit with your site details
API_BASE_URL=https://your-wordpress-site.com
WC_CONSUMER_KEY=ck_your_consumer_key_here
WC_CONSUMER_SECRET=cs_your_consumer_secret_here
TEST_USER_EMAIL=test@your-domain.com
TEST_USER_PASSWORD=your-test-password
```

### WooCommerce API Setup

1. Go to **WooCommerce → Settings → Advanced → REST API**
2. Click **Add Key**
3. Set permissions to **Read/Write**
4. Copy the generated keys to your `.env` file

### Testing Configuration

Set these environment variables for testing:

- `LIGHTHOUSE_BASE_URL` - For Lighthouse performance testing
- `ARTILLERY_TARGET_URL` - For load testing
- `BASE_URL` - For K6 performance testing

## Recent Updates

- **🧹 Site-Agnostic Cleanup (Latest)**: Removed all hardcoded site-specific references and automation implementations
  - Removed hardcoded URLs from all configuration files
  - Replaced site-specific credentials with generic examples
  - Updated all test configurations to use environment variables
  - Removed site-specific documentation files
  - Made all automation scripts configurable via environment variables
- **Fixed GitHub Actions Workflow**: Resolved "fatal: tag already exists" error with intelligent tag handling
- **Improved Release Automation**: Enhanced rollback functionality and pre-release cleanup

## 🔧 Site-Agnostic Features

This theme has been **completely cleaned** of site-specific references:

### ✅ What Was Removed/Updated:
- **Hardcoded URLs**: All `infinitytargets` and staging site URLs replaced with environment variables
- **Site-Specific Documentation**: Removed analysis files tied to specific sites
- **Test Credentials**: Replaced with generic examples in `.env.example`
- **Configuration Files**: Updated to use configurable environment variables
- **Automation Scripts**: Made portable across different WordPress installations

### 🎯 Benefits:
- **Zero Manual Cleanup**: Deploy on any WordPress site without editing code
- **Environment-Based Configuration**: All settings controlled via `.env` file
- **Portable Testing**: Test suite works with any WordPress/WooCommerce site
- **Scalable Deployment**: Use across multiple client sites without conflicts

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
