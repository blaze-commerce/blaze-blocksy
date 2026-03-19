/**
 * Blocksy Product Card Block - Editor Script
 *
 * @package BlazeBlocksy
 * @since 1.5.0
 */

(function () {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { createElement: el } = wp.element;

	registerBlockType( 'custom/blocksy-product-card', {
		edit: function () {
			return el(
				'div',
				{
					className: 'wp-block-custom-blocksy-product-card-editor',
				},
				el(
					'div',
					{ className: 'blocksy-product-card-placeholder' },
					el(
						'svg',
						{
							width: '48',
							height: '48',
							viewBox: '0 0 24 24',
							fill: 'none',
							xmlns: 'http://www.w3.org/2000/svg',
						},
						el( 'path', {
							d: 'M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm0 2v5l3-3 2 2 4-4 7 7V6H4zm0 12h16v-1.17l-7-7-4 4-2-2-3 3V18z',
							fill: 'currentColor',
							opacity: '0.5',
						} )
					),
					el(
						'h4',
						{ style: { margin: '8px 0 4px' } },
						__( 'Blocksy Product Card', 'blaze-blocksy' )
					),
					el(
						'p',
						{
							style: {
								margin: 0,
								fontSize: '12px',
								opacity: 0.7,
							},
						},
						__(
							'Renders using Blocksy Customizer settings (card type, layout, hover effects).',
							'blaze-blocksy'
						)
					)
				)
			);
		},

		save: function () {
			return null;
		},
	} );
})();
