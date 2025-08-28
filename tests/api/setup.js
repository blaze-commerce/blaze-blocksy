/**
 * API Testing Setup
 * 
 * Global setup and configuration for WooCommerce REST API tests
 * 
 * @package BlazeCommerce\Tests\API
 */

const axios = require('axios');

// Global test configuration
global.API_CONFIG = {
  baseURL: process.env.API_BASE_URL || 'https://stg-infinitytargetscom-sitebuild.kinsta.cloud',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'User-Agent': 'BlazeCommerce-API-Tests/1.0.0'
  }
};

// WooCommerce API configuration
global.WC_CONFIG = {
  consumer_key: process.env.WC_CONSUMER_KEY || '',
  consumer_secret: process.env.WC_CONSUMER_SECRET || '',
  version: 'wc/v3',
  verify_ssl: false // For staging environments
};

// Test user credentials
global.TEST_CREDENTIALS = {
  email: process.env.TEST_USER_EMAIL || 'hello@blazecommerce.io',
  password: process.env.TEST_USER_PASSWORD || 'nx$9G2AG1zu2x&d4'
};

// Global axios instance for API calls
global.apiClient = axios.create({
  baseURL: global.API_CONFIG.baseURL,
  timeout: global.API_CONFIG.timeout,
  headers: global.API_CONFIG.headers
});

// WooCommerce API client
global.wcClient = axios.create({
  baseURL: `${global.API_CONFIG.baseURL}/wp-json/${global.WC_CONFIG.version}`,
  timeout: global.API_CONFIG.timeout,
  auth: {
    username: global.WC_CONFIG.consumer_key,
    password: global.WC_CONFIG.consumer_secret
  },
  headers: {
    'Content-Type': 'application/json',
    'User-Agent': 'BlazeCommerce-WC-Tests/1.0.0'
  }
});

// Global test utilities
global.testUtils = {
  /**
   * Generate random test data
   */
  generateRandomString: (length = 10) => {
    return Math.random().toString(36).substring(2, length + 2);
  },
  
  /**
   * Generate random email
   */
  generateRandomEmail: () => {
    return `test-${global.testUtils.generateRandomString()}@blazecommerce.io`;
  },
  
  /**
   * Wait for specified time
   */
  wait: (ms) => {
    return new Promise(resolve => setTimeout(resolve, ms));
  },
  
  /**
   * Retry function with exponential backoff
   */
  retry: async (fn, maxRetries = 3, delay = 1000) => {
    for (let i = 0; i < maxRetries; i++) {
      try {
        return await fn();
      } catch (error) {
        if (i === maxRetries - 1) throw error;
        await global.testUtils.wait(delay * Math.pow(2, i));
      }
    }
  },
  
  /**
   * Clean up test data
   */
  cleanup: {
    products: [],
    orders: [],
    customers: [],
    
    addProduct: (id) => {
      global.testUtils.cleanup.products.push(id);
    },
    
    addOrder: (id) => {
      global.testUtils.cleanup.orders.push(id);
    },
    
    addCustomer: (id) => {
      global.testUtils.cleanup.customers.push(id);
    }
  }
};

// Global error handler
global.handleAPIError = (error, context = '') => {
  const errorInfo = {
    context,
    message: error.message,
    status: error.response?.status,
    statusText: error.response?.statusText,
    data: error.response?.data,
    url: error.config?.url,
    method: error.config?.method
  };
  
  console.error('API Test Error:', JSON.stringify(errorInfo, null, 2));
  return errorInfo;
};

// Setup and teardown hooks
beforeAll(async () => {
  console.log('🚀 Starting API Tests...');
  console.log(`Base URL: ${global.API_CONFIG.baseURL}`);
  
  // Verify API connectivity
  try {
    const response = await global.apiClient.get('/wp-json/wp/v2');
    console.log('✅ WordPress API connectivity verified');
  } catch (error) {
    console.warn('⚠️  WordPress API connectivity check failed:', error.message);
  }
  
  // Verify WooCommerce API connectivity
  if (global.WC_CONFIG.consumer_key && global.WC_CONFIG.consumer_secret) {
    try {
      const response = await global.wcClient.get('/system_status');
      console.log('✅ WooCommerce API connectivity verified');
    } catch (error) {
      console.warn('⚠️  WooCommerce API connectivity check failed:', error.message);
    }
  } else {
    console.warn('⚠️  WooCommerce API credentials not provided');
  }
});

afterAll(async () => {
  console.log('🧹 Cleaning up test data...');
  
  // Clean up test products
  for (const productId of global.testUtils.cleanup.products) {
    try {
      await global.wcClient.delete(`/products/${productId}`, { force: true });
      console.log(`✅ Cleaned up product ${productId}`);
    } catch (error) {
      console.warn(`⚠️  Failed to clean up product ${productId}:`, error.message);
    }
  }
  
  // Clean up test orders
  for (const orderId of global.testUtils.cleanup.orders) {
    try {
      await global.wcClient.delete(`/orders/${orderId}`, { force: true });
      console.log(`✅ Cleaned up order ${orderId}`);
    } catch (error) {
      console.warn(`⚠️  Failed to clean up order ${orderId}:`, error.message);
    }
  }
  
  // Clean up test customers
  for (const customerId of global.testUtils.cleanup.customers) {
    try {
      await global.wcClient.delete(`/customers/${customerId}`, { force: true });
      console.log(`✅ Cleaned up customer ${customerId}`);
    } catch (error) {
      console.warn(`⚠️  Failed to clean up customer ${customerId}:`, error.message);
    }
  }
  
  console.log('✅ API Tests completed');
});

// Global test matchers
expect.extend({
  toBeValidWooCommerceResponse(received) {
    const pass = received && 
                 typeof received === 'object' && 
                 received.hasOwnProperty('id');
    
    if (pass) {
      return {
        message: () => `Expected ${received} not to be a valid WooCommerce response`,
        pass: true
      };
    } else {
      return {
        message: () => `Expected ${received} to be a valid WooCommerce response with an id property`,
        pass: false
      };
    }
  },
  
  toHaveValidAPIStructure(received, expectedFields = []) {
    const pass = expectedFields.every(field => received.hasOwnProperty(field));
    
    if (pass) {
      return {
        message: () => `Expected ${received} not to have valid API structure`,
        pass: true
      };
    } else {
      const missingFields = expectedFields.filter(field => !received.hasOwnProperty(field));
      return {
        message: () => `Expected ${received} to have fields: ${missingFields.join(', ')}`,
        pass: false
      };
    }
  }
});
