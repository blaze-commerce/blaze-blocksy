import { test as base, expect, type Page } from '@playwright/test';
import { siteConfigs, getSiteUrl, type SiteName, type SiteConfig } from './sites';
import * as path from 'path';
import * as fs from 'fs';

/**
 * Extended test fixtures for blaze-blocksy E2E tests.
 */

export interface TestFixtures {
  /** Current site name being tested */
  siteName: SiteName;
  /** Configuration for the current site */
  siteConfig: SiteConfig;
  /** Current viewport type ('desktop' or 'mobile') */
  viewportType: 'desktop' | 'mobile';
  /** Current test environment */
  testEnv: 'staging' | 'production';
  /** Base URL for the current site */
  baseUrl: string;
  /** Helper to take screenshots with proper naming */
  takeScreenshot: (name: string) => Promise<void>;
  /** Helper to wait for page to be fully loaded */
  waitForPageLoad: () => Promise<void>;
}

export const test = base.extend<TestFixtures>({
  siteName: async ({ }, use, testInfo) => {
    const metadata = testInfo.project.metadata as { siteName: SiteName };
    await use(metadata.siteName);
  },

  siteConfig: async ({ siteName }, use) => {
    await use(siteConfigs[siteName]);
  },

  viewportType: async ({ }, use, testInfo) => {
    const metadata = testInfo.project.metadata as { viewportType: 'desktop' | 'mobile' };
    await use(metadata.viewportType);
  },

  testEnv: async ({ }, use, testInfo) => {
    const metadata = testInfo.project.metadata as { testEnv: 'staging' | 'production' };
    await use(metadata.testEnv);
  },

  baseUrl: async ({ siteName, testEnv }, use) => {
    const url = getSiteUrl(siteName, testEnv);
    await use(url);
  },

  takeScreenshot: async ({ page, siteName, viewportType }, use) => {
    const screenshotHelper = async (name: string) => {
      // Create the directory path: e2e/screenshots/{siteName}/{viewportType}/
      const screenshotDir = path.join(
        process.cwd(),
        'e2e',
        'screenshots',
        siteName,
        viewportType
      );

      // Ensure directory exists
      if (!fs.existsSync(screenshotDir)) {
        fs.mkdirSync(screenshotDir, { recursive: true });
      }

      const screenshotPath = path.join(screenshotDir, `${name}.png`);

      await page.screenshot({
        path: screenshotPath,
        fullPage: true,
      });
    };

    await use(screenshotHelper);
  },

  waitForPageLoad: async ({ page }, use) => {
    const waitHelper = async () => {
      // Wait for network to be idle
      await page.waitForLoadState('networkidle');

      // Additional wait for lazy-loaded images
      await page.waitForTimeout(2000);

      // Wait for images with a timeout per image (handles lazy-loaded off-screen images)
      await page.evaluate(async () => {
        const images = Array.from(document.querySelectorAll('img'));
        await Promise.all(
          images.map((img) => {
            if (img.complete) return Promise.resolve();
            return Promise.race([
              new Promise((resolve) => {
                img.addEventListener('load', resolve, { once: true });
                img.addEventListener('error', resolve, { once: true });
              }),
              // Timeout after 5s per image
              new Promise((resolve) => setTimeout(resolve, 5000)),
            ]);
          })
        );
      });
    };

    await use(waitHelper);
  },
});

export { expect };

/**
 * Collect console errors during page load.
 */
export function collectConsoleErrors(page: Page): string[] {
  const errors: string[] = [];

  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      errors.push(msg.text());
    }
  });

  page.on('pageerror', (error) => {
    errors.push(error.message);
  });

  return errors;
}

/**
 * Filter out known/acceptable console errors.
 */
export function filterKnownErrors(errors: string[]): string[] {
  const knownPatterns = [
    // Third-party analytics/tracking
    /google.*analytics/i,
    /gtag/i,
    /facebook.*pixel/i,
    /hotjar/i,
    /clarity/i,
    // Common browser extensions
    /chrome-extension/i,
    // Font loading (non-critical)
    /font.*failed/i,
    // CORS issues with external resources (non-critical for testing)
    /blocked by CORS/i,
  ];

  return errors.filter(
    (error) => !knownPatterns.some((pattern) => pattern.test(error))
  );
}
