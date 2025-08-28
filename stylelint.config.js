module.exports = {
	extends: [
	'stylelint-config-standard',
	'stylelint-config-wordpress'
	],
	rules: {
		// Code Quality Standards
		'max-line-length': 120,
		'max-nesting-depth': 4,
		'selector-max-compound-selectors': 4,
		'selector-max-specificity': '0,4,0',

		// Performance Standards
		'no-duplicate-selectors': true,
		'declaration-no-important': true,
		'font-display-no-swap': null,

		// WordPress Specific
		'at-rule-no-unknown': [
		true,
		{
			ignoreAtRules: [
			'extend',
			'at-root',
			'debug',
			'warn',
			'error',
			'if',
			'else',
			'for',
			'each',
			'while',
			'include',
			'mixin'
			]
		}
		],

		// Allow WordPress naming conventions
		'selector-class-pattern': null,
		'selector-id-pattern': null,

		// Security considerations
		'function-url-no-scheme-relative': true,
		'function-url-scheme-allowed-list': ['https', 'data'],

		// Accessibility
		'color-contrast': null, // Would need additional plugin

		// Browser compatibility
		'property-no-vendor-prefix': null,
		'value-no-vendor-prefix': null
	},
	ignoreFiles: [
	'node_modules/**/*',
	'vendor/**/*',
	'dist/**/*',
	'build/**/*',
	'**/*.min.css'
	]
};
