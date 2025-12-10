# Blocksy Theme - Customizer Technical Guide

## ⚠️ CRITICAL CONCEPT: Layer Options vs Design Options

**MUST UNDERSTAND BEFORE PROCEEDING:**

Blocksy separates options into TWO distinct locations:

### 1. Layer Options (Functional/Content Settings)

**Location**: Inside the layer settings panel
**File**: `inc/options/woocommerce/single-product-layers.php`
**Filter**: `blocksy_woo_single_options_layers:extra`
**Purpose**: Define what the layer does and what content it shows

**Contains**:
- ✅ Text content, titles, descriptions
- ✅ Visibility toggles, checkboxes
- ✅ Icon selection
- ✅ Layout choices (style, alignment, position)
- ✅ Spacing (bottom spacing for the layer)
- ❌ **NO design options** (fonts, colors, backgrounds, borders)

### 2. Design Options (Visual/Styling Settings)

**Location**: Design tab in Product Elements panel
**File**: `inc/options/woocommerce/single-product-elements.php`
**Filter**: `blocksy:options:single_product:elements:design_tab:end`
**Purpose**: Define how the layer looks visually

**Contains**:
- ✅ Typography (fonts, sizes, weights, line-height)
- ✅ Colors (text, background, borders, hover states)
- ✅ Backgrounds (solid, gradient, image)
- ✅ Spacing (padding, margin - 4 sides)
- ✅ Borders and border radius
- ❌ **NO functional options** (content, visibility, layout)

### Why This Separation?

1. **User Experience**: Users expect design options in the Design tab
2. **Organization**: Keeps functional and visual settings separate
3. **Consistency**: Matches Blocksy's architecture for all elements
4. **Conditional Display**: Design options only show when layer is enabled

### Example - Product Tabs:

**Layer Options** (in layer panel):
- Tab visibility checkboxes
- Tab style (default, underline, boxed)
- Tab alignment (left, center, right)
- Bottom spacing

**Design Options** (in Design tab):
- Tabs font (typography)
- Tabs color (initial, active)
- Active indicator color
- Background color
- Border and border radius

---

## Table of Contents
1. [Customizer Architecture](#customizer-architecture)
2. [Adding New Toggle Settings](#adding-new-toggle-settings)
3. [Adding New Elements to Product Elements](#adding-new-elements-to-product-elements)
4. [Adding Design Settings with Live Preview](#adding-design-settings-with-live-preview)
5. [File Structure and Conventions](#file-structure-and-conventions)
6. [Hooks and Filters](#hooks-and-filters)

---

## Customizer Architecture

### Main Directory Structure
```
inc/
├── options/                    # Definisi opsi customizer
│   ├── customizer.php         # Entry point customizer
│   └── woocommerce/           # Opsi WooCommerce
│       ├── single-main.php    # Single Product main options
│       ├── single-product-elements.php
│       ├── single-product-layers.php
│       └── single-product-tabs.php
├── customizer/
│   └── init.php               # Inisialisasi customizer
├── dynamic-styles/            # Generator CSS dinamis
│   └── global/
│       └── woocommerce/
│           └── single-product-layers.php
├── components/
│   └── woocommerce/
│       └── single/
│           ├── single.php     # Rendering layer
│           └── helpers.php
static/js/customizer/
├── sync/                      # Live preview sync
│   └── variables/
│       └── woocommerce/
│           └── single-product-layers.js
```

### Customizer Workflow

1. **Option Registration** (`inc/options/woocommerce/single-main.php`)
   - Define panel and option structure
   - Use `blocksy_get_options()` to load sub-options

2. **Frontend Rendering** (`inc/components/woocommerce/single/single.php`)
   - Method `render_layout()` reads layer configuration
   - Calls method based on `layer['id']` or custom hook

3. **Dynamic CSS** (`inc/dynamic-styles/global/woocommerce/single-product-layers.php`)
   - Generate CSS based on option values
   - Use `blocksy_output_responsive()` for responsive values

4. **Live Preview** (`static/js/customizer/sync/variables/woocommerce/single-product-layers.js`)
   - Synchronize changes without reload
   - Update CSS variables in real-time

---

## Adding New Toggle Settings

### Example: Menonaktifkan Product Tabs

#### 1. Tambahkan Opsi di `inc/options/woocommerce/single-product-tabs.php`

This file already exists with structure:
```php
<?php
$options = [
    'woo_has_product_tabs' => [
        'label' => __( 'Product Tabs', 'blocksy' ),
        'type' => 'ct-panel',
        'switch' => true,  // This creates the on/off toggle
        'value' => 'yes',
        'sync' => blocksy_sync_whole_page([
            'prefix' => 'product',
            'loader_selector' => '.type-product'
        ]),
        'inner-options' => [
            // ... other options
        ]
    ]
];
```

**Parameter Explanation:**
- `type: 'ct-panel'` - Membuat panel yang bisa dibuka
- `switch: true` - Menambahkan toggle on/off
- `value: 'yes'` - Default value (yes/no)
- `sync` - Live preview configuration
  - `blocksy_sync_whole_page()` - Refresh entire page
  - `prefix` - Prefix for selective refresh
  - `loader_selector` - Selector for element to refresh

#### 2. Frontend Implementation

Di `inc/components/woocommerce/single/single.php` atau file terkait:

```php
// Check if product tabs are enabled
$has_product_tabs = blocksy_get_theme_mod('woo_has_product_tabs', 'yes');

if ($has_product_tabs === 'yes') {
    // Display product tabs
    woocommerce_output_product_data_tabs();
} else {
    // Hide or skip
}
```

#### 3. Alternative: Using WordPress Filter

```php
// Di functions.php atau file custom
add_filter('woocommerce_product_tabs', function($tabs) {
    $has_product_tabs = blocksy_get_theme_mod('woo_has_product_tabs', 'yes');
    
    if ($has_product_tabs === 'no') {
        return []; // Empty all tabs
    }
    
    return $tabs;
});
```

---

## Adding New Elements to Product Elements

### Example: Menambahkan "Product Tabs" sebagai Layer

#### 1. Define Layer in `inc/options/woocommerce/single-product-layers.php`

Add using filter `blocksy_woo_single_options_layers:extra`:

```php
// In functions.php or custom plugin
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['product_tabs'] = [
        'label' => __('Product Tabs', 'blocksy'),
        'options' => [
            'spacing' => [
                'label' => __('Bottom Spacing', 'blocksy'),
                'type' => 'ct-slider',
                'min' => 0,
                'max' => 100,
                'value' => 10,
                'responsive' => true,
                'sync' => [
                    'id' => 'woo_single_layout_skip'
                ],
            ],
        ],
    ];
    
    return $options;
});
```

#### 2. Add to Default Layout

Di `inc/components/woocommerce/common/layer-defaults.php`:

```php
add_filter('blocksy_woo_single_options_layers:defaults', function($defaults) {
    $defaults[] = [
        'id' => 'product_tabs',
        'enabled' => true,
    ];

    return $defaults;
});
```

#### 3. Implementasi Rendering

Di `inc/components/woocommerce/single/single.php`, tambahkan method atau gunakan hook:

**Opsi A: Tambahkan Method di Class**
```php
public function product_tabs($layer) {
    do_action('blocksy:woocommerce:product-single:tabs:before');

    // Render product tabs
    woocommerce_output_product_data_tabs();

    do_action('blocksy:woocommerce:product-single:tabs:after');
}
```

**Opsi B: Gunakan Hook Custom Layer**
```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'product_tabs') {
        return;
    }

    // Render product tabs
    woocommerce_output_product_data_tabs();
}, 10, 1);
```

#### 4. Tambahkan Dynamic CSS

Di `inc/dynamic-styles/global/woocommerce/single-product-layers.php`:

```php
// File ini sudah ada loop untuk semua layers
// CSS akan otomatis di-generate untuk spacing
// Jika perlu custom CSS, tambahkan kondisi:

if ($layer['id'] === 'product_tabs') {
    $spacing = blocksy_akg('spacing', $layer, 10);

    blocksy_output_responsive([
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.entry-summary-items > .woocommerce-tabs',
        'variableName' => 'product-element-spacing',
        'value' => $spacing
    ]);
}
```

---

## Adding Design Settings with Live Preview

### Example: Styling Product Tabs

#### 1. Tambahkan Opsi Design di `inc/options/woocommerce/single-product-elements.php`

Dalam tab 'Design', tambahkan opsi dengan conditional:

```php
blocksy_rand_md5() => [
    'type' => 'ct-condition',
    'condition' => ['woo_single_layout:array-ids:product_tabs:enabled' => '!no'],
    'computed_fields' => ['woo_single_layout'],
    'options' => [

        'productTabsTitleFont' => [
            'type' => 'ct-typography',
            'label' => __('Title Font', 'blocksy'),
            'value' => blocksy_typography_default_values([
                'size' => '24px',
                'variation' => 'n4',
            ]),
            'setting' => ['transport' => 'postMessage'],
        ],

        'productTabsTitleColor' => [
            'label' => __('Title Font Color', 'blocksy'),
            'type'  => 'ct-color-picker',
            'design' => 'inline',
            'setting' => ['transport' => 'postMessage'],
            'value' => [
                'default' => [
                    'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
                ],
                'hover' => [
                    'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
                ],
            ],
            'pickers' => [
                [
                    'title' => __('Initial', 'blocksy'),
                    'id' => 'default',
                    'inherit' => 'var(--theme-text-color)'
                ],
                [
                    'title' => __('Hover', 'blocksy'),
                    'id' => 'hover',
                    'inherit' => 'var(--theme-link-hover-color)'
                ],
            ],
        ],

    ],
],
```

**Penjelasan Tipe Opsi:**

- **`ct-typography`**: Font settings (size, weight, line-height, dll)
- **`ct-color-picker`**: Color picker dengan support multiple states
- **`ct-slider`**: Slider untuk nilai numerik
- **`ct-spacing`**: Spacing control (top, right, bottom, left)
- **`ct-background`**: Background settings (color, image, gradient)

**Parameter Penting:**
- `setting: ['transport' => 'postMessage']` - Enable live preview
- `design: 'inline'` - Layout inline
- `divider: 'top:full'` - Tambahkan divider
- `responsive: true` - Enable responsive controls

#### 2. Generate Dynamic CSS

Di `inc/dynamic-styles/global/woocommerce/single-product-layers.php`:

```php
// Typography
blocksy_output_font_css([
    'font_value' => blocksy_get_theme_mod(
        'productTabsTitleFont',
        blocksy_typography_default_values([
            'size' => '24px',
            'variation' => 'n4',
        ])
    ),
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '.woocommerce-tabs .tabs li a'
]);

// Colors
blocksy_output_colors([
    'value' => blocksy_get_theme_mod('productTabsTitleColor'),
    'default' => [
        'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
        'hover' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
    ],
    'css' => $css,
    'variables' => [
        'default' => [
            'selector' => '.woocommerce-tabs .tabs li a',
            'variable' => 'theme-link-initial-color'
        ],
        'hover' => [
            'selector' => '.woocommerce-tabs .tabs li a',
            'variable' => 'theme-link-hover-color'
        ],
    ],
]);
```

#### 3. Setup Live Preview Sync

Di `static/js/customizer/sync/variables/woocommerce/single-product-layers.js`:

```javascript
import { typographyOption } from '../typography'

export const getWooSingleLayersVariablesFor = () => ({
    // ... existing code

    // Typography
    ...typographyOption({
        id: 'productTabsTitleFont',
        selector: '.woocommerce-tabs .tabs li a',
    }),

    // Colors
    productTabsTitleColor: [
        {
            selector: '.woocommerce-tabs .tabs li a',
            'variable': 'theme-link-initial-color',
            type: 'color:default',
        },
        {
            selector: '.woocommerce-tabs .tabs li a',
            variable: 'theme-link-hover-color',
            type: 'color:hover',
        },
    ],
})
```

---

## File Structure and Conventions

### Konvensi Penamaan

#### Option IDs
- Gunakan `snake_case` untuk ID opsi
- Prefix dengan konteks: `woo_`, `product_`, `single_`
- Example: `woo_has_product_tabs`, `product_tabs_font`

#### CSS Variables
- Gunakan `kebab-case`
- Prefix dengan `theme-` untuk global variables
- Example: `--theme-text-color`, `--product-element-spacing`

#### Hooks/Filters
- Format: `blocksy:{context}:{action}:{detail}`
- Example: `blocksy:woocommerce:product-single:tabs:before`

### Helper Functions Penting

#### 1. `blocksy_rand_md5()`
Generate unique ID untuk opsi yang tidak perlu ID spesifik (divider, condition, dll)

```php
blocksy_rand_md5() => [
    'type' => 'ct-divider',
],
```

#### 2. `blocksy_get_theme_mod($key, $default)`
Mengambil nilai opsi dari customizer

```php
$has_tabs = blocksy_get_theme_mod('woo_has_product_tabs', 'yes');
```

#### 3. `blocksy_akg($key, $array, $default)`
Array key getter dengan default value

```php
$spacing = blocksy_akg('spacing', $layer, 10);
```

#### 4. `blocksy_sync_whole_page($args)`
Konfigurasi untuk refresh seluruh halaman

```php
'sync' => blocksy_sync_whole_page([
    'prefix' => 'product',
    'loader_selector' => '.type-product'
]),
```

#### 5. `blocksy_output_responsive($args)`
Output CSS responsive

```php
blocksy_output_responsive([
    'css' => $css,
    'tablet_css' => $tablet_css,
    'mobile_css' => $mobile_css,
    'selector' => '.my-element',
    'variableName' => 'my-variable',
    'value' => $value
]);
```

### Customizer Option Types

#### Basic Controls
- `ct-switch` - Toggle on/off
- `ct-radio` - Radio buttons
- `ct-select` - Dropdown select
- `text` - Text input
- `textarea` - Textarea
- `wp-editor` - WordPress editor

#### Advanced Controls
- `ct-slider` - Slider dengan min/max
- `ct-color-picker` - Color picker
- `ct-typography` - Typography control
- `ct-spacing` - Spacing control (4 sides)
- `ct-background` - Background control
- `ct-image-uploader` - Image uploader
- `ct-layers` - Sortable layers (seperti product elements)

#### Layout Controls
- `ct-panel` - Collapsible panel
- `ct-condition` - Conditional display
- `ct-divider` - Visual divider
- `ct-title` - Section title
- `tab` - Tab container

---

## Hooks and Filters

### Filters untuk Menambahkan Opsi

#### 1. Menambahkan Layer Options
```php
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['my_custom_layer'] = [
        'label' => __('My Custom Layer', 'blocksy'),
        'options' => [
            // ... opsi layer
        ],
    ];
    return $options;
});
```

#### 2. Menambahkan Default Layer
```php
add_filter('blocksy_woo_single_options_layers:defaults', function($defaults) {
    $defaults[] = [
        'id' => 'my_custom_layer',
        'enabled' => true,
    ];
    return $defaults;
});
```

#### 3. Modifikasi Layout
```php
add_filter('blocksy:woocommerce:product-single:layout', function($layout) {
    // Modifikasi $layout array
    return $layout;
});
```

### Actions untuk Rendering

#### 1. Custom Layer Rendering
```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'my_custom_layer') {
        return;
    }

    // Render custom layer
    echo '<div class="my-custom-layer">Content</div>';
}, 10, 1);
```

#### 2. Before/After Hooks
```php
// Before layout
add_action('blocksy:woocommerce:product-single:layout:before', function($args) {
    // Code before layout
});

// After layout
add_action('blocksy:woocommerce:product-single:layout:after', function($args) {
    // Code after layout
});
```

#### 3. Element-Specific Hooks
```php
// Before product title
add_action('blocksy:woocommerce:product-single:title:before', function() {
    // Code before title
});

// After product title
add_action('blocksy:woocommerce:product-single:title:after', function() {
    // Code after title
});
```

### Filters untuk Dynamic CSS

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    $tablet_css = $args['tablet_css'];
    $mobile_css = $args['mobile_css'];

    // Add custom CSS
    $css->put(
        '.my-element',
        '--my-variable: ' . blocksy_get_theme_mod('my_option', 'default')
    );
}, 10, 1);
```

---

## Tips and Best Practices

### 1. Naming Conventions
- **Konsisten**: Gunakan prefix yang sama untuk semua opsi terkait
- **Deskriptif**: Nama harus jelas menggambarkan fungsi
- **Hindari konflik**: Gunakan prefix unik untuk custom code

### 2. Performance
- **Lazy Loading**: Hanya load CSS/JS yang diperlukan
- **Caching**: Gunakan transients untuk data yang jarang berubah
- **Conditional Loading**: Check apakah element enabled sebelum generate CSS

### 3. Compatibility
- **Child Theme Safe**: Gunakan hooks/filters, jangan edit core files
- **Plugin Friendly**: Check plugin existence sebelum integrasi
- **Version Check**: Pastikan kompatibilitas dengan versi Blocksy

### 4. Live Preview
- **Transport**: Gunakan `'transport' => 'postMessage'` untuk live preview
- **Selective Refresh**: Gunakan `blocksy_sync_whole_page()` dengan selector spesifik
- **CSS Variables**: Prefer CSS variables untuk perubahan yang sering

### 5. Responsive Design
- **Mobile First**: Set default untuk mobile terlebih dahulu
- **Breakpoints**: Gunakan breakpoints Blocksy (desktop: >999px, tablet: 690-999px, mobile: <690px)
- **Responsive Controls**: Enable `'responsive' => true` untuk opsi yang perlu responsive

### 6. Accessibility
- **Labels**: Selalu sediakan label yang jelas
- **Descriptions**: Tambahkan `desc` untuk opsi yang kompleks
- **Defaults**: Set default values yang masuk akal

### 7. Internationalization
- **Text Domain**: Gunakan `'blocksy'` atau custom text domain
- **Translatable**: Wrap semua string dengan `__()` atau `_e()`
- **Context**: Gunakan `_x()` untuk string dengan konteks

---

## Debugging

### 1. Check Option Values

```php
// Di template atau functions.php
$value = blocksy_get_theme_mod('your_option_id', 'default');
error_log('Option value: ' . print_r($value, true));
```

### 2. Inspect Layout Array

```php
add_action('blocksy:woocommerce:product-single:layout:before', function($args) {
    error_log('Layout: ' . print_r($args['layout'], true));
});
```

### 3. CSS Output

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];

    // Check generated CSS
    error_log('CSS Output: ' . $css->build_css_structure());
}, 999);
```

### 4. Customizer Preview

Buka browser console di customizer preview untuk melihat:
- JavaScript errors
- AJAX requests
- CSS changes

### 5. Enable WordPress Debug

Di `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

## Quick Reference

### Struktur Dasar Layer Option

```php
add_filter('blocksy_woo_single_options_layers:extra', function($options) {
    $options['my_layer'] = [
        'label' => __('My Layer', 'blocksy'),
        'options' => [
            'my_option' => [
                'label' => __('My Option', 'blocksy'),
                'type' => 'ct-switch',
                'value' => 'yes',
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
            'spacing' => [
                'label' => __('Bottom Spacing', 'blocksy'),
                'type' => 'ct-slider',
                'min' => 0,
                'max' => 100,
                'value' => 10,
                'responsive' => true,
                'sync' => ['id' => 'woo_single_layout_skip'],
            ],
        ],
    ];
    return $options;
});
```

### Struktur Dasar Design Option

```php
blocksy_rand_md5() => [
    'type' => 'ct-condition',
    'condition' => ['woo_single_layout:array-ids:my_layer:enabled' => '!no'],
    'computed_fields' => ['woo_single_layout'],
    'options' => [
        'myLayerFont' => [
            'type' => 'ct-typography',
            'label' => __('Font', 'blocksy'),
            'value' => blocksy_typography_default_values([
                'size' => '16px',
            ]),
            'setting' => ['transport' => 'postMessage'],
        ],
        'myLayerColor' => [
            'label' => __('Color', 'blocksy'),
            'type'  => 'ct-color-picker',
            'design' => 'inline',
            'setting' => ['transport' => 'postMessage'],
            'value' => [
                'default' => [
                    'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
                ],
            ],
            'pickers' => [
                [
                    'title' => __('Initial', 'blocksy'),
                    'id' => 'default',
                    'inherit' => 'var(--theme-text-color)'
                ],
            ],
        ],
    ],
],
```

### Struktur Dasar Rendering

```php
add_action('blocksy:woocommerce:product:custom:layer', function($layer) {
    if ($layer['id'] !== 'my_layer') {
        return;
    }

    $my_option = blocksy_akg('my_option', $layer, 'yes');

    if ($my_option === 'no') {
        return;
    }

    ?>
    <div class="my-custom-layer">
        <!-- Your content here -->
    </div>
    <?php
}, 10, 1);
```

### Struktur Dasar Dynamic CSS

```php
add_action('blocksy:global-dynamic-css:enqueue', function($args) {
    $css = $args['css'];
    $tablet_css = $args['tablet_css'];
    $mobile_css = $args['mobile_css'];

    // Typography
    blocksy_output_font_css([
        'font_value' => blocksy_get_theme_mod('myLayerFont'),
        'css' => $css,
        'tablet_css' => $tablet_css,
        'mobile_css' => $mobile_css,
        'selector' => '.my-custom-layer'
    ]);

    // Color
    blocksy_output_colors([
        'value' => blocksy_get_theme_mod('myLayerColor'),
        'default' => [
            'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
        ],
        'css' => $css,
        'variables' => [
            'default' => [
                'selector' => '.my-custom-layer',
                'variable' => 'theme-text-color'
            ],
        ],
    ]);
}, 10, 1);
```

---

## Conclusion

Dokumentasi ini memberikan panduan lengkap untuk:

1. ✅ Menambahkan toggle setting baru (seperti menonaktifkan product tabs)
2. ✅ Menambahkan element baru ke Product Elements (layers)
3. ✅ Menambahkan pengaturan design dengan live preview
4. ✅ Memahami arsitektur customizer Blocksy
5. ✅ Use hooks dan filters yang tepat
6. ✅ Best practices dan debugging

### Next Steps untuk AI Agent

Gunakan dokumentasi ini sebagai referensi untuk:
- Membuat custom layers baru
- Menambahkan opsi customizer
- Mengimplementasikan live preview
- Generate dynamic CSS
- Debugging issues

### Catatan Penting

- **Selalu gunakan child theme** untuk customization
- **Test di environment development** terlebih dahulu
- **Backup sebelum melakukan perubahan** besar
- **Follow WordPress coding standards**
- **Dokumentasikan custom code** Anda

---

**Dibuat**: 2025-11-26
**Versi**: 1.0.0
**Untuk**: Blocksy Theme Customizer Development


