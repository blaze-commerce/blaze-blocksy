/**
 * Product Image Block - Enhancement Controls (Editor)
 *
 * Adds hover image and wishlist controls to the Product Image block
 * in the WordPress block editor (Gutenberg).
 *
 * @package BlazeBlocksy
 * @since 1.0.0
 */

(function (wp) {
	'use strict';

	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { Fragment } = wp.element;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl, SelectControl } = wp.components;
	const { __ } = wp.i18n;

	/**
	 * Add image enhancement controls to Product Image block
	 */
	const withImageEnhancements = createHigherOrderComponent((BlockEdit) => {
		return (props) => {
			const { attributes, setAttributes, name } = props;

			// Only apply to Product Image block
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
							title={__('Image Enhancements', 'blocksy-child')}
							initialOpen={false}
						>
							<ToggleControl
								label={__('Enable Hover Image', 'blocksy-child')}
								help={__('Show second gallery image on hover', 'blocksy-child')}
								checked={enableHoverImage}
								onChange={(value) => setAttributes({ enableHoverImage: value })}
							/>

							<ToggleControl
								label={__('Show Wishlist Button', 'blocksy-child')}
								help={__('Add wishlist button overlay on product image', 'blocksy-child')}
								checked={showWishlistButton}
								onChange={(value) => setAttributes({ showWishlistButton: value })}
							/>

							{showWishlistButton && (
								<SelectControl
									label={__('Wishlist Button Position', 'blocksy-child')}
									value={wishlistButtonPosition}
									options={[
										{ label: __('Top Left', 'blocksy-child'), value: 'top-left' },
										{ label: __('Top Right', 'blocksy-child'), value: 'top-right' },
										{ label: __('Bottom Left', 'blocksy-child'), value: 'bottom-left' },
										{ label: __('Bottom Right', 'blocksy-child'), value: 'bottom-right' }
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

	// Register the filter
	addFilter(
		'editor.BlockEdit',
		'blaze-blocksy/product-image-enhancements',
		withImageEnhancements
	);

})(window.wp);

