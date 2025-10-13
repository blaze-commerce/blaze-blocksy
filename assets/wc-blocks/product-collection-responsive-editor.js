/**
 * Product Collection Block - Responsive Controls (Editor)
 *
 * Adds responsive column and product count controls to the Product Collection block
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
				return <BlockEdit {...props} />;
			}

			const {
				enableResponsive = false,
				responsiveColumns = { desktop: 4, tablet: 3, mobile: 2 },
				responsiveProductCount = { desktop: 8, tablet: 6, mobile: 4 }
			} = attributes;

			return (
				<Fragment>
					<BlockEdit {...props} />
					<InspectorControls>
						<PanelBody
							title={__('Responsive Settings', 'blocksy-child')}
							initialOpen={false}
						>
							<ToggleControl
								label={__('Enable Responsive Layout', 'blocksy-child')}
								help={__('Configure different columns and product counts for each device type', 'blocksy-child')}
								checked={enableResponsive}
								onChange={(value) => setAttributes({ enableResponsive: value })}
							/>

							{enableResponsive && (
								<Fragment>
									<div style={{ marginTop: '16px', marginBottom: '8px' }}>
										<strong>{__('Columns per Device', 'blocksy-child')}</strong>
									</div>
									
									<RangeControl
										label={__('Desktop Columns', 'blocksy-child')}
										value={responsiveColumns.desktop}
										onChange={(value) => setAttributes({
											responsiveColumns: { ...responsiveColumns, desktop: value }
										})}
										min={1}
										max={6}
										help={__('Number of columns on desktop (≥1024px)', 'blocksy-child')}
									/>
									
									<RangeControl
										label={__('Tablet Columns', 'blocksy-child')}
										value={responsiveColumns.tablet}
										onChange={(value) => setAttributes({
											responsiveColumns: { ...responsiveColumns, tablet: value }
										})}
										min={1}
										max={4}
										help={__('Number of columns on tablet (768px-1023px)', 'blocksy-child')}
									/>
									
									<RangeControl
										label={__('Mobile Columns', 'blocksy-child')}
										value={responsiveColumns.mobile}
										onChange={(value) => setAttributes({
											responsiveColumns: { ...responsiveColumns, mobile: value }
										})}
										min={1}
										max={3}
										help={__('Number of columns on mobile (<768px)', 'blocksy-child')}
									/>

									<div style={{ marginTop: '24px', marginBottom: '8px' }}>
										<strong>{__('Products per Device', 'blocksy-child')}</strong>
									</div>
									
									<RangeControl
										label={__('Desktop Products', 'blocksy-child')}
										value={responsiveProductCount.desktop}
										onChange={(value) => setAttributes({
											responsiveProductCount: { ...responsiveProductCount, desktop: value }
										})}
										min={1}
										max={24}
										help={__('Number of products to show on desktop', 'blocksy-child')}
									/>
									
									<RangeControl
										label={__('Tablet Products', 'blocksy-child')}
										value={responsiveProductCount.tablet}
										onChange={(value) => setAttributes({
											responsiveProductCount: { ...responsiveProductCount, tablet: value }
										})}
										min={1}
										max={18}
										help={__('Number of products to show on tablet', 'blocksy-child')}
									/>
									
									<RangeControl
										label={__('Mobile Products', 'blocksy-child')}
										value={responsiveProductCount.mobile}
										onChange={(value) => setAttributes({
											responsiveProductCount: { ...responsiveProductCount, mobile: value }
										})}
										min={1}
										max={12}
										help={__('Number of products to show on mobile', 'blocksy-child')}
									/>
								</Fragment>
							)}
						</PanelBody>
					</InspectorControls>
				</Fragment>
			);
		};
	}, 'withResponsiveControls');

	// Register the filter
	addFilter(
		'editor.BlockEdit',
		'blaze-blocksy/product-collection-responsive',
		withResponsiveControls
	);

})(window.wp);

