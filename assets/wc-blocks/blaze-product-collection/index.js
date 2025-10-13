/**
 * Blaze Product Collection Block - Editor Registration
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { ServerSideRender } from '@wordpress/editor';

/**
 * Edit component
 */
const Edit = (props) => {
	const { attributes, setAttributes } = props;
	const {
		enableResponsive,
		responsiveColumns,
		responsiveProductCount,
		query,
		displayLayout,
	} = attributes;

	const blockProps = useBlockProps({
		className: 'blaze-product-collection-editor',
	});

	return (
		<div {...blockProps}>
			<InspectorControls>
				{/* Responsive Settings Panel */}
				<PanelBody
					title={__('Blaze Responsive Settings', 'blocksy-child')}
					initialOpen={true}
				>
					<ToggleControl
						label={__('Enable Responsive Display', 'blocksy-child')}
						checked={enableResponsive}
						onChange={(value) => setAttributes({ enableResponsive: value })}
						help={__('Automatically adjust columns and product count based on device.', 'blocksy-child')}
					/>

					{enableResponsive && (
						<>
							<h3>{__('Desktop Settings', 'blocksy-child')}</h3>
							<RangeControl
								label={__('Desktop Columns', 'blocksy-child')}
								value={responsiveColumns.desktop}
								onChange={(value) =>
									setAttributes({
										responsiveColumns: {
											...responsiveColumns,
											desktop: value,
										},
									})
								}
								min={1}
								max={6}
							/>
							<RangeControl
								label={__('Desktop Product Count', 'blocksy-child')}
								value={responsiveProductCount.desktop}
								onChange={(value) =>
									setAttributes({
										responsiveProductCount: {
											...responsiveProductCount,
											desktop: value,
										},
									})
								}
								min={1}
								max={24}
							/>

							<h3>{__('Tablet Settings', 'blocksy-child')}</h3>
							<RangeControl
								label={__('Tablet Columns', 'blocksy-child')}
								value={responsiveColumns.tablet}
								onChange={(value) =>
									setAttributes({
										responsiveColumns: {
											...responsiveColumns,
											tablet: value,
										},
									})
								}
								min={1}
								max={4}
							/>
							<RangeControl
								label={__('Tablet Product Count', 'blocksy-child')}
								value={responsiveProductCount.tablet}
								onChange={(value) =>
									setAttributes({
										responsiveProductCount: {
											...responsiveProductCount,
											tablet: value,
										},
									})
								}
								min={1}
								max={18}
							/>

							<h3>{__('Mobile Settings', 'blocksy-child')}</h3>
							<RangeControl
								label={__('Mobile Columns', 'blocksy-child')}
								value={responsiveColumns.mobile}
								onChange={(value) =>
									setAttributes({
										responsiveColumns: {
											...responsiveColumns,
											mobile: value,
										},
									})
								}
								min={1}
								max={3}
							/>
							<RangeControl
								label={__('Mobile Product Count', 'blocksy-child')}
								value={responsiveProductCount.mobile}
								onChange={(value) =>
									setAttributes({
										responsiveProductCount: {
											...responsiveProductCount,
											mobile: value,
										},
									})
								}
								min={1}
								max={12}
							/>
						</>
					)}
				</PanelBody>

				{/* Query Settings Panel */}
				<PanelBody
					title={__('Query Settings', 'blocksy-child')}
					initialOpen={false}
				>
					<RangeControl
						label={__('Products Per Page', 'blocksy-child')}
						value={query.perPage}
						onChange={(value) =>
							setAttributes({
								query: {
									...query,
									perPage: value,
								},
							})
						}
						min={1}
						max={24}
					/>

					<SelectControl
						label={__('Order By', 'blocksy-child')}
						value={query.orderBy}
						options={[
							{ label: __('Title', 'blocksy-child'), value: 'title' },
							{ label: __('Date', 'blocksy-child'), value: 'date' },
							{ label: __('Price', 'blocksy-child'), value: 'price' },
							{ label: __('Popularity', 'blocksy-child'), value: 'popularity' },
							{ label: __('Rating', 'blocksy-child'), value: 'rating' },
						]}
						onChange={(value) =>
							setAttributes({
								query: {
									...query,
									orderBy: value,
								},
							})
						}
					/>

					<SelectControl
						label={__('Order', 'blocksy-child')}
						value={query.order}
						options={[
							{ label: __('Ascending', 'blocksy-child'), value: 'asc' },
							{ label: __('Descending', 'blocksy-child'), value: 'desc' },
						]}
						onChange={(value) =>
							setAttributes({
								query: {
									...query,
									order: value,
								},
							})
						}
					/>

					<ToggleControl
						label={__('Show Only On Sale Products', 'blocksy-child')}
						checked={query.woocommerceOnSale || false}
						onChange={(value) =>
							setAttributes({
								query: {
									...query,
									woocommerceOnSale: value,
								},
							})
						}
					/>
				</PanelBody>

				{/* Display Settings Panel */}
				<PanelBody
					title={__('Display Settings', 'blocksy-child')}
					initialOpen={false}
				>
					<RangeControl
						label={__('Default Columns', 'blocksy-child')}
						value={displayLayout.columns}
						onChange={(value) =>
							setAttributes({
								displayLayout: {
									...displayLayout,
									columns: value,
								},
							})
						}
						min={1}
						max={6}
						help={__('Used when responsive mode is disabled.', 'blocksy-child')}
					/>
				</PanelBody>
			</InspectorControls>

			{/* Block Preview */}
			<div className="blaze-product-collection-preview">
				<div className="blaze-product-collection-preview__header">
					<h3>{__('Blaze Product Collection', 'blocksy-child')}</h3>
					<p>
						{enableResponsive
							? __('Responsive mode enabled', 'blocksy-child')
							: __('Responsive mode disabled', 'blocksy-child')}
					</p>
				</div>
				<div className="blaze-product-collection-preview__settings">
					{enableResponsive && (
						<>
							<div className="setting-row">
								<strong>{__('Desktop:', 'blocksy-child')}</strong>{' '}
								{responsiveProductCount.desktop} {__('products', 'blocksy-child')} /{' '}
								{responsiveColumns.desktop} {__('columns', 'blocksy-child')}
							</div>
							<div className="setting-row">
								<strong>{__('Tablet:', 'blocksy-child')}</strong>{' '}
								{responsiveProductCount.tablet} {__('products', 'blocksy-child')} /{' '}
								{responsiveColumns.tablet} {__('columns', 'blocksy-child')}
							</div>
							<div className="setting-row">
								<strong>{__('Mobile:', 'blocksy-child')}</strong>{' '}
								{responsiveProductCount.mobile} {__('products', 'blocksy-child')} /{' '}
								{responsiveColumns.mobile} {__('columns', 'blocksy-child')}
							</div>
						</>
					)}
				</div>
				<p className="blaze-product-collection-preview__note">
					{__('Preview will be shown on the frontend.', 'blocksy-child')}
				</p>
			</div>
		</div>
	);
};

/**
 * Register block
 */
registerBlockType('blaze/product-collection', {
	title: __('Blaze Product Collection', 'blocksy-child'),
	description: __('Display a responsive collection of products from your store.', 'blocksy-child'),
	category: 'woocommerce',
	icon: 'grid-view',
	keywords: [
		__('woocommerce', 'blocksy-child'),
		__('products', 'blocksy-child'),
		__('collection', 'blocksy-child'),
		__('responsive', 'blocksy-child'),
	],
	attributes: {
		queryId: {
			type: 'number',
		},
		query: {
			type: 'object',
			default: {
				perPage: 8,
				pages: 0,
				offset: 0,
				postType: 'product',
				order: 'asc',
				orderBy: 'title',
				author: '',
				search: '',
				exclude: [],
				sticky: '',
				inherit: false,
				taxQuery: null,
				parents: [],
				woocommerceOnSale: false,
				woocommerceStockStatus: [],
				woocommerceAttributes: [],
				isProductCollectionBlock: true,
			},
		},
		displayLayout: {
			type: 'object',
			default: {
				type: 'flex',
				columns: 4,
			},
		},
		enableResponsive: {
			type: 'boolean',
			default: true,
		},
		responsiveColumns: {
			type: 'object',
			default: {
				desktop: 4,
				tablet: 3,
				mobile: 2,
			},
		},
		responsiveProductCount: {
			type: 'object',
			default: {
				desktop: 8,
				tablet: 6,
				mobile: 4,
			},
		},
		collection: {
			type: 'string',
		},
		hideControls: {
			type: 'array',
			default: [],
		},
		align: {
			type: 'string',
		},
	},
	supports: {
		align: ['wide', 'full'],
		html: false,
		anchor: true,
	},
	edit: Edit,
	save: () => null, // Server-side rendering
});

