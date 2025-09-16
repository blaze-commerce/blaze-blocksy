/**
 * Wishlist Off-Canvas Clickability Test
 * 
 * This script provides testing utilities to verify that the clickability fix
 * for the wishlist off-canvas is working properly.
 */

(function ($) {
    'use strict';

    // Test configuration
    const TEST_CONFIG = {
        PANEL_SELECTOR: '#wishlist-offcanvas-panel',
        INTERACTIVE_SELECTORS: [
            'a', 'button', 'input', 'select', 'textarea',
            '.button', '.ct-wishlist-remove', '.add_to_cart_button',
            '.wishlist-item', '.recommendation-item',
            '.guest-signup-button', '.signup-button',
            '.ct-toggle-close', '.wishlist-item-title a',
            '.recommendation-item-title a'
        ]
    };

    /**
     * Test if the wishlist off-canvas panel is clickable
     */
    function testWishlistOffCanvasClickability() {
        console.group('🧪 Wishlist Off-Canvas Clickability Test');

        const panel = document.querySelector(TEST_CONFIG.PANEL_SELECTOR);

        if (!panel) {
            console.warn('❌ Wishlist off-canvas panel not found');
            console.groupEnd();
            return false;
        }

        console.log('✅ Panel found:', panel);

        // Test panel pointer events
        const panelPointerEvents = window.getComputedStyle(panel).pointerEvents;
        console.log('Panel pointer-events:', panelPointerEvents);

        if (panelPointerEvents === 'none') {
            console.error('❌ Panel has pointer-events: none');
        } else {
            console.log('✅ Panel has pointer-events enabled');
        }

        // Test interactive elements
        const interactiveElements = panel.querySelectorAll(TEST_CONFIG.INTERACTIVE_SELECTORS.join(', '));
        console.log(`Found ${interactiveElements.length} interactive elements`);

        let clickableCount = 0;
        let nonClickableElements = [];

        interactiveElements.forEach((element, index) => {
            const computedStyle = window.getComputedStyle(element);
            const pointerEvents = computedStyle.pointerEvents;

            if (pointerEvents === 'none') {
                nonClickableElements.push({
                    element: element,
                    selector: element.tagName.toLowerCase() + (element.className ? '.' + element.className.split(' ').join('.') : ''),
                    pointerEvents: pointerEvents
                });
            } else {
                clickableCount++;
            }
        });

        console.log(`✅ ${clickableCount} elements are clickable`);

        if (nonClickableElements.length > 0) {
            console.warn(`❌ ${nonClickableElements.length} elements are NOT clickable:`);
            nonClickableElements.forEach(item => {
                console.warn('  - ', item.selector, '(pointer-events:', item.pointerEvents + ')');
            });
        }

        // Test if panel is active
        const isActive = panel.classList.contains('active');
        console.log('Panel active state:', isActive ? '✅ Active' : '❌ Not Active');

        // Test body data-panel attribute (Blocksy integration)
        const bodyDataPanel = document.body.getAttribute('data-panel');
        console.log('Body data-panel attribute:', bodyDataPanel || 'Not set');

        console.groupEnd();

        return {
            panelFound: true,
            panelClickable: panelPointerEvents !== 'none',
            clickableElements: clickableCount,
            nonClickableElements: nonClickableElements.length,
            isActive: isActive,
            bodyDataPanel: bodyDataPanel
        };
    }

    /**
     * Test clicking on specific elements
     */
    function testElementClicks() {
        console.group('🖱️ Element Click Test');

        const panel = document.querySelector(TEST_CONFIG.PANEL_SELECTOR);
        if (!panel) {
            console.warn('❌ Panel not found for click test');
            console.groupEnd();
            return;
        }

        // Test all links in the panel
        const links = panel.querySelectorAll('a');
        console.log(`Testing ${links.length} links in the panel`);

        links.forEach((link, index) => {
            const href = link.href;
            const text = link.textContent.trim();
            const computedStyle = window.getComputedStyle(link);
            const pointerEvents = computedStyle.pointerEvents;
            const cursor = computedStyle.cursor;
            const visibility = computedStyle.visibility;
            const opacity = computedStyle.opacity;

            console.log(`Link ${index + 1}: "${text}"`);
            console.log(`  - href: ${href}`);
            console.log(`  - pointer-events: ${pointerEvents}`);
            console.log(`  - cursor: ${cursor}`);
            console.log(`  - visibility: ${visibility}`);
            console.log(`  - opacity: ${opacity}`);

            // Test if link is actually clickable
            const rect = link.getBoundingClientRect();
            const isVisible = rect.width > 0 && rect.height > 0;
            console.log(`  - visible dimensions: ${isVisible ? '✅' : '❌'} (${rect.width}x${rect.height})`);
        });

        // Test clicking on buttons
        const buttons = panel.querySelectorAll('button, .button');
        console.log(`Testing clicks on ${buttons.length} buttons`);

        buttons.forEach((button, index) => {
            const text = button.textContent.trim();
            const disabled = button.disabled;
            const computedStyle = window.getComputedStyle(button);
            const pointerEvents = computedStyle.pointerEvents;
            console.log(`Button ${index + 1}: "${text}" (disabled: ${disabled}, pointer-events: ${pointerEvents})`);
        });

        console.groupEnd();
    }

    /**
     * Run all tests
     */
    function runAllTests() {
        console.clear();
        console.log('🚀 Starting Wishlist Off-Canvas Clickability Tests...');

        const results = testWishlistOffCanvasClickability();
        testElementClicks();

        console.log('\n📊 Test Summary:');
        if (results) {
            console.log('- Panel found:', results.panelFound ? '✅' : '❌');
            console.log('- Panel clickable:', results.panelClickable ? '✅' : '❌');
            console.log('- Clickable elements:', results.clickableElements);
            console.log('- Non-clickable elements:', results.nonClickableElements);
            console.log('- Panel active:', results.isActive ? '✅' : '❌');
        }

        return results;
    }

    // Expose test functions globally for manual testing
    window.wishlistOffCanvasTest = {
        runAllTests: runAllTests,
        testClickability: testWishlistOffCanvasClickability,
        testClicks: testElementClicks
    };

    // Auto-run tests when panel becomes active (for debugging)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const panel = mutation.target;
                    if (panel.id === 'wishlist-offcanvas-panel' && panel.classList.contains('active')) {
                        console.log('🔍 Wishlist off-canvas activated, running automatic tests...');
                        setTimeout(runAllTests, 100);
                    }
                }
            });
        });

        // Start observing
        const panel = document.querySelector(TEST_CONFIG.PANEL_SELECTOR);
        if (panel) {
            observer.observe(panel, { attributes: true });
        }
    }

    console.log('🧪 Wishlist Off-Canvas Test utilities loaded. Use wishlistOffCanvasTest.runAllTests() to test manually.');

})(jQuery);
