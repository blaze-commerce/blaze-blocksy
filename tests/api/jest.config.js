/**
 * Jest Configuration for API Testing
 * 
 * Specialized configuration for WooCommerce REST API testing
 * 
 * @package BlazeCommerce\Tests\API
 */

module.exports = {
  displayName: 'API Tests',
  testEnvironment: 'node',
  testMatch: [
    '<rootDir>/**/*.test.js',
    '<rootDir>/**/*.spec.js'
  ],
  setupFilesAfterEnv: [
    '<rootDir>/setup.js'
  ],
  collectCoverageFrom: [
    'assets/js/**/*.js',
    '!assets/js/**/*.min.js',
    '!assets/js/**/*.test.js',
    '!assets/js/**/*.spec.js'
  ],
  coverageDirectory: 'coverage/api',
  coverageReporters: [
    'text',
    'lcov',
    'html',
    'json'
  ],
  testTimeout: 30000, // 30 seconds for API calls
  verbose: true,
  bail: false,
  maxWorkers: 1, // Run API tests sequentially to avoid conflicts
  
  // Global variables for API testing
  globals: {
    API_BASE_URL: process.env.API_BASE_URL || 'https://your-wordpress-site.com',
    WC_CONSUMER_KEY: process.env.WC_CONSUMER_KEY || '',
    WC_CONSUMER_SECRET: process.env.WC_CONSUMER_SECRET || '',
    TEST_USER_EMAIL: process.env.TEST_USER_EMAIL || 'test@example.com',
    TEST_USER_PASSWORD: process.env.TEST_USER_PASSWORD || 'test-password'
  },
  
  // Transform configuration
  transform: {
    '^.+\\.js$': 'babel-jest'
  },
  
  // Module path mapping
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/../../$1',
    '^@tests/(.*)$': '<rootDir>/../../tests/$1',
    '^@api/(.*)$': '<rootDir>/$1'
  },
  
  // Test result processors
  reporters: [
    'default',
    ['jest-junit', {
      outputDirectory: 'coverage',
      outputName: 'api-junit.xml',
      suiteName: 'API Tests'
    }]
  ]
};
