/**
 * Blaze Product Image Block - Editor Registration
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl, RangeControl } from '@wordpress/components';

/**
 * Edit component
 */
const Edit = (props) => {
	const { attributes, setAttributes } = props;
	const {
		showProductLink,
		showSaleBadge,
		saleBadgeAlign,
		showWishlistButton,
		wishlistButtonPosition,
		enableHoverImage,
		imageSizing,
	} = attributes;

	const blockProps = useBlockProps({
		className: 'blaze-product-image-editor',
	});

	return (
		<div {...blockProps}>
			<InspectorControls>
				{/* Image Settings Panel */}
				<PanelBody
					title={__('Image Settings', 'blocksy-child')}
					initialOpen={true}
				>
					<ToggleControl
						label={__('Show Product Link', 'blocksy-child')}
						checked={showProductLink}
						onChange={(value) => setAttributes({ showProductLink: value })}
						help={__('Make the image clickable to product page.', 'blocksy-child')}
					/>

					<SelectControl
						label={__('Image Size', 'blocksy-child')}
						value={imageSizing}
						options={[
							{ label: __('Thumbnail', 'blocksy-child'), value: 'thumbnail' },
							{ label: __('Medium', 'blocksy-child'), value: 'medium' },
							{ label: __('Large', 'blocksy-child'), value: 'large' },
							{ label: __('Full Size', 'blocksy-child'), value: 'full' },
						]}
						onChange={(value) => setAttributes({ imageSizing: value })}
					/>

					<ToggleControl
						label={__('Enable Hover Image', 'blocksy-child')}
						checked={enableHoverImage}
						onChange={(value) => setAttributes({ enableHoverImage: value })}
						help={__('Show second image from gallery on hover.', 'blocksy-child')}
					/>
				</PanelBody>

				{/* Sale Badge Settings Panel */}
				<PanelBody
					title={__('Sale Badge Settings', 'blocksy-child')}
					initialOpen={false}
				>
					<ToggleControl
						label={__('Show Sale Badge', 'blocksy-child')}
						checked={showSaleBadge}
						onChange={(value) => setAttributes({ showSaleBadge: value })}
					/>

					{showSaleBadge && (
						<SelectControl
							label={__('Badge Position', 'blocksy-child')}
							value={saleBadgeAlign}
							options={[
								{ label: __('Left', 'blocksy-child'), value: 'left' },
								{ label: __('Center', 'blocksy-child'), value: 'center' },
								{ label: __('Right', 'blocksy-child'), value: 'right' },
							]}
							onChange={(value) => setAttributes({ saleBadgeAlign: value })}
						/>
					)}
				</PanelBody>

				{/* Wishlist Button Settings Panel */}
				<PanelBody
					title={__('Blaze Wishlist Settings', 'blocksy-child')}
					initialOpen={true}
				>
					<ToggleControl
						label={__('Show Wishlist Button', 'blocksy-child')}
						checked={showWishlistButton}
						onChange={(value) => setAttributes({ showWishlistButton: value })}
						help={__('Display add to wishlist button on the image.', 'blocksy-child')}
					/>

					{showWishlistButton && (
						<SelectControl
							label={__('Wishlist Button Position', 'blocksy-child')}
							value={wishlistButtonPosition}
							options={[
								{ label: __('Top Left', 'blocksy-child'), value: 'top-left' },
								{ label: __('Top Right', 'blocksy-child'), value: 'top-right' },
								{ label: __('Bottom Left', 'blocksy-child'), value: 'bottom-left' },
								{ label: __('Bottom Right', 'blocksy-child'), value: 'bottom-right' },
							]}
							onChange={(value) => setAttributes({ wishlistButtonPosition: value })}
							help={__('Choose where to display the wishlist button.', 'blocksy-child')}
						/>
					)}
				</PanelBody>
			</InspectorControls>

			{/* Block Preview */}
			<div className="blaze-product-image-preview">
				<div className="blaze-product-image-preview__header">
					<h3>{__('Blaze Product Image', 'blocksy-child')}</h3>
					<p>{__('Product image with wishlist and hover effect', 'blocksy-child')}</p>
				</div>

				<div className="blaze-product-image-preview__mockup">
					<div className="mockup-image">
						<div className="mockup-placeholder">
							<svg width="100" height="100" viewBox="0 0 100 100" fill="none">
								<rect width="100" height="100" fill="#f0f0f0"/>
								<path d="M50 30L60 50H40L50 30Z" fill="#ccc"/>
								<circle cx="50" cy="65" r="5" fill="#ccc"/>
							</svg>
						</div>

						{/* Show sale badge if enabled */}
						{showSaleBadge && (
							<div className={`mockup-badge mockup-badge--${saleBadgeAlign}`}>
								{__('Sale!', 'blocksy-child')}
							</div>
						)}

						{/* Show wishlist button if enabled */}
						{showWishlistButton && (
							<div className={`mockup-wishlist mockup-wishlist--${wishlistButtonPosition}`}>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
									<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" stroke="currentColor" strokeWidth="2"/>
								</svg>
							</div>
						)}
					</div>
				</div>

				<div className="blaze-product-image-preview__settings">
					<div className="setting-row">
						<strong>{__('Hover Image:', 'blocksy-child')}</strong>{' '}
						{enableHoverImage ? __('Enabled', 'blocksy-child') : __('Disabled', 'blocksy-child')}
					</div>
					<div className="setting-row">
						<strong>{__('Wishlist Button:', 'blocksy-child')}</strong>{' '}
						{showWishlistButton ? wishlistButtonPosition : __('Hidden', 'blocksy-child')}
					</div>
					<div className="setting-row">
						<strong>{__('Sale Badge:', 'blocksy-child')}</strong>{' '}
						{showSaleBadge ? saleBadgeAlign : __('Hidden', 'blocksy-child')}
					</div>
				</div>

				<p className="blaze-product-image-preview__note">
					{__('Preview will be shown on the frontend with actual product data.', 'blocksy-child')}
				</p>
			</div>
		</div>
	);
};

/**
 * Register block
 */
registerBlockType('blaze/product-image', {
	title: __('Blaze Product Image', 'blocksy-child'),
	description: __('Display product image with wishlist button and hover effect.', 'blocksy-child'),
	category: 'woocommerce',
	icon: 'format-image',
	keywords: [
		__('woocommerce', 'blocksy-child'),
		__('product', 'blocksy-child'),
		__('image', 'blocksy-child'),
		__('wishlist', 'blocksy-child'),
	],
	attributes: {
		productId: {
			type: 'number',
			default: 0,
		},
		showProductLink: {
			type: 'boolean',
			default: true,
		},
		showSaleBadge: {
			type: 'boolean',
			default: true,
		},
		saleBadgeAlign: {
			type: 'string',
			default: 'right',
		},
		imageSizing: {
			type: 'string',
			default: 'full',
		},
		showWishlistButton: {
			type: 'boolean',
			default: true,
		},
		wishlistButtonPosition: {
			type: 'string',
			default: 'top-right',
		},
		enableHoverImage: {
			type: 'boolean',
			default: true,
		},
		width: {
			type: 'string',
		},
		height: {
			type: 'string',
		},
		scale: {
			type: 'string',
			default: 'cover',
		},
		aspectRatio: {
			type: 'string',
		},
		isDescendentOfQueryLoop: {
			type: 'boolean',
			default: false,
		},
	},
	supports: {
		align: true,
		html: false,
	},
	edit: Edit,
	save: () => null, // Server-side rendering
});

