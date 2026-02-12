/**
 * Site configuration for E2E tests.
 *
 * Each site has URLs for staging and production environments,
 * plus feature flags and optional selector overrides.
 */

export interface SiteConfig {
  /** Production URL */
  production: string;
  /** Staging URL (optional, defaults to production) */
  staging?: string;
  /** Feature flags */
  features: {
    hasWooCommerce: boolean;
    hasFluidCheckout: boolean;
    hasContactForm: boolean;
    hasSearch: boolean;
  };
  /** Optional selector overrides for site-specific elements */
  selectors?: {
    header?: string;
    footer?: string;
    mainContent?: string;
    navigation?: string;
  };
  /** Test product URLs for E2E testing */
  testProducts?: {
    /** URL path for simple product */
    simple?: string;
    /** URL path for variable product (future) */
    variable?: string;
  };
  /** Test category URLs for E2E testing */
  testCategories?: {
    /** URL path for main category page */
    main?: string;
  };
}

export const siteConfigs = {
  cannaclear: {
    production: 'https://cannaclear.com',
    staging: 'https://canna-optimize.kn.blz.au',
    features: {
      hasWooCommerce: true,
      hasFluidCheckout: true,
      hasContactForm: true,
      hasSearch: true,
    },
    testProducts: {
      simple: '/product/delta-9-thc-gummies-blue-razzberry/',
    },
    testCategories: {
      main: '/product-category/cbn/',
    },
  },
  birdcontrol: {
    production: 'https://birdcontrolaustralia.com.au',
    staging: 'https://birdcontrol.blz.onl',
    features: {
      hasWooCommerce: true,
      hasFluidCheckout: true,
      hasContactForm: true,
      hasSearch: true,
    },
    testProducts: {
      simple: '/shop/bird-spikes/thorny-devil-polycarbonate-spikes-5-meter-box/',
    },
    testCategories: {
      main: '/product-category/agricultural/',
    },
  },
  hanglogic: {
    production: 'https://hanglogic.com.au',
    staging: 'https://hang.kn.blz.au',
    features: {
      hasWooCommerce: true,
      hasFluidCheckout: true,
      hasContactForm: true,
      hasSearch: true,
    },
    testProducts: {
      simple: '/product/back-frame-hanging-system-floating-mount-kit/',
    },
    testCategories: {
      main: '/product-category/picture-rails/',
    },
  },
  dancewear: {
    production: 'https://dancewear.co.uk',
    staging: 'https://dancewearoptimize.blz.onl',
    features: {
      hasWooCommerce: true,
      hasFluidCheckout: true,
      hasContactForm: true,
      hasSearch: true,
    },
    testProducts: {
      simple: '/product/flared-dance-jazz-yoga-pants/',
    },
    testCategories: {
      main: '/product-category/women/dance-pants/',
    },
  },
} as const satisfies Record<string, SiteConfig>;

export type SiteName = keyof typeof siteConfigs;

/**
 * Get the URL for a site based on the test environment.
 */
export function getSiteUrl(
  siteName: SiteName,
  env: 'staging' | 'production' = 'production',
): string {
  const config = siteConfigs[siteName];
  return env === 'staging' && config.staging
    ? config.staging
    : config.production;
}

/**
 * Get all site names.
 */
export function getAllSiteNames(): SiteName[] {
  return Object.keys(siteConfigs) as SiteName[];
}
