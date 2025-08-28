/**
 * Performance Tests with Playwright
 * 
 * @package BlazeCommerce
 * @subpackage Tests
 */

const { test, expect } = require('@playwright/test');

test.describe('Performance Tests @performance', () => {
  
  test('should meet Core Web Vitals thresholds', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Measure Core Web Vitals
    const vitals = await page.evaluate(() => {
      return new Promise((resolve) => {
        const vitals = {};
        
        // First Contentful Paint
        new PerformanceObserver((list) => {
          const entries = list.getEntries();
          entries.forEach((entry) => {
            if (entry.name === 'first-contentful-paint') {
              vitals.fcp = entry.startTime;
            }
          });
        }).observe({ entryTypes: ['paint'] });
        
        // Largest Contentful Paint
        new PerformanceObserver((list) => {
          const entries = list.getEntries();
          const lastEntry = entries[entries.length - 1];
          if (lastEntry) {
            vitals.lcp = lastEntry.startTime;
          }
        }).observe({ entryTypes: ['largest-contentful-paint'] });
        
        // Cumulative Layout Shift
        let clsValue = 0;
        new PerformanceObserver((list) => {
          const entries = list.getEntries();
          entries.forEach((entry) => {
            if (!entry.hadRecentInput) {
              clsValue += entry.value;
            }
          });
          vitals.cls = clsValue;
        }).observe({ entryTypes: ['layout-shift'] });
        
        // Navigation timing
        const navigation = performance.getEntriesByType('navigation')[0];
        if (navigation) {
          vitals.loadTime = navigation.loadEventEnd - navigation.loadEventStart;
          vitals.domContentLoaded = navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart;
          vitals.responseTime = navigation.responseEnd - navigation.requestStart;
          vitals.ttfb = navigation.responseStart - navigation.requestStart;
        }
        
        // Resolve after a short delay to collect metrics
        setTimeout(() => resolve(vitals), 2000);
      });
    });
    
    console.log('Performance Metrics:', vitals);
    
    // Assert Core Web Vitals thresholds
    if (vitals.fcp) {
      expect(vitals.fcp).toBeLessThan(1800); // FCP < 1.8s
    }
    
    if (vitals.lcp) {
      expect(vitals.lcp).toBeLessThan(2500); // LCP < 2.5s
    }
    
    if (vitals.cls !== undefined) {
      expect(vitals.cls).toBeLessThan(0.1); // CLS < 0.1
    }
    
    if (vitals.ttfb) {
      expect(vitals.ttfb).toBeLessThan(800); // TTFB < 800ms
    }
  });
  
  test('should have reasonable resource loading times', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Get resource timing data
    const resources = await page.evaluate(() => {
      const resources = performance.getEntriesByType('resource');
      return resources.map(resource => ({
        name: resource.name,
        duration: resource.duration,
        size: resource.transferSize || 0,
        type: resource.initiatorType
      }));
    });
    
    console.log(`Loaded ${resources.length} resources`);
    
    // Check that resources load in reasonable time
    const slowResources = resources.filter(resource => resource.duration > 3000);
    expect(slowResources.length).toBeLessThan(3); // Less than 3 slow resources
    
    // Check total page size is reasonable
    const totalSize = resources.reduce((sum, resource) => sum + resource.size, 0);
    expect(totalSize).toBeLessThan(5 * 1024 * 1024); // Less than 5MB total
  });
  
  test('should have good accessibility performance', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Check for basic accessibility elements
    const accessibilityMetrics = await page.evaluate(() => {
      const metrics = {
        hasTitle: !!document.title,
        hasLang: !!document.documentElement.lang,
        hasHeadings: document.querySelectorAll('h1, h2, h3, h4, h5, h6').length > 0,
        hasImages: document.querySelectorAll('img').length,
        imagesWithAlt: document.querySelectorAll('img[alt]').length,
        hasLinks: document.querySelectorAll('a').length,
        linksWithText: document.querySelectorAll('a:not(:empty)').length
      };
      
      return metrics;
    });
    
    console.log('Accessibility Metrics:', accessibilityMetrics);
    
    // Basic accessibility assertions
    expect(accessibilityMetrics.hasTitle).toBe(true);
    expect(accessibilityMetrics.hasHeadings).toBe(true);
    
    // If there are images, check alt text coverage
    if (accessibilityMetrics.hasImages > 0) {
      const altTextCoverage = accessibilityMetrics.imagesWithAlt / accessibilityMetrics.hasImages;
      expect(altTextCoverage).toBeGreaterThan(0.8); // 80% of images should have alt text
    }
    
    // If there are links, check they have text
    if (accessibilityMetrics.hasLinks > 0) {
      const linkTextCoverage = accessibilityMetrics.linksWithText / accessibilityMetrics.hasLinks;
      expect(linkTextCoverage).toBeGreaterThan(0.9); // 90% of links should have text
    }
  });
  
  test('should generate performance report', async ({ page }) => {
    await page.goto('https://example.com');
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Collect comprehensive performance data
    const performanceData = await page.evaluate(() => {
      const navigation = performance.getEntriesByType('navigation')[0];
      const resources = performance.getEntriesByType('resource');
      
      return {
        timestamp: new Date().toISOString(),
        url: window.location.href,
        navigation: {
          loadTime: navigation.loadEventEnd - navigation.loadEventStart,
          domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
          responseTime: navigation.responseEnd - navigation.requestStart,
          ttfb: navigation.responseStart - navigation.requestStart,
          domInteractive: navigation.domInteractive - navigation.navigationStart,
          domComplete: navigation.domComplete - navigation.navigationStart
        },
        resources: {
          total: resources.length,
          totalSize: resources.reduce((sum, r) => sum + (r.transferSize || 0), 0),
          byType: resources.reduce((acc, r) => {
            acc[r.initiatorType] = (acc[r.initiatorType] || 0) + 1;
            return acc;
          }, {})
        },
        memory: performance.memory ? {
          usedJSHeapSize: performance.memory.usedJSHeapSize,
          totalJSHeapSize: performance.memory.totalJSHeapSize,
          jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
        } : null
      };
    });
    
    console.log('Performance Report:', JSON.stringify(performanceData, null, 2));
    
    // Save performance report
    const fs = require('fs');
    const path = require('path');
    
    const reportPath = path.join(process.cwd(), 'coverage', 'performance-report.json');
    fs.writeFileSync(reportPath, JSON.stringify(performanceData, null, 2));
    
    // Basic performance assertions
    expect(performanceData.navigation.loadTime).toBeLessThan(5000);
    expect(performanceData.navigation.ttfb).toBeLessThan(1000);
    expect(performanceData.resources.total).toBeGreaterThan(0);
  });

});
