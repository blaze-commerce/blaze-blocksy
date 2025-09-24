/**
 * Product Variation Swatches Block - Frontend Script
 * 
 * Handles dynamic color label display and swatch interactions
 * 
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Initialize variation swatches functionality
     */
    function initVariationSwatches() {
        // Handle swatch selection for dynamic label updates
        $(document).on('click', '.wp-block-custom-product-variation-swatches .ct-swatch, .wp-block-custom-product-variation-swatches .variation-option', function(e) {
            e.preventDefault();

            var $swatch = $(this);
            var $container = $swatch.closest('.wp-block-custom-product-variation-swatches');
            var attribute = $swatch.data('attribute') || $swatch.closest('[data-attribute]').data('attribute');
            var $labelContainer = $container.find('.dynamic-variation-label[data-attribute="' + attribute + '"]');
            var $labelText = $labelContainer.find('.variation-name-text');

            // Get the selected option name
            var optionName = $swatch.data('option-name') || $swatch.attr('title') || $swatch.text().trim();

            // Show label and update with selected option name only
            if (optionName && $labelText.length) {
                $labelText.text(optionName);
                $labelContainer.show();
            }

            // Remove active class from all swatches in this attribute group
            $container.find('[data-attribute="' + attribute + '"]').removeClass('selected active');

            // Add active class to selected swatch
            $swatch.addClass('selected active');

            // Trigger custom event for other scripts
            $container.trigger('variation_swatch_selected', {
                attribute: attribute,
                value: optionName,
                swatch: $swatch
            });
        });
        
        // Handle deselection (clicking on already selected swatch)
        $(document).on('click', '.wp-block-custom-product-variation-swatches .ct-swatch.selected, .wp-block-custom-product-variation-swatches .variation-option.selected', function(e) {
            e.preventDefault();

            var $swatch = $(this);
            var $container = $swatch.closest('.wp-block-custom-product-variation-swatches');
            var attribute = $swatch.data('attribute') || $swatch.closest('[data-attribute]').data('attribute');
            var $labelContainer = $container.find('.dynamic-variation-label[data-attribute="' + attribute + '"]');

            // Hide label completely (no default label shown)
            $labelContainer.hide();

            // Remove active class
            $swatch.removeClass('selected active');

            // Trigger custom event
            $container.trigger('variation_swatch_deselected', {
                swatch: $swatch
            });
        });
        
        // Handle existing plugin swatch interactions
        $(document).on('click', '.wp-block-custom-product-variation-swatches .wvs-archive-variations-wrapper .ct-swatch', function() {
            var $swatch = $(this);
            var $container = $swatch.closest('.wp-block-custom-product-variation-swatches');

            // Small delay to allow plugin to process first
            setTimeout(function() {
                updateVariationLabel($container, $swatch);
            }, 50);
        });

        // Handle radio button changes (for dropdown variations)
        $(document).on('change', '.wp-block-custom-product-variation-swatches select', function() {
            var $select = $(this);
            var $container = $select.closest('.wp-block-custom-product-variation-swatches');
            var selectedText = $select.find('option:selected').text();
            var attribute = $select.attr('name') || $select.data('attribute');
            var $labelContainer = $container.find('.dynamic-variation-label[data-attribute="' + attribute + '"]');
            var $labelText = $labelContainer.find('.variation-name-text');

            if (selectedText && selectedText !== 'Choose an option' && $labelText.length) {
                $labelText.text(selectedText);
                $labelContainer.show();
            } else {
                $labelContainer.hide();
            }
        });
    }
    
    /**
     * Update variation label based on selected swatch
     */
    function updateVariationLabel($container, $swatch) {
        var attribute = $swatch.data('attribute') || $swatch.closest('[data-attribute]').data('attribute');
        var $labelContainer = $container.find('.dynamic-variation-label[data-attribute="' + attribute + '"]');
        var $labelText = $labelContainer.find('.variation-name-text');
        var optionName = $swatch.attr('title') || $swatch.data('option-name') || $swatch.text().trim();

        if ($swatch.hasClass('selected') || $swatch.hasClass('active')) {
            if (optionName && $labelText.length) {
                $labelText.text(optionName);
                $labelContainer.show();
            }
        } else {
            // Hide label when not selected (no default label)
            $labelContainer.hide();
        }
    }
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        initVariationSwatches();
        
        // Re-initialize after AJAX content loads (for infinite scroll, etc.)
        $(document).on('wc_fragments_refreshed wc_fragments_loaded', function() {
            // Remove existing handlers first to prevent memory leaks
            $(document).off('click.variation-swatches');
            initVariationSwatches();
        });
    });
    
})(jQuery);
