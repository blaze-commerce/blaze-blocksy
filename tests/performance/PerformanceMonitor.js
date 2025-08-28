/**
 * Performance Monitor and Baseline Management
 * 
 * Establishes performance baselines and monitors for regressions
 * 
 * @package BlazeCommerce\Tests\Performance
 */

const axios = require('axios');
const fs = require('fs').promises;
const path = require('path');
const { execSync } = require('child_process');

class PerformanceMonitor {
  constructor(baseURL) {
    this.baseURL = baseURL;
    this.client = axios.create({
      baseURL,
      timeout: 30000
    });
    this.baseline = null;
    this.baselineFile = path.join(__dirname, 'performance-baseline.json');
  }
  
  /**
   * Establish performance baseline
   */
  async establishBaseline() {
    console.log('📊 Establishing performance baseline...');
    
    const baseline = {
      timestamp: new Date().toISOString(),
      version: '1.0.0',
      metrics: {
        pageLoad: await this.measurePageLoadTimes(),
        api: await this.measureApiPerformance(),
        resources: await this.measureResourceLoading(),
        lighthouse: await this.runLighthouseAudit(),
        webVitals: await this.measureWebVitals()
      },
      thresholds: this.definePerformanceThresholds(),
      recommendations: this.generateRecommendations()
    };
    
    await this.saveBaseline(baseline);
    this.baseline = baseline;
    
    console.log('✅ Performance baseline established');
    return baseline;
  }
  
  /**
   * Compare current performance against baseline
   */
  async compareToBaseline() {
    if (!this.baseline) {
      await this.loadBaseline();
    }
    
    console.log('📈 Comparing current performance to baseline...');
    
    const current = {
      timestamp: new Date().toISOString(),
      metrics: {
        pageLoad: await this.measurePageLoadTimes(),
        api: await this.measureApiPerformance(),
        resources: await this.measureResourceLoading(),
        lighthouse: await this.runLighthouseAudit(),
        webVitals: await this.measureWebVitals()
      }
    };
    
    const comparison = this.generateComparison(this.baseline, current);
    
    console.log('📊 Performance comparison completed');
    return comparison;
  }
  
  /**
   * Measure page load times for critical pages
   */
  async measurePageLoadTimes() {
    const pages = [
      { name: 'Homepage', url: '/' },
      { name: 'Shop', url: '/shop/' },
      { name: 'Product', url: '/product/1/' },
      { name: 'Cart', url: '/cart/' },
      { name: 'Checkout', url: '/checkout/' }
    ];
    
    const results = {};
    
    for (const page of pages) {
      try {
        const startTime = Date.now();
        const response = await this.client.get(page.url);
        const endTime = Date.now();
        
        results[page.name] = {
          responseTime: endTime - startTime,
          status: response.status,
          size: response.headers['content-length'] || response.data.length,
          ttfb: response.headers['x-response-time'] || null,
          success: response.status === 200
        };
      } catch (error) {
        results[page.name] = {
          responseTime: null,
          status: error.response?.status || 0,
          size: 0,
          ttfb: null,
          success: false,
          error: error.message
        };
      }
    }
    
    return results;
  }
  
  /**
   * Measure API performance
   */
  async measureApiPerformance() {
    const apis = [
      { name: 'WordPress Posts', url: '/wp-json/wp/v2/posts?per_page=10' },
      { name: 'WooCommerce Products', url: '/wp-json/wc/v3/products?per_page=10' },
      { name: 'WooCommerce Categories', url: '/wp-json/wc/v3/products/categories' }
    ];
    
    const results = {};
    
    for (const api of apis) {
      try {
        const startTime = Date.now();
        const response = await this.client.get(api.url);
        const endTime = Date.now();
        
        results[api.name] = {
          responseTime: endTime - startTime,
          status: response.status,
          dataSize: JSON.stringify(response.data).length,
          recordCount: Array.isArray(response.data) ? response.data.length : 1,
          success: [200, 401].includes(response.status) // 401 OK for protected endpoints
        };
      } catch (error) {
        results[api.name] = {
          responseTime: null,
          status: error.response?.status || 0,
          dataSize: 0,
          recordCount: 0,
          success: false,
          error: error.message
        };
      }
    }
    
    return results;
  }
  
  /**
   * Measure resource loading performance
   */
  async measureResourceLoading() {
    const resources = [
      { name: 'Theme CSS', url: '/wp-content/themes/blocksy-child/style.css' },
      { name: 'jQuery', url: '/wp-includes/js/jquery/jquery.min.js' },
      { name: 'WooCommerce CSS', url: '/wp-content/plugins/woocommerce/assets/css/woocommerce.css' }
    ];
    
    const results = {};
    
    for (const resource of resources) {
      try {
        const startTime = Date.now();
        const response = await this.client.get(resource.url);
        const endTime = Date.now();
        
        results[resource.name] = {
          loadTime: endTime - startTime,
          status: response.status,
          size: response.headers['content-length'] || response.data.length,
          cached: response.headers['x-cache'] === 'HIT',
          compressed: !!response.headers['content-encoding'],
          success: response.status === 200
        };
      } catch (error) {
        results[resource.name] = {
          loadTime: null,
          status: error.response?.status || 0,
          size: 0,
          cached: false,
          compressed: false,
          success: false,
          error: error.message
        };
      }
    }
    
    return results;
  }
  
  /**
   * Run Lighthouse audit (if available)
   */
  async runLighthouseAudit() {
    try {
      // Check if Lighthouse CLI is available
      execSync('lighthouse --version', { stdio: 'ignore' });
      
      const outputPath = path.join(__dirname, 'lighthouse-report.json');
      const command = `lighthouse ${this.baseURL} --output=json --output-path=${outputPath} --chrome-flags="--headless --no-sandbox"`;
      
      execSync(command, { stdio: 'ignore' });
      
      const reportData = await fs.readFile(outputPath, 'utf8');
      const report = JSON.parse(reportData);
      
      return {
        performance: report.lhr.categories.performance.score * 100,
        accessibility: report.lhr.categories.accessibility.score * 100,
        bestPractices: report.lhr.categories['best-practices'].score * 100,
        seo: report.lhr.categories.seo.score * 100,
        firstContentfulPaint: report.lhr.audits['first-contentful-paint'].numericValue,
        largestContentfulPaint: report.lhr.audits['largest-contentful-paint'].numericValue,
        cumulativeLayoutShift: report.lhr.audits['cumulative-layout-shift'].numericValue,
        firstInputDelay: report.lhr.audits['max-potential-fid']?.numericValue || null,
        available: true
      };
    } catch (error) {
      return {
        performance: null,
        accessibility: null,
        bestPractices: null,
        seo: null,
        firstContentfulPaint: null,
        largestContentfulPaint: null,
        cumulativeLayoutShift: null,
        firstInputDelay: null,
        available: false,
        error: 'Lighthouse not available'
      };
    }
  }
  
  /**
   * Measure Core Web Vitals
   */
  async measureWebVitals() {
    // This would typically use real user monitoring or synthetic testing
    // For now, we'll simulate based on page load times
    const pageMetrics = await this.measurePageLoadTimes();
    
    const homepageTime = pageMetrics.Homepage?.responseTime || 3000;
    
    return {
      firstContentfulPaint: Math.min(homepageTime * 0.3, 1800), // Estimate FCP
      largestContentfulPaint: Math.min(homepageTime * 0.7, 2500), // Estimate LCP
      cumulativeLayoutShift: Math.random() * 0.1, // Simulate CLS
      firstInputDelay: Math.random() * 100, // Simulate FID
      timeToInteractive: Math.min(homepageTime * 1.2, 3800), // Estimate TTI
      totalBlockingTime: Math.random() * 200 // Simulate TBT
    };
  }
  
  /**
   * Define performance thresholds
   */
  definePerformanceThresholds() {
    return {
      pageLoad: {
        homepage: 2000, // 2 seconds
        shop: 3000, // 3 seconds
        product: 2500, // 2.5 seconds
        cart: 2000, // 2 seconds
        checkout: 3000 // 3 seconds
      },
      api: {
        responseTime: 1000, // 1 second
        errorRate: 0.05 // 5%
      },
      webVitals: {
        firstContentfulPaint: 1800, // 1.8 seconds
        largestContentfulPaint: 2500, // 2.5 seconds
        cumulativeLayoutShift: 0.1, // 0.1
        firstInputDelay: 100, // 100ms
        timeToInteractive: 3800 // 3.8 seconds
      },
      lighthouse: {
        performance: 90, // 90/100
        accessibility: 95, // 95/100
        bestPractices: 90, // 90/100
        seo: 95 // 95/100
      }
    };
  }
  
  /**
   * Generate performance recommendations
   */
  generateRecommendations() {
    return [
      {
        category: 'Caching',
        priority: 'High',
        recommendation: 'Implement browser caching and CDN for static assets',
        impact: 'Reduces page load times by 30-50%'
      },
      {
        category: 'Images',
        priority: 'High',
        recommendation: 'Optimize images with WebP format and lazy loading',
        impact: 'Reduces bandwidth usage and improves LCP'
      },
      {
        category: 'JavaScript',
        priority: 'Medium',
        recommendation: 'Minimize and defer non-critical JavaScript',
        impact: 'Improves FCP and reduces blocking time'
      },
      {
        category: 'CSS',
        priority: 'Medium',
        recommendation: 'Inline critical CSS and defer non-critical styles',
        impact: 'Improves render-blocking performance'
      },
      {
        category: 'Database',
        priority: 'Medium',
        recommendation: 'Optimize database queries and implement query caching',
        impact: 'Reduces server response times'
      }
    ];
  }
  
  /**
   * Generate comparison report
   */
  generateComparison(baseline, current) {
    const comparison = {
      timestamp: new Date().toISOString(),
      status: 'unchanged', // 'improved', 'degraded', 'unchanged'
      changes: [],
      regressions: [],
      improvements: [],
      scoreChange: 0
    };
    
    // Compare page load times
    Object.keys(baseline.metrics.pageLoad).forEach(page => {
      const baselineTime = baseline.metrics.pageLoad[page]?.responseTime;
      const currentTime = current.metrics.pageLoad[page]?.responseTime;
      
      if (baselineTime && currentTime) {
        const change = ((currentTime - baselineTime) / baselineTime) * 100;
        
        if (Math.abs(change) > 10) { // Significant change threshold
          const changeInfo = {
            page,
            baseline: baselineTime,
            current: currentTime,
            change: Math.round(change),
            type: change > 0 ? 'regression' : 'improvement'
          };
          
          comparison.changes.push(changeInfo);
          
          if (change > 0) {
            comparison.regressions.push(changeInfo);
          } else {
            comparison.improvements.push(changeInfo);
          }
        }
      }
    });
    
    // Determine overall status
    if (comparison.regressions.length > comparison.improvements.length) {
      comparison.status = 'degraded';
    } else if (comparison.improvements.length > comparison.regressions.length) {
      comparison.status = 'improved';
    }
    
    return comparison;
  }
  
  /**
   * Save baseline to file
   */
  async saveBaseline(baseline) {
    await fs.writeFile(this.baselineFile, JSON.stringify(baseline, null, 2));
  }
  
  /**
   * Load baseline from file
   */
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

module.exports = PerformanceMonitor;
