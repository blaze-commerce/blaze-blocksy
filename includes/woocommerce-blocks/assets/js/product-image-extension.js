/**
 * Product Image Block Extension - Editor Script
 *
 * Adds hover image and wishlist controls to the Product Image block editor
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function(wp) {
    'use strict';

    const { addFilter } = wp.hooks;
    const { createHigherOrderComponent } = wp.compose;
    const { Fragment } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl, SelectControl } = wp.components;
    const { __ } = wp.i18n;

    /**
     * Add hover and wishlist controls to Product Image block
     */
    const withImageEnhancements = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, setAttributes, name } = props;

            // Only apply to Product Image block
            if (name !== 'woocommerce/product-image') {
                return wp.element.createElement(BlockEdit, props);
            }

            const {
                enableHoverImage = false,
                showWishlistButton = false,
                wishlistButtonPosition = 'top-right'
            } = attributes;

            return wp.element.createElement(
                Fragment,
                null,
                wp.element.createElement(BlockEdit, props),
                wp.element.createElement(
                    InspectorControls,
                    null,
                    wp.element.createElement(
                        PanelBody,
                        {
                            title: __('Image Enhancements', 'blocksy-child'),
                            initialOpen: false
                        },
                        wp.element.createElement(ToggleControl, {
                            label: __('Enable Hover Image', 'blocksy-child'),
                            help: __('Show second product image on hover (uses first gallery image)', 'blocksy-child'),
                            checked: enableHoverImage,
                            onChange: (value) => setAttributes({ enableHoverImage: value })
                        }),

                        wp.element.createElement(ToggleControl, {
                            label: __('Show Wishlist Button', 'blocksy-child'),
                            help: __('Add wishlist button overlay (integrates with Blocksy wishlist)', 'blocksy-child'),
                            checked: showWishlistButton,
                            onChange: (value) => setAttributes({ showWishlistButton: value })
                        }),

                        showWishlistButton && wp.element.createElement(SelectControl, {
                            label: __('Wishlist Button Position', 'blocksy-child'),
                            value: wishlistButtonPosition,
                            options: [
                                { label: __('Top Left', 'blocksy-child'), value: 'top-left' },
                                { label: __('Top Right', 'blocksy-child'), value: 'top-right' },
                                { label: __('Bottom Left', 'blocksy-child'), value: 'bottom-left' },
                                { label: __('Bottom Right', 'blocksy-child'), value: 'bottom-right' }
                            ],
                            onChange: (value) => setAttributes({ wishlistButtonPosition: value }),
                            help: __('Choose where to display the wishlist button', 'blocksy-child')
                        })
                    )
                )
            );
        };
    }, 'withImageEnhancements');

    // Register the filter
    addFilter(
        'editor.BlockEdit',
        'wc-block-extensions/product-image-enhancements',
        withImageEnhancements
    );

})(window.wp);

