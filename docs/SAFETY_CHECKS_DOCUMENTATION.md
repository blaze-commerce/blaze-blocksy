# Fluid Checkout Customizer - Safety Checks Documentation

**Date**: 2024-11-08  
**Version**: 2.0.0  
**Status**: ✅ Production-Ready with Comprehensive Safety Checks

---

## Overview

This document details all defensive programming practices and safety checks implemented in the Fluid Checkout Customizer to prevent fatal errors and ensure graceful degradation when dependencies are not met.

---

## Safety Philosophy

The Fluid Checkout Customizer follows these core safety principles:

1. **Fail Gracefully**: Never cause a fatal error that breaks the site
2. **Early Detection**: Check dependencies before executing code
3. **Clear Communication**: Log warnings when dependencies are missing
4. **Defensive Programming**: Verify existence of classes, functions, and DOM elements
5. **Error Handling**: Use try-catch blocks for critical operations

---

## File-by-File Safety Implementation

### 1. fluid-checkout-customizer.php

#### Top-Level Dependency Check

**Location**: Lines 15-42  
**Purpose**: Prevent class initialization if Fluid Checkout is not active

```php
if ( ! class_exists( 'FluidCheckout' ) ) {
    // Add admin notice if we're in admin area
    if ( is_admin() ) {
        add_action( 'admin_notices', function() {
            if ( current_user_can( 'activate_plugins' ) ) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php esc_html_e( 'Fluid Checkout Customizer:', 'blocksy-child' ); ?></strong>
                        <?php esc_html_e( 'The Fluid Checkout Customizer requires Fluid Checkout Lite or Fluid Checkout Pro to be installed and activated.', 'blocksy-child' ); ?>
                    </p>
                </div>
                <?php
            }
        } );
    }
    
    // Exit gracefully - do not load the customizer
    return;
}
```

**What it does**:
- Checks if `FluidCheckout` class exists (present in both Lite and Pro versions)
- Shows admin notice to users with `activate_plugins` capability
- Returns early to prevent class definition
- Prevents fatal "Class not found" errors

---

#### Constructor Dependency Check

**Location**: Lines 48-56  
**Purpose**: Double-check dependencies before registering hooks

```php
public function __construct() {
    // Double-check dependencies before registering hooks
    if ( ! $this->check_dependencies() ) {
        return;
    }
    
    // Register customizer hooks
    add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
    add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
    add_action( 'wp_head', array( $this, 'output_customizer_css' ), 999 );
}
```

**What it does**:
- Calls `check_dependencies()` method before registering any hooks
- Returns early if dependencies not met
- Prevents hooks from being registered when they can't function

---

#### check_dependencies() Method

**Location**: Lines 58-73  
**Purpose**: Centralized dependency verification

```php
private function check_dependencies() {
    // Check for FluidCheckout class
    if ( ! class_exists( 'FluidCheckout' ) ) {
        return false;
    }
    
    // Check for WP_Customize_Manager (WordPress Customizer API)
    if ( ! class_exists( 'WP_Customize_Manager' ) ) {
        return false;
    }
    
    return true;
}
```

**What it checks**:
- `FluidCheckout` class existence
- `WP_Customize_Manager` class existence (WordPress Customizer API)
- Returns boolean for easy conditional checks

---

#### register_customizer_settings() Method

**Location**: Lines 80-121  
**Purpose**: Safe customizer registration with error handling

```php
public function register_customizer_settings( $wp_customize ) {
    // Early return if dependencies not met
    if ( ! $this->check_dependencies() ) {
        return;
    }
    
    // Verify $wp_customize is valid
    if ( ! $wp_customize instanceof WP_Customize_Manager ) {
        return;
    }
    
    // ... panel and section registration ...
    
    // Register sections with error handling
    try {
        $this->register_general_colors_section( $wp_customize );
        $this->register_typography_sections( $wp_customize );
        $this->register_form_elements_section( $wp_customize );
        $this->register_buttons_section( $wp_customize );
        $this->register_spacing_section( $wp_customize );
        $this->register_borders_section( $wp_customize );
    } catch ( Exception $e ) {
        // Log error if WP_DEBUG is enabled
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( 'Fluid Checkout Customizer Error: ' . $e->getMessage() );
        }
    }
}
```

**What it does**:
- Checks dependencies before proceeding
- Validates `$wp_customize` parameter type
- Wraps section registration in try-catch block
- Logs errors when WP_DEBUG is enabled
- Prevents fatal errors from propagating

---

#### output_customizer_css() Method

**Location**: Lines 745-805  
**Purpose**: Safe CSS output with method existence checks

```php
public function output_customizer_css() {
    // Early return if dependencies not met
    if ( ! $this->check_dependencies() ) {
        return;
    }
    
    // Only output on checkout pages or if we're in customizer preview
    if ( ! $this->is_checkout_page() && ! is_customize_preview() ) {
        return;
    }
    
    // Verify required functions exist
    if ( ! function_exists( 'is_customize_preview' ) ) {
        return;
    }

    try {
        echo '<style type="text/css" id="blocksy-fluid-checkout-customizer-css">';
        echo '/* Blocksy Child Fluid Checkout - Customizer Styles */';

        // General Colors
        if ( method_exists( $this, 'output_color_css_variables' ) ) {
            $this->output_color_css_variables();
        }

        // Typography
        if ( method_exists( $this, 'output_typography_css' ) ) {
            $this->output_typography_css();
        }

        // ... more method calls with existence checks ...

        echo '</style>';
    } catch ( Exception $e ) {
        // Log error if WP_DEBUG is enabled
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( 'Fluid Checkout Customizer CSS Output Error: ' . $e->getMessage() );
        }
    }
}
```

**What it does**:
- Checks dependencies before outputting CSS
- Verifies page context (checkout or customizer preview)
- Checks function existence (`is_customize_preview`)
- Verifies method existence before calling each CSS output method
- Wraps entire output in try-catch block
- Logs errors when WP_DEBUG is enabled

---

#### enqueue_preview_scripts() Method

**Location**: Lines 1047-1084  
**Purpose**: Safe script enqueuing with file verification

```php
public function enqueue_preview_scripts() {
    // Early return if dependencies not met
    if ( ! $this->check_dependencies() ) {
        return;
    }
    
    // Verify required WordPress functions exist
    if ( ! function_exists( 'wp_enqueue_script' ) || 
         ! function_exists( 'get_stylesheet_directory' ) || 
         ! function_exists( 'get_stylesheet_directory_uri' ) ) {
        return;
    }
    
    // Enqueue customizer preview script if it exists
    $preview_script_path = get_stylesheet_directory() . '/assets/js/fluid-checkout-customizer-preview.js';
    $preview_script_url = get_stylesheet_directory_uri() . '/assets/js/fluid-checkout-customizer-preview.js';
    
    // Verify file exists and is readable
    if ( file_exists( $preview_script_path ) && is_readable( $preview_script_path ) ) {
        wp_enqueue_script(
            'blocksy-fluid-checkout-customizer-preview',
            $preview_script_url,
            array( 'jquery', 'customize-preview' ),
            filemtime( $preview_script_path ), // Use file modification time for cache busting
            true
        );
    } elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        // Log warning if script file is missing
        error_log( 'Fluid Checkout Customizer: Preview script not found at ' . $preview_script_path );
    }
}
```

**What it does**:
- Checks dependencies before enqueuing
- Verifies WordPress functions exist
- Checks file existence and readability
- Uses `filemtime()` for cache busting
- Logs warning if script file is missing

---

#### Class Initialization

**Location**: Lines 1082-1084  
**Purpose**: Conditional class instantiation

```php
// Initialize the customizer integration only if dependencies are met
if ( class_exists( 'FluidCheckout' ) && class_exists( 'WP_Customize_Manager' ) ) {
    new Blocksy_Child_Fluid_Checkout_Customizer();
}
```

**What it does**:
- Only instantiates class if both required classes exist
- Prevents instantiation when dependencies are missing
- Final safety check before initialization

---

### 2. fluid-checkout-customizer-preview.js

#### Dependency Check Function

**Location**: Lines 14-38  
**Purpose**: Verify JavaScript dependencies before execution

```javascript
function checkDependencies() {
    // Check for WordPress Customizer API
    if (typeof wp === 'undefined' || typeof wp.customize === 'undefined') {
        console.warn('Fluid Checkout Customizer: WordPress Customizer API not available');
        return false;
    }

    // Check for jQuery
    if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
        console.warn('Fluid Checkout Customizer: jQuery not available');
        return false;
    }

    // Check for document.documentElement
    if (!document.documentElement) {
        console.warn('Fluid Checkout Customizer: document.documentElement not available');
        return false;
    }

    return true;
}
```

**What it checks**:
- WordPress Customizer API (`wp.customize`)
- jQuery availability
- `document.documentElement` existence
- Logs warnings to console when dependencies missing

---

#### Safe CSS Setter Function

**Location**: Lines 40-52  
**Purpose**: Safely set CSS properties with error handling

```javascript
function safeSetCSS(selector, property, value) {
    try {
        const elements = $(selector);
        if (elements.length > 0) {
            elements.css(property, value);
        }
    } catch (error) {
        console.warn('Fluid Checkout Customizer: Error setting CSS for ' + selector, error);
    }
}
```

**What it does**:
- Wraps jQuery operations in try-catch
- Checks if elements exist before applying CSS
- Logs warnings when errors occur
- Prevents script from breaking on missing elements

---

#### Safe CSS Variable Setter

**Location**: Lines 54-66  
**Purpose**: Safely set CSS custom properties

```javascript
function safeSetCSSVariable(variable, value) {
    try {
        if (document.documentElement && document.documentElement.style) {
            document.documentElement.style.setProperty(variable, value);
        }
    } catch (error) {
        console.warn('Fluid Checkout Customizer: Error setting CSS variable ' + variable, error);
    }
}
```

**What it does**:
- Verifies `document.documentElement` and `style` exist
- Wraps `setProperty` in try-catch
- Logs warnings on errors
- Prevents script errors from breaking customizer

---

#### Early Return Pattern

**Location**: Lines 68-71  
**Purpose**: Exit if dependencies not met

```javascript
// Early return if dependencies not met
if (!checkDependencies()) {
    return;
}
```

**What it does**:
- Calls `checkDependencies()` before any customizer code
- Returns early if dependencies missing
- Prevents execution of customizer bindings

---

#### Error-Wrapped Customizer Bindings

**Location**: Throughout file  
**Purpose**: Wrap all customizer bindings in try-catch blocks

```javascript
// Example: Color settings
Object.keys(colorSettings).forEach(function (setting) {
    try {
        wp.customize(setting, function (value) {
            value.bind(function (newval) {
                safeSetCSSVariable(colorSettings[setting], newval);
            });
        });
    } catch (error) {
        console.warn('Fluid Checkout Customizer: Error binding color setting ' + setting, error);
    }
});
```

**What it does**:
- Wraps each customizer binding in try-catch
- Uses `safeSetCSSVariable` instead of direct DOM manipulation
- Logs specific errors for each setting
- Allows other settings to continue working if one fails

---

#### Safe Dynamic Style Updates

**Location**: Lines 358-371  
**Purpose**: Safely create and update dynamic style elements

```javascript
function updateDynamicStyle(id, css) {
    try {
        if (!document.head) {
            console.warn('Fluid Checkout Customizer: document.head not available');
            return;
        }

        let styleElement = document.getElementById('fc-customizer-dynamic-' + id);
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = 'fc-customizer-dynamic-' + id;
            document.head.appendChild(styleElement);
        }
        styleElement.textContent = css;
    } catch (error) {
        console.warn('Fluid Checkout Customizer: Error updating dynamic style ' + id, error);
    }
}
```

**What it does**:
- Checks `document.head` existence
- Wraps DOM manipulation in try-catch
- Creates style element only if it doesn't exist
- Logs errors when style updates fail

---

### 3. functions.php

#### Conditional File Inclusion

**Location**: Lines 109-114  
**Purpose**: Only load customizer if Fluid Checkout is active

```php
// Conditionally load Fluid Checkout Customizer only if Fluid Checkout is active
// This prevents fatal errors if the Fluid Checkout plugin is deactivated
if ( class_exists( 'FluidCheckout' ) ) {
    $required_files[] = '/includes/customization/fluid-checkout-customizer.php';
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    error_log( 'BlazeCommerce: Fluid Checkout Customizer not loaded - FluidCheckout class not found. Please ensure Fluid Checkout Lite or Pro is installed and activated.' );
}
```

**What it does**:
- Checks for `FluidCheckout` class before adding file to load queue
- Only includes customizer file if dependency is met
- Logs informative message when dependency is missing
- Prevents file from being loaded when it can't function

---

#### Enhanced Error Handling in File Loading

**Location**: Lines 130-143  
**Purpose**: Catch both Error and Exception types

```php
foreach ( $required_files as $file ) {
    $file_path = BLAZE_BLOCKSY_PATH . $file;
    if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
        try {
            require_once $file_path;
        } catch ( Error $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
                error_log( 'BlazeCommerce: Failed to load ' . $file . ': ' . $e->getMessage() );
            }
        } catch ( Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
                error_log( 'BlazeCommerce: Exception loading ' . $file . ': ' . $e->getMessage() );
            }
        }
    } elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( 'BlazeCommerce: File not found: ' . $file_path );
    }
}
```

**What it does**:
- Catches both `Error` (PHP 7+) and `Exception` types
- Logs detailed error messages when WP_DEBUG is enabled
- Continues loading other files even if one fails
- Logs when files are not found

---

## Testing Safety Checks

### How to Test Dependency Checks

1. **Deactivate Fluid Checkout Plugin**:
   - Go to WordPress admin → Plugins
   - Deactivate Fluid Checkout Lite or Pro
   - Verify admin notice appears
   - Verify no fatal errors occur
   - Check error log for informative message

2. **Check Customizer Access**:
   - Navigate to Appearance → Customize
   - Verify "Fluid Checkout Styling" panel does not appear
   - Verify no JavaScript errors in console

3. **Check Frontend**:
   - Visit checkout page
   - Verify no custom CSS is output
   - Verify no JavaScript errors in console

4. **Reactivate Plugin**:
   - Reactivate Fluid Checkout
   - Verify customizer panel reappears
   - Verify all functionality works normally

---

## Error Logging

All safety checks log errors when `WP_DEBUG` and `WP_DEBUG_LOG` are enabled:

```php
if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    error_log( 'Error message here' );
}
```

**To enable error logging**, add to `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

**Log file location**: `wp-content/debug.log`

---

## Summary of Safety Measures

### PHP Safety Checks

| Check Type | Location | Purpose |
|------------|----------|---------|
| Class existence | Top of file | Prevent class definition if dependency missing |
| Admin notice | Top of file | Inform admins of missing dependency |
| Constructor check | `__construct()` | Prevent hook registration if dependency missing |
| Method check | All public methods | Verify dependencies before execution |
| Function existence | Various methods | Check WordPress functions exist |
| File existence | `enqueue_preview_scripts()` | Verify script file exists before enqueuing |
| Try-catch blocks | Critical methods | Catch and log exceptions |
| Conditional loading | `functions.php` | Only load file if dependency met |

### JavaScript Safety Checks

| Check Type | Location | Purpose |
|------------|----------|---------|
| Dependency check | Top of file | Verify wp.customize and jQuery exist |
| Early return | After dependency check | Exit if dependencies missing |
| Safe CSS setter | Helper function | Wrap jQuery operations in try-catch |
| Safe variable setter | Helper function | Wrap CSS variable operations in try-catch |
| Try-catch blocks | All bindings | Catch errors in customizer bindings |
| DOM checks | Dynamic style function | Verify document.head exists |
| Element checks | Safe CSS setter | Verify elements exist before manipulation |

---

## Best Practices Followed

1. ✅ **Early Return Pattern**: Check dependencies at the start of functions
2. ✅ **Defensive Programming**: Verify existence before using classes/functions/methods
3. ✅ **Graceful Degradation**: Fail silently rather than breaking the site
4. ✅ **Clear Communication**: Log informative messages when dependencies missing
5. ✅ **Error Handling**: Use try-catch blocks for critical operations
6. ✅ **WordPress Standards**: Follow WordPress coding standards
7. ✅ **User Experience**: Show admin notices to users who can fix the issue
8. ✅ **Developer Experience**: Log detailed errors for debugging

---

## Maintenance Notes

### When Adding New Features

1. Always check dependencies at the start of new methods
2. Wrap critical operations in try-catch blocks
3. Use `method_exists()` before calling new methods
4. Use `function_exists()` before calling WordPress functions
5. Log errors when WP_DEBUG is enabled

### When Updating

1. Test with Fluid Checkout deactivated
2. Verify admin notices appear correctly
3. Check error logs for informative messages
4. Ensure no fatal errors occur
5. Test customizer functionality after reactivation

---

**Document Version**: 2.0.0  
**Last Updated**: 2024-11-08  
**Maintained By**: Augment Agent

