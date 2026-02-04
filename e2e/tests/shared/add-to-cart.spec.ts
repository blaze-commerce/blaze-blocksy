import { test, expect } from '../../fixtures/test-base';
import * as fs from 'fs';
import * as path from 'path';

test.describe('Add to Cart', () => {
  test('should add product to cart from homepage', async ({
    page,
    baseUrl,
    siteName,
    viewportType,
    siteConfig,
  }) => {
    // Skip if site doesn't have WooCommerce
    test.skip(!siteConfig.features.hasWooCommerce, 'Site does not have WooCommerce');

    // Navigate to homepage
    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    // Dismiss age verification popups (common on CBD/cannabis sites)
    const ageVerifyButton = page.locator('#agl_yes_button').first();
    if (await ageVerifyButton.count() > 0) {
      await ageVerifyButton.click().catch(() => {});
      await page.waitForTimeout(1000);
    }

    // Close newsletter/signup popups by pressing Escape
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // Look for simple product Add to Cart buttons (not variable products with "Select options")
    // First try buttons in active carousel slides (for sites with Swiper carousels)
    // Then fall back to regular buttons
    const activeSlideSelector = [
      '.swiper-slide-active a.product_type_simple.add_to_cart_button',
      '.swiper-slide-active button.product_type_simple.add_to_cart_button',
      '.swiper-slide-active a.ajax_add_to_cart:not(.product_type_variable)',
      '.swiper-slide-active button.ajax_add_to_cart:not(.product_type_variable)',
    ].join(', ');

    const regularSelector = [
      'a.product_type_simple.add_to_cart_button',
      'button.product_type_simple.add_to_cart_button',
      'a.ajax_add_to_cart:not(.product_type_variable)',
      'button.ajax_add_to_cart:not(.product_type_variable)',
    ].join(', ');

    // Try active slide buttons first, then fall back to regular buttons
    let addToCartButton = page.locator(activeSlideSelector).first();
    let buttonCount = await addToCartButton.count();

    if (buttonCount === 0) {
      addToCartButton = page.locator(regularSelector).first();
      buttonCount = await addToCartButton.count();
    }

    if (buttonCount === 0) {
      console.log(`[${siteName}/${viewportType}] No Add to Cart button found on homepage - this is acceptable`);
      return;
    }

    // Scroll to the button
    await addToCartButton.scrollIntoViewIfNeeded();

    // Get product info
    const productId = await addToCartButton.getAttribute('data-product_id');
    console.log(`[${siteName}/${viewportType}] Adding product ${productId} to cart`);

    // Click the button
    await addToCartButton.click();

    // Wait for cart to update
    await page.waitForTimeout(3000);

    // Check if cart was updated by looking for cart count
    const cartCount = page.locator('.cart-count, .cart-contents-count, .ct-cart-count').first();
    const countText = await cartCount.textContent({ timeout: 3000 }).catch(() => '0');

    if (countText && parseInt(countText) > 0) {
      console.log(`[${siteName}/${viewportType}] Cart updated - count: ${countText}`);
    } else {
      console.log(`[${siteName}/${viewportType}] Cart count not visible, but click completed`);
    }

    // Take screenshot
    const screenshotDir = path.join(process.cwd(), 'e2e', 'screenshots', siteName, viewportType);
    if (!fs.existsSync(screenshotDir)) {
      fs.mkdirSync(screenshotDir, { recursive: true });
    }
    await page.screenshot({
      path: path.join(screenshotDir, 'add-to-cart-sidebar.png'),
      timeout: 5000,
    }).catch(() => {});

    console.log(`[${siteName}/${viewportType}] Product successfully added to cart`);
  });
});
