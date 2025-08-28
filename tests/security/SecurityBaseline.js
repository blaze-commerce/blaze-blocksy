/**
 * Security Baseline and Vulnerability Scanner
 * 
 * Establishes security baseline metrics and monitors for regressions
 * 
 * @package BlazeCommerce\Tests\Security
 */

const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');

class SecurityBaseline {
  constructor(baseURL) {
    this.baseURL = baseURL;
    this.client = axios.create({
      baseURL,
      timeout: 30000,
      validateStatus: () => true
    });
    this.baseline = null;
    this.baselineFile = path.join(__dirname, 'security-baseline.json');
  }
  
  /**
   * Establish security baseline
   */
  async establishBaseline() {
    console.log('🔒 Establishing security baseline...');
    
    const baseline = {
      timestamp: new Date().toISOString(),
      version: '1.0.0',
      checks: {
        headers: await this.checkSecurityHeaders(),
        endpoints: await this.checkEndpointSecurity(),
        files: await this.checkFileExposure(),
        authentication: await this.checkAuthenticationSecurity(),
        wordpress: await this.checkWordPressSecurity(),
        woocommerce: await this.checkWooCommerceSecurity()
      },
      vulnerabilities: await this.scanVulnerabilities(),
      recommendations: this.generateRecommendations()
    };
    
    await this.saveBaseline(baseline);
    this.baseline = baseline;
    
    console.log('✅ Security baseline established');
    return baseline;
  }
  
  /**
   * Compare current security state against baseline
   */
  async compareToBaseline() {
    if (!this.baseline) {
      await this.loadBaseline();
    }
    
    console.log('🔍 Comparing current security state to baseline...');
    
    const current = {
      timestamp: new Date().toISOString(),
      checks: {
        headers: await this.checkSecurityHeaders(),
        endpoints: await this.checkEndpointSecurity(),
        files: await this.checkFileExposure(),
        authentication: await this.checkAuthenticationSecurity(),
        wordpress: await this.checkWordPressSecurity(),
        woocommerce: await this.checkWooCommerceSecurity()
      },
      vulnerabilities: await this.scanVulnerabilities()
    };
    
    const comparison = this.generateComparison(this.baseline, current);
    
    console.log('📊 Security comparison completed');
    return comparison;
  }
  
  /**
   * Check security headers
   */
  async checkSecurityHeaders() {
    const response = await this.client.get('/');
    const headers = response.headers;
    
    return {
      'content-security-policy': !!headers['content-security-policy'],
      'x-frame-options': !!headers['x-frame-options'],
      'x-content-type-options': !!headers['x-content-type-options'],
      'x-xss-protection': !!headers['x-xss-protection'],
      'strict-transport-security': !!headers['strict-transport-security'],
      'referrer-policy': !!headers['referrer-policy'],
      'permissions-policy': !!headers['permissions-policy'],
      server_disclosure: this.checkServerDisclosure(headers),
      score: this.calculateHeaderScore(headers)
    };
  }
  
  /**
   * Check endpoint security
   */
  async checkEndpointSecurity() {
    const endpoints = [
      { path: '/wp-admin/', expectedStatus: [302, 401, 403] },
      { path: '/wp-json/wp/v2/users', expectedStatus: [401, 403] },
      { path: '/wp-json/wc/v3/products', expectedStatus: [401, 403] },
      { path: '/xmlrpc.php', expectedStatus: [403, 404, 405] }
    ];
    
    const results = {};
    
    for (const endpoint of endpoints) {
      const response = await this.client.get(endpoint.path);
      results[endpoint.path] = {
        status: response.status,
        secure: endpoint.expectedStatus.includes(response.status),
        headers: this.extractSecurityHeaders(response.headers)
      };
    }
    
    return results;
  }
  
  /**
   * Check file exposure
   */
  async checkFileExposure() {
    const sensitiveFiles = [
      '/wp-config.php',
      '/.htaccess',
      '/readme.html',
      '/license.txt',
      '/.env',
      '/composer.json',
      '/package.json',
      '/wp-config-sample.php'
    ];
    
    const results = {};
    
    for (const file of sensitiveFiles) {
      const response = await this.client.get(file);
      results[file] = {
        status: response.status,
        exposed: ![403, 404].includes(response.status),
        size: response.headers['content-length'] || 0
      };
    }
    
    return results;
  }
  
  /**
   * Check authentication security
   */
  async checkAuthenticationSecurity() {
    const results = {
      login_protection: false,
      brute_force_protection: false,
      two_factor_available: false,
      session_security: false
    };
    
    // Test login endpoint
    const loginResponse = await this.client.get('/wp-login.php');
    if (loginResponse.status === 200) {
      const content = loginResponse.data.toLowerCase();
      
      // Check for security features
      results.login_protection = content.includes('captcha') || 
                                content.includes('recaptcha') ||
                                content.includes('security');
      
      results.two_factor_available = content.includes('two-factor') ||
                                   content.includes('2fa') ||
                                   content.includes('authenticator');
    }
    
    // Test brute force protection
    try {
      const attempts = [];
      for (let i = 0; i < 5; i++) {
        attempts.push(this.client.post('/wp-login.php', {
          log: 'testuser',
          pwd: 'wrongpassword'
        }));
      }
      
      const responses = await Promise.allSettled(attempts);
      const blocked = responses.some(r => 
        r.status === 'fulfilled' && [429, 403].includes(r.value.status)
      );
      
      results.brute_force_protection = blocked;
    } catch (error) {
      results.brute_force_protection = true; // Likely blocked
    }
    
    return results;
  }
  
  /**
   * Check WordPress specific security
   */
  async checkWordPressSecurity() {
    const response = await this.client.get('/');
    const content = response.data.toLowerCase();
    
    return {
      version_hidden: !content.includes('wordpress') || 
                     !content.match(/wordpress \d+\.\d+/),
      generator_removed: !content.includes('generator'),
      file_editing_disabled: true, // Would need admin access to verify
      debug_disabled: !content.includes('wp_debug'),
      xmlrpc_disabled: await this.checkXMLRPCDisabled(),
      directory_browsing_disabled: await this.checkDirectoryBrowsing()
    };
  }
  
  /**
   * Check WooCommerce specific security
   */
  async checkWooCommerceSecurity() {
    const results = {
      api_authentication_required: false,
      checkout_ssl: false,
      customer_data_protected: false
    };
    
    // Check API authentication
    const apiResponse = await this.client.get('/wp-json/wc/v3/products');
    results.api_authentication_required = [401, 403].includes(apiResponse.status);
    
    // Check checkout SSL
    const checkoutResponse = await this.client.get('/checkout/');
    if (checkoutResponse.status === 200) {
      results.checkout_ssl = checkoutResponse.data.includes('https://') ||
                           checkoutResponse.request.protocol === 'https:';
    }
    
    return results;
  }
  
  /**
   * Scan for common vulnerabilities
   */
  async scanVulnerabilities() {
    const vulnerabilities = [];
    
    // Check for common WordPress vulnerabilities
    const wpVulns = await this.checkWordPressVulnerabilities();
    vulnerabilities.push(...wpVulns);
    
    // Check for plugin vulnerabilities
    const pluginVulns = await this.checkPluginVulnerabilities();
    vulnerabilities.push(...pluginVulns);
    
    // Check for theme vulnerabilities
    const themeVulns = await this.checkThemeVulnerabilities();
    vulnerabilities.push(...themeVulns);
    
    return vulnerabilities;
  }
  
  /**
   * Generate security recommendations
   */
  generateRecommendations() {
    return [
      {
        category: 'Headers',
        priority: 'High',
        recommendation: 'Implement Content Security Policy (CSP) headers',
        impact: 'Prevents XSS attacks and code injection'
      },
      {
        category: 'Authentication',
        priority: 'High', 
        recommendation: 'Enable two-factor authentication for admin users',
        impact: 'Significantly reduces account compromise risk'
      },
      {
        category: 'WordPress',
        priority: 'Medium',
        recommendation: 'Hide WordPress version information',
        impact: 'Reduces information disclosure to attackers'
      },
      {
        category: 'SSL/TLS',
        priority: 'High',
        recommendation: 'Enforce HTTPS for all pages, especially checkout',
        impact: 'Protects data in transit from interception'
      },
      {
        category: 'File Permissions',
        priority: 'Medium',
        recommendation: 'Restrict access to sensitive configuration files',
        impact: 'Prevents unauthorized access to credentials'
      }
    ];
  }
  
  /**
   * Helper methods
   */
  checkServerDisclosure(headers) {
    const server = headers['server'] || '';
    const xPoweredBy = headers['x-powered-by'] || '';
    
    return {
      server_version_exposed: /\d+\.\d+/.test(server),
      php_version_exposed: /php\/\d+\.\d+/i.test(xPoweredBy),
      technology_stack_exposed: !!(server || xPoweredBy)
    };
  }
  
  calculateHeaderScore(headers) {
    const securityHeaders = [
      'content-security-policy',
      'x-frame-options', 
      'x-content-type-options',
      'x-xss-protection',
      'strict-transport-security',
      'referrer-policy'
    ];
    
    const present = securityHeaders.filter(header => headers[header]).length;
    return Math.round((present / securityHeaders.length) * 100);
  }
  
  extractSecurityHeaders(headers) {
    const securityHeaders = {};
    const relevantHeaders = [
      'content-security-policy',
      'x-frame-options',
      'x-content-type-options', 
      'x-xss-protection',
      'strict-transport-security'
    ];
    
    relevantHeaders.forEach(header => {
      if (headers[header]) {
        securityHeaders[header] = headers[header];
      }
    });
    
    return securityHeaders;
  }
  
  async checkXMLRPCDisabled() {
    const response = await this.client.post('/xmlrpc.php', 
      '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>',
      { headers: { 'Content-Type': 'text/xml' } }
    );
    
    return [403, 404, 405].includes(response.status);
  }
  
  async checkDirectoryBrowsing() {
    const directories = ['/wp-content/', '/wp-includes/'];
    
    for (const dir of directories) {
      const response = await this.client.get(dir);
      if (response.status === 200 && 
          response.data.toLowerCase().includes('index of')) {
        return false;
      }
    }
    
    return true;
  }
  
  async checkWordPressVulnerabilities() {
    // This would integrate with vulnerability databases
    // For now, return placeholder
    return [];
  }
  
  async checkPluginVulnerabilities() {
    // This would check installed plugins against vulnerability databases
    return [];
  }
  
  async checkThemeVulnerabilities() {
    // This would check active theme against vulnerability databases
    return [];
  }
  
  generateComparison(baseline, current) {
    const comparison = {
      timestamp: new Date().toISOString(),
      status: 'improved', // 'improved', 'degraded', 'unchanged'
      changes: [],
      new_vulnerabilities: [],
      resolved_vulnerabilities: [],
      score_change: 0
    };
    
    // Compare security scores
    const baselineScore = this.calculateOverallScore(baseline);
    const currentScore = this.calculateOverallScore(current);
    comparison.score_change = currentScore - baselineScore;
    
    if (comparison.score_change > 0) {
      comparison.status = 'improved';
    } else if (comparison.score_change < 0) {
      comparison.status = 'degraded';
    } else {
      comparison.status = 'unchanged';
    }
    
    return comparison;
  }
  
  calculateOverallScore(data) {
    // Calculate weighted security score
    const headerScore = data.checks.headers.score || 0;
    const endpointScore = this.calculateEndpointScore(data.checks.endpoints);
    const fileScore = this.calculateFileScore(data.checks.files);
    
    return Math.round((headerScore * 0.4 + endpointScore * 0.3 + fileScore * 0.3));
  }
  
  calculateEndpointScore(endpoints) {
    const total = Object.keys(endpoints).length;
    const secure = Object.values(endpoints).filter(e => e.secure).length;
    return total > 0 ? Math.round((secure / total) * 100) : 0;
  }
  
  calculateFileScore(files) {
    const total = Object.keys(files).length;
    const secure = Object.values(files).filter(f => !f.exposed).length;
    return total > 0 ? Math.round((secure / total) * 100) : 0;
  }
  
  async saveBaseline(baseline) {
    await fs.writeFile(this.baselineFile, JSON.stringify(baseline, null, 2));
  }
  
  async loadBaseline() {
    try {
      const data = await fs.readFile(this.baselineFile, 'utf8');
      this.baseline = JSON.parse(data);
    } catch (error) {
      console.warn('No existing baseline found, establishing new baseline...');
      await this.establishBaseline();
    }
  }
}

module.exports = SecurityBaseline;
