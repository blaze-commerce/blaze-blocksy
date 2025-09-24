/**
 * Product Variation Swatches Block - Editor Script
 * 
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl, RangeControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el } = wp.element;
    
    /**
     * Register the Product Variation Swatches block
     */
    registerBlockType('custom/product-variation-swatches', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const {
                showLabel,
                maxVisible,
                showMoreButton,
                onlyVariableProducts
            } = attributes;
            
            return el('div', {
                className: 'wp-block-custom-product-variation-swatches-editor'
            }, [
                // Inspector Controls
                el(InspectorControls, {
                    key: 'inspector'
                }, [
                    el(PanelBody, {
                        title: __('Swatch Settings', 'blocksy-child'),
                        key: 'swatch-settings'
                    }, [
                        el(ToggleControl, {
                            label: __('Show attribute labels', 'blocksy-child'),
                            help: __('Display the attribute name above swatches (e.g., "Color", "Size")', 'blocksy-child'),
                            checked: showLabel,
                            onChange: function(value) {
                                setAttributes({ showLabel: value });
                            },
                            key: 'show-label'
                        }),
                        
                        el(RangeControl, {
                            label: __('Maximum visible swatches', 'blocksy-child'),
                            help: __('Number of swatches to show before "View More" button', 'blocksy-child'),
                            value: maxVisible,
                            onChange: function(value) {
                                setAttributes({ maxVisible: value });
                            },
                            min: 1,
                            max: 10,
                            key: 'max-visible'
                        }),
                        
                        el(ToggleControl, {
                            label: __('Show "View More" button', 'blocksy-child'),
                            help: __('Display a button to show additional swatches when there are more than the maximum visible', 'blocksy-child'),
                            checked: showMoreButton,
                            onChange: function(value) {
                                setAttributes({ showMoreButton: value });
                            },
                            key: 'show-more-button'
                        }),
                        
                        el(ToggleControl, {
                            label: __('Only for variable products', 'blocksy-child'),
                            help: __('Only display swatches for products with variations', 'blocksy-child'),
                            checked: onlyVariableProducts,
                            onChange: function(value) {
                                setAttributes({ onlyVariableProducts: value });
                            },
                            key: 'only-variable'
                        })
                    ])
                ]),
                
                // Block preview in editor
                el('div', {
                    className: 'variation-swatches-preview',
                    key: 'preview'
                }, [
                    el('div', {
                        className: 'preview-header',
                        key: 'header'
                    }, [
                        el('h4', {
                            key: 'title'
                        }, __('Product Variation Swatches', 'blocksy-child')),
                        
                        el('p', {
                            className: 'preview-description',
                            key: 'description'
                        }, __('Swatches will appear here for variable products on the frontend.', 'blocksy-child'))
                    ]),
                    
                    // Mock preview of swatches
                    el('div', {
                        className: 'mock-swatches',
                        key: 'mock'
                    }, [
                        showLabel && el('div', {
                            className: 'mock-label',
                            key: 'mock-label'
                        }, __('Color:', 'blocksy-child')),
                        
                        el('div', {
                            className: 'mock-swatch-container',
                            key: 'mock-container'
                        }, [
                            // Mock color swatches
                            el('span', {
                                className: 'mock-swatch',
                                style: { backgroundColor: '#ff0000' },
                                key: 'red'
                            }),
                            el('span', {
                                className: 'mock-swatch',
                                style: { backgroundColor: '#00ff00' },
                                key: 'green'
                            }),
                            el('span', {
                                className: 'mock-swatch',
                                style: { backgroundColor: '#0000ff' },
                                key: 'blue'
                            }),
                            
                            // Show more button if enabled and max visible is less than total
                            (showMoreButton && maxVisible < 5) && el('button', {
                                className: 'mock-view-more',
                                key: 'view-more',
                                disabled: true
                            }, '+' + (5 - maxVisible) + ' ' + __('More', 'blocksy-child'))
                        ])
                    ]),
                    
                    // Settings summary
                    el('div', {
                        className: 'settings-summary',
                        key: 'summary'
                    }, [
                        el('small', {
                            key: 'summary-text'
                        }, [
                            __('Settings: ', 'blocksy-child'),
                            showLabel ? __('Labels shown', 'blocksy-child') : __('Labels hidden', 'blocksy-child'),
                            ', ',
                            __('Max visible: ', 'blocksy-child') + maxVisible,
                            showMoreButton ? ', ' + __('View More enabled', 'blocksy-child') : ''
                        ])
                    ])
                ])
            ]);
        },
        
        save: function() {
            // Server-side rendering, so return null
            return null;
        }
    });
})();
