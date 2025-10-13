<?php
/**
 * Product Image Block Extension
 *
 * Adds hover image swap and Blocksy wishlist integration to WooCommerce Product Image block
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class WC_Product_Image_Extension
 *
 * Extends Product Image block with hover and wishlist features
 */
class WC_Product_Image_Extension {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('render_block_woocommerce/product-image', array($this, 'add_hover_and_wishlist'), 10, 2);
        add_filter('block_type_metadata', array($this, 'extend_product_image_metadata'));
        
        // AJAX handlers for wishlist
        add_action('wp_ajax_wc_block_toggle_wishlist', array($this, 'ajax_toggle_wishlist'));
        add_action('wp_ajax_nopriv_wc_block_toggle_wishlist', array($this, 'ajax_toggle_wishlist'));
    }

    /**
     * Extend Product Image block metadata
     *
     * @param array $metadata Block metadata
     * @return array Modified metadata
     */
    public function extend_product_image_metadata($metadata) {
        if (isset($metadata['name']) && $metadata['name'] === 'woocommerce/product-image') {
            // Ensure attributes array exists
            if (!isset($metadata['attributes'])) {
                $metadata['attributes'] = array();
            }

            // Add enhancement attributes
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
     *
     * @param string $block_content Block HTML content
     * @param array  $block Block data
     * @return string Modified block content
     */
    public function add_hover_and_wishlist($block_content, $block) {
        $enable_hover = $block['attrs']['enableHoverImage'] ?? false;
        $show_wishlist = $block['attrs']['showWishlistButton'] ?? false;

        // Skip if no enhancements are enabled
        if (!$enable_hover && !$show_wishlist) {
            return $block_content;
        }

        // Get product ID from context
        $post_id = $block['context']['postId'] ?? 0;
        if (!$post_id) {
            return $block_content;
        }

        $product = wc_get_product($post_id);
        if (!$product) {
            return $block_content;
        }

        // Process the block content
        if (class_exists('WP_HTML_Tag_Processor')) {
            return $this->process_with_html_processor($block_content, $product, $block['attrs']);
        }

        // Fallback for older WordPress versions
        return $this->process_with_regex($block_content, $product, $block['attrs']);
    }

    /**
     * Process block content using WP_HTML_Tag_Processor
     *
     * @param string $block_content Block HTML content
     * @param WC_Product $product Product object
     * @param array $attributes Block attributes
     * @return string Modified block content
     */
    private function process_with_html_processor($block_content, $product, $attributes) {
        $processor = new WP_HTML_Tag_Processor($block_content);
        $enable_hover = $attributes['enableHoverImage'] ?? false;
        $show_wishlist = $attributes['showWishlistButton'] ?? false;

        // Find the product image wrapper
        if ($processor->next_tag(array('class_name' => 'wc-block-components-product-image'))) {
            $processor->add_class('wc-enhanced-product-image');

            // Add hover image functionality
            if ($enable_hover) {
                $processor->add_class('wc-hover-image-enabled');
                $hover_image_data = $this->get_hover_image_data($product);
                if ($hover_image_data) {
                    $processor->set_attribute('data-hover-image', wp_json_encode($hover_image_data));
                }
            }

            // Add wishlist functionality
            if ($show_wishlist) {
                $processor->add_class('wc-wishlist-enabled');
                $position = $attributes['wishlistButtonPosition'] ?? 'top-right';
                $processor->set_attribute('data-wishlist-position', $position);
                $processor->set_attribute('data-product-id', $product->get_id());
            }
        }

        $modified_content = $processor->get_updated_html();

        // Add wishlist button HTML if enabled
        if ($show_wishlist) {
            $wishlist_button = $this->get_wishlist_button_html($product, $attributes);
            // Insert button before closing div
            $modified_content = preg_replace(
                '/(<\/div>\s*<\/div>\s*)$/',
                $wishlist_button . '$1',
                $modified_content,
                1
            );
        }

        return $modified_content;
    }

    /**
     * Process block content using regex (fallback)
     *
     * @param string $block_content Block HTML content
     * @param WC_Product $product Product object
     * @param array $attributes Block attributes
     * @return string Modified block content
     */
    private function process_with_regex($block_content, $product, $attributes) {
        $enable_hover = $attributes['enableHoverImage'] ?? false;
        $show_wishlist = $attributes['showWishlistButton'] ?? false;
        
        $classes = array('wc-enhanced-product-image');
        $data_attrs = array();

        if ($enable_hover) {
            $classes[] = 'wc-hover-image-enabled';
            $hover_image_data = $this->get_hover_image_data($product);
            if ($hover_image_data) {
                $data_attrs[] = sprintf('data-hover-image=\'%s\'', esc_attr(wp_json_encode($hover_image_data)));
            }
        }

        if ($show_wishlist) {
            $classes[] = 'wc-wishlist-enabled';
            $position = $attributes['wishlistButtonPosition'] ?? 'top-right';
            $data_attrs[] = sprintf('data-wishlist-position="%s"', esc_attr($position));
            $data_attrs[] = sprintf('data-product-id="%d"', $product->get_id());
        }

        // Add classes and data attributes
        $pattern = '/<div([^>]*class="[^"]*wc-block-components-product-image[^"]*"[^>]*)>/';
        $replacement = sprintf(
            '<div$1 class="%s" %s>',
            esc_attr(implode(' ', $classes)),
            implode(' ', $data_attrs)
        );
        $block_content = preg_replace($pattern, $replacement, $block_content, 1);

        // Add wishlist button
        if ($show_wishlist) {
            $wishlist_button = $this->get_wishlist_button_html($product, $attributes);
            $block_content = preg_replace(
                '/(<\/div>\s*<\/div>\s*)$/',
                $wishlist_button . '$1',
                $block_content,
                1
            );
        }

        return $block_content;
    }

    /**
     * Get hover image data for product
     *
     * @param WC_Product $product Product object
     * @return array|null Hover image data or null
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
     *
     * @param WC_Product $product Product object
     * @param array $attributes Block attributes
     * @return string Wishlist button HTML
     */
    private function get_wishlist_button_html($product, $attributes) {
        $position = $attributes['wishlistButtonPosition'] ?? 'top-right';
        $product_id = $product->get_id();
        
        // Check if product is in wishlist (Blocksy integration)
        $is_in_wishlist = $this->is_product_in_wishlist($product_id);
        $button_class = $is_in_wishlist ? 'wc-wishlist-button wc-wishlist-added' : 'wc-wishlist-button';

        $button_html = sprintf(
            '<button class="%s wc-wishlist-button--%s" data-product-id="%d" aria-label="%s">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="%s" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </button>',
            esc_attr($button_class),
            esc_attr($position),
            esc_attr($product_id),
            esc_attr__('Add to wishlist', 'blocksy-child'),
            $is_in_wishlist ? 'currentColor' : 'none'
        );

        return $button_html;
    }

    /**
     * Check if product is in Blocksy wishlist
     *
     * @param int $product_id Product ID
     * @return bool True if in wishlist
     */
    private function is_product_in_wishlist($product_id) {
        // Use Blocksy wishlist helper if available
        if (class_exists('BlocksyChildWishlistHelper')) {
            $wishlist = BlocksyChildWishlistHelper::get_current_wishlist();
            $wishlist_ids = BlocksyChildWishlistHelper::extract_product_ids($wishlist);
            return in_array($product_id, $wishlist_ids);
        }

        // Fallback: check Blocksy extension directly
        if (function_exists('blc_get_ext')) {
            $ext = blc_get_ext('woocommerce-extra');
            if ($ext) {
                $wishlist_instance = $ext->get_wish_list();
                if ($wishlist_instance) {
                    $wishlist = $wishlist_instance->get_current_wish_list();
                    if (!empty($wishlist)) {
                        foreach ($wishlist as $item) {
                            $item_id = is_array($item) ? ($item['product_id'] ?? 0) : $item;
                            if ($item_id == $product_id) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * AJAX handler for toggling wishlist
     */
    public function ajax_toggle_wishlist() {
        check_ajax_referer('wc_block_extensions_nonce', 'nonce');

        $product_id = intval($_POST['product_id'] ?? 0);
        
        if (!$product_id) {
            wp_send_json_error(array('message' => __('Invalid product ID', 'blocksy-child')));
        }

        // Use Blocksy wishlist functionality
        $result = $this->toggle_blocksy_wishlist($product_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Toggle product in Blocksy wishlist
     *
     * @param int $product_id Product ID
     * @return array Result array
     */
    private function toggle_blocksy_wishlist($product_id) {
        // Check if Blocksy wishlist is available
        if (!function_exists('blc_get_ext')) {
            return array(
                'success' => false,
                'message' => __('Wishlist functionality not available', 'blocksy-child')
            );
        }

        $ext = blc_get_ext('woocommerce-extra');
        if (!$ext) {
            return array(
                'success' => false,
                'message' => __('Wishlist extension not found', 'blocksy-child')
            );
        }

        $wishlist_instance = $ext->get_wish_list();
        if (!$wishlist_instance) {
            return array(
                'success' => false,
                'message' => __('Wishlist instance not available', 'blocksy-child')
            );
        }

        // Check if product is currently in wishlist
        $is_in_wishlist = $this->is_product_in_wishlist($product_id);

        if ($is_in_wishlist) {
            // Remove from wishlist
            $wishlist_instance->remove_from_wish_list($product_id);
            return array(
                'success' => true,
                'action' => 'removed',
                'message' => __('Removed from wishlist', 'blocksy-child'),
                'in_wishlist' => false
            );
        } else {
            // Add to wishlist
            $wishlist_instance->add_to_wish_list($product_id);
            return array(
                'success' => true,
                'action' => 'added',
                'message' => __('Added to wishlist', 'blocksy-child'),
                'in_wishlist' => true
            );
        }
    }
}

