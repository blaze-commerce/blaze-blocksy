/**
 * K6 Load Testing Script
 * 
 * Advanced performance testing for WordPress/WooCommerce applications
 * 
 * @package BlazeCommerce\Tests\Performance
 */

import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Rate, Trend, Counter } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const pageLoadTime = new Trend('page_load_time');
const apiResponseTime = new Trend('api_response_time');
const checkoutSteps = new Counter('checkout_steps_completed');

// Test configuration
export const options = {
  stages: [
    // Warm-up
    { duration: '1m', target: 5 },
    
    // Ramp-up to normal load
    { duration: '2m', target: 20 },
    
    // Stay at normal load
    { duration: '5m', target: 20 },
    
    // Ramp-up to high load
    { duration: '2m', target: 50 },
    
    // Stay at high load
    { duration: '3m', target: 50 },
    
    // Ramp-up to peak load
    { duration: '1m', target: 100 },
    
    // Stay at peak load
    { duration: '2m', target: 100 },
    
    // Ramp-down
    { duration: '2m', target: 0 },
  ],
  
  // Performance thresholds
  thresholds: {
    // HTTP request duration should be < 2s for 95% of requests
    http_req_duration: ['p(95)<2000'],
    
    // HTTP request duration should be < 5s for 99% of requests
    'http_req_duration{name:Homepage}': ['p(99)<3000'],
    'http_req_duration{name:ProductPage}': ['p(99)<4000'],
    'http_req_duration{name:CheckoutPage}': ['p(99)<5000'],
    
    // Error rate should be < 5%
    errors: ['rate<0.05'],
    
    // 95% of requests should return 200
    http_req_failed: ['rate<0.05'],
    
    // Custom metrics thresholds
    page_load_time: ['p(95)<3000'],
    api_response_time: ['p(95)<1000'],
  },
  
  // Resource limits
  noConnectionReuse: false,
  userAgent: 'K6-LoadTest/1.0 (BlazeCommerce Performance Testing)',
};

// Base URL configuration
const BASE_URL = __ENV.BASE_URL || 'https://your-wordpress-site.com';

// Test data
const PRODUCTS = [1, 2, 3, 4, 5];
const SEARCH_TERMS = ['target', 'shooting', 'practice', 'training', 'range'];

// Helper functions
function randomChoice(array) {
  return array[Math.floor(Math.random() * array.length)];
}

function measurePageLoad(response, pageName) {
  const loadTime = response.timings.duration;
  pageLoadTime.add(loadTime, { page: pageName });
  
  return check(response, {
    [`${pageName} status is 200`]: (r) => r.status === 200,
    [`${pageName} loads in < 3s`]: (r) => r.timings.duration < 3000,
    [`${pageName} has content`]: (r) => r.body.length > 1000,
  });
}

function measureApiResponse(response, apiName) {
  const responseTime = response.timings.duration;
  apiResponseTime.add(responseTime, { api: apiName });
  
  return check(response, {
    [`${apiName} status is 200 or 401`]: (r) => [200, 401].includes(r.status),
    [`${apiName} responds in < 2s`]: (r) => r.timings.duration < 2000,
    [`${apiName} returns JSON`]: (r) => r.headers['Content-Type'] && r.headers['Content-Type'].includes('application/json'),
  });
}

// Main test function
export default function () {
  // Simulate different user behaviors
  const userBehavior = Math.random();
  
  if (userBehavior < 0.4) {
    // 40% - Browse products
    browseProducts();
  } else if (userBehavior < 0.7) {
    // 30% - Complete checkout process
    checkoutProcess();
  } else if (userBehavior < 0.9) {
    // 20% - API interactions
    apiInteractions();
  } else {
    // 10% - Admin/WordPress interactions
    wordpressInteractions();
  }
}

function browseProducts() {
  group('Product Browsing Journey', function () {
    // Visit homepage
    let response = http.get(`${BASE_URL}/`, {
      tags: { name: 'Homepage' },
    });
    
    const homepageSuccess = measurePageLoad(response, 'Homepage');
    errorRate.add(!homepageSuccess);
    
    sleep(Math.random() * 3 + 1); // 1-4 seconds thinking time
    
    // Visit shop page
    response = http.get(`${BASE_URL}/shop/`, {
      tags: { name: 'ShopPage' },
    });
    
    const shopSuccess = measurePageLoad(response, 'Shop Page');
    errorRate.add(!shopSuccess);
    
    sleep(Math.random() * 2 + 1); // 1-3 seconds
    
    // Search for products
    const searchTerm = randomChoice(SEARCH_TERMS);
    response = http.get(`${BASE_URL}/shop/?s=${searchTerm}`, {
      tags: { name: 'ProductSearch' },
    });
    
    const searchSuccess = check(response, {
      'Search results status is 200': (r) => r.status === 200,
      'Search results load quickly': (r) => r.timings.duration < 2000,
    });
    errorRate.add(!searchSuccess);
    
    sleep(Math.random() * 2 + 1);
    
    // View product details
    const productId = randomChoice(PRODUCTS);
    response = http.get(`${BASE_URL}/product/${productId}/`, {
      tags: { name: 'ProductPage' },
    });
    
    const productSuccess = check(response, {
      'Product page loads': (r) => [200, 404].includes(r.status), // 404 OK for non-existent products
      'Product page performance': (r) => r.timings.duration < 4000,
    });
    errorRate.add(!productSuccess && response.status !== 404);
  });
}

function checkoutProcess() {
  group('Checkout Process', function () {
    // Add product to cart
    const productId = randomChoice(PRODUCTS);
    let response = http.post(`${BASE_URL}/?wc-ajax=add_to_cart`, {
      product_id: productId,
      quantity: 1,
    }, {
      tags: { name: 'AddToCart' },
    });
    
    const addToCartSuccess = check(response, {
      'Add to cart succeeds': (r) => r.status === 200,
      'Add to cart is fast': (r) => r.timings.duration < 2000,
    });
    
    if (addToCartSuccess) {
      checkoutSteps.add(1, { step: 'add_to_cart' });
    }
    errorRate.add(!addToCartSuccess);
    
    sleep(Math.random() * 2 + 1);
    
    // View cart
    response = http.get(`${BASE_URL}/cart/`, {
      tags: { name: 'CartPage' },
    });
    
    const cartSuccess = measurePageLoad(response, 'Cart Page');
    if (cartSuccess) {
      checkoutSteps.add(1, { step: 'view_cart' });
    }
    errorRate.add(!cartSuccess);
    
    sleep(Math.random() * 3 + 2); // More thinking time before checkout
    
    // Proceed to checkout
    response = http.get(`${BASE_URL}/checkout/`, {
      tags: { name: 'CheckoutPage' },
    });
    
    const checkoutSuccess = measurePageLoad(response, 'Checkout Page');
    if (checkoutSuccess) {
      checkoutSteps.add(1, { step: 'view_checkout' });
    }
    errorRate.add(!checkoutSuccess);
  });
}

function apiInteractions() {
  group('API Performance', function () {
    const headers = {};
    
    // Add authentication if available
    if (__ENV.WC_CONSUMER_KEY && __ENV.WC_CONSUMER_SECRET) {
      const auth = `${__ENV.WC_CONSUMER_KEY}:${__ENV.WC_CONSUMER_SECRET}`;
      headers['Authorization'] = `Basic ${btoa(auth)}`;
    }
    
    // Test WooCommerce REST API
    let response = http.get(`${BASE_URL}/wp-json/wc/v3/products?per_page=10`, {
      headers: headers,
      tags: { name: 'ProductsAPI' },
    });
    
    const productsApiSuccess = measureApiResponse(response, 'Products API');
    errorRate.add(!productsApiSuccess);
    
    sleep(0.5);
    
    // Test categories API
    response = http.get(`${BASE_URL}/wp-json/wc/v3/products/categories?per_page=10`, {
      headers: headers,
      tags: { name: 'CategoriesAPI' },
    });
    
    const categoriesApiSuccess = measureApiResponse(response, 'Categories API');
    errorRate.add(!categoriesApiSuccess);
    
    sleep(0.5);
    
    // Test WordPress REST API
    response = http.get(`${BASE_URL}/wp-json/wp/v2/posts?per_page=5`, {
      tags: { name: 'PostsAPI' },
    });
    
    const postsApiSuccess = check(response, {
      'Posts API status is 200': (r) => r.status === 200,
      'Posts API is fast': (r) => r.timings.duration < 1500,
    });
    errorRate.add(!postsApiSuccess);
  });
}

function wordpressInteractions() {
  group('WordPress Core', function () {
    // Test admin redirect
    let response = http.get(`${BASE_URL}/wp-admin/`, {
      tags: { name: 'AdminRedirect' },
      redirects: 0, // Don't follow redirects
    });
    
    const adminSuccess = check(response, {
      'Admin redirects properly': (r) => [200, 302, 401, 403].includes(r.status),
      'Admin response is fast': (r) => r.timings.duration < 2000,
    });
    errorRate.add(!adminSuccess);
    
    sleep(1);
    
    // Test login page
    response = http.get(`${BASE_URL}/wp-login.php`, {
      tags: { name: 'LoginPage' },
    });
    
    const loginSuccess = check(response, {
      'Login page loads': (r) => r.status === 200,
      'Login page is fast': (r) => r.timings.duration < 2000,
      'Login page has form': (r) => r.body.includes('wp-submit'),
    });
    errorRate.add(!loginSuccess);
    
    sleep(1);
    
    // Test RSS feed
    response = http.get(`${BASE_URL}/feed/`, {
      tags: { name: 'RSSFeed' },
    });
    
    const rssSuccess = check(response, {
      'RSS feed works': (r) => r.status === 200,
      'RSS is XML': (r) => r.headers['Content-Type'] && r.headers['Content-Type'].includes('xml'),
    });
    errorRate.add(!rssSuccess);
  });
}

// Setup function (runs once per VU)
export function setup() {
  console.log(`Starting load test against: ${BASE_URL}`);
  console.log('Test configuration:');
  console.log('- Stages: Warm-up → Ramp-up → Normal → High → Peak → Ramp-down');
  console.log('- Max VUs: 100');
  console.log('- Duration: ~18 minutes');
  console.log('- Scenarios: Product browsing, Checkout, API, WordPress core');
  
  // Verify target is accessible
  const response = http.get(BASE_URL);
  if (response.status !== 200) {
    console.error(`Target ${BASE_URL} is not accessible (status: ${response.status})`);
  }
  
  return { baseUrl: BASE_URL };
}

// Teardown function (runs once after all VUs finish)
export function teardown(data) {
  console.log('Load test completed');
  console.log('Check the results for performance metrics and threshold violations');
}
