/**
 * Lighthouse CI Configuration
 *
 * @package BlazeCommerce
 * @subpackage Tests
 */

module.exports = {
	ci: {
		collect: {
			url: [
			'https://stg-infinitytargetscom-sitebuild.kinsta.cloud/',
			'https://stg-infinitytargetscom-sitebuild.kinsta.cloud/shop/',
			'https://stg-infinitytargetscom-sitebuild.kinsta.cloud/cart/',
			'https://stg-infinitytargetscom-sitebuild.kinsta.cloud/checkout/'
			],
			numberOfRuns: 3,
			settings: {
				chromeFlags: '--no-sandbox --disable-dev-shm-usage',
				preset: 'desktop',
				throttling: {
					rttMs: 40,
					throughputKbps: 10240,
					cpuSlowdownMultiplier: 1,
					requestLatencyMs: 0,
					downloadThroughputKbps: 0,
					uploadThroughputKbps: 0
				},
				auditMode: false,
				gatherMode: false,
				disableStorageReset: false,
				emulatedFormFactor: 'desktop',
				internalDisableDeviceScreenEmulation: true,
				channel: 'cli'
			}
		},
		assert: {
			assertions: {
				'categories:performance': ['error', { minScore: 0.8 }],
				'categories:accessibility': ['error', { minScore: 0.9 }],
				'categories:best-practices': ['error', { minScore: 0.8 }],
				'categories:seo': ['error', { minScore: 0.8 }],

				// Core Web Vitals
				'first-contentful-paint': ['error', { maxNumericValue: 1800 }],
				'largest-contentful-paint': ['error', { maxNumericValue: 2500 }],
				'cumulative-layout-shift': ['error', { maxNumericValue: 0.1 }],
				'total-blocking-time': ['error', { maxNumericValue: 300 }],

				// Performance metrics
				'speed-index': ['error', { maxNumericValue: 3000 }],
				'interactive': ['error', { maxNumericValue: 3800 }],

				// Best practices
				'uses-https': 'off', // Disabled for local development
				'uses-http2': 'off', // Disabled for local development
				'uses-text-compression': ['error', { minScore: 0.8 }],
				'uses-responsive-images': ['error', { minScore: 0.8 }],
				'efficient-animated-content': ['error', { minScore: 0.8 }],

				// Accessibility
				'color-contrast': ['error', { minScore: 1 }],
				'heading-order': ['error', { minScore: 1 }],
				'html-has-lang': ['error', { minScore: 1 }],
				'image-alt': ['error', { minScore: 1 }],
				'link-name': ['error', { minScore: 1 }],

				// SEO
				'document-title': ['error', { minScore: 1 }],
				'meta-description': ['error', { minScore: 1 }],
				'robots-txt': 'off', // May not be applicable for all sites
				'canonical': 'off' // May not be applicable for all pages
			}
		},
		upload: {
			target: 'filesystem',
			outputDir: './coverage/lighthouse',
			reportFilenamePattern: '%%PATHNAME%%-%%DATETIME%%-report.%%EXTENSION%%'
		},
		server: {
			port: 9001,
			storage: {
				storageMethod: 'filesystem',
				storagePath: './coverage/lighthouse/.lighthouseci'
			}
		},
		wizard: {
			// Configuration for Lighthouse CI wizard
		}
	}
};
