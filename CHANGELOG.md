# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.6.0] - 2025-08-27

## [1.5.0] - 2025-08-26

## [1.4.0] - 2025-08-26

## [2.0.3] - 2025-08-26

### Added
- Complete Blaze Commerce branding implementation for thank you page
- Comprehensive documentation suite with implementation guides
- Real order data analysis from staging environment (Order #1001380)
- Security enhancements with proper email output escaping
- Reliability improvements with null-safe order date handling
- Performance optimization with updated asset versioning

### Changed
- Complete rebranding from Figma to Blaze Commerce naming conventions
- CSS classes: `.figma-*` → `.blaze-commerce-*` across all selectors
- PHP functions: `blocksy_child_figma_*` → `blocksy_child_blaze_commerce_*`
- JavaScript selectors, function names, and console messages updated
- All comments and documentation updated with Blaze Commerce references
- Asset versions updated from 2.0.1 to 2.0.3 for proper cache invalidation

### Fixed
- Critical typo in thank you message: "Thank for your Order!" → "Thank you for your Order!"
- Email output security with `esc_html()` wrapper implementation
- Order date null handling with "N/A" fallback for missing data
- Cache management with proper asset versioning

### Documentation
- Added `docs/THANK-YOU-PAGE-CUSTOMIZATION.md` - Complete customization guide (306 lines)
- Added `docs/blaze-commerce-thank-you-implementation.md` - Implementation specifications (219 lines)
- Added `docs/thank-you-page-analysis.md` - Technical analysis with real order data (640 lines)
- Updated `README.md` with Blaze Commerce features and documentation links
- Renamed and updated all documentation files with consistent branding

## [1.3.0] - 2025-08-26

### Added
- Comprehensive versioning automation with Augment tooling exclusions
- Enhanced GitHub Actions workflow with intelligent commit analysis
- Automated changelog maintenance during releases

### Changed
- Improved release workflow to prevent cascading releases from tooling changes
- Enhanced commit filtering to exclude non-functional changes

### Fixed
- Cascading versioning issue from automated version bump PRs
- Empty releases triggered by Augment tooling file changes

## [1.2.0] - 2025-08-23

### Added
- .augmentignore file for optimized codebase indexing
- Comprehensive Augment documentation rules configuration
- CI integration with taxonomy and validation enhancements

### Changed
- Switched to structured priority objects for Augment configuration
- Enhanced YAML configuration with proper escaping

### Fixed
- YAML escaping issues in configuration files

## [1.1.0] - 2025-08-22

### Added
- Automated semantic versioning with branch protection support
- GitHub App token authentication for PR creation
- Comprehensive theme functionality enhancements
- Advanced theme customizations for Infinity Targets site
- Image file extensions to .gitignore
- Custom checkout styling and functionality
- Search customizations and theme assets

### Changed
- Enhanced functions.php with comprehensive theme functionality
- Modified style.css for Infinity Targets theme customizations
- Enforced branch-protected release flow
- Tightened ZIP exclusions for release packages

### Fixed
- GitHub Actions PR creation restrictions with App token
- Existing release branches handling to prevent push conflicts
- Critical workflow errors and security vulnerabilities
- Command injection vulnerabilities with proper escaping
- Changelog URL generation for first releases
- Version format validation and update verification

### Security
- Added proper error handling with set -e throughout workflow
- Implemented secure command escaping to prevent injection attacks
- Enhanced token-based authentication for automated operations

## [1.0.0] - 2025-08-14

### Added
- Initial Blocksy child theme implementation
- Basic theme structure and configuration
- Core theme files and assets

[2.0.3]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.3.0...v2.0.3
[1.4.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.3.0...v1.4.0
[1.5.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.4.0...v1.5.0
[1.6.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.5.0...v1.6.0
[unreleased]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.6.0...HEAD
[1.3.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/blaze-commerce/blaze-blocksy/releases/tag/v1.0.0
