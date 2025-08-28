/**
 * Basic Playwright Tests
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

const { test, expect } = require('@playwright/test');

test.describe('Basic Playwright Tests', () => {
  
  test('should load example.com successfully @smoke', async ({ page }) => {
    // Navigate to example.com
    await page.goto('https://example.com');
    
    // Check that page loads
    await expect(page).toHaveTitle(/Example Domain/);
    
    // Check for basic HTML elements
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('h1')).toBeVisible();
    
    // Check that the page contains expected text
    await expect(page.locator('h1')).toContainText('Example Domain');
  });

  test('should have proper meta tags @seo', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Check for essential meta tags
    await expect(page.locator('meta[charset]')).toHaveCount(1);
    
    // Check title
    const title = await page.title();
    expect(title).toBeTruthy();
    expect(title.length).toBeGreaterThan(0);
  });

  test('should be responsive @responsive', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Test desktop view
    await page.setViewportSize({ width: 1200, height: 800 });
    await expect(page.locator('body')).toBeVisible();
    
    // Test tablet view
    await page.setViewportSize({ width: 768, height: 1024 });
    await expect(page.locator('body')).toBeVisible();
    
    // Test mobile view
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('body')).toBeVisible();
  });

  test('should take visual regression screenshot @visual', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Take screenshot for visual regression testing
    await expect(page).toHaveScreenshot('example-homepage.png', {
      animations: 'disabled'
    });
  });

  test('should measure basic performance @performance', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Measure basic performance metrics
    const metrics = await page.evaluate(() => {
      const navigation = performance.getEntriesByType('navigation')[0];
      return {
        loadTime: navigation.loadEventEnd - navigation.loadEventStart,
        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
        responseTime: navigation.responseEnd - navigation.requestStart
      };
    });
    
    // Basic performance assertions
    expect(metrics.loadTime).toBeLessThan(5000); // Load time < 5s
    expect(metrics.responseTime).toBeLessThan(3000); // Response time < 3s
  });

});
