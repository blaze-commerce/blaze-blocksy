/**
 * Jest Configuration for Security Testing
 * 
 * Specialized configuration for security and vulnerability testing
 * 
 * @package BlazeCommerce\Tests\Security
 */

module.exports = {
  displayName: 'Security Tests',
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
  coverageDirectory: 'coverage/security',
  coverageReporters: [
    'text',
    'lcov',
    'html',
    'json'
  ],
  testTimeout: 60000, // 60 seconds for security scans
  verbose: true,
  bail: false,
  maxWorkers: 1, // Run security tests sequentially
  
  // Global variables for security testing
  globals: {
    SECURITY_BASE_URL: process.env.API_BASE_URL || 'https://stg-infinitytargetscom-sitebuild.kinsta.cloud',
    SECURITY_TIMEOUT: 30000,
    ENABLE_VULNERABILITY_SCAN: process.env.ENABLE_VULNERABILITY_SCAN === 'true',
    SECURITY_BASELINE_PATH: '<rootDir>/tests/security/security-baseline.json'
  },
  
  // Transform configuration
  transform: {
    '^.+\\.js$': 'babel-jest'
  },
  
  // Module path mapping
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/../../$1',
    '^@tests/(.*)$': '<rootDir>/../../tests/$1',
    '^@security/(.*)$': '<rootDir>/$1'
  },
  
  // Test result processors
  reporters: [
    'default',
    ['jest-junit', {
      outputDirectory: 'coverage',
      outputName: 'security-junit.xml',
      suiteName: 'Security Tests'
    }]
  ]
};
