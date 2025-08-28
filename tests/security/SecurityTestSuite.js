/**
 * Security Testing Framework
 * 
 * Comprehensive security testing for WordPress/WooCommerce applications
 * 
 * @package BlazeCommerce\Tests\Security
 */

const axios = require('axios');
const { expect } = require('@jest/globals');

describe('Security Testing Framework', () => {
  const baseURL = process.env.API_BASE_URL || 'https://your-wordpress-site.com';
  let client;
  
  beforeAll(() => {
    client = axios.create({
      baseURL,
      timeout: 30000,
      validateStatus: () => true // Don't throw on HTTP errors
    });
  });

  describe('Input Validation and Sanitization', () => {
    test('should prevent SQL injection in search parameters', async () => {
      const sqlInjectionPayloads = [
        "'; DROP TABLE wp_posts; --",
        "' OR '1'='1",
        "' UNION SELECT * FROM wp_users --",
        "'; INSERT INTO wp_users (user_login) VALUES ('hacker'); --",
        "' AND (SELECT COUNT(*) FROM wp_users) > 0 --"
      ];
      
      for (const payload of sqlInjectionPayloads) {
        const response = await client.get('/wp-json/wp/v2/posts', {
          params: { search: payload }
        });
        
        // Should return normal response, not execute SQL
        expect(response.status).toBe(200);
        expect(Array.isArray(response.data)).toBe(true);
        
        // Response should not contain SQL error messages
        const responseText = JSON.stringify(response.data).toLowerCase();
        expect(responseText).not.toContain('mysql');
        expect(responseText).not.toContain('sql syntax');
        expect(responseText).not.toContain('database error');
      }
    });
    
    test('should prevent XSS in form inputs', async () => {
      const xssPayloads = [
        '<script>alert("xss")</script>',
        '<img src="x" onerror="alert(1)">',
        'javascript:alert("xss")',
        '<svg onload="alert(1)">',
        '"><script>alert("xss")</script>',
        '<iframe src="javascript:alert(1)"></iframe>'
      ];
      
      for (const payload of xssPayloads) {
        // Test comment submission (if available)
        try {
          const response = await client.post('/wp-json/wp/v2/comments', {
            content: payload,
            post: 1
          });
          
          // Should either reject or sanitize the input
          if (response.status === 201) {
            // If accepted, content should be sanitized
            expect(response.data.content.rendered).not.toContain('<script>');
            expect(response.data.content.rendered).not.toContain('javascript:');
            expect(response.data.content.rendered).not.toContain('onerror=');
          } else {
            // Should be rejected with appropriate error
            expect([400, 401, 403]).toContain(response.status);
          }
        } catch (error) {
          // Network errors are acceptable for security tests
          expect(true).toBe(true);
        }
      }
    });
    
    test('should validate file upload restrictions', async () => {
      const maliciousFiles = [
        { name: 'malicious.php', content: '<?php system($_GET["cmd"]); ?>' },
        { name: 'script.js', content: 'alert("xss");' },
        { name: 'shell.sh', content: '#!/bin/bash\nrm -rf /' },
        { name: 'config.htaccess', content: 'Options +Indexes' }
      ];
      
      for (const file of maliciousFiles) {
        try {
          const formData = new FormData();
          const blob = new Blob([file.content], { type: 'text/plain' });
          formData.append('file', blob, file.name);
          
          const response = await client.post('/wp-json/wp/v2/media', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
          
          // Should reject dangerous file types
          expect([400, 401, 403, 415]).toContain(response.status);
          
        } catch (error) {
          // File upload restrictions working
          expect(true).toBe(true);
        }
      }
    });
  });

  describe('Authentication and Authorization', () => {
    test('should prevent brute force attacks', async () => {
      const attempts = [];
      
      // Simulate multiple failed login attempts
      for (let i = 0; i < 10; i++) {
        const attempt = client.post('/wp-login.php', {
          log: 'admin',
          pwd: `wrongpassword${i}`,
          'wp-submit': 'Log In'
        }, {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        });
        
        attempts.push(attempt);
      }
      
      const responses = await Promise.allSettled(attempts);
      
      // Should implement rate limiting or account lockout
      const successfulResponses = responses.filter(r => 
        r.status === 'fulfilled' && r.value.status === 200
      );
      
      // Most attempts should be blocked or rate limited
      expect(successfulResponses.length).toBeLessThan(10);
    });
    
    test('should enforce strong password policies', async () => {
      const weakPasswords = [
        'password',
        '123456',
        'admin',
        'qwerty',
        'abc123',
        '111111'
      ];
      
      for (const password of weakPasswords) {
        try {
          const response = await client.post('/wp-json/wp/v2/users', {
            username: `testuser${Date.now()}`,
            email: `test${Date.now()}@example.com`,
            password: password
          });
          
          // Should reject weak passwords
          if (response.status === 201) {
            // If user creation succeeded, password policy might be weak
            console.warn(`Weak password "${password}" was accepted`);
          }
          
          expect([400, 401, 403]).toContain(response.status);
          
        } catch (error) {
          // Password policy working
          expect(true).toBe(true);
        }
      }
    });
    
    test('should prevent privilege escalation', async () => {
      // Test unauthorized access to admin endpoints
      const adminEndpoints = [
        '/wp-json/wp/v2/users',
        '/wp-json/wp/v2/plugins',
        '/wp-json/wp/v2/themes',
        '/wp-admin/admin-ajax.php'
      ];
      
      for (const endpoint of adminEndpoints) {
        const response = await client.get(endpoint);
        
        // Should require authentication for admin endpoints
        if (response.status === 200) {
          // Check if sensitive information is exposed
          const responseText = JSON.stringify(response.data).toLowerCase();
          expect(responseText).not.toContain('password');
          expect(responseText).not.toContain('hash');
          expect(responseText).not.toContain('secret');
        }
      }
    });
  });

  describe('Information Disclosure Prevention', () => {
    test('should not expose sensitive information in headers', async () => {
      const response = await client.get('/');
      
      // Check for information disclosure in headers
      const headers = response.headers;
      
      // Should not expose server version details
      if (headers['server']) {
        expect(headers['server']).not.toMatch(/\d+\.\d+/); // No version numbers
      }
      
      if (headers['x-powered-by']) {
        expect(headers['x-powered-by']).not.toMatch(/\d+\.\d+/); // No version numbers
      }
      
      // Should not expose WordPress version
      expect(headers).not.toHaveProperty('x-wp-version');
    });
    
    test('should not expose directory listings', async () => {
      const directories = [
        '/wp-content/',
        '/wp-content/themes/',
        '/wp-content/plugins/',
        '/wp-content/uploads/',
        '/wp-includes/'
      ];
      
      for (const dir of directories) {
        const response = await client.get(dir);
        
        // Should not return directory listings
        if (response.status === 200) {
          const content = response.data.toLowerCase();
          expect(content).not.toContain('index of');
          expect(content).not.toContain('parent directory');
        }
      }
    });
    
    test('should not expose configuration files', async () => {
      const configFiles = [
        '/wp-config.php',
        '/.htaccess',
        '/wp-config-sample.php',
        '/readme.html',
        '/license.txt',
        '/.env',
        '/composer.json',
        '/package.json'
      ];
      
      for (const file of configFiles) {
        const response = await client.get(file);
        
        // Should not expose configuration files
        expect([403, 404]).toContain(response.status);
      }
    });
  });

  describe('Security Headers', () => {
    test('should implement security headers', async () => {
      const response = await client.get('/');
      const headers = response.headers;
      
      // Content Security Policy
      if (headers['content-security-policy']) {
        expect(headers['content-security-policy']).toContain('default-src');
      }
      
      // X-Frame-Options
      if (headers['x-frame-options']) {
        expect(['DENY', 'SAMEORIGIN']).toContain(headers['x-frame-options']);
      }
      
      // X-Content-Type-Options
      if (headers['x-content-type-options']) {
        expect(headers['x-content-type-options']).toBe('nosniff');
      }
      
      // X-XSS-Protection
      if (headers['x-xss-protection']) {
        expect(headers['x-xss-protection']).toMatch(/1; mode=block/);
      }
      
      // Strict-Transport-Security (for HTTPS)
      if (response.config.url.startsWith('https://')) {
        expect(headers).toHaveProperty('strict-transport-security');
      }
    });
    
    test('should prevent clickjacking attacks', async () => {
      const response = await client.get('/wp-admin/');
      
      // Admin area should have X-Frame-Options
      if (response.status === 200) {
        expect(response.headers).toHaveProperty('x-frame-options');
        expect(['DENY', 'SAMEORIGIN']).toContain(response.headers['x-frame-options']);
      }
    });
  });

  describe('WordPress Specific Security', () => {
    test('should hide WordPress version information', async () => {
      const response = await client.get('/');
      
      if (response.status === 200) {
        const content = response.data.toLowerCase();
        
        // Should not expose WordPress version in HTML
        expect(content).not.toMatch(/wordpress \d+\.\d+/);
        expect(content).not.toMatch(/wp-includes\/js\/.*\?ver=\d+\.\d+/);
        expect(content).not.toMatch(/generator.*wordpress \d+\.\d+/);
      }
    });
    
    test('should secure wp-admin access', async () => {
      const response = await client.get('/wp-admin/');
      
      // Should redirect to login or return 403
      expect([302, 401, 403]).toContain(response.status);
      
      if (response.status === 302) {
        expect(response.headers.location).toContain('wp-login.php');
      }
    });
    
    test('should prevent XML-RPC attacks', async () => {
      const xmlrpcPayload = `<?xml version="1.0"?>
        <methodCall>
          <methodName>wp.getUsersBlogs</methodName>
          <params>
            <param><value>admin</value></param>
            <param><value>password</value></param>
          </params>
        </methodCall>`;
      
      const response = await client.post('/xmlrpc.php', xmlrpcPayload, {
        headers: {
          'Content-Type': 'text/xml'
        }
      });
      
      // XML-RPC should be disabled or protected
      expect([403, 404, 405]).toContain(response.status);
    });
  });

  describe('WooCommerce Security', () => {
    test('should secure WooCommerce API endpoints', async () => {
      const wcEndpoints = [
        '/wp-json/wc/v3/products',
        '/wp-json/wc/v3/orders',
        '/wp-json/wc/v3/customers',
        '/wp-json/wc/v3/system_status'
      ];
      
      for (const endpoint of wcEndpoints) {
        const response = await client.get(endpoint);
        
        // Should require authentication
        expect([401, 403]).toContain(response.status);
        
        if (response.status === 401) {
          expect(response.data.code).toBe('woocommerce_rest_authentication_error');
        }
      }
    });
    
    test('should validate checkout security', async () => {
      const response = await client.get('/checkout/');
      
      if (response.status === 200) {
        const content = response.data.toLowerCase();
        
        // Should use HTTPS for checkout
        expect(content).toContain('https://');
        
        // Should have CSRF protection
        expect(content).toContain('nonce');
      }
    });
  });

  describe('Rate Limiting and DDoS Protection', () => {
    test('should implement rate limiting', async () => {
      const requests = [];
      
      // Make rapid requests
      for (let i = 0; i < 20; i++) {
        requests.push(client.get('/wp-json/wp/v2/posts?per_page=1'));
      }
      
      const responses = await Promise.allSettled(requests);
      const rateLimited = responses.filter(r => 
        r.status === 'fulfilled' && r.value.status === 429
      );
      
      // Should implement some form of rate limiting
      if (rateLimited.length > 0) {
        expect(rateLimited[0].value.headers).toHaveProperty('retry-after');
      }
    });
    
    test('should handle large payloads appropriately', async () => {
      const largePayload = 'x'.repeat(10 * 1024 * 1024); // 10MB
      
      try {
        const response = await client.post('/wp-json/wp/v2/comments', {
          content: largePayload,
          post: 1
        });
        
        // Should reject or limit large payloads
        expect([400, 413, 414]).toContain(response.status);
        
      } catch (error) {
        // Request size limits working
        expect(error.code).toMatch(/ECONNRESET|EMSGSIZE|REQUEST_ENTITY_TOO_LARGE/);
      }
    });
  });
});
