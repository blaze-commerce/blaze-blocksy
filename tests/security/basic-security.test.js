/**
 * Basic Security Tests
 * 
 * Essential security tests for WordPress/WooCommerce applications
 * 
 * @package BlazeCommerce\Tests\Security
 */

const axios = require('axios');

describe('Basic Security Tests', () => {
  const baseURL = process.env.API_BASE_URL || 'https://stg-infinitytargetscom-sitebuild.kinsta.cloud';
  let client;
  
  beforeAll(() => {
    client = axios.create({
      baseURL,
      timeout: 30000,
      validateStatus: () => true // Don't throw on HTTP errors
    });
  });

  describe('Essential Security Checks', () => {
    test('should not expose sensitive configuration files', async () => {
      const sensitiveFiles = [
        '/wp-config.php',
        '/.htaccess',
        '/readme.html',
        '/.env'
      ];
      
      for (const file of sensitiveFiles) {
        const response = await client.get(file);
        
        // Should not expose configuration files
        expect([403, 404]).toContain(response.status);
      }
    });
    
    test('should require authentication for admin areas', async () => {
      const adminEndpoints = [
        '/wp-admin/',
        '/wp-json/wp/v2/users'
      ];
      
      for (const endpoint of adminEndpoints) {
        const response = await client.get(endpoint);
        
        // Should require authentication
        expect([302, 401, 403]).toContain(response.status);
      }
    });
    
    test('should prevent basic SQL injection attempts', async () => {
      const sqlPayload = "'; DROP TABLE wp_posts; --";
      
      const response = await client.get('/wp-json/wp/v2/posts', {
        params: { search: sqlPayload }
      });
      
      // Should return normal response, not execute SQL
      expect(response.status).toBe(200);
      expect(Array.isArray(response.data)).toBe(true);
      
      // Response should not contain SQL error messages
      const responseText = JSON.stringify(response.data).toLowerCase();
      expect(responseText).not.toContain('mysql');
      expect(responseText).not.toContain('sql syntax');
    });
    
    test('should implement basic security headers', async () => {
      const response = await client.get('/');
      const headers = response.headers;
      
      // Check for at least some security headers
      const hasSecurityHeaders = !!(
        headers['x-frame-options'] ||
        headers['x-content-type-options'] ||
        headers['x-xss-protection'] ||
        headers['content-security-policy']
      );
      
      expect(hasSecurityHeaders).toBe(true);
    });
    
    test('should secure WooCommerce API endpoints', async () => {
      const wcEndpoints = [
        '/wp-json/wc/v3/products',
        '/wp-json/wc/v3/orders'
      ];
      
      for (const endpoint of wcEndpoints) {
        const response = await client.get(endpoint);
        
        // Should require authentication
        expect([401, 403]).toContain(response.status);
      }
    });
  });

  describe('Information Disclosure Prevention', () => {
    test('should not expose WordPress version in HTML', async () => {
      const response = await client.get('/');
      
      if (response.status === 200) {
        const content = response.data.toLowerCase();
        
        // Should not expose WordPress version
        expect(content).not.toMatch(/wordpress \d+\.\d+/);
        expect(content).not.toMatch(/wp-includes\/js\/.*\?ver=\d+\.\d+/);
      }
    });
    
    test('should not expose server version details', async () => {
      const response = await client.get('/');
      const headers = response.headers;
      
      // Should not expose detailed server version
      if (headers['server']) {
        expect(headers['server']).not.toMatch(/\d+\.\d+\.\d+/);
      }
      
      if (headers['x-powered-by']) {
        expect(headers['x-powered-by']).not.toMatch(/php\/\d+\.\d+/i);
      }
    });
  });

  describe('Basic Input Validation', () => {
    test('should handle malformed requests gracefully', async () => {
      const malformedRequests = [
        { url: '/wp-json/wp/v2/posts', params: { per_page: 'invalid' } },
        { url: '/wp-json/wp/v2/posts', params: { page: -1 } },
        { url: '/wp-json/wp/v2/posts', params: { orderby: '<script>' } }
      ];
      
      for (const request of malformedRequests) {
        const response = await client.get(request.url, { params: request.params });
        
        // Should handle gracefully, not crash
        expect([200, 400, 422]).toContain(response.status);
        
        if (response.status === 200) {
          expect(Array.isArray(response.data)).toBe(true);
        }
      }
    });
  });
});
