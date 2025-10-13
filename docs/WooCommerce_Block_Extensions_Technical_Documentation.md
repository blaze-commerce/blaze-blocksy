# WooCommerce Gutenberg Block Extensions - Technical Documentation

## Overview

This document provides technical implementation guidelines for extending WooCommerce Gutenberg blocks without modifying the core WooCommerce plugin. The modifications include:

1. **Product Collection Block**: Adding responsive column and product count controls
2. **Product Image Block**: Adding hover image swap and wishlist functionality

## Prerequisites

- WordPress 6.0+
- WooCommerce 8.0+
- Basic knowledge of WordPress block development
- Understanding of React/JavaScript and PHP
- Familiarity with WordPress hooks and filters

## Architecture Overview

WooCommerce blocks are built using:
- **Block.json** metadata files for block definitions
- **PHP classes** extending `AbstractBlock` for server-side rendering
- **React components** for editor interface
- **Frontend JavaScript** for interactivity

## Implementation Strategy

### Approach 1: Block Variations (Recommended)
Create custom block variations that extend existing blocks with additional attributes and functionality.

### Approach 2: Block Filters
Use WordPress filters to modify block attributes and rendering without touching core files.

### Approach 3: Custom Plugin
Develop a standalone plugin that registers enhanced versions of the blocks.

---

## 1. Product Collection Block Enhancement

### 1.1 Responsive Columns and Product Count

#### File Structure
```
wp-content/plugins/wc-block-extensions/
├── wc-block-extensions.php
├── includes/
│   ├── class-product-collection-extension.php
│   └── class-block-extensions-loader.php
├── assets/
│   ├── js/
│   │   ├── product-collection-extension.js
│   │   └── product-collection-frontend.js
│   └── css/
│       └── product-collection-extension.css
└── block-extensions/
    └── product-collection-responsive/
        ├── block.json
        ├── edit.js
        ├── save.js
        └── index.js
```

#### 1.2 Main Plugin File

**File: `wp-content/plugins/wc-block-extensions/wc-block-extensions.php`**

```php
<?php
/**
 * Plugin Name: WooCommerce Block Extensions
 * Description: Extends WooCommerce Gutenberg blocks with responsive features
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 */

defined('ABSPATH') || exit;

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

define('WC_BLOCK_EXTENSIONS_VERSION', '1.0.0');
define('WC_BLOCK_EXTENSIONS_PLUGIN_FILE', __FILE__);
define('WC_BLOCK_EXTENSIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_BLOCK_EXTENSIONS_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Initialize the plugin
add_action('plugins_loaded', function() {
    require_once WC_BLOCK_EXTENSIONS_PLUGIN_PATH . 'includes/class-block-extensions-loader.php';
    new WC_Block_Extensions_Loader();
});
```

#### 1.3 Block Extensions Loader

**File: `includes/class-block-extensions-loader.php`**

```php
<?php
defined('ABSPATH') || exit;

class WC_Block_Extensions_Loader {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function init() {
        // Register block variations
        add_action('init', array($this, 'register_block_variations'));
        
        // Extend Product Collection block
        require_once WC_BLOCK_EXTENSIONS_PLUGIN_PATH . 'includes/class-product-collection-extension.php';
        new WC_Product_Collection_Extension();
    }
    
    public function register_block_variations() {
        // Register enhanced Product Collection variation
        register_block_type(
            WC_BLOCK_EXTENSIONS_PLUGIN_PATH . 'block-extensions/product-collection-responsive'
        );
    }
    
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'wc-block-extensions-editor',
            WC_BLOCK_EXTENSIONS_PLUGIN_URL . 'assets/js/product-collection-extension.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            WC_BLOCK_EXTENSIONS_VERSION
        );
        
        wp_enqueue_style(
            'wc-block-extensions-editor',
            WC_BLOCK_EXTENSIONS_PLUGIN_URL . 'assets/css/product-collection-extension.css',
            array(),
            WC_BLOCK_EXTENSIONS_VERSION
        );
    }
    
    public function enqueue_frontend_assets() {
        if (has_block('woocommerce/product-collection')) {
            wp_enqueue_script(
                'wc-block-extensions-frontend',
                WC_BLOCK_EXTENSIONS_PLUGIN_URL . 'assets/js/product-collection-frontend.js',
                array('jquery'),
                WC_BLOCK_EXTENSIONS_VERSION,
                true
            );
        }
    }
}
```

#### 1.4 Product Collection Extension Class

**File: `includes/class-product-collection-extension.php`**

```php
<?php
defined('ABSPATH') || exit;

class WC_Product_Collection_Extension {

    public function __construct() {
        add_filter('render_block_woocommerce/product-collection', array($this, 'add_responsive_attributes'), 10, 2);
        add_filter('block_type_metadata', array($this, 'extend_product_collection_metadata'));
    }

    /**
     * Extend Product Collection block metadata with responsive attributes
     */
    public function extend_product_collection_metadata($metadata) {
        if (isset($metadata['name']) && $metadata['name'] === 'woocommerce/product-collection') {
            $metadata['attributes'] = array_merge($metadata['attributes'], array(
                'responsiveColumns' => array(
                    'type' => 'object',
                    'default' => array(
                        'desktop' => 3,
                        'tablet' => 2,
                        'mobile' => 1
                    )
                ),
                'responsiveProductCount' => array(
                    'type' => 'object',
                    'default' => array(
                        'desktop' => 9,
                        'tablet' => 6,
                        'mobile' => 4
                    )
                ),
                'enableResponsive' => array(
                    'type' => 'boolean',
                    'default' => false
                )
            ));
        }
        return $metadata;
    }

    /**
     * Add responsive CSS classes and data attributes to Product Collection block
     */
    public function add_responsive_attributes($block_content, $block) {
        if (!isset($block['attrs']['enableResponsive']) || !$block['attrs']['enableResponsive']) {
            return $block_content;
        }

        $responsive_columns = $block['attrs']['responsiveColumns'] ?? array();
        $responsive_counts = $block['attrs']['responsiveProductCount'] ?? array();

        // Add CSS classes and data attributes for responsive behavior
        $processor = new WP_HTML_Tag_Processor($block_content);

        if ($processor->next_tag(array('class_name' => 'wp-block-woocommerce-product-collection'))) {
            $processor->add_class('wc-responsive-collection');

            // Add data attributes for JavaScript
            if (!empty($responsive_columns)) {
                $processor->set_attribute('data-responsive-columns', wp_json_encode($responsive_columns));
            }
            if (!empty($responsive_counts)) {
                $processor->set_attribute('data-responsive-counts', wp_json_encode($responsive_counts));
            }
        }

        return $processor->get_updated_html();
    }
}
```

#### 1.5 Block Editor JavaScript Extension

**File: `assets/js/product-collection-extension.js`**

```javascript
(function(wp) {
    const { addFilter } = wp.hooks;
    const { createHigherOrderComponent } = wp.compose;
    const { Fragment } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl, RangeControl } = wp.components;
    const { __ } = wp.i18n;

    // Add responsive controls to Product Collection block
    const withResponsiveControls = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, setAttributes, name } = props;

            if (name !== 'woocommerce/product-collection') {
                return <BlockEdit {...props} />;
            }

            const {
                enableResponsive = false,
                responsiveColumns = { desktop: 3, tablet: 2, mobile: 1 },
                responsiveProductCount = { desktop: 9, tablet: 6, mobile: 4 }
            } = attributes;

            return (
                <Fragment>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <PanelBody
                            title={__('Responsive Settings', 'wc-block-extensions')}
                            initialOpen={false}
                        >
                            <ToggleControl
                                label={__('Enable Responsive Layout', 'wc-block-extensions')}
                                checked={enableResponsive}
                                onChange={(value) => setAttributes({ enableResponsive: value })}
                            />

                            {enableResponsive && (
                                <Fragment>
                                    <h4>{__('Columns per Device', 'wc-block-extensions')}</h4>
                                    <RangeControl
                                        label={__('Desktop Columns', 'wc-block-extensions')}
                                        value={responsiveColumns.desktop}
                                        onChange={(value) => setAttributes({
                                            responsiveColumns: { ...responsiveColumns, desktop: value }
                                        })}
                                        min={1}
                                        max={6}
                                    />
                                    <RangeControl
                                        label={__('Tablet Columns', 'wc-block-extensions')}
                                        value={responsiveColumns.tablet}
                                        onChange={(value) => setAttributes({
                                            responsiveColumns: { ...responsiveColumns, tablet: value }
                                        })}
                                        min={1}
                                        max={4}
                                    />
                                    <RangeControl
                                        label={__('Mobile Columns', 'wc-block-extensions')}
                                        value={responsiveColumns.mobile}
                                        onChange={(value) => setAttributes({
                                            responsiveColumns: { ...responsiveColumns, mobile: value }
                                        })}
                                        min={1}
                                        max={2}
                                    />

                                    <h4>{__('Products per Device', 'wc-block-extensions')}</h4>
                                    <RangeControl
                                        label={__('Desktop Products', 'wc-block-extensions')}
                                        value={responsiveProductCount.desktop}
                                        onChange={(value) => setAttributes({
                                            responsiveProductCount: { ...responsiveProductCount, desktop: value }
                                        })}
                                        min={1}
                                        max={20}
                                    />
                                    <RangeControl
                                        label={__('Tablet Products', 'wc-block-extensions')}
                                        value={responsiveProductCount.tablet}
                                        onChange={(value) => setAttributes({
                                            responsiveProductCount: { ...responsiveProductCount, tablet: value }
                                        })}
                                        min={1}
                                        max={12}
                                    />
                                    <RangeControl
                                        label={__('Mobile Products', 'wc-block-extensions')}
                                        value={responsiveProductCount.mobile}
                                        onChange={(value) => setAttributes({
                                            responsiveProductCount: { ...responsiveProductCount, mobile: value }
                                        })}
                                        min={1}
                                        max={8}
                                    />
                                </Fragment>
                            )}
                        </PanelBody>
                    </InspectorControls>
                </Fragment>
            );
        };
    }, 'withResponsiveControls');

    addFilter(
        'editor.BlockEdit',
        'wc-block-extensions/product-collection-responsive',
        withResponsiveControls
    );

})(window.wp);
```

#### 1.6 Frontend JavaScript for Responsive Behavior

**File: `assets/js/product-collection-frontend.js`**

```javascript
(function($) {
    'use strict';

    class ResponsiveProductCollection {
        constructor() {
            this.init();
        }

        init() {
            this.setupResponsiveCollections();
            this.bindEvents();
        }

        setupResponsiveCollections() {
            $('.wc-responsive-collection').each((index, element) => {
                this.setupCollection($(element));
            });
        }

        setupCollection($collection) {
            const responsiveColumns = $collection.data('responsive-columns');
            const responsiveCounts = $collection.data('responsive-counts');

            if (!responsiveColumns || !responsiveCounts) {
                return;
            }

            this.applyResponsiveLayout($collection, responsiveColumns, responsiveCounts);
        }

        applyResponsiveLayout($collection, columns, counts) {
            const currentBreakpoint = this.getCurrentBreakpoint();
            const targetColumns = columns[currentBreakpoint] || columns.desktop;
            const targetCount = counts[currentBreakpoint] || counts.desktop;

            // Apply column classes
            $collection.removeClass('columns-1 columns-2 columns-3 columns-4 columns-5 columns-6');
            $collection.addClass(`columns-${targetColumns}`);

            // Hide/show products based on count
            const $products = $collection.find('.wp-block-woocommerce-product-template .wp-block-post');
            $products.each((index, product) => {
                if (index < targetCount) {
                    $(product).show();
                } else {
                    $(product).hide();
                }
            });

            // Update CSS custom properties for responsive grid
            $collection[0].style.setProperty('--wc-responsive-columns', targetColumns);
        }

        getCurrentBreakpoint() {
            const width = window.innerWidth;

            if (width >= 1024) {
                return 'desktop';
            } else if (width >= 768) {
                return 'tablet';
            } else {
                return 'mobile';
            }
        }

        bindEvents() {
            let resizeTimer;
            $(window).on('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.setupResponsiveCollections();
                }, 250);
            });
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        new ResponsiveProductCollection();
    });

})(jQuery);
```

#### 1.7 CSS Styles

**File: `assets/css/product-collection-extension.css`**

```css
/* Responsive Product Collection Styles */
.wc-responsive-collection {
    --wc-responsive-columns: 3;
}

.wc-responsive-collection .wp-block-woocommerce-product-template {
    display: grid !important;
    grid-template-columns: repeat(var(--wc-responsive-columns), 1fr);
    gap: 1.5rem;
}

/* Responsive breakpoints */
@media (max-width: 1023px) {
    .wc-responsive-collection {
        --wc-responsive-columns: 2;
    }
}

@media (max-width: 767px) {
    .wc-responsive-collection {
        --wc-responsive-columns: 1;
    }

    .wc-responsive-collection .wp-block-woocommerce-product-template {
        gap: 1rem;
    }
}

/* Editor styles */
.wp-block-woocommerce-product-collection.wc-responsive-collection .wp-block-woocommerce-product-template {
    display: grid;
    grid-template-columns: repeat(var(--wc-responsive-columns), 1fr);
    gap: 1.5rem;
}
```

---

## 2. Product Image Block Enhancement

### 2.1 Hover Image Swap and Wishlist Integration

#### 2.2 Product Image Extension Class

**File: `includes/class-product-image-extension.php`**

```php
<?php
defined('ABSPATH') || exit;

class WC_Product_Image_Extension {

    public function __construct() {
        add_filter('render_block_woocommerce/product-image', array($this, 'add_hover_and_wishlist'), 10, 2);
        add_filter('block_type_metadata', array($this, 'extend_product_image_metadata'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_product_image_assets'));
    }

    /**
     * Extend Product Image block metadata
     */
    public function extend_product_image_metadata($metadata) {
        if (isset($metadata['name']) && $metadata['name'] === 'woocommerce/product-image') {
            $metadata['attributes'] = array_merge($metadata['attributes'], array(
                'enableHoverImage' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'showWishlistButton' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'wishlistButtonPosition' => array(
                    'type' => 'string',
                    'default' => 'top-right',
                    'enum' => array('top-left', 'top-right', 'bottom-left', 'bottom-right')
                )
            ));
        }
        return $metadata;
    }

    /**
     * Add hover image and wishlist functionality
     */
    public function add_hover_and_wishlist($block_content, $block) {
        $enable_hover = $block['attrs']['enableHoverImage'] ?? false;
        $show_wishlist = $block['attrs']['showWishlistButton'] ?? false;

        if (!$enable_hover && !$show_wishlist) {
            return $block_content;
        }

        $post_id = $block['context']['postId'] ?? 0;
        if (!$post_id) {
            return $block_content;
        }

        $product = wc_get_product($post_id);
        if (!$product) {
            return $block_content;
        }

        $processor = new WP_HTML_Tag_Processor($block_content);

        if ($processor->next_tag(array('class_name' => 'wc-block-components-product-image'))) {
            $processor->add_class('wc-enhanced-product-image');

            if ($enable_hover) {
                $processor->add_class('wc-hover-image-enabled');
                $hover_image_data = $this->get_hover_image_data($product);
                if ($hover_image_data) {
                    $processor->set_attribute('data-hover-image', wp_json_encode($hover_image_data));
                }
            }

            if ($show_wishlist) {
                $processor->add_class('wc-wishlist-enabled');
                $position = $block['attrs']['wishlistButtonPosition'] ?? 'top-right';
                $processor->set_attribute('data-wishlist-position', $position);
                $processor->set_attribute('data-product-id', $post_id);
            }
        }

        // Add wishlist button HTML if enabled
        if ($show_wishlist) {
            $wishlist_button = $this->get_wishlist_button_html($product, $block['attrs']);
            $block_content = str_replace('</div>', $wishlist_button . '</div>', $block_content);
        }

        return $processor->get_updated_html();
    }

    /**
     * Get hover image data for product
     */
    private function get_hover_image_data($product) {
        $gallery_images = $product->get_gallery_image_ids();

        if (empty($gallery_images)) {
            return null;
        }

        $hover_image_id = $gallery_images[0];
        $hover_image_url = wp_get_attachment_image_url($hover_image_id, 'woocommerce_thumbnail');
        $hover_image_srcset = wp_get_attachment_image_srcset($hover_image_id, 'woocommerce_thumbnail');

        return array(
            'url' => $hover_image_url,
            'srcset' => $hover_image_srcset,
            'alt' => get_post_meta($hover_image_id, '_wp_attachment_image_alt', true)
        );
    }

    /**
     * Generate wishlist button HTML
     */
    private function get_wishlist_button_html($product, $attributes) {
        $position = $attributes['wishlistButtonPosition'] ?? 'top-right';
        $product_id = $product->get_id();

        $button_html = sprintf(
            '<button class="wc-wishlist-button wc-wishlist-button--%s" data-product-id="%d" aria-label="%s">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </button>',
            esc_attr($position),
            esc_attr($product_id),
            esc_attr__('Add to wishlist', 'wc-block-extensions')
        );

        return $button_html;
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_product_image_assets() {
        if (has_block('woocommerce/product-image')) {
            wp_enqueue_script(
                'wc-product-image-extensions',
                WC_BLOCK_EXTENSIONS_PLUGIN_URL . 'assets/js/product-image-frontend.js',
                array('jquery'),
                WC_BLOCK_EXTENSIONS_VERSION,
                true
            );

            wp_enqueue_style(
                'wc-product-image-extensions',
                WC_BLOCK_EXTENSIONS_PLUGIN_URL . 'assets/css/product-image-extension.css',
                array(),
                WC_BLOCK_EXTENSIONS_VERSION
            );
        }
    }
}
```

#### 2.3 Product Image Editor JavaScript

**File: `assets/js/product-image-extension.js`**

```javascript
(function(wp) {
    const { addFilter } = wp.hooks;
    const { createHigherOrderComponent } = wp.compose;
    const { Fragment } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl, SelectControl } = wp.components;
    const { __ } = wp.i18n;

    // Add hover and wishlist controls to Product Image block
    const withImageEnhancements = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, setAttributes, name } = props;

            if (name !== 'woocommerce/product-image') {
                return <BlockEdit {...props} />;
            }

            const {
                enableHoverImage = false,
                showWishlistButton = false,
                wishlistButtonPosition = 'top-right'
            } = attributes;

            return (
                <Fragment>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <PanelBody
                            title={__('Image Enhancements', 'wc-block-extensions')}
                            initialOpen={false}
                        >
                            <ToggleControl
                                label={__('Enable Hover Image', 'wc-block-extensions')}
                                help={__('Show second product image on hover', 'wc-block-extensions')}
                                checked={enableHoverImage}
                                onChange={(value) => setAttributes({ enableHoverImage: value })}
                            />

                            <ToggleControl
                                label={__('Show Wishlist Button', 'wc-block-extensions')}
                                help={__('Add wishlist button overlay', 'wc-block-extensions')}
                                checked={showWishlistButton}
                                onChange={(value) => setAttributes({ showWishlistButton: value })}
                            />

                            {showWishlistButton && (
                                <SelectControl
                                    label={__('Wishlist Button Position', 'wc-block-extensions')}
                                    value={wishlistButtonPosition}
                                    options={[
                                        { label: __('Top Left', 'wc-block-extensions'), value: 'top-left' },
                                        { label: __('Top Right', 'wc-block-extensions'), value: 'top-right' },
                                        { label: __('Bottom Left', 'wc-block-extensions'), value: 'bottom-left' },
                                        { label: __('Bottom Right', 'wc-block-extensions'), value: 'bottom-right' }
                                    ]}
                                    onChange={(value) => setAttributes({ wishlistButtonPosition: value })}
                                />
                            )}
                        </PanelBody>
                    </InspectorControls>
                </Fragment>
            );
        };
    }, 'withImageEnhancements');

    addFilter(
        'editor.BlockEdit',
        'wc-block-extensions/product-image-enhancements',
        withImageEnhancements
    );

})(window.wp);
```

#### 2.4 Frontend JavaScript for Image Enhancements

**File: `assets/js/product-image-frontend.js`**

```javascript
(function($) {
    'use strict';

    class ProductImageEnhancements {
        constructor() {
            this.init();
        }

        init() {
            this.setupHoverImages();
            this.setupWishlistButtons();
        }

        setupHoverImages() {
            $('.wc-hover-image-enabled').each((index, element) => {
                this.initHoverImage($(element));
            });
        }

        initHoverImage($container) {
            const hoverImageData = $container.data('hover-image');
            if (!hoverImageData) {
                return;
            }

            const $image = $container.find('img').first();
            if (!$image.length) {
                return;
            }

            const originalSrc = $image.attr('src');
            const originalSrcset = $image.attr('srcset');

            $container.on('mouseenter', () => {
                $image.attr('src', hoverImageData.url);
                if (hoverImageData.srcset) {
                    $image.attr('srcset', hoverImageData.srcset);
                }
                if (hoverImageData.alt) {
                    $image.attr('alt', hoverImageData.alt);
                }
            });

            $container.on('mouseleave', () => {
                $image.attr('src', originalSrc);
                if (originalSrcset) {
                    $image.attr('srcset', originalSrcset);
                }
            });
        }

        setupWishlistButtons() {
            $('.wc-wishlist-enabled').each((index, element) => {
                this.initWishlistButton($(element));
            });
        }

        initWishlistButton($container) {
            const $button = $container.find('.wc-wishlist-button');
            if (!$button.length) {
                return;
            }

            $button.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const productId = $button.data('product-id');
                this.toggleWishlist(productId, $button);
            });
        }

        toggleWishlist(productId, $button) {
            const isInWishlist = $button.hasClass('wc-wishlist-added');

            // Add loading state
            $button.addClass('wc-wishlist-loading');

            // Make AJAX request to toggle wishlist
            $.ajax({
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'toggle_wishlist'),
                type: 'POST',
                data: {
                    product_id: productId,
                    action: isInWishlist ? 'remove' : 'add',
                    security: wc_add_to_cart_params.wc_ajax_url_nonce
                },
                success: (response) => {
                    if (response.success) {
                        $button.toggleClass('wc-wishlist-added');
                        this.showWishlistMessage(response.data.message);
                    }
                },
                error: () => {
                    this.showWishlistMessage('Error updating wishlist');
                },
                complete: () => {
                    $button.removeClass('wc-wishlist-loading');
                }
            });
        }

        showWishlistMessage(message) {
            // Create and show a temporary message
            const $message = $('<div class="wc-wishlist-message">' + message + '</div>');
            $('body').append($message);

            setTimeout(() => {
                $message.fadeOut(() => {
                    $message.remove();
                });
            }, 3000);
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        new ProductImageEnhancements();
    });

})(jQuery);
```

#### 2.5 CSS Styles for Image Enhancements

**File: `assets/css/product-image-extension.css`**

```css
/* Product Image Enhancements */
.wc-enhanced-product-image {
    position: relative;
    overflow: hidden;
}

/* Hover Image Styles */
.wc-hover-image-enabled img {
    transition: opacity 0.3s ease-in-out;
}

.wc-hover-image-enabled:hover img {
    opacity: 0.9;
}

/* Wishlist Button Styles */
.wc-wishlist-button {
    position: absolute;
    z-index: 10;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0;
    transform: scale(0.8);
}

.wc-enhanced-product-image:hover .wc-wishlist-button {
    opacity: 1;
    transform: scale(1);
}

/* Wishlist Button Positions */
.wc-wishlist-button--top-left {
    top: 10px;
    left: 10px;
}

.wc-wishlist-button--top-right {
    top: 10px;
    right: 10px;
}

.wc-wishlist-button--bottom-left {
    bottom: 10px;
    left: 10px;
}

.wc-wishlist-button--bottom-right {
    bottom: 10px;
    right: 10px;
}

/* Wishlist Button States */
.wc-wishlist-button:hover {
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.wc-wishlist-button.wc-wishlist-added {
    background: #e74c3c;
    color: white;
}

.wc-wishlist-button.wc-wishlist-loading {
    opacity: 0.6;
    pointer-events: none;
}

.wc-wishlist-button svg {
    width: 18px;
    height: 18px;
}

/* Wishlist Message */
.wc-wishlist-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #2c3e50;
    color: white;
    padding: 12px 20px;
    border-radius: 4px;
    z-index: 9999;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .wc-wishlist-button {
        width: 36px;
        height: 36px;
        opacity: 1;
        transform: scale(1);
    }

    .wc-wishlist-button svg {
        width: 16px;
        height: 16px;
    }
}
```

---

## 3. Implementation Steps

### 3.1 Installation Process

1. **Create Plugin Directory**
   ```bash
   mkdir wp-content/plugins/wc-block-extensions
   ```

2. **Upload Files**
   - Copy all files according to the structure outlined above
   - Ensure proper file permissions (644 for files, 755 for directories)

3. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Activate "WooCommerce Block Extensions"

### 3.2 Configuration

1. **Product Collection Block**
   - Add Product Collection block to any page/post
   - In block settings, enable "Responsive Settings"
   - Configure columns and product counts for each device

2. **Product Image Block**
   - Add Product Image block (usually within Product Collection)
   - In block settings, enable "Image Enhancements"
   - Configure hover image and wishlist options

### 3.3 Testing

1. **Responsive Testing**
   - Test on different screen sizes
   - Verify column changes work correctly
   - Check product count adjustments

2. **Image Enhancement Testing**
   - Verify hover image swap functionality
   - Test wishlist button positioning
   - Check AJAX functionality (requires wishlist plugin integration)

---

## 4. Advanced Customization

### 4.1 Custom Breakpoints

Modify the JavaScript breakpoint detection in `product-collection-frontend.js`:

```javascript
getCurrentBreakpoint() {
    const width = window.innerWidth;

    // Custom breakpoints
    if (width >= 1200) {
        return 'desktop';
    } else if (width >= 992) {
        return 'tablet-large';
    } else if (width >= 768) {
        return 'tablet';
    } else if (width >= 576) {
        return 'mobile-large';
    } else {
        return 'mobile';
    }
}
```

### 4.2 Integration with Existing Wishlist Plugins

For integration with popular wishlist plugins like "YITH WooCommerce Wishlist":

```php
// Add to class-product-image-extension.php
private function get_wishlist_button_html($product, $attributes) {
    // Check if YITH Wishlist is active
    if (function_exists('YITH_WCWL')) {
        return do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . $product->get_id() . '"]');
    }

    // Fallback to custom implementation
    return $this->get_custom_wishlist_button_html($product, $attributes);
}
```

### 4.3 Performance Optimization

1. **Lazy Loading**
   - Implement intersection observer for hover images
   - Load secondary images only when needed

2. **Caching**
   - Cache responsive settings in transients
   - Minimize DOM manipulations

---

## 5. Troubleshooting

### 5.1 Common Issues

1. **Blocks Not Showing Enhanced Options**
   - Verify plugin is activated
   - Check browser console for JavaScript errors
   - Ensure WooCommerce is active and updated

2. **Responsive Layout Not Working**
   - Check CSS conflicts with theme
   - Verify JavaScript is loading correctly
   - Test with default WordPress theme

3. **Hover Images Not Loading**
   - Ensure products have gallery images
   - Check image permissions and URLs
   - Verify browser supports required features

### 5.2 Debug Mode

Add debug logging to track issues:

```php
// Add to main plugin file
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('WC Block Extensions: Debug mode enabled');
}
```

---

## 6. Maintenance and Updates

### 6.1 Version Compatibility

- Test with each WooCommerce update
- Monitor WordPress block editor changes
- Update deprecated functions as needed

### 6.2 Performance Monitoring

- Monitor page load times
- Check for JavaScript errors
- Optimize CSS delivery

---

This documentation provides a complete implementation guide for extending WooCommerce Gutenberg blocks without modifying core files. The solution uses WordPress hooks, filters, and block variations to achieve the desired functionality while maintaining compatibility with future updates.
