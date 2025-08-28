# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Enhanced changelog generation system with automatic categorization from conventional commits
- Comprehensive changelog generation script (`scripts/generate-changelog.py`) for manual use
- Detailed documentation for changelog maintenance and best practices

### Changed
- Improved release workflow to automatically generate detailed changelog sections when [Unreleased] is empty
- Enhanced Python script in release workflow to parse conventional commits and categorize changes
- Updated changelog generation to preserve manual entries while providing automatic fallback

### Fixed
- Changelog generation issue where versions after 2.0.3 had empty sections
- Missing categorized information (Added, Changed, Fixed, Documentation, etc.) in recent releases
- Workflow dependency on manual changelog maintenance that was causing empty release notes

### Documentation
- Added `docs/CHANGELOG-GENERATION.md` with comprehensive guide for developers
- Updated workflow documentation to explain new automatic changelog generation
- Provided examples and best practices for conventional commits and manual changelog entries

## [1.13.0] - 2025-08-28

### Added
- Comprehensive testing framework implementation with code review enhancements
- Checkout sidebar widget area with security improvements

## [1.12.0] - 2025-08-28

### Added
- Checkout sidebar widget area with security improvements

### Changed
- Updated my-account custom CSS styling

### Fixed
- Circular dependency in auto-merge workflow that caused PR #48 failure

## [1.11.0] - 2025-08-28

### Added
- Auto-merge workflow integration with BlazeCommerce automation bot for version bump PRs

### Changed
- Updated my-account custom CSS styling
- Optimized admin performance by removing emoji scripts
- Verified auto-merge workflow functionality

### Fixed
- Circular dependency in auto-merge workflow that caused PR #48 failure

## [1.10.0] - 2025-08-28

### Added
- Auto-merge workflow integration with BlazeCommerce automation bot for version bump PRs
- Standardized address grid layout and updated CSS naming convention

### Changed
- Optimized admin performance by removing emoji scripts
- Verified auto-merge workflow functionality
- Fixed header z-index for mobile menu

### Fixed
- GitHub Actions workflow authentication failure

## [1.9.0] - 2025-08-27

### Added
- Standardized address grid layout and updated CSS naming convention
- Critical security fixes and performance optimizations for WooCommerce thank you page asset loading
- Workflow fix documentation to README
- Three-tier responsive layout for thank-you page container
- Automated cleanup system for outdated version bump PRs
- Comprehensive semantic versioning workflow with automated validation
- WordPress theme ZIP structure improvements for consistent folder naming

### Changed
- Fixed header z-index for mobile menu

### Fixed
- GitHub Actions workflow authentication failure
- GitHub Actions workflow failure with existing tags (multiple instances)
- YAML syntax error and regex patterns in cleanup workflow
- Bash arithmetic operation failure in PR validation workflow
- Excluded .augmentignore and backup files from release ZIP

### Security
- Excluded shell scripts and executable files from release ZIP

## [1.8.0] - 2025-08-27

### Added
- My-account settings migration to WordPress Customizer with live preview

### Changed
- Enhanced my-account customization functionality

## [1.7.0] - 2025-08-27

### Added
- Enhanced thank you page styling and layout for Blaze Commerce

## [1.6.0] - 2025-08-27

### Added
- Complete Blaze Commerce thank you page implementation
- Thank you page customization integration with WooCommerce for checkout enhancement

## [1.5.0] - 2025-08-26

### Added
- Checkout customizations integration with Blaze Commerce theme
- Reorganized Augment rules configuration structure

## [1.4.0] - 2025-08-26

### Added
- Complete Blaze Commerce rebranding and critical fixes for thank you page (v2.0.3)

### Documentation
- Updated CHANGELOG.md with v2.0.3 Blaze Commerce branding release
- Added comprehensive Blaze Commerce documentation and branding updates

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
[1.7.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.6.0...v1.7.0
[1.8.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.7.0...v1.8.0
[1.9.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.9.0
[1.10.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.10.0
[1.11.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.11.0
[1.12.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.12.0
[1.13.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.13.0
[unreleased]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.13.0...HEAD
[1.3.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/blaze-commerce/blaze-blocksy/releases/tag/v1.0.0
