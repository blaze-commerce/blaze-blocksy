import { test, expect } from '../../fixtures/test-base';

/**
 * Visual comparison tests for homepage and product page.
 *
 * These tests capture full-page screenshots and compare against baselines.
 * On first run, baselines are created. On subsequent runs, differences are detected.
 *
 * To update baselines: npx playwright test --update-snapshots
 */

/**
 * Wait for page to be fully stable before taking visual snapshot.
 * Uses timeout-based approach to handle sites with persistent connections.
 */
async function waitForVisualStability(page: import('@playwright/test').Page) {
  // Wait for network to be idle (with timeout for sites with persistent connections)
  await Promise.race([
    page.waitForLoadState('networkidle'),
    page.waitForTimeout(15000), // Max 15s wait for network
  ]);

  // Wait for fonts to load
  await page.evaluate(() => document.fonts.ready);

  // Wait for above-the-fold images to load
  await page.evaluate(async () => {
    const viewportHeight = window.innerHeight;
    const images = Array.from(document.querySelectorAll('img')).filter((img) => {
      const rect = img.getBoundingClientRect();
      return rect.top < viewportHeight * 2; // Images in first 2 viewports
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

  // Final stabilization delay
  await page.waitForTimeout(2000);
}

/**
 * Dismiss all popups, modals, and overlays that might interfere with screenshots.
 * Waits for potential delayed popups, then closes them.
 */
async function dismissAllPopups(page: import('@playwright/test').Page) {
  // Wait a bit for delayed popups to appear (newsletter, discount, etc.)
  await page.waitForTimeout(3000);

  // Try clicking common close buttons for popups/modals
  const closeSelectors = [
    // Generic close buttons
    '.popup-close, .modal-close, .close-popup, .close-modal',
    '[class*="popup"] [class*="close"]',
    '[class*="modal"] [class*="close"]',
    // X buttons
    'button[aria-label="Close"], button[aria-label="close"]',
    '.popup .close, .modal .close',
    // Newsletter/signup popups
    '.newsletter-popup .close, .signup-popup .close',
    '.mc-closeModal, .mc-modal-close',
    // Klaviyo popups
    '.klaviyo-close-form, [class*="klaviyo"] [class*="close"]',
    // OptinMonster
    '.om-close, #om-close',
    // Generic overlay close
    '.overlay-close, .lightbox-close',
  ];

  for (const selector of closeSelectors) {
    const closeBtn = page.locator(selector).first();
    if (await closeBtn.isVisible({ timeout: 500 }).catch(() => false)) {
      await closeBtn.click().catch(() => {});
      await page.waitForTimeout(300);
    }
  }

  // Press Escape multiple times to dismiss any remaining popups
  await page.keyboard.press('Escape');
  await page.waitForTimeout(300);
  await page.keyboard.press('Escape');
  await page.waitForTimeout(300);

  // Click outside any modal (on body) to dismiss
  await page.evaluate(() => {
    document.body.click();
  });

  // Hide any remaining popups/modals via CSS
  await page.evaluate(() => {
    const style = document.createElement('style');
    style.id = 'hide-popups';
    style.textContent = `
      /* Hide common popup/modal elements */
      .popup, .modal, [class*="popup"], [class*="modal"],
      .overlay, .lightbox, [class*="overlay"],
      .klaviyo-form, [class*="klaviyo"],
      .om-holder, [id*="optinmonster"],
      .mc-modal, [class*="mailchimp"],
      .newsletter-popup, .signup-popup,
      [role="dialog"], [aria-modal="true"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
      }
    `;
    document.head.appendChild(style);
  });

  await page.waitForTimeout(500);
}

/**
 * Stop all animations, videos, and dynamic content for stable screenshots.
 */
async function stopAllAnimations(page: import('@playwright/test').Page) {
  await page.evaluate(() => {
    // Pause all videos
    document.querySelectorAll('video').forEach((v) => {
      v.pause();
      v.currentTime = 0;
    });

    // Stop all CSS animations and transitions
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

    // Stop common slider/carousel libraries
    // Swiper
    document.querySelectorAll('.swiper').forEach((el: any) => {
      if (el.swiper) {
        el.swiper.autoplay?.stop();
        el.swiper.slideTo?.(0);
      }
    });
    // Slick
    (window as any).jQuery?.('.slick-slider').slick?.('slickPause');
    (window as any).jQuery?.('.slick-slider').slick?.('slickGoTo', 0);
  });

  await page.waitForTimeout(500);
}

test.describe('Visual Comparison', () => {
  // Increase timeout for visual tests (screenshots can be slow)
  test.setTimeout(120000);

  test('homepage should match visual baseline', async ({
    page,
    baseUrl,
    siteName,
    viewportType,
  }) => {
    // Navigate to homepage
    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
    await waitForVisualStability(page);

    // Dismiss all popups and stop animations
    await dismissAllPopups(page);
    await stopAllAnimations(page);

    // Scroll to top to ensure consistent starting position
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(500);

    // Define elements to mask (dynamic content that changes between runs)
    const maskLocators = [
      // Carousels and sliders (common source of instability)
      page.locator('.swiper, .swiper-container, .carousel, .slider, .slick-slider'),
      // Hero banners with animations
      page.locator('.hero-slider, .hero-carousel, [class*="hero-slide"]'),
      // Cart count/badge
      page.locator('.cart-count, .cart-contents .count, .woo-cart-count'),
      // Any live chat widgets
      page.locator('#livechat-compact-container, .crisp-client, .intercom-lightweight-app'),
      // Cookie banners
      page.locator('.cookie-notice, #cookie-notice, .cc-window'),
      // Time-based content
      page.locator('[data-countdown], .countdown-timer'),
    ];

    // Filter to only visible elements to avoid errors
    const visibleMasks = [];
    for (const locator of maskLocators) {
      if (await locator.first().isVisible().catch(() => false)) {
        visibleMasks.push(locator);
      }
    }

    // Take visual comparison screenshot (viewport only for stability)
    await expect(page).toHaveScreenshot('homepage.png', {
      fullPage: false, // Viewport only - full page is unstable due to lazy loading
      mask: visibleMasks,
      animations: 'disabled',
      timeout: 30000,
    });

    console.log(`[${siteName}/${viewportType}] Homepage visual comparison passed`);
  });

  test('product page should match visual baseline', async ({
    page,
    baseUrl,
    siteName,
    viewportType,
    siteConfig,
  }) => {
    // Skip if site doesn't have WooCommerce
    test.skip(!siteConfig.features.hasWooCommerce, 'Site does not have WooCommerce');

    // Skip if no test product configured
    test.skip(!siteConfig.testProducts?.simple, 'No test product URL configured');

    // Navigate to product page
    const productUrl = baseUrl + siteConfig.testProducts!.simple;
    await page.goto(productUrl, { waitUntil: 'domcontentloaded' });
    await waitForVisualStability(page);

    // Handle age gate (if present)
    // Wait for the age gate to exist in the DOM — it may load asynchronously
    const ageGateOverlay = page.locator('#agl_wrapper');
    try {
      await ageGateOverlay.waitFor({ state: 'attached', timeout: 5000 });
      await page.evaluate(() => {
        const btn = document.querySelector('#agl_yes_button') as HTMLElement;
        if (btn) { btn.click(); return; }
        const link = document.querySelector('#agl_wrapper a') as HTMLElement;
        if (link) link.click();
      });
      await page.waitForTimeout(2000);
    } catch {
      // No age gate on this site, continue
    }

    // Always remove the age gate wrapper if still present
    await page.evaluate(() => {
      document.querySelector('#agl_wrapper')?.remove();
    });

    // Dismiss any popups (newsletter, etc.)
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // Dismiss all popups and stop animations
    await dismissAllPopups(page);
    await stopAllAnimations(page);

    // Scroll to top to ensure consistent starting position
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(500);

    // Click first thumbnail in product gallery to ensure consistent image
    const firstThumbnail = page.locator('.woocommerce-product-gallery__image, .flex-control-thumbs li, .product-gallery-thumbnail').first();
    if (await firstThumbnail.isVisible({ timeout: 1000 }).catch(() => false)) {
      await firstThumbnail.click().catch(() => {});
      await page.waitForTimeout(500);
    }

    // Reset any product image slider to first slide
    await page.evaluate(() => {
      // WooCommerce Flexslider
      (window as any).jQuery?.('.woocommerce-product-gallery').flexslider?.(0);
      // Swiper
      document.querySelectorAll('.woocommerce-product-gallery .swiper').forEach((el: any) => {
        if (el.swiper) el.swiper.slideTo?.(0);
      });
    });
    await page.waitForTimeout(500);

    // Define elements to mask
    const maskLocators = [
      // Product gallery images (can show different images due to sliders)
      page.locator('.woocommerce-product-gallery__image img, .woocommerce-product-gallery .swiper-slide img'),
      // Cart count/badge
      page.locator('.cart-count, .cart-contents .count, .woo-cart-count'),
      // Stock status (may change)
      page.locator('.stock, .availability'),
      // Live chat widgets
      page.locator('#livechat-compact-container, .crisp-client, .intercom-lightweight-app'),
      // Cookie banners
      page.locator('.cookie-notice, #cookie-notice, .cc-window'),
      // Price (if it can change dynamically)
      // Uncomment if prices change frequently:
      // page.locator('.price, .woocommerce-Price-amount'),
    ];

    // Filter to only visible elements
    const visibleMasks = [];
    for (const locator of maskLocators) {
      if (await locator.first().isVisible().catch(() => false)) {
        visibleMasks.push(locator);
      }
    }

    // Take visual comparison screenshot (viewport only for stability)
    await expect(page).toHaveScreenshot('product-page.png', {
      fullPage: false, // Viewport only - full page is unstable due to lazy loading
      mask: visibleMasks,
      animations: 'disabled',
      timeout: 30000,
    });

    console.log(`[${siteName}/${viewportType}] Product page visual comparison passed`);
  });

  test('category page should match visual baseline', async ({
    page,
    baseUrl,
    siteName,
    viewportType,
    siteConfig,
  }) => {
    // Skip if site doesn't have WooCommerce
    test.skip(!siteConfig.features.hasWooCommerce, 'Site does not have WooCommerce');

    // Skip if no test category configured
    test.skip(!siteConfig.testCategories?.main, 'No test category URL configured');

    // Navigate to category page
    const categoryUrl = baseUrl + siteConfig.testCategories!.main;
    await page.goto(categoryUrl, { waitUntil: 'domcontentloaded' });
    await waitForVisualStability(page);

    // Handle age gate (if present, e.g., CannaClear)
    // Wait for the age gate to exist in the DOM — it may load asynchronously
    const ageGateOverlay2 = page.locator('#agl_wrapper');
    try {
      await ageGateOverlay2.waitFor({ state: 'attached', timeout: 5000 });
      await page.evaluate(() => {
        const btn = document.querySelector('#agl_yes_button') as HTMLElement;
        if (btn) { btn.click(); return; }
        const link = document.querySelector('#agl_wrapper a') as HTMLElement;
        if (link) link.click();
      });
      await page.waitForTimeout(2000);
    } catch {
      // No age gate on this site, continue
    }

    // Always remove the age gate wrapper if still present
    await page.evaluate(() => {
      document.querySelector('#agl_wrapper')?.remove();
    });

    // Dismiss any popups (newsletter, etc.)
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // Dismiss all popups and stop animations
    await dismissAllPopups(page);
    await stopAllAnimations(page);

    // Scroll to top to ensure consistent starting position
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(500);

    // Define elements to mask (dynamic content that changes between runs)
    const maskLocators = [
      // Carousels and sliders
      page.locator('.swiper, .swiper-container, .carousel, .slider, .slick-slider'),
      // Cart count/badge
      page.locator('.cart-count, .cart-contents .count, .woo-cart-count'),
      // Live chat widgets
      page.locator('#livechat-compact-container, .crisp-client, .intercom-lightweight-app'),
      // Cookie banners
      page.locator('.cookie-notice, #cookie-notice, .cc-window'),
      // Time-based content
      page.locator('[data-countdown], .countdown-timer'),
    ];

    // Filter to only visible elements to avoid errors
    const visibleMasks = [];
    for (const locator of maskLocators) {
      if (await locator.first().isVisible().catch(() => false)) {
        visibleMasks.push(locator);
      }
    }

    // Take visual comparison screenshot (viewport only for stability)
    await expect(page).toHaveScreenshot('category-page.png', {
      fullPage: false, // Viewport only - full page is unstable due to lazy loading
      mask: visibleMasks,
      animations: 'disabled',
      timeout: 30000,
    });

    console.log(`[${siteName}/${viewportType}] Category page visual comparison passed`);
  });
});
