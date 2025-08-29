# Dokumentasi Teknis: Menambahkan Field URL pada Mini Cart Blocksy via Plugin

## Gambaran Umum

Dokumentasi ini menjelaskan cara menambahkan field URL kustom pada pengaturan mini cart di theme Blocksy WordPress melalui **plugin terpisah**, yang memungkinkan user untuk memasukkan URL tertentu yang akan ditampilkan di mini cart tanpa memodifikasi theme secara langsung.

## Keuntungan Implementasi via Plugin

1. **Theme Independence** - Tidak terpengaruh update theme
2. **Maintainability** - Mudah dikelola dan di-update
3. **Portability** - Dapat dipindah ke theme lain
4. **Clean Code** - Tidak mengotori theme files

## Struktur Plugin

### 1. File Utama Plugin

- **Path**: `wp-content/plugins/blocksy-mini-cart-url/blocksy-mini-cart-url.php`
- **Fungsi**: File utama plugin dengan header dan inisialisasi

### 2. File Class Handler

- **Path**: `wp-content/plugins/blocksy-mini-cart-url/includes/class-mini-cart-url.php`
- **Fungsi**: Class utama untuk menangani semua functionality

### 3. File Assets (Optional)

- **Path**: `wp-content/plugins/blocksy-mini-cart-url/assets/`
- **Fungsi**: CSS dan JS tambahan jika diperlukan

## Langkah-Langkah Implementasi via Plugin

### Langkah 1: Membuat File Utama Plugin

Buat file `wp-content/plugins/blocksy-mini-cart-url/blocksy-mini-cart-url.php`:

```php
<?php
/**
 * Plugin Name: Blocksy Mini Cart URL
 * Description: Menambahkan field URL custom pada mini cart Blocksy
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: blocksy-mini-cart-url
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BMCU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BMCU_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BMCU_VERSION', '1.0.0');

// Check if Blocksy theme is active
function bmcu_check_blocksy_theme() {
    $theme = wp_get_theme();
    return ($theme->get('Name') === 'Blocksy' || $theme->get('Template') === 'blocksy');
}

// Initialize plugin
function bmcu_init() {
    if (!bmcu_check_blocksy_theme()) {
        add_action('admin_notices', 'bmcu_blocksy_required_notice');
        return;
    }

    // Load main class
    require_once BMCU_PLUGIN_PATH . 'includes/class-mini-cart-url.php';
    new BMCU_Mini_Cart_URL();
}
add_action('plugins_loaded', 'bmcu_init');

// Admin notice if Blocksy is not active
function bmcu_blocksy_required_notice() {
    echo '<div class="notice notice-error"><p>';
    echo __('Blocksy Mini Cart URL plugin requires Blocksy theme to be active.', 'blocksy-mini-cart-url');
    echo '</p></div>';
}
```

### Langkah 2: Membuat Class Handler Utama

Buat file `wp-content/plugins/blocksy-mini-cart-url/includes/class-mini-cart-url.php`:

```php
<?php
/**
 * Main class for Blocksy Mini Cart URL functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class BMCU_Mini_Cart_URL {

    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    public function init() {
        // Hook into Blocksy's option system
        add_filter('blocksy:options:retrieve', array($this, 'add_cart_options'), 10, 3);

        // Hook into mini cart rendering
        add_action('woocommerce_widget_shopping_cart_before_totals', array($this, 'render_custom_url_before_total'));
        add_action('woocommerce_widget_shopping_cart_after_buttons', array($this, 'render_custom_url_after_buttons'));
        add_action('woocommerce_after_mini_cart', array($this, 'render_custom_url_bottom'));

        // Add CSS for styling
        add_action('wp_head', array($this, 'add_custom_styles'));

        // Add customizer live preview support
        add_action('customize_preview_init', array($this, 'customize_preview_js'));
    }
```

### Langkah 3: Menambahkan Options ke Blocksy Customizer

Lanjutkan class handler dengan method untuk menambahkan options:

```php
    /**
     * Add custom options to Blocksy cart options
     */
    public function add_cart_options($options, $path, $pass_inside) {
        // Check if this is the cart options file
        if (strpos($path, 'panel-builder/header/cart/options.php') === false) {
            return $options;
        }

        // Find the right place to insert our options
        $custom_options = array(
            'bmcu_divider' => array(
                'type' => 'ct-divider',
            ),

            'bmcu_section_title' => array(
                'type' => 'ct-title',
                'label' => __('Custom URL Settings', 'blocksy-mini-cart-url'),
            ),

            'mini_cart_custom_url' => array(
                'label' => __('Custom URL', 'blocksy-mini-cart-url'),
                'type' => 'text',
                'value' => '',
                'design' => 'block',
                'desc' => __('Enter a custom URL to display in the mini cart.', 'blocksy-mini-cart-url'),
                'setting' => array('transport' => 'postMessage'),
            ),

            'mini_cart_custom_url_text' => array(
                'label' => __('Custom URL Text', 'blocksy-mini-cart-url'),
                'type' => 'text',
                'value' => __('View More', 'blocksy-mini-cart-url'),
                'design' => 'block',
                'desc' => __('Text to display for the custom URL link.', 'blocksy-mini-cart-url'),
                'setting' => array('transport' => 'postMessage'),
            ),

            'mini_cart_custom_url_position' => array(
                'label' => __('URL Position', 'blocksy-mini-cart-url'),
                'type' => 'ct-radio',
                'value' => 'after_buttons',
                'view' => 'text',
                'design' => 'block',
                'choices' => array(
                    'before_total' => __('Before Total', 'blocksy-mini-cart-url'),
                    'after_buttons' => __('After Buttons', 'blocksy-mini-cart-url'),
                    'bottom' => __('Bottom', 'blocksy-mini-cart-url'),
                ),
                'setting' => array('transport' => 'postMessage'),
            ),

            'mini_cart_custom_url_color' => array(
                'label' => __('Custom URL Color', 'blocksy-mini-cart-url'),
                'type' => 'ct-color-picker',
                'design' => 'block:right',
                'responsive' => true,
                'setting' => array('transport' => 'postMessage'),
                'value' => array(
                    'default' => array(
                        'color' => 'var(--theme-link-initial-color)',
                    ),
                    'hover' => array(
                        'color' => 'var(--theme-link-hover-color)',
                    ),
                ),
                'pickers' => array(
                    array(
                        'title' => __('Initial', 'blocksy-mini-cart-url'),
                        'id' => 'default',
                    ),
                    array(
                        'title' => __('Hover', 'blocksy-mini-cart-url'),
                        'id' => 'hover',
                    ),
                ),
            ),
        );

        // Merge our options with existing options
        return array_merge($options, $custom_options);
    }
```

### Langkah 4: Menambahkan Method untuk Rendering URL

Lanjutkan class handler dengan method untuk merender URL custom:

```php
    /**
     * Get custom URL data from theme options
     */
    private function get_custom_url_data() {
        // Get cart options from Blocksy
        if (class_exists('Blocksy_Header_Builder_Render')) {
            $header = new Blocksy_Header_Builder_Render();
            $atts = $header->get_item_data_for('cart');
        } else {
            $atts = array();
        }

        $custom_url = blocksy_akg('mini_cart_custom_url', $atts, '');
        $custom_url_text = blocksy_akg('mini_cart_custom_url_text', $atts, __('View More', 'blocksy-mini-cart-url'));
        $custom_url_position = blocksy_akg('mini_cart_custom_url_position', $atts, 'after_buttons');

        if (empty($custom_url)) {
            return false;
        }

        return array(
            'url' => $custom_url,
            'text' => $custom_url_text,
            'position' => $custom_url_position
        );
    }

    /**
     * Render custom URL HTML
     */
    private function render_custom_url_html() {
        $url_data = $this->get_custom_url_data();

        if (!$url_data) {
            return;
        }

        echo '<div class="bmcu-custom-url-wrapper">';
        echo '<a href="' . esc_url($url_data['url']) . '" class="bmcu-custom-url" target="_blank">';
        echo esc_html($url_data['text']);
        echo '</a>';
        echo '</div>';
    }

    /**
     * Render URL before total
     */
    public function render_custom_url_before_total() {
        $url_data = $this->get_custom_url_data();
        if ($url_data && $url_data['position'] === 'before_total') {
            $this->render_custom_url_html();
        }
    }

    /**
     * Render URL after buttons
     */
    public function render_custom_url_after_buttons() {
        $url_data = $this->get_custom_url_data();
        if ($url_data && $url_data['position'] === 'after_buttons') {
            $this->render_custom_url_html();
        }
    }

    /**
     * Render URL at bottom
     */
    public function render_custom_url_bottom() {
        $url_data = $this->get_custom_url_data();
        if ($url_data && $url_data['position'] === 'bottom') {
            $this->render_custom_url_html();
        }
    }
```

### Langkah 5: Menambahkan CSS Styling

Lanjutkan class handler dengan method untuk styling:

```php
    /**
     * Add custom CSS styles
     */
    public function add_custom_styles() {
        // Get cart options from Blocksy
        if (class_exists('Blocksy_Header_Builder_Render')) {
            $header = new Blocksy_Header_Builder_Render();
            $atts = $header->get_item_data_for('cart');
        } else {
            $atts = array();
        }

        $url_data = $this->get_custom_url_data();
        if (!$url_data) {
            return;
        }

        $color_settings = blocksy_akg('mini_cart_custom_url_color', $atts, array());
        $default_color = isset($color_settings['default']['color']) ? $color_settings['default']['color'] : 'var(--theme-link-initial-color)';
        $hover_color = isset($color_settings['hover']['color']) ? $color_settings['hover']['color'] : 'var(--theme-link-hover-color)';

        ?>
        <style id="bmcu-custom-styles">
        .bmcu-custom-url-wrapper {
            margin: 10px 0;
            text-align: center;
        }

        .bmcu-custom-url {
            color: <?php echo esc_attr($default_color); ?>;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            padding: 8px 16px;
            border: 1px solid currentColor;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .bmcu-custom-url:hover {
            color: <?php echo esc_attr($hover_color); ?>;
            text-decoration: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .bmcu-custom-url-wrapper {
                margin: 8px 0;
            }

            .bmcu-custom-url {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
        </style>
        <?php
    }

    /**
     * Add customizer preview JavaScript
     */
    public function customize_preview_js() {
        wp_enqueue_script(
            'bmcu-customize-preview',
            BMCU_PLUGIN_URL . 'assets/customize-preview.js',
            array('jquery', 'customize-preview'),
            BMCU_VERSION,
            true
        );
    }

} // End of class
```

### Langkah 6: Membuat File JavaScript untuk Live Preview (Optional)

Buat file `wp-content/plugins/blocksy-mini-cart-url/assets/customize-preview.js`:

```javascript
/**
 * Customizer live preview for Blocksy Mini Cart URL
 */
(function ($) {
  "use strict";

  // Live preview for custom URL
  wp.customize("mini_cart_custom_url", function (value) {
    value.bind(function (newval) {
      if (newval) {
        $(".bmcu-custom-url").attr("href", newval);
        $(".bmcu-custom-url-wrapper").show();
      } else {
        $(".bmcu-custom-url-wrapper").hide();
      }
    });
  });

  // Live preview for custom URL text
  wp.customize("mini_cart_custom_url_text", function (value) {
    value.bind(function (newval) {
      $(".bmcu-custom-url").text(newval || "View More");
    });
  });

  // Live preview for URL position
  wp.customize("mini_cart_custom_url_position", function (value) {
    value.bind(function (newval) {
      // This would require more complex logic to move the element
      // For now, just trigger a refresh
      wp.customize.preview.send("refresh");
    });
  });

  // Live preview for colors
  wp.customize("mini_cart_custom_url_color", function (value) {
    value.bind(function (newval) {
      var defaultColor = newval.default
        ? newval.default.color
        : "var(--theme-link-initial-color)";
      var hoverColor = newval.hover
        ? newval.hover.color
        : "var(--theme-link-hover-color)";

      // Update CSS
      var style = '<style id="bmcu-preview-styles">';
      style += ".bmcu-custom-url { color: " + defaultColor + "; }";
      style += ".bmcu-custom-url:hover { color: " + hoverColor + "; }";
      style += "</style>";

      $("#bmcu-preview-styles").remove();
      $("head").append(style);
    });
  });
})(jQuery);
```

## Sistem Option Management Blocksy

### Fungsi Utama untuk Mengambil Options

1. **`blocksy_akg($key, $array, $default)`**

   - Mengambil nilai dari array dengan path key
   - Contoh: `blocksy_akg('mini_cart_custom_url', $atts, '')`

2. **`blocksy_default_akg($key, $array, $default)`**

   - Alias untuk blocksy_akg dengan default value

3. **`blocksy_get_theme_mod($name, $default)`**
   - Mengambil theme modification dari database
   - Digunakan untuk pengaturan customizer

### Struktur Data Options

Options disimpan dalam array dengan struktur:

```php
$options = [
    'option_key' => [
        'label' => 'Label Text',
        'type' => 'field_type',
        'value' => 'default_value',
        'setting' => ['transport' => 'postMessage'],
        // ... other properties
    ]
];
```

### Tipe Field yang Tersedia

- `text` - Input text biasa
- `ct-color-picker` - Color picker
- `ct-radio` - Radio buttons
- `ct-switch` - Toggle switch
- `ct-slider` - Slider input
- `ct-select` - Dropdown select

## Cara Menggunakan Plugin

### 1. Instalasi Plugin

1. Upload folder `blocksy-mini-cart-url` ke direktori `wp-content/plugins/`
2. Aktifkan plugin melalui WordPress admin
3. Pastikan theme Blocksy sudah aktif

### 2. Konfigurasi

1. Buka **WordPress Customizer**
2. Navigate ke **Header → Cart**
3. Scroll ke bagian **"Custom URL Settings"**
4. Isi field yang tersedia:
   - **Custom URL**: URL yang ingin ditampilkan
   - **Custom URL Text**: Text untuk link
   - **URL Position**: Posisi tampilan di mini cart
   - **Custom URL Color**: Warna link (initial & hover)

### 3. Preview dan Publish

1. Gunakan live preview untuk melihat perubahan
2. Klik **Publish** untuk menyimpan pengaturan

## Hook dan Filter yang Digunakan

### WordPress Hooks

- `plugins_loaded` - Inisialisasi plugin
- `init` - Setup functionality
- `wp_head` - Menambahkan CSS custom
- `customize_preview_init` - Live preview support

### WooCommerce Hooks

- `woocommerce_widget_shopping_cart_before_totals` - Sebelum total
- `woocommerce_widget_shopping_cart_after_buttons` - Setelah tombol
- `woocommerce_after_mini_cart` - Di bagian bawah

### Blocksy Hooks

- `blocksy:options:retrieve` - Menambahkan options ke customizer

## Sistem Option Management via Plugin

### Mengakses Blocksy Options

```php
// Mendapatkan cart options
if (class_exists('Blocksy_Header_Builder_Render')) {
    $header = new Blocksy_Header_Builder_Render();
    $atts = $header->get_item_data_for('cart');
}

// Mengambil nilai option
$custom_url = blocksy_akg('mini_cart_custom_url', $atts, '');
```

### Menambahkan Options ke Blocksy

```php
// Hook ke sistem option Blocksy
add_filter('blocksy:options:retrieve', array($this, 'add_cart_options'), 10, 3);

// Method untuk menambahkan options
public function add_cart_options($options, $path, $pass_inside) {
    if (strpos($path, 'panel-builder/header/cart/options.php') === false) {
        return $options;
    }

    // Tambahkan options custom
    return array_merge($options, $custom_options);
}
```

## CSS Classes dan Styling

### Classes yang Ditambahkan

- `.ct-custom-url-wrapper` - Container untuk URL custom
- `.ct-custom-url` - Link URL custom
- `.woocommerce-mini-cart .ct-custom-url` - Selector spesifik untuk styling

### CSS Variables yang Digunakan

- `--theme-link-initial-color` - Warna link initial
- `--theme-link-hover-color` - Warna link hover

### Classes yang Ditambahkan

- `.bmcu-custom-url-wrapper` - Container untuk URL custom
- `.bmcu-custom-url` - Link URL custom

### CSS Variables yang Digunakan

- `--theme-link-initial-color` - Warna link initial
- `--theme-link-hover-color` - Warna link hover

## Testing dan Debugging

### Cara Testing

1. Buka WordPress Customizer
2. Navigate ke **Header → Cart**
3. Scroll ke bagian **"Custom URL Settings"**
4. Masukkan URL dan text
5. Pilih posisi tampilan
6. Preview perubahan di frontend

### Debug Functions

```php
// Untuk debug nilai option
if (class_exists('Blocksy_Header_Builder_Render')) {
    $header = new Blocksy_Header_Builder_Render();
    $atts = $header->get_item_data_for('cart');
    error_log('Cart options: ' . print_r($atts, true));
}

// Untuk debug URL custom
$url_data = $this->get_custom_url_data();
error_log('Custom URL data: ' . print_r($url_data, true));
```

## Keuntungan Implementasi via Plugin

### 1. Theme Independence

- Plugin tidak terpengaruh update theme
- Functionality tetap berjalan meski ganti theme (selama masih Blocksy)

### 2. Maintainability

- Mudah di-update dan dikelola
- Kode terorganisir dalam plugin terpisah

### 3. Reusability

- Dapat digunakan di multiple site
- Mudah didistribusikan

### 4. Clean Architecture

- Tidak mengotori theme files
- Menggunakan WordPress hooks dan filters

## Troubleshooting

### Plugin Tidak Muncul di Customizer

1. Pastikan theme Blocksy aktif
2. Check apakah plugin sudah diaktifkan
3. Verify path file plugin benar

### URL Tidak Muncul di Mini Cart

1. Check apakah URL sudah diisi di customizer
2. Verify posisi tampilan sudah dipilih
3. Clear cache jika menggunakan caching plugin

### Live Preview Tidak Berfungsi

1. Pastikan JavaScript file ter-load
2. Check console browser untuk error
3. Verify transport setting menggunakan 'postMessage'

## Ekstensi Lanjutan

### Menambahkan Icon Support

```php
'mini_cart_custom_url_icon' => array(
    'label' => __('Custom URL Icon', 'blocksy-mini-cart-url'),
    'type' => 'ct-icon-picker',
    'value' => '',
    'design' => 'inline',
),
```

### Conditional Display

```php
'mini_cart_custom_url_condition' => array(
    'label' => __('Show URL When', 'blocksy-mini-cart-url'),
    'type' => 'ct-radio',
    'value' => 'always',
    'choices' => array(
        'always' => __('Always', 'blocksy-mini-cart-url'),
        'has_items' => __('Cart Has Items', 'blocksy-mini-cart-url'),
        'empty_cart' => __('Cart Is Empty', 'blocksy-mini-cart-url'),
    ),
),
```

### Multiple URL Support

```php
'mini_cart_custom_urls' => array(
    'label' => __('Custom URLs', 'blocksy-mini-cart-url'),
    'type' => 'ct-layers',
    'value' => array(),
    'settings' => array(
        'url' => array(
            'label' => __('URL', 'blocksy-mini-cart-url'),
            'type' => 'text',
        ),
        'text' => array(
            'label' => __('Text', 'blocksy-mini-cart-url'),
            'type' => 'text',
        ),
    ),
),
```

## Kesimpulan

Dokumentasi ini memberikan panduan lengkap untuk implementasi field URL custom pada mini cart Blocksy melalui **plugin terpisah**. Pendekatan ini lebih maintainable dan professional dibandingkan memodifikasi theme secara langsung.

**Keuntungan utama:**

- ✅ Theme independence
- ✅ Easy maintenance
- ✅ Professional approach
- ✅ Extensible architecture
- ✅ WordPress best practices
