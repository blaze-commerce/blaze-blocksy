# WooCommerce Product Collection and Product Image Blocks - Technical Documentation

## Overview

This documentation provides comprehensive technical details for cloning and implementing the WooCommerce Product Collection and Product Image blocks in a child theme. These blocks are core components of WooCommerce's block-based product display system.

## Table of Contents

1. [Product Collection Block](#product-collection-block)
2. [Product Image Block](#product-image-block)
3. [Implementation Guide](#implementation-guide)
4. [File Structure](#file-structure)
5. [Dependencies](#dependencies)
6. [Registration Process](#registration-process)
7. [Customization Examples](#customization-examples)

## Product Collection Block

### Block Metadata (block.json)

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "woocommerce/product-collection",
  "version": "1.0.0",
  "title": "Product Collection",
  "description": "Display a collection of products from your store.",
  "category": "woocommerce",
  "keywords": ["WooCommerce", "Products (Beta)", "all products", "by category", "by tag", "by attribute"],
  "textdomain": "woocommerce",
  "attributes": {
    "queryId": {"type": "number"},
    "query": {"type": "object"},
    "tagName": {"type": "string"},
    "displayLayout": {"type": "object"},
    "dimensions": {"type": "object"},
    "convertedFromProducts": {"type": "boolean", "default": false},
    "collection": {"type": "string"},
    "hideControls": {"default": [], "type": "array"},
    "queryContextIncludes": {"type": "array"},
    "forcePageReload": {"type": "boolean", "default": false},
    "__privatePreviewState": {"type": "object"}
  },
  "providesContext": {
    "queryId": "queryId",
    "query": "query",
    "displayLayout": "displayLayout",
    "dimensions": "dimensions",
    "queryContextIncludes": "queryContextIncludes",
    "collection": "collection",
    "__privateProductCollectionPreviewState": "__privatePreviewState"
  },
  "usesContext": ["templateSlug", "postId"],
  "supports": {
    "align": ["wide", "full"],
    "anchor": true,
    "html": false,
    "__experimentalLayout": true,
    "interactivity": true
  }
}
```

### PHP Controller Class

The main PHP class structure for Product Collection:

```php
<?php
namespace YourTheme\Blocks\ProductCollection;

use Automattic\WooCommerce\Blocks\BlockTypes\AbstractBlock;

class Controller extends AbstractBlock {
    protected $block_name = 'product-collection';
    
    protected function initialize() {
        parent::initialize();
        
        $this->query_builder = new QueryBuilder();
        $this->renderer = new Renderer();
        $this->collection_handler_registry = new HandlerRegistry();
        
        // Register hooks and filters
        add_filter('query_loop_block_query_vars', array($this, 'build_frontend_query'), 10, 3);
        add_filter('pre_render_block', array($this, 'add_support_for_filter_blocks'), 10, 2);
        add_action('rest_api_init', array($this, 'register_settings'));
        add_filter('rest_product_query', array($this, 'update_rest_query_in_editor'), 10, 2);
    }
    
    protected function enqueue_data(array $attributes = array()) {
        parent::enqueue_data($attributes);
        
        $this->asset_data_registry->add(
            'loopShopPerPage', 
            apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page())
        );
    }
    
    public function build_frontend_query($query, $block, $page) {
        // Query building logic
        $block_context_query = $block->context['query'];
        $inherit = $block->context['query']['inherit'] ?? false;
        $filterable = $block->context['query']['filterable'] ?? false;
        
        $collection_args = array(
            'name' => $block->context['collection'] ?? '',
            'productCollectionLocation' => $block->context['productCollectionLocation'] ?? null,
        );
        
        return $this->query_builder->get_final_frontend_query(
            $collection_args,
            $block_context_query,
            $page,
            !($inherit || $filterable)
        );
    }
}
```

### Key Components

#### 1. QueryBuilder Class
Handles the construction of WP_Query arguments for product retrieval:

```php
class QueryBuilder {
    public function get_final_frontend_query($collection_args, $query, $page, $is_exclude_applied_filters) {
        // Build and return query arguments
        return $this->build_query_args($collection_args, $query, $page);
    }
    
    private function build_query_args($collection_args, $query, $page) {
        // Query construction logic
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $query['perPage'] ?? 9,
            'paged' => $page,
            'orderby' => $query['orderBy'] ?? 'title',
            'order' => $query['order'] ?? 'ASC'
        );
        
        return $args;
    }
}
```

#### 2. HandlerRegistry Class
Manages collection handlers for different product collection types:

```php
class HandlerRegistry {
    private $collection_handler_store = array();
    
    public function register_collection_handlers($collection_name, $build_query, $frontend_args = null, $editor_args = null, $preview_query = null) {
        if (isset($this->collection_handler_store[$collection_name])) {
            throw new InvalidArgumentException('Collection handlers already registered for ' . esc_html($collection_name));
        }
        
        $this->collection_handler_store[$collection_name] = [
            'build_query' => $build_query,
            'frontend_args' => $frontend_args,
            'editor_args' => $editor_args,
            'preview_query' => $preview_query,
        ];
        
        return $this->collection_handler_store[$collection_name];
    }
}
```

#### 3. Renderer Class
Handles the frontend rendering and interactivity:

```php
class Renderer {
    public function enhance_product_collection_with_interactivity($block_content, $block) {
        $is_product_collection_block = $block['attrs']['query']['isProductCollectionBlock'] ?? false;
        
        if ($is_product_collection_block) {
            wp_enqueue_script_module('woocommerce/product-collection');
            
            $collection = $block['attrs']['collection'] ?? '';
            $is_enhanced_pagination_enabled = !($block['attrs']['forcePageReload'] ?? false);
            $context = array('notices' => array());
            
            if ($collection) {
                $context['collection'] = $collection;
            }
            
            $p = new \WP_HTML_Tag_Processor($block_content);
            if ($p->next_tag(array('class_name' => 'wp-block-woocommerce-product-collection'))) {
                $p->set_attribute('data-wp-interactive', 'woocommerce/product-collection');
                $p->set_attribute('data-wp-init', 'callbacks.onRender');
                $p->set_attribute('data-wp-context', wp_json_encode($context, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
                
                if ($is_enhanced_pagination_enabled && isset($this->parsed_block)) {
                    $p->set_attribute(
                        'data-wp-router-region',
                        'wc-product-collection-' . $this->parsed_block['attrs']['queryId']
                    );
                }
            }
            
            $block_content = $p->get_updated_html();
        }
        
        return $block_content;
    }
}
```

## Product Image Block

### Block Metadata (block.json)

```json
{
  "name": "woocommerce/product-image",
  "version": "1.0.0",
  "title": "Product Image",
  "description": "Display the main product image.",
  "category": "woocommerce-product-elements",
  "attributes": {
    "showProductLink": {"type": "boolean", "default": true},
    "showSaleBadge": {"type": "boolean", "default": true},
    "saleBadgeAlign": {"type": "string", "default": "right"},
    "imageSizing": {"type": "string", "default": "single"},
    "productId": {"type": "number", "default": 0},
    "isDescendentOfQueryLoop": {"type": "boolean", "default": false},
    "isDescendentOfSingleProductBlock": {"type": "boolean", "default": false},
    "width": {"type": "string"},
    "height": {"type": "string"},
    "scale": {"type": "string", "default": "cover"},
    "aspectRatio": {"type": "string"}
  },
  "usesContext": ["query", "queryId", "postId"],
  "keywords": ["WooCommerce"],
  "textdomain": "woocommerce",
  "apiVersion": 3,
  "$schema": "https://schemas.wp.org/trunk/block.json"
}
```

### PHP Class Structure

```php
<?php
namespace YourTheme\Blocks;

use Automattic\WooCommerce\Blocks\BlockTypes\AbstractBlock;

class ProductImage extends AbstractBlock {
    protected $block_name = 'product-image';
    
    private function parse_attributes($attributes) {
        $defaults = array(
            'showProductLink' => true,
            'showSaleBadge' => true,
            'saleBadgeAlign' => 'right',
            'imageSizing' => 'single',
            'productId' => 'number',
            'isDescendentOfQueryLoop' => 'false',
            'scale' => 'cover',
        );
        
        return wp_parse_args($attributes, $defaults);
    }
    
    protected function render($attributes, $content, $block) {
        $parsed_attributes = $this->parse_attributes($attributes);
        $product = $this->get_product($block);
        
        if (!$product) {
            return '';
        }
        
        $wrapper_attributes = get_block_wrapper_attributes();
        
        return sprintf(
            '<div %1$s>%2$s</div>',
            $wrapper_attributes,
            $this->render_anchor(
                $product,
                $this->render_on_sale_badge($product, $parsed_attributes),
                $this->render_image($product, $parsed_attributes),
                $parsed_attributes
            )
        );
    }
    
    private function render_image($product, $attributes) {
        $image_size = $this->get_image_size($attributes['imageSizing']);
        $image_style = '';
        
        if (!empty($attributes['width'])) {
            $image_style .= sprintf('width:%s;', $attributes['width']);
        }
        
        if (!empty($attributes['height'])) {
            $image_style .= sprintf('height:%s;', $attributes['height']);
        }
        
        if (!empty($attributes['aspectRatio'])) {
            $image_style .= sprintf('aspect-ratio:%s;', $attributes['aspectRatio']);
        }
        
        $image_id = $product->get_image_id();
        $alt_text = '';
        $title = '';
        
        if ($image_id) {
            $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $title = get_the_title($image_id);
        }
        
        return $product->get_image(
            $image_size,
            array(
                'alt' => empty($alt_text) ? $product->get_title() : $alt_text,
                'data-testid' => 'product-image',
                'style' => $image_style,
                'title' => $title,
            )
        );
    }
    
    protected function enqueue_data(array $attributes = []) {
        $this->asset_data_registry->add('isBlockThemeEnabled', wc_current_theme_is_fse_theme());
    }
}
```

## Implementation Guide

### Step 1: Create Directory Structure

```
your-child-theme/
├── blocks/
│   ├── product-collection/
│   │   ├── block.json
│   │   ├── index.js
│   │   ├── edit.js
│   │   ├── save.js
│   │   ├── style.scss
│   │   └── editor.scss
│   └── product-image/
│       ├── block.json
│       ├── index.js
│       ├── edit.js
│       ├── save.js
│       ├── style.scss
│       └── editor.scss
├── includes/
│   ├── blocks/
│   │   ├── class-product-collection.php
│   │   ├── class-product-image.php
│   │   └── class-blocks-loader.php
│   └── class-theme-blocks.php
└── functions.php
```

### Step 2: Main Theme Integration

Add to your child theme's `functions.php`:

```php
<?php
// Enqueue child theme blocks
function your_theme_enqueue_blocks() {
    if (class_exists('WooCommerce')) {
        require_once get_stylesheet_directory() . '/includes/class-theme-blocks.php';
        new YourTheme_Blocks();
    }
}
add_action('init', 'your_theme_enqueue_blocks');
```

### Step 3: Blocks Loader Class

Create `includes/class-theme-blocks.php`:

```php
<?php
class YourTheme_Blocks {
    
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function register_blocks() {
        // Register Product Collection block
        register_block_type(
            get_stylesheet_directory() . '/blocks/product-collection',
            array(
                'render_callback' => array($this, 'render_product_collection'),
            )
        );
        
        // Register Product Image block
        register_block_type(
            get_stylesheet_directory() . '/blocks/product-image',
            array(
                'render_callback' => array($this, 'render_product_image'),
            )
        );
    }
    
    public function render_product_collection($attributes, $content, $block) {
        require_once get_stylesheet_directory() . '/includes/blocks/class-product-collection.php';
        $product_collection = new YourTheme_Product_Collection();
        return $product_collection->render($attributes, $content, $block);
    }
    
    public function render_product_image($attributes, $content, $block) {
        require_once get_stylesheet_directory() . '/includes/blocks/class-product-image.php';
        $product_image = new YourTheme_Product_Image();
        return $product_image->render($attributes, $content, $block);
    }
    
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'your-theme-blocks-editor',
            get_stylesheet_directory_uri() . '/assets/js/blocks-editor.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            filemtime(get_stylesheet_directory() . '/assets/js/blocks-editor.js')
        );
        
        wp_enqueue_style(
            'your-theme-blocks-editor',
            get_stylesheet_directory_uri() . '/assets/css/blocks-editor.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/css/blocks-editor.css')
        );
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'your-theme-blocks',
            get_stylesheet_directory_uri() . '/assets/css/blocks.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/css/blocks.css')
        );
        
        wp_enqueue_script(
            'your-theme-blocks-frontend',
            get_stylesheet_directory_uri() . '/assets/js/blocks-frontend.js',
            array('wp-element'),
            filemtime(get_stylesheet_directory() . '/assets/js/blocks-frontend.js'),
            true
        );
    }
}
```

## Dependencies

### Required WordPress/WooCommerce Dependencies

```json
{
  "dependencies": [
    "react",
    "wc-blocks-registry",
    "wc-customer-effort-score",
    "wc-price-format",
    "wc-settings",
    "wc-types",
    "wp-api-fetch",
    "wp-block-editor",
    "wp-blocks",
    "wp-components",
    "wp-compose",
    "wp-core-data",
    "wp-data",
    "wp-editor",
    "wp-element",
    "wp-escape-html",
    "wp-hooks",
    "wp-html-entities",
    "wp-i18n",
    "wp-is-shallow-equal",
    "wp-polyfill",
    "wp-primitives",
    "wp-url"
  ]
}
```

### PHP Dependencies

- WooCommerce 8.0+
- WordPress 6.0+
- PHP 7.4+

## JavaScript Implementation

### Product Collection Block Registration

Create `blocks/product-collection/index.js`:

```javascript
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType(metadata.name, {
    ...metadata,
    edit,
    save,
    icon: {
        src: 'grid-view',
        foreground: '#7e57c2',
    },
    example: {
        attributes: {
            query: {
                perPage: 6,
                pages: 1,
            },
            displayLayout: {
                type: 'flex',
                columns: 3,
            },
        },
    },
});
```

### Product Image Block Registration

Create `blocks/product-image/index.js`:

```javascript
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType(metadata.name, {
    ...metadata,
    edit,
    save,
    icon: {
        src: 'format-image',
        foreground: '#7e57c2',
    },
    example: {
        attributes: {
            showProductLink: true,
            showSaleBadge: true,
            saleBadgeAlign: 'right',
        },
    },
});
```

## CSS Styling

### Product Collection Styles

```scss
.wp-block-your-theme-product-collection {
    .wc-block-components-product-stock-indicator {
        text-align: center;
    }
    
    .product-collection-grid {
        display: grid;
        gap: 1rem;
        
        &.columns-2 { grid-template-columns: repeat(2, 1fr); }
        &.columns-3 { grid-template-columns: repeat(3, 1fr); }
        &.columns-4 { grid-template-columns: repeat(4, 1fr); }
        &.columns-5 { grid-template-columns: repeat(5, 1fr); }
        &.columns-6 { grid-template-columns: repeat(6, 1fr); }
        
        @media (max-width: 768px) {
            &.responsive {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            &.responsive {
                grid-template-columns: 1fr;
            }
        }
    }
}
```

### Product Image Styles

```scss
.wp-block-your-theme-product-image {
    display: block;
    position: relative;
    text-decoration: none;
    
    a {
        border: 0;
        border-radius: inherit;
        box-shadow: none;
        display: block;
        text-decoration: none;
    }
    
    img {
        border-radius: inherit;
        height: auto;
        vertical-align: middle;
        width: 100%;
        
        &[hidden] {
            display: none;
        }
        
        &[alt=""] {
            border: 1px solid #f2f2f2;
        }
    }
    
    .wc-block-components-product-sale-badge {
        background: #fff;
        border: 1px solid #43454b;
        border-radius: 4px;
        color: #43454b;
        font-size: 0.875em;
        font-weight: 600;
        padding: 0.25em 0.75em;
        position: absolute;
        text-transform: uppercase;
        z-index: 9;
        
        &--align-left {
            left: 4px;
            top: 4px;
        }
        
        &--align-center {
            left: 50%;
            top: 4px;
            transform: translateX(-50%);
        }
        
        &--align-right {
            right: 4px;
            top: 4px;
        }
    }
}
```

## Advanced Customization Examples

### Custom Product Collection Variations

You can create custom collection variations by extending the base functionality:

```php
// Add custom collection handlers
class YourTheme_Collection_Handlers {

    public function register_custom_collections() {
        // Featured Products with Custom Meta
        $this->register_collection_handlers(
            'your-theme/featured-premium',
            function($collection_args, $common_query_values, $query) {
                return array(
                    'meta_query' => array(
                        array(
                            'key' => '_featured_premium',
                            'value' => 'yes',
                            'compare' => '='
                        )
                    )
                );
            }
        );

        // Products by Custom Taxonomy
        $this->register_collection_handlers(
            'your-theme/by-brand',
            function($collection_args, $common_query_values, $query) {
                if (empty($query['brand_slug'])) {
                    return array('post__in' => array(-1));
                }

                return array(
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_brand',
                            'field' => 'slug',
                            'terms' => $query['brand_slug']
                        )
                    )
                );
            }
        );
    }
}
```

### Custom Product Image Rendering

Extend the Product Image block with additional features:

```php
class YourTheme_Product_Image_Extended extends YourTheme_Product_Image {

    protected function render_image($product, $attributes) {
        // Add lazy loading
        $attributes['loading'] = 'lazy';

        // Add custom image sizes
        $image_size = $this->get_custom_image_size($attributes);

        // Add WebP support
        $image_html = $this->render_webp_image($product, $attributes, $image_size);

        // Add image zoom functionality
        if ($attributes['enableZoom'] ?? false) {
            $image_html = $this->add_zoom_functionality($image_html, $product);
        }

        return $image_html;
    }

    private function render_webp_image($product, $attributes, $image_size) {
        $image_id = $product->get_image_id();

        if (!$image_id) {
            return $this->render_placeholder($attributes);
        }

        $webp_url = wp_get_attachment_image_url($image_id, $image_size, false);
        $fallback_url = wp_get_attachment_image_url($image_id, $image_size, false);

        return sprintf(
            '<picture>
                <source srcset="%s" type="image/webp">
                <img src="%s" alt="%s" loading="lazy" %s>
            </picture>',
            esc_url($webp_url),
            esc_url($fallback_url),
            esc_attr($product->get_title()),
            $this->build_image_attributes($attributes)
        );
    }
}
```

### JavaScript Interactivity Extensions

Add custom interactivity to your blocks:

```javascript
// blocks/product-collection/frontend.js
import { store, getContext, getElement } from '@wordpress/interactivity';

store('your-theme/product-collection', {
    actions: {
        *loadMore() {
            const context = getContext();
            const { ref } = getElement();

            try {
                const response = yield fetch('/wp-json/wc/store/v1/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        page: context.currentPage + 1,
                        per_page: context.perPage,
                        ...context.queryArgs
                    })
                });

                const products = yield response.json();

                if (products.length > 0) {
                    context.products = [...context.products, ...products];
                    context.currentPage += 1;
                } else {
                    context.hasMore = false;
                }
            } catch (error) {
                console.error('Failed to load more products:', error);
            }
        },

        *filterByPrice() {
            const context = getContext();
            const { ref } = getElement();

            const minPrice = ref.querySelector('[data-min-price]').value;
            const maxPrice = ref.querySelector('[data-max-price]').value;

            context.queryArgs.min_price = minPrice;
            context.queryArgs.max_price = maxPrice;

            yield this.refreshProducts();
        },

        *refreshProducts() {
            const context = getContext();

            try {
                const response = yield fetch('/wp-json/wc/store/v1/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        page: 1,
                        per_page: context.perPage,
                        ...context.queryArgs
                    })
                });

                const products = yield response.json();

                context.products = products;
                context.currentPage = 1;
                context.hasMore = products.length === context.perPage;
            } catch (error) {
                console.error('Failed to refresh products:', error);
            }
        }
    },

    callbacks: {
        *onInit() {
            const context = getContext();

            // Initialize infinite scroll
            if (context.enableInfiniteScroll) {
                yield this.setupInfiniteScroll();
            }

            // Initialize filters
            if (context.enableFilters) {
                yield this.setupFilters();
            }
        },

        *setupInfiniteScroll() {
            const { ref } = getElement();

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && getContext().hasMore) {
                        this.actions.loadMore();
                    }
                });
            });

            const sentinel = ref.querySelector('.load-more-sentinel');
            if (sentinel) {
                observer.observe(sentinel);
            }
        }
    }
});
```

### Custom Block Variations

Register custom block variations for specific use cases:

```javascript
// blocks/product-collection/variations.js
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// Bestsellers with custom styling
registerBlockVariation('your-theme/product-collection', {
    name: 'bestsellers-hero',
    title: __('Bestsellers Hero', 'your-theme'),
    description: __('Display bestselling products in a hero layout', 'your-theme'),
    icon: 'star-filled',
    attributes: {
        collection: 'your-theme/bestsellers-hero',
        displayLayout: {
            type: 'flex',
            columns: 4,
            shrinkColumns: true
        },
        query: {
            orderBy: 'popularity',
            order: 'desc',
            perPage: 8,
            pages: 1
        },
        hideControls: ['order', 'filterable'],
        className: 'is-style-hero-layout'
    },
    scope: ['inserter', 'block'],
    isActive: (blockAttributes) => {
        return blockAttributes.collection === 'your-theme/bestsellers-hero';
    }
});

// Sale products with countdown
registerBlockVariation('your-theme/product-collection', {
    name: 'sale-countdown',
    title: __('Sale with Countdown', 'your-theme'),
    description: __('Display sale products with countdown timer', 'your-theme'),
    icon: 'clock',
    attributes: {
        collection: 'your-theme/sale-countdown',
        displayLayout: {
            type: 'grid',
            columns: 3,
            shrinkColumns: true
        },
        query: {
            woocommerceOnSale: true,
            perPage: 6,
            pages: 1
        },
        enableCountdown: true,
        className: 'is-style-sale-countdown'
    },
    scope: ['inserter', 'block']
});
```

### REST API Extensions

Extend the WooCommerce REST API for custom functionality:

```php
class YourTheme_REST_API_Extensions {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_custom_endpoints'));
        add_filter('woocommerce_rest_product_object_query', array($this, 'custom_product_query_args'), 10, 2);
    }

    public function register_custom_endpoints() {
        register_rest_route('your-theme/v1', '/products/featured-premium', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_featured_premium_products'),
            'permission_callback' => '__return_true',
            'args' => array(
                'per_page' => array(
                    'default' => 10,
                    'sanitize_callback' => 'absint',
                ),
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }

    public function get_featured_premium_products($request) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $request['per_page'],
            'paged' => $request['page'],
            'meta_query' => array(
                array(
                    'key' => '_featured_premium',
                    'value' => 'yes',
                    'compare' => '='
                )
            )
        );

        $products = get_posts($args);
        $formatted_products = array();

        foreach ($products as $product_post) {
            $product = wc_get_product($product_post->ID);
            if ($product) {
                $formatted_products[] = $this->format_product_data($product);
            }
        }

        return rest_ensure_response($formatted_products);
    }

    private function format_product_data($product) {
        return array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'permalink' => $product->get_permalink(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'on_sale' => $product->is_on_sale(),
            'images' => $this->get_product_images($product),
            'categories' => $this->get_product_categories($product),
            'attributes' => $this->get_product_attributes($product),
        );
    }
}
```

### Performance Optimization

Implement caching and optimization strategies:

```php
class YourTheme_Block_Cache {

    private $cache_group = 'your_theme_blocks';
    private $cache_expiry = 3600; // 1 hour

    public function get_cached_products($cache_key, $query_args) {
        $cached_data = wp_cache_get($cache_key, $this->cache_group);

        if (false === $cached_data) {
            $products = $this->fetch_products($query_args);
            wp_cache_set($cache_key, $products, $this->cache_group, $this->cache_expiry);
            return $products;
        }

        return $cached_data;
    }

    public function invalidate_product_cache($product_id = null) {
        if ($product_id) {
            // Invalidate specific product caches
            wp_cache_delete("product_{$product_id}", $this->cache_group);
        } else {
            // Invalidate all product collection caches
            wp_cache_flush_group($this->cache_group);
        }
    }

    private function fetch_products($query_args) {
        $query = new WP_Query($query_args);
        $products = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                if ($product) {
                    $products[] = $this->prepare_product_data($product);
                }
            }
            wp_reset_postdata();
        }

        return $products;
    }
}
```

## Testing and Debugging

### Unit Testing

Create unit tests for your custom blocks:

```php
class YourTheme_Product_Collection_Test extends WP_UnitTestCase {

    private $product_collection;

    public function setUp(): void {
        parent::setUp();
        $this->product_collection = new YourTheme_Product_Collection();
    }

    public function test_render_with_valid_attributes() {
        $attributes = array(
            'query' => array(
                'perPage' => 6,
                'orderBy' => 'date',
                'order' => 'desc'
            ),
            'displayLayout' => array(
                'type' => 'grid',
                'columns' => 3
            )
        );

        $content = '';
        $block = new WP_Block(array(), array());

        $result = $this->product_collection->render($attributes, $content, $block);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('wp-block-your-theme-product-collection', $result);
    }

    public function test_query_builder_with_custom_args() {
        $query_builder = new YourTheme_Query_Builder();

        $collection_args = array('name' => 'featured');
        $query = array('perPage' => 10, 'orderBy' => 'popularity');
        $page = 1;

        $result = $query_builder->get_final_frontend_query($collection_args, $query, $page, false);

        $this->assertIsArray($result);
        $this->assertEquals(10, $result['posts_per_page']);
        $this->assertEquals('popularity', $result['orderby']);
    }
}
```

### Debug Utilities

Add debugging helpers for development:

```php
class YourTheme_Block_Debug {

    public static function log_block_render($block_name, $attributes, $content) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'Block Render: %s | Attributes: %s | Content Length: %d',
                $block_name,
                wp_json_encode($attributes),
                strlen($content)
            ));
        }
    }

    public static function log_query_performance($query_args, $execution_time) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'Query Performance: %s | Time: %f seconds',
                wp_json_encode($query_args),
                $execution_time
            ));
        }
    }

    public static function add_debug_info_to_block($content, $debug_info) {
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            $debug_html = sprintf(
                '<!-- Block Debug Info: %s -->',
                wp_json_encode($debug_info)
            );
            return $debug_html . $content;
        }

        return $content;
    }
}
```

This comprehensive documentation provides everything needed to clone and customize the WooCommerce Product Collection and Product Image blocks in your child theme. The examples show how to extend functionality, add custom features, optimize performance, and maintain code quality through testing.
