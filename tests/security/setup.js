/**
 * Security Testing Setup
 * 
 * Global setup and configuration for security tests
 * 
 * @package BlazeCommerce\Tests\Security
 */

const SecurityBaseline = require('./SecurityBaseline');

// Global security testing configuration
global.SECURITY_CONFIG = {
  baseURL: process.env.API_BASE_URL || 'https://stg-infinitytargetscom-sitebuild.kinsta.cloud',
  timeout: 30000,
  enableVulnerabilityScan: process.env.ENABLE_VULNERABILITY_SCAN === 'true',
  maxRetries: 3,
  retryDelay: 1000
};

// Security baseline instance
global.securityBaseline = new SecurityBaseline(global.SECURITY_CONFIG.baseURL);

// Security test utilities
global.securityUtils = {
  /**
   * Generate malicious payloads for testing
   */
  generatePayloads: {
    sqlInjection: [
      "'; DROP TABLE wp_posts; --",
      "' OR '1'='1",
      "' UNION SELECT * FROM wp_users --",
      "'; INSERT INTO wp_users (user_login) VALUES ('hacker'); --",
      "' AND (SELECT COUNT(*) FROM wp_users) > 0 --",
      "1' AND SLEEP(5) --",
      "' OR BENCHMARK(1000000,MD5(1)) --"
    ],
    
    xss: [
      '<script>alert("xss")</script>',
      '<img src="x" onerror="alert(1)">',
      'javascript:alert("xss")',
      '<svg onload="alert(1)">',
      '"><script>alert("xss")</script>',
      '<iframe src="javascript:alert(1)"></iframe>',
      '<body onload="alert(1)">',
      '<input onfocus="alert(1)" autofocus>'
    ],
    
    pathTraversal: [
      '../../../etc/passwd',
      '..\\..\\..\\windows\\system32\\drivers\\etc\\hosts',
      '....//....//....//etc/passwd',
      '%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd',
      '..%252f..%252f..%252fetc%252fpasswd'
    ],
    
    commandInjection: [
      '; ls -la',
      '| cat /etc/passwd',
      '&& whoami',
      '`id`',
      '$(id)',
      '; rm -rf /',
      '| nc -l -p 4444 -e /bin/sh'
    ]
  },
  
  /**
   * Check if response indicates vulnerability
   */
  isVulnerable: (response, payload) => {
    const content = JSON.stringify(response.data).toLowerCase();
    const headers = JSON.stringify(response.headers).toLowerCase();
    
    // SQL injection indicators
    if (payload.includes('DROP TABLE') || payload.includes('UNION SELECT')) {
      return content.includes('mysql') || 
             content.includes('sql syntax') ||
             content.includes('database error') ||
             response.status === 500;
    }
    
    // XSS indicators
    if (payload.includes('<script>') || payload.includes('javascript:')) {
      return content.includes('<script>') ||
             content.includes('javascript:') ||
             content.includes('onerror=');
    }
    
    // Path traversal indicators
    if (payload.includes('../') || payload.includes('etc/passwd')) {
      return content.includes('root:x:') ||
             content.includes('[users]') ||
             response.status === 200;
    }
    
    return false;
  },
  
  /**
   * Analyze security headers
   */
  analyzeSecurityHeaders: (headers) => {
    const analysis = {
      score: 0,
      missing: [],
      present: [],
      recommendations: []
    };
    
    const requiredHeaders = {
      'content-security-policy': 'Prevents XSS and code injection attacks',
      'x-frame-options': 'Prevents clickjacking attacks',
      'x-content-type-options': 'Prevents MIME type sniffing',
      'x-xss-protection': 'Enables browser XSS filtering',
      'strict-transport-security': 'Enforces HTTPS connections',
      'referrer-policy': 'Controls referrer information'
    };
    
    Object.entries(requiredHeaders).forEach(([header, description]) => {
      if (headers[header]) {
        analysis.present.push({ header, value: headers[header] });
        analysis.score += 1;
      } else {
        analysis.missing.push({ header, description });
      }
    });
    
    analysis.score = Math.round((analysis.score / Object.keys(requiredHeaders).length) * 100);
    
    return analysis;
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
        await global.securityUtils.wait(delay * Math.pow(2, i));
      }
    }
  }
};

// Global error handler for security tests
global.handleSecurityError = (error, context = '') => {
  const errorInfo = {
    context,
    message: error.message,
    status: error.response?.status,
    statusText: error.response?.statusText,
    data: error.response?.data,
    url: error.config?.url,
    method: error.config?.method,
    timestamp: new Date().toISOString()
  };
  
  console.error('Security Test Error:', JSON.stringify(errorInfo, null, 2));
  return errorInfo;
};

// Setup and teardown hooks
beforeAll(async () => {
  console.log('🔒 Starting Security Tests...');
  console.log(`Target URL: ${global.SECURITY_CONFIG.baseURL}`);
  console.log(`Vulnerability Scan: ${global.SECURITY_CONFIG.enableVulnerabilityScan ? 'Enabled' : 'Disabled'}`);
  
  // Load or establish security baseline
  try {
    await global.securityBaseline.loadBaseline();
    console.log('✅ Security baseline loaded');
  } catch (error) {
    console.warn('⚠️  Failed to load security baseline:', error.message);
  }
});

afterAll(async () => {
  console.log('📊 Security test summary:');
  
  // Generate security report
  try {
    const comparison = await global.securityBaseline.compareToBaseline();
    console.log(`Security Status: ${comparison.status}`);
    console.log(`Score Change: ${comparison.score_change > 0 ? '+' : ''}${comparison.score_change}`);
    
    if (comparison.new_vulnerabilities.length > 0) {
      console.warn(`⚠️  New vulnerabilities found: ${comparison.new_vulnerabilities.length}`);
    }
    
    if (comparison.resolved_vulnerabilities.length > 0) {
      console.log(`✅ Vulnerabilities resolved: ${comparison.resolved_vulnerabilities.length}`);
    }
    
  } catch (error) {
    console.warn('⚠️  Failed to generate security comparison:', error.message);
  }
  
  console.log('🔒 Security Tests completed');
});

// Global test matchers for security testing
expect.extend({
  toBeSecureEndpoint(received) {
    const secureStatuses = [401, 403, 404, 405];
    const pass = secureStatuses.includes(received.status);
    
    if (pass) {
      return {
        message: () => `Expected endpoint not to be secure (status ${received.status})`,
        pass: true
      };
    } else {
      return {
        message: () => `Expected endpoint to be secure, but got status ${received.status}`,
        pass: false
      };
    }
  },
  
  toHaveSecurityHeaders(received, requiredHeaders = []) {
    const headers = received.headers || {};
    const missing = requiredHeaders.filter(header => !headers[header]);
    
    if (missing.length === 0) {
      return {
        message: () => `Expected response not to have security headers: ${requiredHeaders.join(', ')}`,
        pass: true
      };
    } else {
      return {
        message: () => `Expected response to have security headers: ${missing.join(', ')}`,
        pass: false
      };
    }
  },
  
  toPreventInjection(received, payload) {
    const vulnerable = global.securityUtils.isVulnerable(received, payload);
    
    if (!vulnerable) {
      return {
        message: () => `Expected response to be vulnerable to injection: ${payload}`,
        pass: true
      };
    } else {
      return {
        message: () => `Expected response to prevent injection, but payload succeeded: ${payload}`,
        pass: false
      };
    }
  }
});
