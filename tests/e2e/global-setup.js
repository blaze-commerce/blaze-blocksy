/**
 * Playwright Global Setup
 * 
 * This file runs once before all tests and sets up the testing environment.
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

const { chromium } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

async function globalSetup(config) {
  console.log('🚀 Starting Playwright Global Setup...');
  
  // Create necessary directories
  const dirs = [
    'coverage',
    'tests/e2e/screenshots',
    'tests/e2e/videos',
    'tests/e2e/traces'
  ];
  
  dirs.forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
      console.log(`📁 Created directory: ${dir}`);
    }
  });
  
  // Set up authentication state if needed
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();
  
  try {
    // Navigate to login page and authenticate if needed
    const baseURL = process.env.PLAYWRIGHT_BASE_URL || config.use.baseURL;
    
    if (baseURL) {
      console.log(`🌐 Testing connection to: ${baseURL}`);
      
      // Test if the site is accessible
      const response = await page.goto(baseURL, { 
        waitUntil: 'networkidle',
        timeout: 30000 
      });
      
      if (response && response.ok()) {
        console.log('✅ Site is accessible');
        
        // Save authentication state if login is successful
        await setupAuthentication(page, baseURL);
        
      } else {
        console.warn('⚠️ Site may not be accessible, tests may fail');
      }
    }
    
  } catch (error) {
    console.warn('⚠️ Global setup warning:', error.message);
  } finally {
    await context.close();
    await browser.close();
  }
  
  console.log('✅ Playwright Global Setup Complete');
}

/**
 * Set up authentication for WordPress admin
 */
async function setupAuthentication(page, baseURL) {
  try {
    // Check if we need to authenticate
    const adminURL = `${baseURL}/wp-admin/`;
    await page.goto(adminURL);
    
    // If we're redirected to login page, authenticate
    if (page.url().includes('wp-login.php')) {
      console.log('🔐 Setting up WordPress authentication...');
      
      // Use environment variables for credentials
      const username = process.env.WP_TEST_USER || 'admin';
      const password = process.env.WP_TEST_PASSWORD || 'password';
      
      await page.fill('#user_login', username);
      await page.fill('#user_pass', password);
      await page.click('#wp-submit');
      
      // Wait for successful login
      await page.waitForURL('**/wp-admin/**', { timeout: 10000 });
      
      // Save authentication state
      await page.context().storageState({ 
        path: 'tests/e2e/auth-state.json' 
      });
      
      console.log('✅ WordPress authentication saved');
    }
    
  } catch (error) {
    console.warn('⚠️ Authentication setup failed:', error.message);
  }
}

module.exports = globalSetup;
