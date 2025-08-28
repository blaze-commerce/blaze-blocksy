/**
 * Playwright Global Teardown
 * 
 * This file runs once after all tests and cleans up the testing environment.
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

const fs = require('fs');
const path = require('path');

async function globalTeardown(config) {
  console.log('🧹 Starting Playwright Global Teardown...');
  
  try {
    // Clean up authentication state
    const authStatePath = 'tests/e2e/auth-state.json';
    if (fs.existsSync(authStatePath)) {
      fs.unlinkSync(authStatePath);
      console.log('🗑️ Cleaned up authentication state');
    }
    
    // Generate test summary
    await generateTestSummary();
    
    // Clean up temporary files if not in CI
    if (!process.env.CI) {
      await cleanupTempFiles();
    }
    
  } catch (error) {
    console.warn('⚠️ Teardown warning:', error.message);
  }
  
  console.log('✅ Playwright Global Teardown Complete');
}

/**
 * Generate a summary of test results
 */
async function generateTestSummary() {
  try {
    const resultsPath = 'coverage/playwright-results.json';
    
    if (fs.existsSync(resultsPath)) {
      const results = JSON.parse(fs.readFileSync(resultsPath, 'utf8'));
      
      const summary = {
        timestamp: new Date().toISOString(),
        total: results.stats?.total || 0,
        passed: results.stats?.passed || 0,
        failed: results.stats?.failed || 0,
        skipped: results.stats?.skipped || 0,
        duration: results.stats?.duration || 0
      };
      
      fs.writeFileSync(
        'coverage/test-summary.json', 
        JSON.stringify(summary, null, 2)
      );
      
      console.log('📊 Test summary generated');
    }
    
  } catch (error) {
    console.warn('⚠️ Could not generate test summary:', error.message);
  }
}

/**
 * Clean up temporary files
 */
async function cleanupTempFiles() {
  try {
    const tempDirs = [
      'tests/e2e/screenshots',
      'tests/e2e/videos',
      'tests/e2e/traces'
    ];
    
    tempDirs.forEach(dir => {
      if (fs.existsSync(dir)) {
        const files = fs.readdirSync(dir);
        
        // Only keep files from failed tests
        files.forEach(file => {
          if (!file.includes('failed') && !file.includes('retry')) {
            const filePath = path.join(dir, file);
            try {
              fs.unlinkSync(filePath);
            } catch (err) {
              // Ignore errors for cleanup
            }
          }
        });
      }
    });
    
    console.log('🗑️ Cleaned up temporary test files');
    
  } catch (error) {
    console.warn('⚠️ Could not clean up temp files:', error.message);
  }
}

module.exports = globalTeardown;
