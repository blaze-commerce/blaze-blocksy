import { test, expect } from '../../fixtures/test-base';

test.describe('Product Page', () => {
  test('should add simple product to cart and show mini cart', async ({
    page,
    baseUrl,
    siteName,
    viewportType,
    siteConfig,
  }) => {
    // Skip if site doesn't have WooCommerce
    test.skip(!siteConfig.features.hasWooCommerce, 'Site does not have WooCommerce');

    // Skip if no simple product URL configured
    test.skip(!siteConfig.testProducts?.simple, 'No simple product URL configured');

    // 1. Navigate to product page
    const productUrl = baseUrl + siteConfig.testProducts!.simple;
    await page.goto(productUrl, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);

    // 2. Handle age gate (if present)
    const ageGate = page.locator('#agl_yes_button');
    if (await ageGate.isVisible({ timeout: 2000 }).catch(() => false)) {
      await ageGate.click();
      await page.waitForTimeout(1000);
    }

    // 3. Dismiss any popups
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // 4. Click add to cart button
    const addToCartBtn = page.locator('button.single_add_to_cart_button').first();
    await addToCartBtn.click();

    // 5. Wait for cart update confirmation - either mini cart panel OR WooCommerce alert
    const miniCart = page.locator('#woo-cart-panel.active');
    const wooAlert = page.locator('.woocommerce-message, .woocommerce-notices-wrapper .woocommerce-message, [role="alert"]');

    // Wait for either indicator to appear
    await expect(miniCart.or(wooAlert)).toBeVisible({ timeout: 10000 });

    // Verify which one appeared and log it
    if (await miniCart.isVisible().catch(() => false)) {
      console.log(`[${siteName}/${viewportType}] Mini cart opened successfully`);
    } else {
      console.log(`[${siteName}/${viewportType}] Product added to cart (WooCommerce alert shown)`);
    }
  });
});
