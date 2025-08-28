/**
 * Basic tests for the documentation organization system
 * These tests verify core functionality without requiring complex setup
 */

const { categorizeFile, DOC_CONFIG } = require('../scripts/organize-docs');

describe('Documentation Organization Tests', () => {
  describe('categorizeFile', () => {
    test('should categorize API files correctly', () => {
      const file = {
        fileName: 'api-endpoints.md',
        relativePath: 'api-endpoints.md',
        fullPath: '/test/api-endpoints.md',
        directory: '.'
      };
      
      const result = categorizeFile(file);
      expect(result.category).toBe('api');
      expect(result.reason).toBe('Content analysis match');
    });

    test('should skip root exception files', () => {
      const file = {
        fileName: 'README.md',
        relativePath: 'README.md',
        fullPath: '/test/README.md',
        directory: '.'
      };
      
      const result = categorizeFile(file);
      expect(result.category).toBe('root');
      expect(result.reason).toBe('Root exception');
    });

    test('should skip files already in docs subdirectories', () => {
      const file = {
        fileName: 'test.md',
        relativePath: 'docs/api/test.md',
        fullPath: '/test/docs/api/test.md',
        directory: 'docs/api'
      };
      
      const result = categorizeFile(file);
      expect(result.category).toBe('skip');
      expect(result.reason).toBe('Already in docs subdirectory');
    });

    test('should categorize development files correctly', () => {
      const file = {
        fileName: 'git-workflow.md',
        relativePath: 'git-workflow.md',
        fullPath: '/test/git-workflow.md',
        directory: '.'
      };
      
      const result = categorizeFile(file);
      expect(result.category).toBe('development');
    });

    test('should use default category for uncategorized files', () => {
      const file = {
        fileName: 'random-notes.md',
        relativePath: 'random-notes.md',
        fullPath: '/test/random-notes.md',
        directory: '.'
      };
      
      const result = categorizeFile(file);
      expect(result.category).toBe('general');
      expect(result.reason).toBe('Default categorization');
    });
  });

  describe('Configuration', () => {
    test('should have valid configuration structure', () => {
      expect(DOC_CONFIG).toBeDefined();
      expect(DOC_CONFIG.categories).toBeDefined();
      expect(DOC_CONFIG.rootExceptions).toBeDefined();
      expect(Array.isArray(DOC_CONFIG.rootExceptions)).toBe(true);
    });

    test('should include standard root exceptions', () => {
      expect(DOC_CONFIG.rootExceptions).toContain('README.md');
      expect(DOC_CONFIG.rootExceptions).toContain('CHANGELOG.md');
    });

    test('should have valid category definitions', () => {
      Object.entries(DOC_CONFIG.categories).forEach(([name, config]) => {
        expect(config.path).toBeDefined();
        expect(config.patterns).toBeDefined();
        expect(config.keywords).toBeDefined();
        expect(Array.isArray(config.patterns)).toBe(true);
        expect(Array.isArray(config.keywords)).toBe(true);
      });
    });
  });
});

// Mock fs module for testing
jest.mock('fs', () => ({
  existsSync: jest.fn(() => false),
  readFileSync: jest.fn(() => 'test content'),
  readdirSync: jest.fn(() => []),
  statSync: jest.fn(() => ({ isDirectory: () => false })),
  mkdirSync: jest.fn(),
  renameSync: jest.fn(),
  accessSync: jest.fn(),
  constants: { W_OK: 2 }
}));
