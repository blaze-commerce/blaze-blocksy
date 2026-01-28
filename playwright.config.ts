import { defineConfig, devices, type Project } from '@playwright/test';
import { siteConfigs, type SiteName } from './e2e/fixtures/sites';

/**
 * Playwright configuration for blaze-blocksy E2E tests.
 *
 * Supports dynamic project generation per site + viewport.
 * Use TEST_ENV to switch between staging/production.
 * Use TEST_SITE to run tests for a specific site or all sites.
 */

const testEnv = (process.env.TEST_ENV || 'production') as 'staging' | 'production';
const testSite = process.env.TEST_SITE || 'all';
const recordVideo = process.env.RECORD_VIDEO === 'true' ? 'on' : 'retain-on-failure';

// Viewport configurations
const viewports = {
  desktop: { width: 1920, height: 1080 },
  mobile: { width: 375, height: 667 }, // iPhone 12
};

// Generate projects dynamically based on sites and viewports
function generateProjects(): Project[] {
  const projects: Project[] = [];
  const siteNames = Object.keys(siteConfigs) as SiteName[];

  // Filter to specific site if TEST_SITE is set
  const sitesToTest = testSite === 'all'
    ? siteNames
    : siteNames.filter(name => name === testSite);

  for (const siteName of sitesToTest) {
    const siteConfig = siteConfigs[siteName];

    // Desktop project
    projects.push({
      name: `${siteName}-desktop`,
      use: {
        browserName: 'chromium',
        viewport: viewports.desktop,
        userAgent: devices['Desktop Chrome'].userAgent,
      },
      metadata: {
        siteName,
        siteConfig,
        viewportType: 'desktop',
        testEnv,
      },
    });

    // Mobile project
    projects.push({
      name: `${siteName}-mobile`,
      use: {
        browserName: 'chromium',
        viewport: viewports.mobile,
        isMobile: true,
        hasTouch: true,
        userAgent: devices['Pixel 5'].userAgent,
      },
      metadata: {
        siteName,
        siteConfig,
        viewportType: 'mobile',
        testEnv,
      },
    });
  }

  return projects;
}

export default defineConfig({
  testDir: './e2e/tests',

  // Run tests in parallel
  fullyParallel: true,

  // Fail the build on CI if you accidentally left test.only in the source code
  forbidOnly: !!process.env.CI,

  // Retry on CI only
  retries: process.env.CI ? 2 : 0,

  // Limit parallel workers on CI
  workers: process.env.CI ? 2 : undefined,

  // Reporter configuration
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['list'],
    ...(process.env.CI ? [['github'] as const] : []),
  ],

  // Shared settings for all projects
  use: {
    // Base URL will be set per-test based on site config
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: recordVideo,
  },

  // Output directory for test artifacts
  outputDir: 'test-results',

  // Timeout for each test
  timeout: 60000,

  // Timeout for expect assertions
  expect: {
    timeout: 10000,
  },

  // Dynamic projects based on sites
  projects: generateProjects(),
});
