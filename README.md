# Blocksy Child Theme - Blaze Commerce Edition

A WordPress child theme for Blocksy by BlazeCommerce with automated semantic versioning, release management, and comprehensive WooCommerce customizations.

## Features

- **Blaze Commerce Thank You Page**: Complete custom thank you page implementation with responsive design
- **Responsive visibility classes**: Hide/show elements on mobile, tablet, desktop
- **Judge.me review carousel customizations**: Enhanced product review displays
- **Sticky header z-index optimizations**: Improved navigation behavior
- **Mobile-responsive design improvements**: Optimized for all viewport sizes
- **WooCommerce integration**: Enhanced checkout and order confirmation experience

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
