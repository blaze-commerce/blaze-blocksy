import { test, expect } from '../../fixtures/test-base';

/**
 * Checkout page tests with visual comparison.
 *
 * Tests the checkout flow and captures visual baselines for mobile/desktop.
 */

/**
 * Wait for page to be fully stable before taking visual snapshot.
 */
async function waitForVisualStability(page: import('@playwright/test').Page) {
  await Promise.race([
    page.waitForLoadState('networkidle'),
    page.waitForTimeout(15000),
  ]);

  await page.evaluate(() => document.fonts.ready);

  await page.evaluate(async () => {
    const viewportHeight = window.innerHeight;
    const images = Array.from(document.querySelectorAll('img')).filter((img) => {
      const rect = img.getBoundingClientRect();
      return rect.top < viewportHeight * 2;
    });

    await Promise.all(
      images.map((img) => {
        if (img.complete && img.naturalWidth > 0) return Promise.resolve();
        return new Promise((resolve) => {
          img.addEventListener('load', resolve, { once: true });
          img.addEventListener('error', resolve, { once: true });
          setTimeout(resolve, 5000);
        });
      })
    );
  });

  await page.waitForTimeout(2000);
}

/**
 * Stop all animations for stable screenshots.
 */
async function stopAllAnimations(page: import('@playwright/test').Page) {
  await page.evaluate(() => {
    document.querySelectorAll('video').forEach((v) => {
      v.pause();
      v.currentTime = 0;
    });

    const style = document.createElement('style');
    style.id = 'stop-animations';
    style.textContent = `
      *, *::before, *::after {
        animation: none !important;
        animation-delay: 0s !important;
        animation-duration: 0s !important;
        transition: none !important;
        transition-delay: 0s !important;
        transition-duration: 0s !important;
      }
    `;
    document.head.appendChild(style);
  });

  await page.waitForTimeout(500);
}

test.describe('Checkout', () => {
  test.setTimeout(120000);

  test('should navigate to checkout and match visual baseline', async ({
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

    // 3. Dismiss any popups on product page
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // 4. Click add to cart and wait for AJAX request to complete
    const addToCartBtn = page.locator('button.single_add_to_cart_button').first();
    await expect(addToCartBtn).toBeVisible({ timeout: 10000 });

    // Wait for the add-to-cart AJAX request to complete
    await Promise.all([
      page.waitForResponse(
        (resp) =>
          (resp.url().includes('?wc-ajax=add_to_cart') ||
            resp.url().includes('admin-ajax.php') ||
            resp.url().includes('?add-to-cart=')) &&
          resp.status() === 200,
        { timeout: 15000 }
      ).catch(() => null),
      addToCartBtn.click(),
    ]);

    // Wait for add-to-cart confirmation (different sites have different behaviors)
    // Option 1: Mini cart panel opens
    // Option 2: WooCommerce message/notice appears
    // Option 3: "View cart" link appears on button
    const addToCartConfirmation = page.locator('#woo-cart-panel.active, .woocommerce-message, .added_to_cart.wc-forward').first();

    await expect(addToCartConfirmation).toBeVisible({ timeout: 10000 });

    console.log(`[${siteName}/${viewportType}] Product added to cart`);

    // Close mini cart panel if it's open
    const miniCartPanel = page.locator('#woo-cart-panel.active');
    if (await miniCartPanel.isVisible().catch(() => false)) {
      await page.keyboard.press('Escape');
      await page.waitForTimeout(500);
    }

    // 5. Navigate directly to checkout
    await page.goto(baseUrl + '/checkout/', { waitUntil: 'domcontentloaded' });

    // 6. Wait for checkout page to be stable
    await waitForVisualStability(page);

    // Verify we're on checkout (not redirected away)
    // Check for checkout page indicators that work across different checkout types
    const checkoutPageUrl = page.url();
    expect(checkoutPageUrl).toContain('checkout');

    // Wait for any checkout content to be visible
    const checkoutContent = page.locator('.woocommerce-checkout, .fc-content, .fc-step, #customer_details, .checkout-content').first();
    await expect(checkoutContent).toBeVisible({ timeout: 10000 });

    // Extra check: make sure cart is not empty on checkout
    const emptyCartMessage = page.locator('.cart-empty, .wc-empty-cart-message, .woocommerce-info:has-text("cart is currently empty")');
    const isCartEmpty = await emptyCartMessage.isVisible({ timeout: 2000 }).catch(() => false);
    if (isCartEmpty) {
      throw new Error('Cart is empty on checkout page - add to cart may have failed');
    }

    console.log(`[${siteName}/${viewportType}] Checkout page loaded`);

    // 7. Stop animations for stable screenshot
    await stopAllAnimations(page);

    // 8. Scroll to top
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(500);

    // 9. Define elements to mask (dynamic content)
    const maskLocators = [
      // Cart item images (may vary)
      page.locator('.cart_item img, .product-thumbnail img'),
      // Cart totals (prices change)
      page.locator('.cart-subtotal, .order-total, .woocommerce-Price-amount'),
      // Product names in cart (may be long/truncated differently)
      page.locator('.product-name a, .cart_item .product-name'),
      // Cart count in header
      page.locator('.cart-count, .cart-contents .count, .woo-cart-count'),
      // Live chat widgets
      page.locator('#livechat-compact-container, .crisp-client, .intercom-lightweight-app'),
      // Cookie banners
      page.locator('.cookie-notice, #cookie-notice, .cc-window'),
      // Shipping options (may change based on location)
      page.locator('.shipping-methods, #shipping_method'),
    ];

    // Filter to only visible elements
    const visibleMasks = [];
    for (const locator of maskLocators) {
      if (await locator.first().isVisible().catch(() => false)) {
        visibleMasks.push(locator);
      }
    }

    // 10. Take visual comparison screenshot
    await expect(page).toHaveScreenshot('checkout-page.png', {
      fullPage: false, // Viewport only for stability
      mask: visibleMasks,
      animations: 'disabled',
      timeout: 30000,
    });

    console.log(`[${siteName}/${viewportType}] Checkout page visual comparison passed`);
  });
});
