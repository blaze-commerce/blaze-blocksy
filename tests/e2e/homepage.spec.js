/**
 * Homepage End-to-End Tests
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

const { test, expect } = require('@playwright/test');

test.describe('Homepage Tests', () => {
  
  test.beforeEach(async ({ page }) => {
    // Navigate to a test site for verification
    await page.goto('https://example.com');
  });

  test('should load homepage successfully @smoke', async ({ page }) => {
    // Check that page loads
    await expect(page).toHaveTitle(/.*/, { timeout: 10000 });
    
    // Check for basic WordPress elements
    await expect(page.locator('body')).toBeVisible();
    
    // Check for theme-specific elements
    const header = page.locator('header, .header, #header');
    await expect(header).toBeVisible();
  });

  test('should have proper meta tags @seo', async ({ page }) => {
    // Check for essential meta tags
    await expect(page.locator('meta[charset]')).toHaveCount(1);
    await expect(page.locator('meta[name="viewport"]')).toHaveCount(1);
    
    // Check for SEO meta tags
    const title = await page.title();
    expect(title).toBeTruthy();
    expect(title.length).toBeGreaterThan(0);
    expect(title.length).toBeLessThan(60); // SEO best practice
  });

  test('should be accessible @accessibility', async ({ page }) => {
    // Check for accessibility landmarks
    await expect(page.locator('main, [role="main"]')).toBeVisible();
    
    // Check for skip links
    const skipLink = page.locator('a[href="#main"], a[href="#content"]').first();
    if (await skipLink.count() > 0) {
      await expect(skipLink).toHaveAttribute('href');
    }
    
    // Check for proper heading hierarchy
    const h1 = page.locator('h1');
    await expect(h1).toHaveCount(1); // Should have exactly one H1
  });

  test('should have working navigation @navigation', async ({ page }) => {
    // Check for navigation menu
    const nav = page.locator('nav, .nav, .navigation, .menu');
    await expect(nav.first()).toBeVisible();
    
    // Check for menu items
    const menuItems = page.locator('nav a, .nav a, .navigation a, .menu a');
    const count = await menuItems.count();
    expect(count).toBeGreaterThan(0);
    
    // Test first menu item if it exists
    if (count > 0) {
      const firstMenuItem = menuItems.first();
      await expect(firstMenuItem).toBeVisible();
      await expect(firstMenuItem).toHaveAttribute('href');
    }
  });

  test('should load CSS and JavaScript @performance', async ({ page }) => {
    // Check that stylesheets are loaded
    const stylesheets = page.locator('link[rel="stylesheet"]');
    const stylesheetCount = await stylesheets.count();
    expect(stylesheetCount).toBeGreaterThan(0);
    
    // Check for theme stylesheet
    const themeStylesheet = page.locator('link[href*="style.css"]');
    await expect(themeStylesheet).toHaveCount(1);
    
    // Check that JavaScript is loaded (if any)
    const scripts = page.locator('script[src]');
    const scriptCount = await scripts.count();
    
    if (scriptCount > 0) {
      // Verify scripts load without errors
      const errors = [];
      page.on('pageerror', error => errors.push(error));
      
      await page.reload();
      expect(errors).toHaveLength(0);
    }
  });

  test('should be responsive @responsive', async ({ page }) => {
    // Test desktop view
    await page.setViewportSize({ width: 1200, height: 800 });
    await expect(page.locator('body')).toBeVisible();
    
    // Test tablet view
    await page.setViewportSize({ width: 768, height: 1024 });
    await expect(page.locator('body')).toBeVisible();
    
    // Test mobile view
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page.locator('body')).toBeVisible();
    
    // Check for mobile menu if it exists
    const mobileMenu = page.locator('.mobile-menu, .hamburger, .menu-toggle');
    if (await mobileMenu.count() > 0) {
      await expect(mobileMenu.first()).toBeVisible();
    }
  });

  test('should have proper footer @layout', async ({ page }) => {
    // Check for footer
    const footer = page.locator('footer, .footer, #footer');
    await expect(footer).toBeVisible();
    
    // Check for copyright or site info
    const copyright = page.locator('footer *:has-text("©"), footer *:has-text("Copyright")');
    if (await copyright.count() > 0) {
      await expect(copyright.first()).toBeVisible();
    }
  });

  test('should pass Core Web Vitals @performance', async ({ page }) => {
    // Navigate and wait for load
    await page.goto('/', { waitUntil: 'networkidle' });
    
    // Measure performance metrics
    const metrics = await page.evaluate(() => {
      return new Promise((resolve) => {
        new PerformanceObserver((list) => {
          const entries = list.getEntries();
          const vitals = {};
          
          entries.forEach((entry) => {
            if (entry.name === 'first-contentful-paint') {
              vitals.fcp = entry.startTime;
            }
            if (entry.entryType === 'largest-contentful-paint') {
              vitals.lcp = entry.startTime;
            }
            if (entry.entryType === 'layout-shift' && !entry.hadRecentInput) {
              vitals.cls = (vitals.cls || 0) + entry.value;
            }
          });
          
          resolve(vitals);
        }).observe({ entryTypes: ['paint', 'largest-contentful-paint', 'layout-shift'] });
        
        // Fallback timeout
        setTimeout(() => resolve({}), 5000);
      });
    });
    
    // Assert Core Web Vitals thresholds
    if (metrics.fcp) {
      expect(metrics.fcp).toBeLessThan(1800); // FCP < 1.8s
    }
    
    if (metrics.lcp) {
      expect(metrics.lcp).toBeLessThan(2500); // LCP < 2.5s
    }
    
    if (metrics.cls !== undefined) {
      expect(metrics.cls).toBeLessThan(0.1); // CLS < 0.1
    }
  });

  test('should take visual regression screenshot @visual', async ({ page }) => {
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Take full page screenshot for visual regression testing
    await expect(page).toHaveScreenshot('homepage-full.png', {
      fullPage: true,
      animations: 'disabled'
    });
    
    // Take viewport screenshot
    await expect(page).toHaveScreenshot('homepage-viewport.png', {
      animations: 'disabled'
    });
  });

});
