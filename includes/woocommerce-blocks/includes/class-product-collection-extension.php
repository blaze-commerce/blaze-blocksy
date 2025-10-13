<?php
/**
 * Product Collection Block Extension
 *
 * Adds responsive column and product count controls to WooCommerce Product Collection block
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class WC_Product_Collection_Extension
 *
 * Extends Product Collection block with responsive features
 */
class WC_Product_Collection_Extension {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('render_block_woocommerce/product-collection', array($this, 'add_responsive_attributes'), 10, 2);
        add_filter('block_type_metadata', array($this, 'extend_product_collection_metadata'));
    }

    /**
     * Extend Product Collection block metadata with responsive attributes
     *
     * @param array $metadata Block metadata
     * @return array Modified metadata
     */
    public function extend_product_collection_metadata($metadata) {
        if (isset($metadata['name']) && $metadata['name'] === 'woocommerce/product-collection') {
            // Ensure attributes array exists
            if (!isset($metadata['attributes'])) {
                $metadata['attributes'] = array();
            }

            // Add responsive attributes
            $metadata['attributes'] = array_merge($metadata['attributes'], array(
                'responsiveColumns' => array(
                    'type' => 'object',
                    'default' => array(
                        'desktop' => 4,
                        'tablet' => 3,
                        'mobile' => 2
                    )
                ),
                'responsiveProductCount' => array(
                    'type' => 'object',
                    'default' => array(
                        'desktop' => 8,
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
     *
     * @param string $block_content Block HTML content
     * @param array  $block Block data
     * @return string Modified block content
     */
    public function add_responsive_attributes($block_content, $block) {
        // Check if responsive mode is enabled
        if (!isset($block['attrs']['enableResponsive']) || !$block['attrs']['enableResponsive']) {
            return $block_content;
        }

        $responsive_columns = $block['attrs']['responsiveColumns'] ?? array(
            'desktop' => 4,
            'tablet' => 3,
            'mobile' => 2
        );
        
        $responsive_counts = $block['attrs']['responsiveProductCount'] ?? array(
            'desktop' => 8,
            'tablet' => 6,
            'mobile' => 4
        );

        // Use WP_HTML_Tag_Processor to safely modify HTML
        if (class_exists('WP_HTML_Tag_Processor')) {
            $processor = new WP_HTML_Tag_Processor($block_content);

            // Find the product collection wrapper
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

        // Fallback for older WordPress versions
        return $this->add_responsive_attributes_fallback($block_content, $responsive_columns, $responsive_counts);
    }

    /**
     * Fallback method for adding responsive attributes (for older WP versions)
     *
     * @param string $block_content Block HTML content
     * @param array  $responsive_columns Responsive column settings
     * @param array  $responsive_counts Responsive product count settings
     * @return string Modified block content
     */
    private function add_responsive_attributes_fallback($block_content, $responsive_columns, $responsive_counts) {
        // Find the product collection div
        $pattern = '/<div([^>]*class="[^"]*wp-block-woocommerce-product-collection[^"]*"[^>]*)>/';
        
        $replacement = sprintf(
            '<div$1 class="wc-responsive-collection" data-responsive-columns=\'%s\' data-responsive-counts=\'%s\'>',
            esc_attr(wp_json_encode($responsive_columns)),
            esc_attr(wp_json_encode($responsive_counts))
        );

        return preg_replace($pattern, $replacement, $block_content, 1);
    }
}

