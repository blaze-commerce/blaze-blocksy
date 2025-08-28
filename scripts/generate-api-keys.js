#!/usr/bin/env node
/**
 * WooCommerce API Key Generator
 * 
 * Helps generate and test WooCommerce API credentials
 * 
 * @package BlazeCommerce\Scripts
 */

const axios = require('axios');
const crypto = require('crypto');

class WooCommerceAPIKeyGenerator {
  constructor(baseURL, adminCredentials) {
    this.baseURL = baseURL;
    this.adminCredentials = adminCredentials;
    this.client = axios.create({
      baseURL,
      timeout: 30000,
      withCredentials: true
    });
  }
  
  /**
   * Generate API key pair
   */
  generateKeyPair() {
    const consumerKey = 'ck_' + crypto.randomBytes(32).toString('hex');
    const consumerSecret = 'cs_' + crypto.randomBytes(32).toString('hex');
    
    return {
      consumer_key: consumerKey,
      consumer_secret: consumerSecret
    };
  }
  
  /**
   * Test API credentials
   */
  async testCredentials(consumerKey, consumerSecret) {
    try {
      console.log('🔍 Testing API credentials...');
      
      const response = await axios.get(`${this.baseURL}/wp-json/wc/v3/system_status`, {
        auth: {
          username: consumerKey,
          password: consumerSecret
        },
        timeout: 10000
      });
      
      if (response.status === 200) {
        console.log('✅ API credentials are valid!');
        console.log(`📊 WooCommerce Version: ${response.data.environment?.version || 'Unknown'}`);
        return true;
      }
      
    } catch (error) {
      console.log('❌ API credentials test failed:');
      console.log(`   Status: ${error.response?.status || 'Network Error'}`);
      console.log(`   Message: ${error.response?.data?.message || error.message}`);
      return false;
    }
  }
  
  /**
   * Test various API endpoints
   */
  async testEndpoints(consumerKey, consumerSecret) {
    const endpoints = [
      { name: 'System Status', path: '/system_status' },
      { name: 'Products', path: '/products?per_page=1' },
      { name: 'Orders', path: '/orders?per_page=1' },
      { name: 'Customers', path: '/customers?per_page=1' }
    ];
    
    console.log('\n🧪 Testing API endpoints...');
    
    for (const endpoint of endpoints) {
      try {
        const response = await axios.get(`${this.baseURL}/wp-json/wc/v3${endpoint.path}`, {
          auth: {
            username: consumerKey,
            password: consumerSecret
          },
          timeout: 10000
        });
        
        console.log(`✅ ${endpoint.name}: ${response.status} (${Array.isArray(response.data) ? response.data.length : 1} items)`);
        
      } catch (error) {
        console.log(`❌ ${endpoint.name}: ${error.response?.status || 'Error'} - ${error.response?.data?.message || error.message}`);
      }
    }
  }
  
  /**
   * Generate .env configuration
   */
  generateEnvConfig(consumerKey, consumerSecret) {
    return `
# WooCommerce API Credentials (Generated: ${new Date().toISOString()})
WC_CONSUMER_KEY=${consumerKey}
WC_CONSUMER_SECRET=${consumerSecret}
API_BASE_URL=${this.baseURL}
TEST_USER_EMAIL=hello@blazecommerce.io
TEST_USER_PASSWORD=nx$9G2AG1zu2x&d4
`;
  }
}

// Main execution
async function main() {
  const baseURL = process.env.API_BASE_URL || 'https://stg-infinitytargetscom-sitebuild.kinsta.cloud';
  
  console.log('🔑 WooCommerce API Key Generator');
  console.log(`🌐 Target URL: ${baseURL}`);
  console.log('');
  
  const generator = new WooCommerceAPIKeyGenerator(baseURL);
  
  // Check if credentials are provided via environment
  const existingKey = process.env.WC_CONSUMER_KEY;
  const existingSecret = process.env.WC_CONSUMER_SECRET;
  
  if (existingKey && existingSecret) {
    console.log('🔍 Found existing credentials in environment variables');
    console.log(`   Consumer Key: ${existingKey.substring(0, 10)}...`);
    console.log(`   Consumer Secret: ${existingSecret.substring(0, 10)}...`);
    
    const isValid = await generator.testCredentials(existingKey, existingSecret);
    
    if (isValid) {
      await generator.testEndpoints(existingKey, existingSecret);
      console.log('\n✅ Existing credentials are working!');
      console.log('🚀 You can now run: npm run test:api:rest');
    } else {
      console.log('\n❌ Existing credentials are not working');
      console.log('📋 Please follow the setup guide in tests/api/API_CREDENTIALS_SETUP.md');
    }
    
  } else {
    console.log('❌ No API credentials found in environment variables');
    console.log('');
    console.log('📋 To set up API credentials:');
    console.log('1. Follow the guide: tests/api/API_CREDENTIALS_SETUP.md');
    console.log('2. Generate API keys in WooCommerce admin');
    console.log('3. Add credentials to .env file');
    console.log('4. Run this script again to test');
    console.log('');
    
    // Generate sample credentials for reference
    const sampleKeys = generator.generateKeyPair();
    console.log('📝 Sample .env configuration:');
    console.log(generator.generateEnvConfig(sampleKeys.consumer_key, sampleKeys.consumer_secret));
  }
}

// Run the script
if (require.main === module) {
  main().catch(error => {
    console.error('💥 Script failed:', error.message);
    process.exit(1);
  });
}

module.exports = WooCommerceAPIKeyGenerator;
