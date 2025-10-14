/**
 * Thank You Page Customizer Preview
 *
 * Handles live preview functionality for the thank you page toggle option
 * in the WordPress Customizer.
 */

(function($) {
    'use strict';

    // Handle thank you page toggle changes
    wp.customize('blocksy_child_enable_custom_thank_you_page', function(value) {
        value.bind(function(newval) {
            // Since this affects the entire page structure, we need to refresh
            // the preview to properly show/hide the custom thank you page
            wp.customize.preview.send('refresh');
        });
    });

})(jQuery);
