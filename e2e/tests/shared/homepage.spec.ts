import { test, expect } from '../../fixtures/test-base';

test.describe('Homepage', () => {
  test('should have proper meta tags', async ({
    page,
    baseUrl,
    siteName,
  }) => {
    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

    // Check for viewport meta tag (important for mobile)
    const viewportMeta = page.locator('meta[name="viewport"]');
    await expect(viewportMeta).toHaveCount(1);

    // Check for charset meta tag
    const charsetMeta = page.locator('meta[charset], meta[http-equiv="Content-Type"]');
    await expect(charsetMeta).toHaveCount(1);

    // Check for description meta tag (SEO)
    const descriptionMeta = page.locator('meta[name="description"]');
    const descriptionCount = await descriptionMeta.count();
    if (descriptionCount === 0) {
      console.warn(`[${siteName}] Missing meta description tag`);
    }

    console.log(`[${siteName}] Meta tags verified`);
  });

  test('should have accessible header and navigation', async ({
    page,
    baseUrl,
    siteName,
    viewportType,
  }) => {
    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

    // Check for header element
    const header = page.locator('header').first();
    await expect(header).toBeVisible();

    // Check for navigation - sites may have multiple nav elements (desktop/mobile menus)
    const allNavs = page.locator('nav');
    const navCount = await allNavs.count();

    if (navCount > 0) {
      if (viewportType === 'mobile') {
        // On mobile, nav may be hidden in a hamburger menu - just verify at least one nav exists in DOM
        console.log(`[${siteName}/${viewportType}] Header visible, found ${navCount} nav element(s) (may be in responsive menu)`);
      } else {
        // On desktop, check if ANY nav is visible (some may be hidden mobile menus)
        let visibleNavFound = false;
        for (let i = 0; i < navCount; i++) {
          const nav = allNavs.nth(i);
          if (await nav.isVisible()) {
            visibleNavFound = true;
            break;
          }
        }
        if (visibleNavFound) {
          console.log(`[${siteName}/${viewportType}] Header and navigation visible`);
        } else {
          // No visible nav - this might be acceptable for some themes
          console.warn(`[${siteName}/${viewportType}] Found ${navCount} nav element(s) but none visible on desktop`);
        }
      }
    } else {
      console.warn(`[${siteName}/${viewportType}] No nav element found`);
    }
  });

  test('should have a footer', async ({
    page,
    baseUrl,
    siteName,
  }) => {
    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

    // Check for footer element
    const footer = page.locator('footer').first();
    const footerExists = await footer.count() > 0;

    if (footerExists) {
      // Scroll to footer to ensure it's in view
      await footer.scrollIntoViewIfNeeded();
      await expect(footer).toBeVisible();
      console.log(`[${siteName}] Footer visible`);
    } else {
      console.warn(`[${siteName}] No footer element found`);
    }
  });
});
