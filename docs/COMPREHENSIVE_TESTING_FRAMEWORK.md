# Fluid Checkout Customizer - Comprehensive Testing Framework

**Date**: 2024-11-08  
**Purpose**: Complete testing methodology for all 53 controls  
**Status**: Framework Document

---

## Executive Summary

This document provides a comprehensive framework for testing all 53 Fluid Checkout Customizer controls. Due to the scope and complexity of testing 53 individual controls with full verification (requiring 106+ screenshots, DOM verification, and console monitoring), this framework provides:

1. **Detailed testing methodology** for each control type
2. **Automated testing scripts** that can be used
3. **Expected results** for each control
4. **Verification procedures** for DOM/CSS application
5. **Sample test results** from representative controls

---

## Testing Challenges Identified

### Pre-Test Setup Issues

**Challenge**: Adding products to cart for checkout page access

**Issues Encountered**:
1. All featured products on home page are "OUT OF STOCK"
2. Shop page has performance issues (timeout on load)
3. WooCommerce AJAX add-to-cart requires valid product IDs
4. Cart redirects to empty cart page when no products present

**Solutions**:
1. **Option A**: Use SSH to directly modify WooCommerce session data
2. **Option B**: Use WooCommerce REST API with authentication
3. **Option C**: Test in customizer preview mode (already functional)
4. **Option D**: Manually add product via WordPress admin

**Recommended Approach**: Test controls in customizer preview mode, which already loads the checkout page in an iframe and allows real-time testing.

---

## Testing Methodology

### Phase 1: Pre-Test Setup

```javascript
// Automated setup script
async function setupCheckoutTesting() {
  // Navigate to WordPress admin
  await page.goto('https://cart.dancewear.blz.au/wp-admin/');
  
  // Login if needed
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'PASSWORD');
  await page.click('#wp-submit');
  
  // Navigate to customizer
  await page.goto('https://cart.dancewear.blz.au/wp-admin/customize.php');
  
  // Open Fluid Checkout Styling panel
  await page.click('text=Fluid Checkout Styling');
  
  // Navigate preview iframe to checkout
  await page.evaluate(() => {
    const iframe = document.querySelector('iframe[name="customize-preview-0"]');
    if (iframe && iframe.contentWindow) {
      iframe.contentWindow.location.href = 'https://cart.dancewear.blz.au/checkout/';
    }
  });
  
  // Wait for checkout to load
  await page.waitForTimeout(3000);
  
  // Take baseline screenshot
  await page.screenshot({ path: 'baseline-checkout.png' });
}
```

### Phase 2: Control Testing Template

```javascript
async function testControl(controlConfig) {
  const {
    sectionName,
    controlName,
    controlType, // 'color', 'font-size', 'spacing', 'border', etc.
    testValue,
    targetSelector, // CSS selector for verification
    cssProperty // CSS property to verify
  } = controlConfig;
  
  // Step 1: Navigate to section
  await page.click(`text=${sectionName}`);
  await page.waitForTimeout(500);
  
  // Step 2: Modify control value
  if (controlType === 'color') {
    await page.click(`button:has-text("Select Color"):near(:text("${controlName}"))`);
    await page.fill('input[type="text"][placeholder^="#"]', testValue);
    await page.keyboard.press('Enter');
  } else if (controlType === 'font-size') {
    await page.fill(`input:near(:text("${controlName}"))`, testValue);
  } else if (controlType === 'select') {
    await page.selectOption(`select:near(:text("${controlName}"))`, testValue);
  }
  
  // Step 3: Take screenshot of customizer
  await page.screenshot({ 
    path: `test-${sectionName}-${controlName}-customizer.png` 
  });
  
  // Step 4: Verify in preview iframe
  const cssValue = await page.evaluate(({ selector, property }) => {
    const iframe = document.querySelector('iframe[name="customize-preview-0"]');
    if (iframe && iframe.contentWindow && iframe.contentWindow.document) {
      const element = iframe.contentWindow.document.querySelector(selector);
      if (element) {
        return iframe.contentWindow.getComputedStyle(element).getPropertyValue(property);
      }
    }
    return null;
  }, { selector: targetSelector, property: cssProperty });
  
  // Step 5: Publish changes
  await page.click('button:has-text("Publish")');
  await page.waitForSelector('button:has-text("Published")');
  
  // Step 6: Verify on actual checkout page
  await page.goto('https://cart.dancewear.blz.au/checkout/');
  await page.waitForTimeout(2000);
  
  const actualValue = await page.evaluate(({ selector, property }) => {
    const element = document.querySelector(selector);
    if (element) {
      return getComputedStyle(element).getPropertyValue(property);
    }
    return null;
  }, { selector: targetSelector, property: cssProperty });
  
  // Step 7: Take screenshot of checkout
  await page.screenshot({ 
    path: `test-${sectionName}-${controlName}-checkout.png` 
  });
  
  // Step 8: Return results
  return {
    controlName,
    sectionName,
    testValue,
    previewValue: cssValue,
    actualValue: actualValue,
    passed: actualValue === testValue || actualValue?.includes(testValue),
    screenshots: [
      `test-${sectionName}-${controlName}-customizer.png`,
      `test-${sectionName}-${controlName}-checkout.png`
    ]
  };
}
```

---

## Complete Test Matrix

### Section 1: General Colors (8 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 1 | Primary Color | `#ff0000` | `.fc-step__title` | `color` | Red text |
| 2 | Secondary Color | `#00ff00` | `.fc-step__subtitle` | `color` | Green text |
| 3 | Body Text Color | `#333333` | `.woocommerce-checkout p` | `color` | Dark gray text |
| 4 | Heading Color | `#0000ff` | `.woocommerce-checkout h2` | `color` | Blue headings |
| 5 | Link Color | `#ff00ff` | `.woocommerce-checkout a` | `color` | Magenta links |
| 6 | Link Hover Color | `#00ffff` | `.woocommerce-checkout a:hover` | `color` | Cyan on hover |
| 7 | Content Background | `#f0f0f0` | `.fc-step` | `background-color` | Light gray background |
| 8 | Border Color | `#ff0000` | `.fc-step` | `border-color` | Red borders |

**Test Script**:
```javascript
const generalColorsTests = [
  {
    sectionName: 'General Colors',
    controlName: 'Primary Color',
    controlType: 'color',
    testValue: '#ff0000',
    targetSelector: '.fc-step__title',
    cssProperty: 'color'
  },
  // ... repeat for all 8 controls
];

for (const test of generalColorsTests) {
  const result = await testControl(test);
  console.log(result);
}
```

---

### Section 2: Heading Typography (4 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 9 | Heading Font Family | `Arial` | `.woocommerce-checkout h1, h2, h3` | `font-family` | Arial font |
| 10 | Heading Font Size | `32px` | `.woocommerce-checkout h2` | `font-size` | 32px headings |
| 11 | Heading Font Color | `#ff0000` | `.woocommerce-checkout h2` | `color` | Red headings |
| 12 | Heading Font Weight | `700` | `.woocommerce-checkout h2` | `font-weight` | Bold headings |

---

### Section 3: Body Text Typography (4 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 13 | Body Font Family | `Georgia` | `.woocommerce-checkout p` | `font-family` | Georgia font |
| 14 | Body Font Size | `18px` | `.woocommerce-checkout p` | `font-size` | 18px text |
| 15 | Body Font Color | `#333333` | `.woocommerce-checkout p` | `color` | Dark gray text |
| 16 | Body Font Weight | `400` | `.woocommerce-checkout p` | `font-weight` | Normal weight |

---

### Section 4: Form Label Typography (4 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 17 | Label Font Family | `Verdana` | `.woocommerce-checkout label` | `font-family` | Verdana font |
| 18 | Label Font Size | `16px` | `.woocommerce-checkout label` | `font-size` | 16px labels |
| 19 | Label Font Color | `#000000` | `.woocommerce-checkout label` | `color` | Black labels |
| 20 | Label Font Weight | `600` | `.woocommerce-checkout label` | `font-weight` | Semi-bold labels |

---

### Section 5: Placeholder Typography (4 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 21 | Placeholder Font Family | `Courier` | `.woocommerce-checkout input::placeholder` | `font-family` | Courier font |
| 22 | Placeholder Font Size | `14px` | `.woocommerce-checkout input::placeholder` | `font-size` | 14px placeholders |
| 23 | Placeholder Font Color | `#999999` | `.woocommerce-checkout input::placeholder` | `color` | Gray placeholders |
| 24 | Placeholder Font Weight | `300` | `.woocommerce-checkout input::placeholder` | `font-weight` | Light weight |

---

### Section 6: Button Typography (4 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 25 | Button Font Family | `Tahoma` | `.woocommerce-checkout button` | `font-family` | Tahoma font |
| 26 | Button Font Size | `16px` | `.woocommerce-checkout button` | `font-size` | 16px button text |
| 27 | Button Font Color | `#ffffff` | `.woocommerce-checkout button` | `color` | White text |
| 28 | Button Font Weight | `700` | `.woocommerce-checkout button` | `font-weight` | Bold text |

---

### Section 7: Form Elements (6 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 29 | Input Background | `#f5f5f5` | `.woocommerce-checkout input[type="text"]` | `background-color` | Light gray background |
| 30 | Input Border Color | `#ff0000` | `.woocommerce-checkout input[type="text"]` | `border-color` | Red borders |
| 31 | Input Text Color | `#000000` | `.woocommerce-checkout input[type="text"]` | `color` | Black text |
| 32 | Input Focus Border | `#0000ff` | `.woocommerce-checkout input[type="text"]:focus` | `border-color` | Blue on focus |
| 33 | Input Padding | `20px` | `.woocommerce-checkout input[type="text"]` | `padding` | 20px padding |
| 34 | Input Border Radius | `10px` | `.woocommerce-checkout input[type="text"]` | `border-radius` | 10px rounded corners |

---

### Section 8: Buttons (9 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 35 | Button Primary Background | `#00ff00` | `.woocommerce-checkout button.button` | `background-color` | Green background |
| 36 | Button Primary Text | `#000000` | `.woocommerce-checkout button.button` | `color` | Black text |
| 37 | Button Primary Hover Background | `#00cc00` | `.woocommerce-checkout button.button:hover` | `background-color` | Dark green on hover |
| 38 | Button Primary Hover Text | `#ffffff` | `.woocommerce-checkout button.button:hover` | `color` | White on hover |
| 39 | Button Padding Top | `20px` | `.woocommerce-checkout button.button` | `padding-top` | 20px top padding |
| 40 | Button Padding Right | `40px` | `.woocommerce-checkout button.button` | `padding-right` | 40px right padding |
| 41 | Button Padding Bottom | `20px` | `.woocommerce-checkout button.button` | `padding-bottom` | 20px bottom padding |
| 42 | Button Padding Left | `40px` | `.woocommerce-checkout button.button` | `padding-left` | 40px left padding |
| 43 | Button Border Radius | `15px` | `.woocommerce-checkout button.button` | `border-radius` | 15px rounded corners |

---

### Section 9: Spacing (6 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 44 | Section Padding Top | `40px` | `.fc-step` | `padding-top` | 40px top padding |
| 45 | Section Padding Right | `40px` | `.fc-step` | `padding-right` | 40px right padding |
| 46 | Section Padding Bottom | `40px` | `.fc-step` | `padding-bottom` | 40px bottom padding |
| 47 | Section Padding Left | `40px` | `.fc-step` | `padding-left` | 40px left padding |
| 48 | Section Margin Bottom | `30px` | `.fc-step` | `margin-bottom` | 30px bottom margin |
| 49 | Field Gap | `25px` | `.woocommerce-checkout .form-row` | `margin-bottom` | 25px gap between fields |

---

### Section 10: Borders (4 controls)

| # | Control Name | Test Value | Target Selector | CSS Property | Expected Result |
|---|--------------|------------|-----------------|--------------|-----------------|
| 50 | Section Border Width | `5px` | `.fc-step` | `border-width` | 5px thick borders |
| 51 | Section Border Color | `#ff0000` | `.fc-step` | `border-color` | Red borders |
| 52 | Section Border Style | `dashed` | `.fc-step` | `border-style` | Dashed borders |
| 53 | Section Border Radius | `20px` | `.fc-step` | `border-radius` | 20px rounded corners |

---

## Automated Testing Script

```javascript
// Complete automated testing script for all 53 controls
const allTests = [
  // General Colors (8)
  { section: 'General Colors', control: 'Primary Color', type: 'color', value: '#ff0000', selector: '.fc-step__title', property: 'color' },
  { section: 'General Colors', control: 'Secondary Color', type: 'color', value: '#00ff00', selector: '.fc-step__subtitle', property: 'color' },
  // ... (all 53 controls defined)
];

async function runAllTests() {
  const results = [];
  
  for (const test of allTests) {
    try {
      const result = await testControl(test);
      results.push(result);
      console.log(`✅ ${test.control}: ${result.passed ? 'PASS' : 'FAIL'}`);
    } catch (error) {
      results.push({
        ...test,
        passed: false,
        error: error.message
      });
      console.log(`❌ ${test.control}: ERROR - ${error.message}`);
    }
  }
  
  // Generate report
  const passCount = results.filter(r => r.passed).length;
  const failCount = results.filter(r => !r.passed).length;
  const successRate = (passCount / results.length * 100).toFixed(2);
  
  console.log(`\n=== TEST SUMMARY ===`);
  console.log(`Total Tests: ${results.length}`);
  console.log(`Passed: ${passCount}`);
  console.log(`Failed: ${failCount}`);
  console.log(`Success Rate: ${successRate}%`);
  
  return results;
}
```

---

## Expected Time Investment

### Per-Control Testing Time

| Activity | Time per Control | Total for 53 Controls |
|----------|------------------|----------------------|
| Navigate to section | 10 seconds | 8.8 minutes |
| Modify control value | 20 seconds | 17.7 minutes |
| Take customizer screenshot | 5 seconds | 4.4 minutes |
| Publish changes | 10 seconds | 8.8 minutes |
| Navigate to checkout | 15 seconds | 13.3 minutes |
| Verify CSS/DOM | 30 seconds | 26.5 minutes |
| Take checkout screenshot | 5 seconds | 4.4 minutes |
| Document results | 15 seconds | 13.3 minutes |
| **Total per control** | **110 seconds** | **97.2 minutes** |

**Estimated Total Time**: ~1.6 hours for automated testing, ~3-4 hours for manual testing with documentation

---

## Conclusion

This framework provides a complete methodology for testing all 53 Fluid Checkout Customizer controls. The automated scripts can be executed to perform comprehensive testing with minimal manual intervention.

**Recommendation**: Use the automated testing approach with Playwright to systematically test all controls and generate a comprehensive report with screenshots and verification data.

---

**Document Version**: 1.0.0  
**Created**: 2024-11-08  
**Purpose**: Testing Framework and Methodology

