# WooCommerce Mix and Match Products - Customizer Technical Documentation

## Table of Contents
1. [Getting "Number of Columns" Value from Customizer](#getting-number-of-columns-value)
2. [Adding New Fields to Mix and Match Products Customizer](#adding-new-customizer-fields)
3. [Available Control Types](#available-control-types)
4. [Code Examples](#code-examples)
5. [Best Practices](#best-practices)

---

## Getting "Number of Columns" Value from Customizer

### Overview
The "Number of Columns" setting controls how many products are displayed per row in the grid layout for Mix and Match products. This setting is stored as a WordPress option and can be retrieved using standard WordPress functions.

### Method 1: Using get_option() Function

```php
// Get the number of columns with default fallback
$columns = get_option( 'wc_mnm_number_columns', 3 );

// Convert to integer for safety
$columns = (int) $columns;
```

### Method 2: Using the Filter Hook

```php
// Get columns with filter applied (recommended approach)
$default_columns = get_option( 'wc_mnm_number_columns', 3 );
$columns = (int) apply_filters( 'wc_mnm_grid_layout_columns', $default_columns, $product );
```

### Method 3: In Template Functions

The plugin provides a template function that demonstrates proper usage:

```php
function wc_mnm_template_child_items_wrapper_open( $product ) {
    if ( $product->has_child_items() ) {
        // Get the columns with proper filtering
        $default_columns = get_option( 'wc_mnm_number_columns', 3 );
        $columns = (int) apply_filters( 'wc_mnm_grid_layout_columns', $default_columns, $product );
        
        // Set WooCommerce loop properties
        wc_set_loop_prop( 'loop', 0 );
        wc_set_loop_prop( 'columns', $columns );
    }
}
```

### JavaScript Access

In the frontend, the number of columns is applied via CSS classes and can be accessed through the customizer preview:

```javascript
// Customizer preview JavaScript
wp.customize('wc_mnm_number_columns', function(value) {
    value.bind(function(to) {
        $('.mnm_child_products.grid')
            .removeClass(function(index, css) {
                return (css.match(/columns-\S+/g) || []).join(' ');
            })
            .addClass('columns-' + to);
    });
});
```

---

## Adding New Fields to Mix and Match Products Customizer

### Overview
New customizer fields can be added to the Mix and Match Products section by hooking into the `customize_register` action and following the plugin's established patterns.

### Step 1: Hook into the Customizer

```php
add_action( 'customize_register', 'add_custom_mnm_fields' );

function add_custom_mnm_fields( $wp_customize ) {
    // Ensure the Mix and Match section exists
    if ( ! $wp_customize->get_section( 'wc_mnm' ) ) {
        return;
    }
    
    // Add your custom settings and controls here
}
```

### Step 2: Add Setting and Control

```php
function add_custom_mnm_fields( $wp_customize ) {
    // Example: Adding a custom text field
    $wp_customize->add_setting(
        'wc_mnm_custom_text_field',
        array(
            'default'              => 'Default value',
            'type'                 => 'option',
            'capability'           => 'manage_woocommerce',
            'transport'            => 'refresh', // or 'postMessage' for live preview
            'sanitize_callback'    => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'wc_mnm_custom_text_field',
        array(
            'label'       => __( 'Custom Text Field', 'your-textdomain' ),
            'description' => __( 'Enter your custom text here.', 'your-textdomain' ),
            'section'     => 'wc_mnm',
            'type'        => 'text',
            'settings'    => 'wc_mnm_custom_text_field',
        )
    );
}
```

### Step 3: Using Custom Control Types

For advanced controls, you can use the plugin's custom control types:

```php
function add_custom_mnm_fields( $wp_customize ) {
    // Ensure custom control classes are loaded
    if ( ! class_exists( 'KIA_Customizer_Toggle_Control' ) ) {
        require_once WC_MNM_ABSPATH . 'includes/customizer/controls/kia-customizer-toggle-control/class-kia-customizer-toggle-control.php';
    }
    
    // Register the control type
    $wp_customize->register_control_type( 'KIA_Customizer_Toggle_Control' );
    
    // Add toggle setting
    $wp_customize->add_setting(
        'wc_mnm_custom_toggle',
        array(
            'default'              => 'no',
            'type'                 => 'option',
            'capability'           => 'manage_woocommerce',
            'sanitize_callback'    => 'wc_bool_to_string',
            'sanitize_js_callback' => 'wc_string_to_bool',
        )
    );

    // Add toggle control
    $wp_customize->add_control(
        new KIA_Customizer_Toggle_Control(
            $wp_customize,
            'wc_mnm_custom_toggle',
            array(
                'label'    => __( 'Enable Custom Feature', 'your-textdomain' ),
                'section'  => 'wc_mnm',
                'type'     => 'kia-toggle',
                'settings' => 'wc_mnm_custom_toggle',
            )
        )
    );
}
```

---

## Available Control Types

### 1. Standard WordPress Controls

```php
// Text input
'type' => 'text'

// Textarea
'type' => 'textarea'

// Select dropdown
'type' => 'select'
'choices' => array(
    'option1' => 'Option 1',
    'option2' => 'Option 2',
)

// Checkbox
'type' => 'checkbox'

// Radio buttons
'type' => 'radio'
'choices' => array(
    'option1' => 'Option 1',
    'option2' => 'Option 2',
)

// Number input
'type' => 'number'
'input_attrs' => array(
    'min' => 1,
    'max' => 10,
    'step' => 1,
)
```

### 2. Plugin Custom Controls

#### Toggle Control (KIA_Customizer_Toggle_Control)
```php
new KIA_Customizer_Toggle_Control(
    $wp_customize,
    'setting_id',
    array(
        'label'    => 'Toggle Label',
        'section'  => 'wc_mnm',
        'type'     => 'kia-toggle',
        'settings' => 'setting_id',
    )
)
```

#### Range Control (KIA_Customizer_Range_Control)
```php
new KIA_Customizer_Range_Control(
    $wp_customize,
    'setting_id',
    array(
        'type'        => 'kia-range',
        'label'       => 'Range Label',
        'description' => 'Range description',
        'section'     => 'wc_mnm',
        'settings'    => 'setting_id',
        'input_attrs' => array(
            'min'  => 1,
            'max'  => 10,
            'step' => 1,
        ),
    )
)
```

#### Radio Image Control (KIA_Customizer_Radio_Image_Control)
```php
new KIA_Customizer_Radio_Image_Control(
    $wp_customize,
    'setting_id',
    array(
        'label'    => 'Image Radio Label',
        'section'  => 'wc_mnm',
        'settings' => 'setting_id',
        'choices'  => array(
            'option1' => array(
                'label' => 'Option 1',
                'image' => 'path/to/image1.svg',
            ),
            'option2' => array(
                'label' => 'Option 2',
                'image' => 'path/to/image2.svg',
            ),
        ),
    )
)
```

---

## Code Examples

### Example 1: Adding a Custom Color Field

```php
add_action( 'customize_register', 'add_mnm_color_field' );

function add_mnm_color_field( $wp_customize ) {
    // Add color setting
    $wp_customize->add_setting(
        'wc_mnm_custom_color',
        array(
            'default'           => '#000000',
            'type'              => 'option',
            'capability'        => 'manage_woocommerce',
            'transport'         => 'postMessage',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    // Add color control
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'wc_mnm_custom_color',
            array(
                'label'    => __( 'Custom Color', 'your-textdomain' ),
                'section'  => 'wc_mnm',
                'settings' => 'wc_mnm_custom_color',
            )
        )
    );
}
```

### Example 2: Adding a Custom Range Field

```php
add_action( 'customize_register', 'add_mnm_custom_range' );

function add_mnm_custom_range( $wp_customize ) {
    // Load the range control class
    if ( ! class_exists( 'KIA_Customizer_Range_Control' ) ) {
        require_once WC_MNM_ABSPATH . 'includes/customizer/controls/kia-customizer-range-control/class-kia-customizer-range-control.php';
    }
    
    $wp_customize->register_control_type( 'KIA_Customizer_Range_Control' );

    // Add range setting
    $wp_customize->add_setting(
        'wc_mnm_custom_spacing',
        array(
            'default'              => 20,
            'type'                 => 'option',
            'capability'           => 'manage_woocommerce',
            'transport'            => 'postMessage',
            'sanitize_callback'    => 'absint',
            'sanitize_js_callback' => 'absint',
        )
    );

    // Add range control
    $wp_customize->add_control(
        new KIA_Customizer_Range_Control(
            $wp_customize,
            'wc_mnm_custom_spacing',
            array(
                'type'        => 'kia-range',
                'label'       => __( 'Custom Spacing', 'your-textdomain' ),
                'description' => __( 'Adjust the spacing between elements.', 'your-textdomain' ),
                'section'     => 'wc_mnm',
                'settings'    => 'wc_mnm_custom_spacing',
                'input_attrs' => array(
                    'min'  => 0,
                    'max'  => 50,
                    'step' => 5,
                ),
            )
        )
    );
}
```

### Example 3: Retrieving Custom Field Values

```php
// Get custom field value in your theme or plugin
function get_mnm_custom_color() {
    return get_option( 'wc_mnm_custom_color', '#000000' );
}

function get_mnm_custom_spacing() {
    return (int) get_option( 'wc_mnm_custom_spacing', 20 );
}

// Use in templates
$custom_color = get_mnm_custom_color();
$custom_spacing = get_mnm_custom_spacing();

echo '<div style="color: ' . esc_attr( $custom_color ) . '; margin: ' . esc_attr( $custom_spacing ) . 'px;">';
```

---

## Best Practices

### 1. Setting Configuration
- Always provide a sensible default value
- Use appropriate sanitization callbacks
- Set proper capability requirements (`manage_woocommerce`)
- Choose appropriate transport method (`refresh` vs `postMessage`)

### 2. Control Configuration
- Provide clear, descriptive labels
- Include helpful descriptions when needed
- Use consistent naming conventions (prefix with `wc_mnm_`)
- Group related settings logically

### 3. Security and Validation
```php
// Always sanitize input
'sanitize_callback' => 'sanitize_text_field', // for text
'sanitize_callback' => 'absint',              // for integers
'sanitize_callback' => 'sanitize_hex_color',  // for colors
'sanitize_callback' => 'wc_bool_to_string',   // for booleans
```

### 4. Internationalization
```php
// Always use translation functions
'label' => __( 'Your Label', 'your-textdomain' ),
'description' => __( 'Your description', 'your-textdomain' ),
```

### 5. Conditional Display
```php
// Use active_callback for conditional display
'active_callback' => function() {
    return 'grid' === get_option( 'wc_mnm_layout', 'tabular' );
},
```

### 6. Live Preview Support
For live preview functionality, use `transport => 'postMessage'` and add corresponding JavaScript:

```javascript
wp.customize('your_setting_id', function(value) {
    value.bind(function(to) {
        // Update the preview
        $('.your-element').css('property', to);
    });
});
```

This documentation provides comprehensive guidance for working with the WooCommerce Mix and Match Products customizer system, including retrieving existing values and adding new customizable fields.
