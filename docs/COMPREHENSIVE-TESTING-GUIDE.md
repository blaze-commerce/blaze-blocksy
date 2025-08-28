# Comprehensive Testing Infrastructure Guide

## Overview

This document provides a complete guide to the testing infrastructure implemented for the BlazeCommerce WordPress child theme. The testing suite includes unit tests, integration tests, end-to-end tests, visual regression testing, performance monitoring, and security testing.

## Testing Architecture

### 1. Unit Testing (PHP)
- **Framework**: PHPUnit 9.6
- **Purpose**: Test individual functions and classes in isolation
- **Location**: `tests/unit/`
- **Configuration**: `phpunit.xml`

### 2. Integration Testing (PHP)
- **Framework**: PHPUnit with WordPress Test Suite
- **Purpose**: Test WordPress hooks, filters, and theme integration
- **Location**: `tests/integration/`
- **Database**: Separate test database

### 3. End-to-End Testing (JavaScript)
- **Framework**: Playwright
- **Purpose**: Test complete user workflows and browser interactions
- **Location**: `tests/e2e/`
- **Configuration**: `playwright.config.js`

### 4. Visual Regression Testing
- **Framework**: Playwright Screenshots
- **Purpose**: Detect visual changes and UI regressions
- **Storage**: `tests/e2e/screenshots/`

### 5. Performance Monitoring
- **Framework**: Lighthouse CI
- **Purpose**: Monitor Core Web Vitals and performance metrics
- **Configuration**: `lighthouserc.js`

### 6. Security Testing
- **Framework**: Psalm (Static Analysis)
- **Purpose**: Detect security vulnerabilities and code quality issues
- **Configuration**: Integrated with Composer

## Installation and Setup

### Prerequisites
- PHP 7.4 or higher
- Node.js 16 or higher
- MySQL/MariaDB
- Composer
- npm/yarn

### Initial Setup

1. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

2. **Install Node.js Dependencies**:
   ```bash
   npm install
   ```

3. **Install WordPress Test Suite**:
   ```bash
   composer run setup-tests
   # or manually:
   bash bin/install-wp-tests.sh wordpress_test root root localhost latest
   ```

4. **Install Playwright Browsers**:
   ```bash
   npx playwright install
   ```

## Running Tests

### Unit Tests
```bash
# Run all PHP unit tests
composer test:unit
npm run test:php

# Run with coverage
composer test:coverage
```

### Integration Tests
```bash
# Run integration tests
composer test:integration

# Run all PHP tests
composer test
```

### JavaScript Unit Tests
```bash
# Run Jest tests
npm run test:js

# Run with coverage
npm run coverage
```

### End-to-End Tests
```bash
# Run all E2E tests
npm run test:e2e

# Run in headed mode (visible browser)
npm run test:e2e:headed

# Run specific test file
npx playwright test homepage.spec.js

# Debug mode
npm run test:e2e:debug
```

### Visual Regression Tests
```bash
# Run visual tests
npm run test:visual

# Update screenshots (when intentional changes are made)
npx playwright test --update-snapshots
```

### Performance Tests
```bash
# Run performance audits
npm run performance:audit

# Run Lighthouse CI
npm run performance:lighthouse
```

### Security Tests
```bash
# Run static analysis
composer psalm

# Run security scan
npm run security:scan
```

## Test Organization

### Directory Structure
```
tests/
├── unit/                 # PHP unit tests
├── integration/          # PHP integration tests
├── e2e/                  # End-to-end tests
│   ├── global-setup.js   # Global test setup
│   ├── global-teardown.js # Global test cleanup
│   └── *.spec.js         # Test specifications
├── fixtures/             # Test data and fixtures
├── helpers/              # Test helper classes
└── mocks/               # Mock objects and data
```

### Test Categories

#### Unit Tests
- Theme function tests
- Utility function tests
- Class method tests
- Hook and filter tests

#### Integration Tests
- WordPress integration tests
- WooCommerce integration tests
- Database interaction tests
- Plugin compatibility tests

#### E2E Tests
- User workflow tests
- Form submission tests
- Navigation tests
- Responsive design tests
- Accessibility tests

## Writing Tests

### PHP Unit Test Example
```php
<?php
namespace BlazeCommerce\Tests\Unit;

use BlazeCommerce\Tests\Helpers\TestCase;

class ExampleTest extends TestCase {
    
    public function test_example_function() {
        $result = example_function('input');
        $this->assertEquals('expected', $result);
    }
}
```

### Playwright E2E Test Example
```javascript
const { test, expect } = require('@playwright/test');

test('example test', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('h1')).toBeVisible();
});
```

## Test Data and Fixtures

### Using Fixtures
```php
// In PHP tests
$data = $this->load_fixture('sample-data');

// In JavaScript tests
const data = require('../fixtures/sample-data.json');
```

### Creating Test Data
```php
// Create test user
$user_id = $this->create_test_user([
    'role' => 'customer',
    'user_email' => 'test@example.com'
]);

// Create test post
$post_id = $this->create_test_post([
    'post_type' => 'product',
    'post_status' => 'publish'
]);
```

## Continuous Integration

### GitHub Actions Integration
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: |
          composer install
          npm install
      - name: Run tests
        run: |
          composer test
          npm test
```

## Performance Monitoring

### Core Web Vitals Thresholds
- **First Contentful Paint (FCP)**: < 1.8s
- **Largest Contentful Paint (LCP)**: < 2.5s
- **Cumulative Layout Shift (CLS)**: < 0.1
- **Total Blocking Time (TBT)**: < 300ms

### Lighthouse Scores
- **Performance**: > 80
- **Accessibility**: > 90
- **Best Practices**: > 80
- **SEO**: > 80

## Visual Regression Testing

### Screenshot Management
```bash
# Update all screenshots
npx playwright test --update-snapshots

# Update specific test screenshots
npx playwright test homepage.spec.js --update-snapshots

# Compare screenshots
npx playwright show-report
```

### Best Practices
- Disable animations for consistent screenshots
- Use full-page screenshots for layout tests
- Test across multiple viewports
- Update screenshots when making intentional changes

## Debugging Tests

### PHP Tests
```bash
# Run with verbose output
vendor/bin/phpunit --verbose

# Run specific test
vendor/bin/phpunit tests/unit/ExampleTest.php

# Debug with Xdebug
XDEBUG_MODE=debug vendor/bin/phpunit
```

### Playwright Tests
```bash
# Debug mode
npx playwright test --debug

# Headed mode
npx playwright test --headed

# Trace viewer
npx playwright show-trace trace.zip
```

## Test Coverage

### PHP Coverage
```bash
# Generate HTML coverage report
composer test:coverage

# View coverage report
open coverage/php/index.html
```

### JavaScript Coverage
```bash
# Generate coverage report
npm run coverage

# View coverage report
open coverage/lcov-report/index.html
```

## Best Practices

### General
1. Write tests before fixing bugs
2. Keep tests simple and focused
3. Use descriptive test names
4. Mock external dependencies
5. Clean up after tests

### PHP Tests
1. Use proper namespacing
2. Extend base TestCase class
3. Use WordPress test factories
4. Test both success and failure cases
5. Use Brain Monkey for mocking

### E2E Tests
1. Use page object pattern for complex pages
2. Wait for elements properly
3. Use data attributes for selectors
4. Test across multiple browsers
5. Keep tests independent

## Troubleshooting

### Common Issues

#### WordPress Test Suite Not Found
```bash
# Reinstall test suite
bash bin/install-wp-tests.sh wordpress_test root root localhost latest
```

#### Playwright Browser Issues
```bash
# Reinstall browsers
npx playwright install --force
```

#### Database Connection Issues
```bash
# Check MySQL service
sudo service mysql status

# Reset test database
mysql -u root -p -e "DROP DATABASE IF EXISTS wordpress_test; CREATE DATABASE wordpress_test;"
```

## Maintenance

### Regular Tasks
1. Update dependencies monthly
2. Review and update test data
3. Monitor test performance
4. Update screenshots when UI changes
5. Review and improve test coverage

### Dependency Updates
```bash
# Update PHP dependencies
composer update

# Update Node.js dependencies
npm update

# Update Playwright
npm install @playwright/test@latest
npx playwright install
```

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Testing Handbook](https://make.wordpress.org/core/handbook/testing/)
- [Playwright Documentation](https://playwright.dev/)
- [Lighthouse CI Documentation](https://github.com/GoogleChrome/lighthouse-ci)
- [Jest Documentation](https://jestjs.io/docs/getting-started)
