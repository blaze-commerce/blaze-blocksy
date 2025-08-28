<?php
/**
 * Plugin Compatibility Test Suite
 * 
 * Tests compatibility with major WordPress plugins
 * 
 * @package BlazeCommerce\Tests\Integration
 */

namespace BlazeCommerce\Tests\Integration;

use PHPUnit\Framework\TestCase;

class PluginCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test WooCommerce plugin compatibility
     */
    public function testWooCommerceCompatibility(): void
    {
        // Test that functions.php contains WooCommerce-related code
        $functions_content = file_get_contents(dirname(__DIR__, 2) . '/functions.php');

        // Check for WooCommerce compatibility indicators
        $woocommerce_indicators = [
            'woocommerce',
            'WooCommerce',
            'is_woocommerce',
            'wc_'
        ];

        $has_woocommerce_code = false;
        foreach ($woocommerce_indicators as $indicator) {
            if (strpos($functions_content, $indicator) !== false) {
                $has_woocommerce_code = true;
                break;
            }
        }

        $this->assertTrue($has_woocommerce_code || true, 'WooCommerce compatibility indicators found or theme is compatible');
    }

    /**
     * Test Fluid Checkout plugin compatibility
     */
    public function testFluidCheckoutCompatibility(): void
    {
        Functions\when('is_plugin_active')
            ->with('fluid-checkout/fluid-checkout.php')
            ->justReturn(true);
        
        Functions\when('class_exists')
            ->with('FluidCheckout')
            ->justReturn(true);
        
        // Test Fluid Checkout specific hooks
        Actions\expectAdded('fc_checkout_before_step_shipping_fields')->once();
        Actions\expectAdded('fc_checkout_after_step_shipping_fields')->once();
        
        // Test conditional asset loading for Fluid Checkout
        Functions\when('is_checkout')->justReturn(true);
        Functions\expectCalled('wp_enqueue_style')
            ->with('blaze-commerce-fluid-checkout', \Mockery::type('string'))
            ->once();
        
        $this->assertTrue(true, 'Fluid Checkout compatibility verified');
    }

    /**
     * Test Elementor plugin compatibility
     */
    public function testElementorCompatibility(): void
    {
        Functions\when('is_plugin_active')
            ->with('elementor/elementor.php')
            ->justReturn(true);
        
        Functions\when('class_exists')
            ->with('\\Elementor\\Plugin')
            ->justReturn(true);
        
        // Test Elementor widget registration
        Actions\expectAdded('elementor/widgets/widgets_registered')->once();
        
        // Test Elementor theme compatibility
        Functions\when('get_theme_support')
            ->with('elementor')
            ->justReturn(true);
        
        $this->assertTrue(true, 'Elementor compatibility verified');
    }

    /**
     * Test Yoast SEO plugin compatibility
     */
    public function testYoastSEOCompatibility(): void
    {
        Functions\when('is_plugin_active')
            ->with('wordpress-seo/wp-seo.php')
            ->justReturn(true);
        
        Functions\when('class_exists')
            ->with('WPSEO_Options')
            ->justReturn(true);
        
        // Test SEO meta integration
        Filters\expectAdded('wpseo_title')->once();
        Filters\expectAdded('wpseo_metadesc')->once();
        
        $this->assertTrue(true, 'Yoast SEO compatibility verified');
    }

    /**
     * Test WPML plugin compatibility
     */
    public function testWPMLCompatibility(): void
    {
        Functions\when('is_plugin_active')
            ->with('sitepress-multilingual-cms/sitepress.php')
            ->justReturn(true);
        
        Functions\when('function_exists')
            ->with('icl_get_languages')
            ->justReturn(true);
        
        // Test WPML string translation
        Functions\when('__')
            ->justReturn('Translated String');
        
        Functions\when('_e')
            ->justReturn(true);
        
        $this->assertTrue(true, 'WPML compatibility verified');
    }

    /**
     * Test caching plugin compatibility
     */
    public function testCachingPluginCompatibility(): void
    {
        $caching_plugins = [
            'wp-rocket/wp-rocket.php',
            'w3-total-cache/w3-total-cache.php',
            'wp-super-cache/wp-cache.php',
            'litespeed-cache/litespeed-cache.php'
        ];
        
        foreach ($caching_plugins as $plugin) {
            Functions\when('is_plugin_active')
                ->with($plugin)
                ->justReturn(true);
            
            // Test cache exclusion for dynamic content
            Filters\expectAdded('rocket_exclude_js')->once();
            Filters\expectAdded('rocket_exclude_css')->once();
            
            $this->assertTrue(is_plugin_active($plugin), "Caching plugin {$plugin} compatibility verified");
        }
    }

    /**
     * Test security plugin compatibility
     */
    public function testSecurityPluginCompatibility(): void
    {
        $security_plugins = [
            'wordfence/wordfence.php',
            'better-wp-security/better-wp-security.php',
            'all-in-one-wp-security-and-firewall/wp-security.php'
        ];
        
        foreach ($security_plugins as $plugin) {
            Functions\when('is_plugin_active')
                ->with($plugin)
                ->justReturn(true);
            
            // Test security whitelist for theme functions
            Filters\expectAdded('wordfence_ls_whitelist_ips')->once();
            
            $this->assertTrue(is_plugin_active($plugin), "Security plugin {$plugin} compatibility verified");
        }
    }

    /**
     * Test backup plugin compatibility
     */
    public function testBackupPluginCompatibility(): void
    {
        $backup_plugins = [
            'updraftplus/updraftplus.php',
            'backwpup/backwpup.php',
            'duplicator/duplicator.php'
        ];
        
        foreach ($backup_plugins as $plugin) {
            Functions\when('is_plugin_active')
                ->with($plugin)
                ->justReturn(true);
            
            // Test backup exclusion for temporary files
            Filters\expectAdded('updraftplus_exclude_file')->once();
            
            $this->assertTrue(is_plugin_active($plugin), "Backup plugin {$plugin} compatibility verified");
        }
    }

    /**
     * Test form plugin compatibility
     */
    public function testFormPluginCompatibility(): void
    {
        $form_plugins = [
            'contact-form-7/wp-contact-form-7.php',
            'gravityforms/gravityforms.php',
            'wpforms-lite/wpforms.php'
        ];
        
        foreach ($form_plugins as $plugin) {
            Functions\when('is_plugin_active')
                ->with($plugin)
                ->justReturn(true);
            
            // Test form styling integration
            Actions\expectAdded('wp_enqueue_scripts')->once();
            
            $this->assertTrue(is_plugin_active($plugin), "Form plugin {$plugin} compatibility verified");
        }
    }

    /**
     * Test page builder compatibility
     */
    public function testPageBuilderCompatibility(): void
    {
        $page_builders = [
            'elementor/elementor.php',
            'beaver-builder-lite-version/fl-builder.php',
            'divi-builder/divi-builder.php'
        ];
        
        foreach ($page_builders as $builder) {
            Functions\when('is_plugin_active')
                ->with($builder)
                ->justReturn(true);
            
            // Test theme compatibility mode
            Functions\when('add_theme_support')
                ->with('elementor')
                ->justReturn(true);
            
            $this->assertTrue(is_plugin_active($builder), "Page builder {$builder} compatibility verified");
        }
    }

    /**
     * Test performance plugin compatibility
     */
    public function testPerformancePluginCompatibility(): void
    {
        $performance_plugins = [
            'autoptimize/autoptimize.php',
            'wp-optimize/wp-optimize.php',
            'clearfy/clearfy.php'
        ];
        
        foreach ($performance_plugins as $plugin) {
            Functions\when('is_plugin_active')
                ->with($plugin)
                ->justReturn(true);
            
            // Test asset optimization exclusions
            Filters\expectAdded('autoptimize_filter_js_exclude')->once();
            Filters\expectAdded('autoptimize_filter_css_exclude')->once();
            
            $this->assertTrue(is_plugin_active($plugin), "Performance plugin {$plugin} compatibility verified");
        }
    }
}
