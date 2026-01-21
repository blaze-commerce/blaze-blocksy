# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.61.0] - 2026-01-21

### Changed
- Update checkout mobile toggle default colors from white to black for better contrast
- Remove border-radius from checkout mobile toggle header for cleaner appearance

## [1.60.0] - 2026-01-15

### Added
- Add mobile/tablet checkout order summary toggle (#171)
- add auto-scroll to checkout validation errors (#169)
- Add Wishlist Overlay Backdrop with Z-Index Fix (#166)
- add customizer live preview sync for font and color options
- add font and color customizer options for product price and subtotal
- add Product Title Font customizer option for mini cart items
- add customizer option for SVG icon in cart panel heading
- improve mini-cart, product card and single product styles and functionality
- add SVG support in content rendering
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.59.0 (#183)
- revert(mini-cart) (#182)
- bump theme version to 1.58.0 (#180)
- bump theme version to 1.57.0 (#178)
- bump theme version to 1.56.0 (#176)
- bump theme version to 1.55.0 (#172)
- bump theme version to 1.54.0 (#170)
- bump theme version to 1.53.0 (#167)
- Fix FOUC issue with product carousel showing multiple rows before JS init (#157)
- bump theme version to 1.52.0 (#165)
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- update mini-cart, product-card, and single-product styles and functionality
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- remove orphaned assets/product/information files
- consolidate CSS and JS for single product performance
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- update CSS styles and add git commit rules
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- improve filter header persistence and code maintainability
- add function_exists guards to prevent redeclaration errors (#179)
- off-canvas menu z-index overlap at 1000px+ viewport (#177)
- wishlist css causing other diaglog to have overlay issue
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- restore Blocksy Customizer integration for image options
- simplify recursive function to properly handle Blocksy nested options
- use recursive function to insert design options in nested Blocksy structure
- fix grammar in empty cart message and button text
- Allow Checkout page in Blocksy header conditions (#150)
- fix padding and border-radius not working in customizer live preview
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.59.0] - 2026-01-13

### Added
- Add mobile/tablet checkout order summary toggle (#171)
- add auto-scroll to checkout validation errors (#169)
- Add Wishlist Overlay Backdrop with Z-Index Fix (#166)
- add customizer live preview sync for font and color options
- add font and color customizer options for product price and subtotal
- add Product Title Font customizer option for mini cart items
- add customizer option for SVG icon in cart panel heading
- improve mini-cart, product card and single product styles and functionality
- add SVG support in content rendering
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- revert(mini-cart) (#182)
- bump theme version to 1.58.0 (#180)
- bump theme version to 1.57.0 (#178)
- bump theme version to 1.56.0 (#176)
- bump theme version to 1.55.0 (#172)
- bump theme version to 1.54.0 (#170)
- bump theme version to 1.53.0 (#167)
- Fix FOUC issue with product carousel showing multiple rows before JS init (#157)
- bump theme version to 1.52.0 (#165)
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- update mini-cart, product-card, and single-product styles and functionality
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- remove orphaned assets/product/information files
- consolidate CSS and JS for single product performance
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- update CSS styles and add git commit rules
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- add function_exists guards to prevent redeclaration errors (#179)
- off-canvas menu z-index overlap at 1000px+ viewport (#177)
- wishlist css causing other diaglog to have overlay issue
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- restore Blocksy Customizer integration for image options
- simplify recursive function to properly handle Blocksy nested options
- use recursive function to insert design options in nested Blocksy structure
- fix grammar in empty cart message and button text
- Allow Checkout page in Blocksy header conditions (#150)
- fix padding and border-radius not working in customizer live preview
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.58.0] - 2026-01-13

### Added
- Add mobile/tablet checkout order summary toggle (#171)
- add auto-scroll to checkout validation errors (#169)
- Add Wishlist Overlay Backdrop with Z-Index Fix (#166)
- add customizer live preview sync for font and color options
- add font and color customizer options for product price and subtotal
- add Product Title Font customizer option for mini cart items
- add customizer option for SVG icon in cart panel heading
- improve mini-cart, product card and single product styles and functionality
- add SVG support in content rendering
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.57.0 (#178)
- bump theme version to 1.56.0 (#176)
- bump theme version to 1.55.0 (#172)
- bump theme version to 1.54.0 (#170)
- bump theme version to 1.53.0 (#167)
- Fix FOUC issue with product carousel showing multiple rows before JS init (#157)
- bump theme version to 1.52.0 (#165)
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- update mini-cart, product-card, and single-product styles and functionality
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- remove orphaned assets/product/information files
- consolidate CSS and JS for single product performance
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- update CSS styles and add git commit rules
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- add function_exists guards to prevent redeclaration errors (#179)
- off-canvas menu z-index overlap at 1000px+ viewport (#177)
- wishlist css causing other diaglog to have overlay issue
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- restore Blocksy Customizer integration for image options
- simplify recursive function to properly handle Blocksy nested options
- use recursive function to insert design options in nested Blocksy structure
- fix grammar in empty cart message and button text
- Allow Checkout page in Blocksy header conditions (#150)
- fix padding and border-radius not working in customizer live preview
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.57.0] - 2026-01-12

### Added
- Add mobile/tablet checkout order summary toggle (#171)
- add auto-scroll to checkout validation errors (#169)
- Add Wishlist Overlay Backdrop with Z-Index Fix (#166)
- add customizer live preview sync for font and color options
- add font and color customizer options for product price and subtotal
- add Product Title Font customizer option for mini cart items
- add customizer option for SVG icon in cart panel heading
- improve mini-cart, product card and single product styles and functionality
- add SVG support in content rendering
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.56.0 (#176)
- bump theme version to 1.55.0 (#172)
- bump theme version to 1.54.0 (#170)
- bump theme version to 1.53.0 (#167)
- Fix FOUC issue with product carousel showing multiple rows before JS init (#157)
- bump theme version to 1.52.0 (#165)
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- update mini-cart, product-card, and single-product styles and functionality
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- remove orphaned assets/product/information files
- consolidate CSS and JS for single product performance
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- update CSS styles and add git commit rules
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- off-canvas menu z-index overlap at 1000px+ viewport (#177)
- wishlist css causing other diaglog to have overlay issue
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- restore Blocksy Customizer integration for image options
- simplify recursive function to properly handle Blocksy nested options
- use recursive function to insert design options in nested Blocksy structure
- fix grammar in empty cart message and button text
- Allow Checkout page in Blocksy header conditions (#150)
- fix padding and border-radius not working in customizer live preview
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.56.0] - 2026-01-08

### Added
- Add mobile/tablet checkout order summary toggle (#171)
- add auto-scroll to checkout validation errors (#169)
- Add Wishlist Overlay Backdrop with Z-Index Fix (#166)
- add customizer live preview sync for font and color options
- add font and color customizer options for product price and subtotal
- add Product Title Font customizer option for mini cart items
- add customizer option for SVG icon in cart panel heading
- improve mini-cart, product card and single product styles and functionality
- add SVG support in content rendering
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.55.0 (#172)
- bump theme version to 1.54.0 (#170)
- bump theme version to 1.53.0 (#167)
- Fix FOUC issue with product carousel showing multiple rows before JS init (#157)
- bump theme version to 1.52.0 (#165)
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- update mini-cart, product-card, and single-product styles and functionality
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- remove orphaned assets/product/information files
- consolidate CSS and JS for single product performance
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- update CSS styles and add git commit rules
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- wishlist css causing other diaglog to have overlay issue
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- restore Blocksy Customizer integration for image options
- simplify recursive function to properly handle Blocksy nested options
- use recursive function to insert design options in nested Blocksy structure
- fix grammar in empty cart message and button text
- Allow Checkout page in Blocksy header conditions (#150)
- fix padding and border-radius not working in customizer live preview
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.55.0] - 2026-01-07

### Added
- Add mobile/tablet checkout order summary toggle (#171)
- add auto-scroll to checkout validation errors (#169)
- Add Wishlist Overlay Backdrop with Z-Index Fix (#166)
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.54.0 (#170)
- bump theme version to 1.53.0 (#167)
- Fix FOUC issue with product carousel showing multiple rows before JS init (#157)
- bump theme version to 1.52.0 (#165)
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.55.0] - 2026-01-06

### Added
- Mobile/tablet checkout order summary toggle with collapsible functionality
- Reordered checkout layout to display order summary above form on mobile/tablet
- Shopping cart icon and chevron indicator for order summary toggle
- Responsive CSS and JavaScript for checkout mobile enhancements (max-width: 1024px)

### Changed
- Order summary now appears above checkout form on mobile/tablet viewports
- Order summary starts collapsed by default on mobile/tablet for better UX
- Toggle header matches Figma design with cart icon, text, chevron, and total price

## [1.54.0] - 2026-01-01

### Added
- Wishlist overlay backdrop with dark overlay effect when panel opens
- Click-to-close functionality for wishlist panel (click outside to close)
- Smooth fade transitions for overlay appearance/disappearance
- Comprehensive documentation for wishlist overlay implementation

## [1.53.0] - 2026-01-01

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- Fix FOUC issue with product carousel showing multiple rows before JS init (#157)
- bump theme version to 1.52.0 (#165)
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.52.0] - 2025-12-23

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- fix enqueue path url
- Extracted all inline styles into a dedicated CSS file
- Add support for Result Count Placement
- bump theme version to 1.51.0 (#161)
- bump theme version to 1.50.0 (#159)
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Replace jQuery document.ready with robust DOM ready detection (#162)
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.51.0] - 2025-12-19

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.50.0 (#159)
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Restore full wishlist off-canvas functionality (#160)
- Remove transparent background override from search input (#158)
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.50.0] - 2025-12-15

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.49.0 (#156)
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Remove transparent background override from search input (#158)
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.49.0] - 2025-12-11

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- Revert "Fix: Allow Checkout page in Blocksy header conditions" (#155)
- bump theme version to 1.48.0 (#154)
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.48.0] - 2025-12-10

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- Remove infinitytargets custom code from functions.php (#153)
- bump theme version to 1.47.0 (#152)
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.47.0] - 2025-12-10

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- bump theme version to 1.46.0 (#151)
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- Allow Checkout page in Blocksy header conditions (#150)
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.46.0] - 2025-12-10

### Added
- replace Blocksy flexy with OwlCarousel + Fancybox for mobile slideshow
- add slideshow on mobile for stacked gallery
- jump user to the error wrapper
- add customizer option for recommendation products layout
- enhance product page styling and mini-cart improvements
- add Product Full Description element with live preview spacing
- add Product Stock element with instant live preview
- add documentation
- add Product Tabs element and disable option

### Changed
- add missing trailing newlines to CSS files
- bump theme version to 1.45.0 (#148)
- remove using buffer and use javascript handler to change the cart title
- replace external CDN resources with local assets
- bump theme version to 1.44.0
- standardize customizer options following product-stock pattern
- update
- checkpoint

### Fixed
- skip cache for empty cart to prevent cache key collision
- add file existence check before enqueuing customizer preview script in product-stock.php
- issue with static value in javascript
- remove unneeded script
- issue with changelog readme

### Documentation
- add live preview troubleshooting and update customizer documentation

## [1.45.0] - 2025-12-05

### Added
- add Order Summary typography control and Blocksy custom fonts integration (#132)
- Add custom field label replacement options for Fluid Checkout (#130)
- Add Fluid Checkout spacing fix using wp_add_inline_style (#128)
- add Progress Bar customization section to Fluid Checkout Styling (#126)
- reposition and style progress bar for full width display (#124)
- add default/inherit options to Fluid Checkout customizer controls (#121)
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add offcanvas documentation
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- complete modular offcanvas content
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.44.0 (#144)
- bump theme version to 1.43.0 (#133)
- bump theme version to 1.42.0 (#131)
- bump theme version to 1.41.0 (#129)
- bump theme version to 1.40.0 (#127)
- bump theme version to 1.39.0 (#125)
- update the reference doc
- bump theme version to 1.38.0 (#120)
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- bundle products add-to-cart redirects to product page
- Add proper spacing in Fluid Checkout payment notification for $0 orders (#145)
- apply FiboSearch product card layout fixes from hanglogiclive (#139)
- wishlist changes as this is on another pr
- incorrect minicart subtotal when coupon is added
- wishlist items not clickable
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.44.0] - 2025-12-05

### Added
- add Order Summary typography control and Blocksy custom fonts integration (#132)
- Add custom field label replacement options for Fluid Checkout (#130)
- Add Fluid Checkout spacing fix using wp_add_inline_style (#128)
- add Progress Bar customization section to Fluid Checkout Styling (#126)
- reposition and style progress bar for full width display (#124)
- add default/inherit options to Fluid Checkout customizer controls (#121)
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add offcanvas documentation
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- complete modular offcanvas content
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.43.0 (#133)
- bump theme version to 1.42.0 (#131)
- bump theme version to 1.41.0 (#129)
- bump theme version to 1.40.0 (#127)
- bump theme version to 1.39.0 (#125)
- update the reference doc
- bump theme version to 1.38.0 (#120)
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- apply FiboSearch product card layout fixes from hanglogiclive (#139)
- wishlist changes as this is on another pr
- incorrect minicart subtotal when coupon is added
- wishlist items not clickable
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.43.0] - 2025-11-11

### Added
- add Order Summary typography control and Blocksy custom fonts integration (#132)
- Add custom field label replacement options for Fluid Checkout (#130)
- Add Fluid Checkout spacing fix using wp_add_inline_style (#128)
- add Progress Bar customization section to Fluid Checkout Styling (#126)
- reposition and style progress bar for full width display (#124)
- add default/inherit options to Fluid Checkout customizer controls (#121)
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add offcanvas documentation
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- complete modular offcanvas content
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.42.0 (#131)
- bump theme version to 1.41.0 (#129)
- bump theme version to 1.40.0 (#127)
- bump theme version to 1.39.0 (#125)
- update the reference doc
- bump theme version to 1.38.0 (#120)
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.42.0] - 2025-11-11

### Added
- Add custom field label replacement options for Fluid Checkout (#130)
- Add Fluid Checkout spacing fix using wp_add_inline_style (#128)
- add Progress Bar customization section to Fluid Checkout Styling (#126)
- reposition and style progress bar for full width display (#124)
- add default/inherit options to Fluid Checkout customizer controls (#121)
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add offcanvas documentation
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- complete modular offcanvas content
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.41.0 (#129)
- bump theme version to 1.40.0 (#127)
- bump theme version to 1.39.0 (#125)
- update the reference doc
- bump theme version to 1.38.0 (#120)
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.41.0] - 2025-11-10

### Added
- Add Fluid Checkout spacing fix using wp_add_inline_style (#128)
- add Progress Bar customization section to Fluid Checkout Styling (#126)
- reposition and style progress bar for full width display (#124)
- add default/inherit options to Fluid Checkout customizer controls (#121)
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add offcanvas documentation
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- complete modular offcanvas content
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.40.0 (#127)
- bump theme version to 1.39.0 (#125)
- update the reference doc
- bump theme version to 1.38.0 (#120)
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.40.0] - 2025-11-10

### Added
- add Progress Bar customization section to Fluid Checkout Styling (#126)
- reposition and style progress bar for full width display (#124)
- add default/inherit options to Fluid Checkout customizer controls (#121)
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add offcanvas documentation
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- complete modular offcanvas content
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.39.0 (#125)
- update the reference doc
- bump theme version to 1.38.0 (#120)
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.39.0] - 2025-11-10

### Added
- reposition and style progress bar for full width display (#124)
- add default/inherit options to Fluid Checkout customizer controls (#121)
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add offcanvas documentation
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- complete modular offcanvas content
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- update the reference doc
- bump theme version to 1.38.0 (#120)
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.38.0] - 2025-11-10

### Added
- add Fluid Checkout customizer controls for step indicators and content text (#119)
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.37.0 (#118)
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.37.0] - 2025-11-10

### Added
- Remove Fluid Checkout reset functionality and reorganize documentation (#117)
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.36.0 (#115)
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.36.0] - 2025-11-08

### Added
- add Klaviyo star ratings integration with Blocksy theme compatibility (#114)
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- bump theme version to 1.35.0 (#113)
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.35.0] - 2025-10-29

### Added
- override product image size to use WooCommerce thumbnail (#112)
- add Blocksy customizer toggle for custom thank you page (#109)
- add gitignore for all custom files
- site customization init
- improve mix-and-match configuration
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- clean up unneeded code
- update docs
- bump theme version to 1.34.0 (#104)
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- restore missing form labels in template2 login form
- remove unneeded scripts
- adjust feedbacks
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.34.0] - 2025-09-26

### Added
- mini cart total adjusment
- shipping calculation adjustment
- adjustment to the feedbacks
- mini-cart adjustment for free shipping
- adjustment
- complete mix-and-match feature
- update gutenberg block
- change from modify the content to redirect user, since later we will implement cache function and it will be a problem if we modify the content
- check access to dealer-resources page if admin, editor or wholesale user
- add gutenberg block
- replace admin email with WooCommerce from email in thank-you page (#94)
- display navigation on product slider
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed
- refactor variables for shipping calculator
- update star rating
- update comment rating
- adjust star rating
- bump theme version to 1.33.0 (#103)
- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- adjust hover color link based on global var
- bump theme version to 1.32.0 (#100)
- readjust carousel
- mix and max adjustment
- wip
- wip
- bump theme version to 1.32.0
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed
- remove unneeded code
- merge conflict in changelog.md
- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- search
- readjust header.css and translate a doc
- switch from class component to function component
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.33.0] - 2025-09-24

### Added

- replace admin email with WooCommerce from email in thank-you page (#94)
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed

- Security fixes: Add plugin dependency safety checks and prevent memory leaks in variation swatches block (#102)
- bump theme version to 1.32.0 (#100)
- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed

- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.32.0] - 2025-09-23

### Added

- replace admin email with WooCommerce from email in thank-you page (#94)
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed

- add fields for my-account dashboard
- bump theme version to 1.31.0 (#95)
- bump theme version to 1.30.0 (#93)
- update css for dashboard
- add border radius field
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed

- Eliminate duplicate reviews tabs by adding conditional Judge.me plugin detection (#99)
- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.31.0] - 2025-09-16

### Added

- replace admin email with WooCommerce from email in thank-you page (#94)
- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed

- bump theme version to 1.30.0 (#93)
- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed

- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.30.0] - 2025-09-15

### Added

- modify layout on shop or product category page if sidebar enable
- refactor product card and add view-more button on the swatches options
- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed

- bump theme version to 1.29.0 (#91)
- Fix search issues and add product category customization (#90)
- wip
- update mobile search
- wip
- enqueue js scripts
- checkpoint
- bump theme version to 1.29.0
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed

- variation swatches
- filter toggle button
- hero banner style when sidebar active
- load recent-products ajax-ly
- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.29.0] - 2025-09-04

### Added

- add customizable URL field for mini cart help link
- update mini-cart and product features with enhanced styling and functionality
- load more fix
- load more button
- modification load more button
- update several things
- complete mini cart style
- product page adjustment
- apply judgeme
- complete product card
- wip product information block
- add documents

### Changed

- Fix search issues and add product category customization (#90)
- remove non-english docs
- WIP
- wip
- WIP
- WIP
- update doc
- update doc
- augment WIP
- WIP
- wip
- adjust you may also like section
- set heading offcanvas
- set heading style for filter offcanvas
- adjust mini cart
- adjust
- adjust style
- update several styles
- mini-cart improvement
- wip
- category
- update heading style in the product page
- disable conflic codes
- wip
- search customization for infinity targets
- update
- wip

### Fixed

- issue with wrong path
- update add to cart button
- downgrade php version to 8.1

## [1.28.0] - 2025-09-01

### Added

- disable minicart control script (#86)
- integrate resend email functionality with WooCommerce for enhanced user experience (#80)
- enhance auto-merge security with comprehensive improvements (#83)
- remove obsolete files and cleanup codebase for improved maintainability (#82)
- enforce mandatory /docs directory structure for all documentation (#81)
- integrate BlazeCommerce minicart control with WooCommerce for enhanced cart flow (#78)
- fix critical code quality issues in BlazeCommerce child theme (#75)
- fix critical CSS syntax error in style.css for layout stability (#74)
- integrate code cleanup with WordPress theme for improved stability and performance (#72)
- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- implement recently viewed products for empty wishlist
- standardize address grid layout and update CSS naming convention (#44)
- Implement Blocksy wishlist off-canvas customizations and cleanup
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming
- Add off-canvas wishlist functionality with customizer settings

### Changed

- bump theme version to 1.27.0 (#87)
- bump theme version to 1.26.0 (#85)
- bump theme version to 1.25.0 (#84)
- bump theme version to 1.24.0 (#79)
- bump theme version to 1.23.0 (#76)
- bump theme version to 1.22.0 (#73)
- bump theme version to 1.21.0 (#71)
- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- Remove WISHLIST-OFFCANVAS-FIX.md file
- Refactor wishlist.php for improved code organization and maintainability
- Refactor wishlist-offcanvas.css for improved organization and maintainability
- bump theme version to 1.11.0 (#48)
- Fix wishlist-item-actions div to only show when buttons are present
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- centralize guest notice HTML generation
- Wishlist off-canvas: remove hardcoded max-width so Customizer width applies; adjust close button size and padding
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP
- Wishlist off-canvas: apply Off-Canvas Width setting as max-width (width: 100vw, max-width responsive per device) so panel honors customizer width.
- Wishlist off-canvas: unify styling between wishlist and recommendation product cards; keep customizer-driven visibility intact. Also includes latest local tweaks.
- Wishlist off-canvas: show guest notice + Sign Up above recommendations for logged-out users; add helper + CSS; avoid duplicate notice in empty state
- Add wishlist AJAX interception functionality
- initial wishlist files
- initial wishlist files

### Fixed

- header having margin bottom that causes alignment issue
- empty cart icon not in the center
- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.27.0] - 2025-08-31

### Added

- disable minicart control script (#86)
- integrate resend email functionality with WooCommerce for enhanced user experience (#80)
- enhance auto-merge security with comprehensive improvements (#83)
- remove obsolete files and cleanup codebase for improved maintainability (#82)
- enforce mandatory /docs directory structure for all documentation (#81)
- integrate BlazeCommerce minicart control with WooCommerce for enhanced cart flow (#78)
- fix critical code quality issues in BlazeCommerce child theme (#75)
- fix critical CSS syntax error in style.css for layout stability (#74)
- integrate code cleanup with WordPress theme for improved stability and performance (#72)
- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.26.0 (#85)
- bump theme version to 1.25.0 (#84)
- bump theme version to 1.24.0 (#79)
- bump theme version to 1.23.0 (#76)
- bump theme version to 1.22.0 (#73)
- bump theme version to 1.21.0 (#71)
- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.26.0] - 2025-08-30

### Added

- integrate resend email functionality with WooCommerce for enhanced user experience (#80)
- enhance auto-merge security with comprehensive improvements (#83)
- remove obsolete files and cleanup codebase for improved maintainability (#82)
- enforce mandatory /docs directory structure for all documentation (#81)
- integrate BlazeCommerce minicart control with WooCommerce for enhanced cart flow (#78)
- fix critical code quality issues in BlazeCommerce child theme (#75)
- fix critical CSS syntax error in style.css for layout stability (#74)
- integrate code cleanup with WordPress theme for improved stability and performance (#72)
- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.25.0 (#84)
- bump theme version to 1.24.0 (#79)
- bump theme version to 1.23.0 (#76)
- bump theme version to 1.22.0 (#73)
- bump theme version to 1.21.0 (#71)
- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.25.0] - 2025-08-30

### Added

- remove obsolete files and cleanup codebase for improved maintainability (#82)
- enforce mandatory /docs directory structure for all documentation (#81)
- integrate BlazeCommerce minicart control with WooCommerce for enhanced cart flow (#78)
- fix critical code quality issues in BlazeCommerce child theme (#75)
- fix critical CSS syntax error in style.css for layout stability (#74)
- integrate code cleanup with WordPress theme for improved stability and performance (#72)
- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.24.0 (#79)
- bump theme version to 1.23.0 (#76)
- bump theme version to 1.22.0 (#73)
- bump theme version to 1.21.0 (#71)
- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.24.0] - 2025-08-30

### Added

- integrate BlazeCommerce minicart control with WooCommerce for enhanced cart flow (#78)
- fix critical code quality issues in BlazeCommerce child theme (#75)
- fix critical CSS syntax error in style.css for layout stability (#74)
- integrate code cleanup with WordPress theme for improved stability and performance (#72)
- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.23.0 (#76)
- bump theme version to 1.22.0 (#73)
- bump theme version to 1.21.0 (#71)
- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.23.0] - 2025-08-29

### Added

- fix critical code quality issues in BlazeCommerce child theme (#75)
- fix critical CSS syntax error in style.css for layout stability (#74)
- integrate code cleanup with WordPress theme for improved stability and performance (#72)
- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.22.0 (#73)
- bump theme version to 1.21.0 (#71)
- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.22.0] - 2025-08-28

### Added

- integrate code cleanup with WordPress theme for improved stability and performance (#72)
- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.21.0 (#71)
- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.21.0] - 2025-08-28

### Added

- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- remove development automation and security tooling while preserving core functionality (#70)
- bump theme version to 1.20.0 (#69)
- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.20.0] - 2025-08-28

### Added

- implement comprehensive site-agnostic cleanup and security hardening (#68)
- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.19.0 (#67)
- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.19.0] - 2025-08-28

### Added

- remove checkout sidebar widget area implementation (#66)
- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.18.0 (#65)
- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.18.0] - 2025-08-28

### Added

- improve code quality and security in blocksy-child theme (#64)
- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.17.0 (#63)
- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.17.0] - 2025-08-28

### Added

- implement automated markdown file organization system with intelligent categorization (#62)
- integrate comprehensive production release exclusions with GitHub Actions workflow (#60)
- implement enhanced changelog generation system and restore missing version history (#58)
- enhance changelog generation scripts with comprehensive code quality improvements (#56)
- enhance auto-merge workflow with configuration and comprehensive improvements (#55)
- comprehensive testing framework implementation with code review enhancements (#35)
- implement checkout sidebar widget area with security improvements (#50)
- integrate auto-merge workflow with BlazeCommerce automation bot for version bump PRs
- standardize address grid layout and update CSS naming convention (#44)
- implement critical security fixes and performance optimizations for WooCommerce thank you page asset loading (#40)
- add workflow fix documentation to README (#38)
- implement three-tier responsive layout for thank-you page container
- implement automated cleanup system for outdated version bump PRs
- implement comprehensive semantic versioning workflow with automated validation
- improve WordPress theme ZIP structure for consistent folder naming

### Changed

- bump theme version to 1.16.0 (#61)
- bump theme version to 1.15.0 (#57)
- bump theme version to 1.14.0 (#54)
- bump theme version to 1.13.0 (#53)
- bump theme version to 1.12.0 (#52)
- update my-account custom css
- bump theme version to 1.11.0 (#48)
- optimize admin performance by removing emoji scripts (#47)
- verify auto-merge workflow functionality (#46)
- bump theme version to 1.10.0 (#45)
- Fix/header z index mobile menu (#23)
- bump theme version to 1.9.0 (#41)
- exclude shell scripts and executable files from release ZIP

### Fixed

- respect branch protection rules in auto-merge workflow (#59)
- respect branch protection rules in auto-merge workflow
- add null checks to prevent jq errors in auto-merge workflow
- resolve circular dependency in auto-merge workflow that caused PR #48 failure (#51)
- resolve GitHub Actions workflow authentication failure (#42)
- resolve GitHub Actions workflow failure with existing tags (#39)
- resolve GitHub Actions workflow failure with existing tags
- resolve YAML syntax error and regex patterns in cleanup workflow
- resolve bash arithmetic operation failure in PR validation workflow
- exclude .augmentignore and backup files from release ZIP

### Documentation

- enhance auto-merge workflow with configuration and documentation

## [1.16.0] - 2025-08-28

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

## [1.15.0] - 2025-08-28

## [1.14.0] - 2025-08-28

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
[1.14.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.14.0
[1.15.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.15.0
[1.16.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.16.0
[1.17.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.17.0
[1.18.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.18.0
[1.19.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.19.0
[1.20.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.20.0
[1.21.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.21.0
[1.22.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.22.0
[1.23.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.23.0
[1.24.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.24.0
[1.25.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.25.0
[1.26.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.26.0
[1.27.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.27.0
[1.28.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.8.0...v1.28.0
[1.29.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.29.0
[1.30.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.30.0
[1.31.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.31.0
[1.32.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.32.0
[1.34.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.34.0
[1.35.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.35.0
[1.36.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.36.0
[1.37.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.37.0
[1.38.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.38.0
[1.39.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.39.0
[1.40.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.40.0
[1.41.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.41.0
[1.42.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.42.0
[1.43.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.43.0
[1.44.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.44.0
[1.45.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.28.0...v1.45.0
[1.46.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.46.0
[1.47.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.47.0
[1.48.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.48.0
[1.49.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.49.0
[1.50.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.50.0
[1.51.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.51.0
[1.52.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.52.0
[1.53.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.53.0
[1.54.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.54.0
[1.55.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.55.0
[1.56.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.56.0
[1.57.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.57.0
[1.58.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.58.0
[1.59.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.59.0
[1.60.0]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.45.0...v1.60.0
[unreleased]: https://github.com/blaze-commerce/blaze-blocksy/compare/v1.60.0...HEAD
