/**
 * Product Collection Block Extension - Editor Script
 *
 * Adds responsive column and product count controls to the Product Collection block editor
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
    const { PanelBody, ToggleControl, RangeControl } = wp.components;
    const { __ } = wp.i18n;

    /**
     * Add responsive controls to Product Collection block
     */
    const withResponsiveControls = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { attributes, setAttributes, name } = props;

            // Only apply to Product Collection block
            if (name !== 'woocommerce/product-collection') {
                return wp.element.createElement(BlockEdit, props);
            }

            const {
                enableResponsive = false,
                responsiveColumns = { desktop: 4, tablet: 3, mobile: 2 },
                responsiveProductCount = { desktop: 8, tablet: 6, mobile: 4 }
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
                            title: __('Responsive Settings', 'blocksy-child'),
                            initialOpen: false
                        },
                        wp.element.createElement(ToggleControl, {
                            label: __('Enable Responsive Layout', 'blocksy-child'),
                            help: __('Configure different columns and product counts for desktop, tablet, and mobile devices', 'blocksy-child'),
                            checked: enableResponsive,
                            onChange: (value) => setAttributes({ enableResponsive: value })
                        }),

                        enableResponsive && wp.element.createElement(
                            Fragment,
                            null,
                            wp.element.createElement('h4', { style: { marginTop: '16px', marginBottom: '8px' } }, 
                                __('Columns per Device', 'blocksy-child')
                            ),
                            wp.element.createElement(RangeControl, {
                                label: __('Desktop Columns', 'blocksy-child'),
                                value: responsiveColumns.desktop,
                                onChange: (value) => setAttributes({
                                    responsiveColumns: { ...responsiveColumns, desktop: value }
                                }),
                                min: 1,
                                max: 6,
                                help: __('Number of columns on desktop (≥1024px)', 'blocksy-child')
                            }),
                            wp.element.createElement(RangeControl, {
                                label: __('Tablet Columns', 'blocksy-child'),
                                value: responsiveColumns.tablet,
                                onChange: (value) => setAttributes({
                                    responsiveColumns: { ...responsiveColumns, tablet: value }
                                }),
                                min: 1,
                                max: 4,
                                help: __('Number of columns on tablet (768px-1023px)', 'blocksy-child')
                            }),
                            wp.element.createElement(RangeControl, {
                                label: __('Mobile Columns', 'blocksy-child'),
                                value: responsiveColumns.mobile,
                                onChange: (value) => setAttributes({
                                    responsiveColumns: { ...responsiveColumns, mobile: value }
                                }),
                                min: 1,
                                max: 2,
                                help: __('Number of columns on mobile (<768px)', 'blocksy-child')
                            }),

                            wp.element.createElement('h4', { style: { marginTop: '16px', marginBottom: '8px' } }, 
                                __('Products per Device', 'blocksy-child')
                            ),
                            wp.element.createElement(RangeControl, {
                                label: __('Desktop Products', 'blocksy-child'),
                                value: responsiveProductCount.desktop,
                                onChange: (value) => setAttributes({
                                    responsiveProductCount: { ...responsiveProductCount, desktop: value }
                                }),
                                min: 1,
                                max: 20,
                                help: __('Number of products to show on desktop', 'blocksy-child')
                            }),
                            wp.element.createElement(RangeControl, {
                                label: __('Tablet Products', 'blocksy-child'),
                                value: responsiveProductCount.tablet,
                                onChange: (value) => setAttributes({
                                    responsiveProductCount: { ...responsiveProductCount, tablet: value }
                                }),
                                min: 1,
                                max: 12,
                                help: __('Number of products to show on tablet', 'blocksy-child')
                            }),
                            wp.element.createElement(RangeControl, {
                                label: __('Mobile Products', 'blocksy-child'),
                                value: responsiveProductCount.mobile,
                                onChange: (value) => setAttributes({
                                    responsiveProductCount: { ...responsiveProductCount, mobile: value }
                                }),
                                min: 1,
                                max: 8,
                                help: __('Number of products to show on mobile', 'blocksy-child')
                            })
                        )
                    )
                )
            );
        };
    }, 'withResponsiveControls');

    // Register the filter
    addFilter(
        'editor.BlockEdit',
        'wc-block-extensions/product-collection-responsive',
        withResponsiveControls
    );

})(window.wp);

