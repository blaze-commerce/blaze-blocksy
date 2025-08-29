# Dokumentasi Teknis: Menampilkan Produk Wishlist Secara Programatis
## Blocksy Companion Pro - WooCommerce Extra Extension

### 1. OVERVIEW SISTEM WISHLIST

Sistem wishlist di Blocksy Companion Pro menggunakan:
- **Storage**: User meta `blc_products_wish_list` untuk user login, cookies untuk guest
- **Class utama**: `\Blocksy\Extensions\WoocommerceExtra\WishList`
- **Namespace**: `Blocksy\Extensions\WoocommerceExtra`
- **File utama**: `framework/premium/extensions/woocommerce-extra/features/wish-list/`

### 2. CARA MENGAKSES INSTANCE WISHLIST

```php
// Mendapatkan instance wishlist
$wishlist_instance = blc_get_ext('woocommerce-extra')->get_wish_list();

// Mendapatkan daftar wishlist user saat ini
$current_wishlist = $wishlist_instance->get_current_wish_list();
```

### 3. METODE UTAMA UNTUK MENAMPILKAN WISHLIST

#### A. Menggunakan Template Bawaan

```php
// Menampilkan tabel wishlist lengkap
echo blocksy_render_view(
    BLOCKSY_PATH . 'framework/premium/extensions/woocommerce-extra/features/wish-list/table.php',
    []
);
```

#### B. Membuat Custom Display

```php
function display_custom_wishlist($user_id = null, $limit = -1) {
    // Mendapatkan instance wishlist
    $wishlist_instance = blc_get_ext('woocommerce-extra')->get_wish_list();
    
    // Mendapatkan wishlist items
    if ($user_id) {
        // Untuk user tertentu
        $wishlist_items = $wishlist_instance->get_user_wish_list($user_id);
    } else {
        // Untuk user saat ini
        $wishlist_items = $wishlist_instance->get_current_wish_list();
    }
    
    if (empty($wishlist_items)) {
        return '<p>Wishlist kosong</p>';
    }
    
    $output = '<div class="custom-wishlist-display">';
    $count = 0;
    
    foreach ($wishlist_items as $item) {
        if ($limit > 0 && $count >= $limit) break;
        
        $product_id = is_object($item) ? $item->id : $item;
        $product = wc_get_product($product_id);
        
        if (!$product || !$product->exists()) continue;
        
        $output .= '<div class="wishlist-item" data-product-id="' . $product_id . '">';
        $output .= '<div class="product-image">';
        $output .= '<a href="' . $product->get_permalink() . '">';
        $output .= $product->get_image('thumbnail');
        $output .= '</a>';
        $output .= '</div>';
        
        $output .= '<div class="product-details">';
        $output .= '<h3><a href="' . $product->get_permalink() . '">' . $product->get_name() . '</a></h3>';
        $output .= '<span class="price">' . $product->get_price_html() . '</span>';
        $output .= '</div>';
        
        $output .= '<div class="product-actions">';
        // Tombol Add to Cart
        if ($product->is_purchasable() && $product->is_in_stock()) {
            $output .= '<a href="' . $product->add_to_cart_url() . '" class="button add-to-cart">Add to Cart</a>';
        }
        // Tombol Remove dari Wishlist
        $output .= '<button class="remove-from-wishlist" data-product-id="' . $product_id . '">Remove</button>';
        $output .= '</div>';
        
        $output .= '</div>';
        $count++;
    }
    
    $output .= '</div>';
    return $output;
}
```

### 4. SHORTCODE CUSTOM

```php
// Mendaftarkan shortcode
add_shortcode('blocksy_wishlist', 'blocksy_wishlist_shortcode');

function blocksy_wishlist_shortcode($atts) {
    $atts = shortcode_atts([
        'limit' => -1,
        'user_id' => null,
        'columns' => 3,
        'show_price' => 'yes',
        'show_add_to_cart' => 'yes',
        'show_remove' => 'yes'
    ], $atts);
    
    return display_custom_wishlist($atts['user_id'], $atts['limit']);
}

// Penggunaan: [blocksy_wishlist limit="6" columns="3"]
```

### 5. WIDGET CUSTOM

```php
class Blocksy_Wishlist_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'blocksy_wishlist_widget',
            'Blocksy Wishlist Widget',
            ['description' => 'Display user wishlist products']
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $limit = !empty($instance['limit']) ? $instance['limit'] : 5;
        echo display_custom_wishlist(null, $limit);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'My Wishlist';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">Number of products:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" 
                   name="<?php echo $this->get_field_name('limit'); ?>" type="number" 
                   value="<?php echo esc_attr($limit); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 5;
        return $instance;
    }
}

// Mendaftarkan widget
add_action('widgets_init', function() {
    register_widget('Blocksy_Wishlist_Widget');
});
```

### 6. AJAX ENDPOINTS YANG TERSEDIA

```php
// Mendapatkan semua wishlist items
wp_ajax_blc_ext_wish_list_get_all_likes
wp_ajax_nopriv_blc_ext_wish_list_get_all_likes

// Sinkronisasi wishlist (add/remove items)
wp_ajax_blc_ext_wish_list_sync_likes
wp_ajax_nopriv_blc_ext_wish_list_sync_likes

// Contoh penggunaan AJAX
function get_wishlist_via_ajax() {
    $response = wp_remote_post(admin_url('admin-ajax.php'), [
        'body' => [
            'action' => 'blc_ext_wish_list_get_all_likes'
        ]
    ]);
    
    if (!is_wp_error($response)) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['data']['likes']['items'];
    }
    
    return [];
}
```

### 7. HOOKS DAN FILTERS YANG TERSEDIA

```php
// Filter untuk mengaktifkan/menonaktifkan wishlist
add_filter('blocksy:ext:woocommerce-extra:wish-list:enabled', function($enabled, $place) {
    // $place bisa: 'single', 'archive', 'quick-view'
    return $enabled;
}, 10, 2);

// Filter untuk mengubah slug wishlist
add_filter('blocksy:pro:woocommerce-extra:wish-list:slug', function($slug) {
    return 'my-custom-wishlist'; // default: 'woo-wish-list'
});

// Action ketika wishlist berubah (JavaScript event)
// Tersedia di frontend: blocksy:woocommerce:wish-list-change
```

### 8. STRUKTUR DATA WISHLIST

```php
// Format data wishlist item
$wishlist_item = [
    'id' => 123,                    // Product ID
    'attributes' => [               // Untuk variable products
        'color' => 'red',
        'size' => 'large'
    ]
];

// Format lengkap wishlist
$wishlist_data = [
    'v' => 2,                       // Version
    'items' => [
        ['id' => 123],
        ['id' => 456, 'attributes' => ['color' => 'blue']]
    ]
];
```

### 9. STYLING DAN CSS CLASSES

```css
/* CSS Classes yang tersedia */
.ct-woocommerce-wishlist-table     /* Container tabel wishlist */
.wishlist-product-thumbnail        /* Kolom gambar produk */
.wishlist-product-name             /* Kolom nama produk */
.wishlist-product-actions          /* Kolom aksi (add to cart, dll) */
.wishlist-product-remove           /* Kolom tombol remove */
.ct-wishlist-button-single         /* Tombol wishlist di single product */
.ct-wishlist-button-archive        /* Tombol wishlist di archive */
.ct-header-wishlist                /* Wishlist di header */
.ct-dynamic-count-wishlist         /* Counter jumlah item */
```

### 10. CONTOH IMPLEMENTASI LENGKAP

```php
// File: functions.php atau plugin custom

class Custom_Wishlist_Display {
    
    public function __construct() {
        add_action('init', [$this, 'init']);
    }
    
    public function init() {
        // Shortcode
        add_shortcode('my_wishlist', [$this, 'wishlist_shortcode']);
        
        // Widget
        add_action('widgets_init', [$this, 'register_widget']);
        
        // AJAX handler custom
        add_action('wp_ajax_get_wishlist_count', [$this, 'get_wishlist_count']);
        add_action('wp_ajax_nopriv_get_wishlist_count', [$this, 'get_wishlist_count']);
    }
    
    public function wishlist_shortcode($atts) {
        $atts = shortcode_atts([
            'limit' => 6,
            'layout' => 'grid', // grid, list, slider
            'show_price' => true,
            'show_excerpt' => false
        ], $atts);
        
        return $this->render_wishlist($atts);
    }
    
    public function render_wishlist($args = []) {
        if (!function_exists('blc_get_ext')) {
            return '<p>Blocksy Companion Pro tidak aktif</p>';
        }
        
        $wishlist_instance = blc_get_ext('woocommerce-extra')->get_wish_list();
        $items = $wishlist_instance->get_current_wish_list();
        
        if (empty($items)) {
            return '<div class="wishlist-empty">Wishlist Anda kosong</div>';
        }
        
        $output = '<div class="custom-wishlist-grid">';
        $count = 0;
        
        foreach ($items as $item) {
            if ($args['limit'] > 0 && $count >= $args['limit']) break;
            
            $product_id = is_object($item) ? $item->id : $item;
            $product = wc_get_product($product_id);
            
            if (!$product) continue;
            
            $output .= $this->render_product_card($product, $args);
            $count++;
        }
        
        $output .= '</div>';
        return $output;
    }
    
    private function render_product_card($product, $args) {
        ob_start();
        ?>
        <div class="wishlist-product-card">
            <div class="product-image">
                <a href="<?php echo $product->get_permalink(); ?>">
                    <?php echo $product->get_image('medium'); ?>
                </a>
            </div>
            <div class="product-info">
                <h3><a href="<?php echo $product->get_permalink(); ?>"><?php echo $product->get_name(); ?></a></h3>
                <?php if ($args['show_price']): ?>
                    <div class="price"><?php echo $product->get_price_html(); ?></div>
                <?php endif; ?>
                <?php if ($args['show_excerpt']): ?>
                    <div class="excerpt"><?php echo wp_trim_words($product->get_short_description(), 15); ?></div>
                <?php endif; ?>
                <div class="product-actions">
                    <?php if ($product->is_purchasable()): ?>
                        <a href="<?php echo $product->add_to_cart_url(); ?>" class="button add-to-cart">Add to Cart</a>
                    <?php endif; ?>
                    <button class="remove-wishlist" data-product-id="<?php echo $product->get_id(); ?>">Remove</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function get_wishlist_count() {
        if (!function_exists('blc_get_ext')) {
            wp_send_json_error('Plugin not active');
        }
        
        $wishlist_instance = blc_get_ext('woocommerce-extra')->get_wish_list();
        $items = $wishlist_instance->get_current_wish_list();
        
        wp_send_json_success(['count' => count($items)]);
    }
}

new Custom_Wishlist_Display();
```

### 11. CATATAN PENTING

1. **Dependency**: Pastikan Blocksy Companion Pro dan WooCommerce aktif
2. **Permissions**: Cek user permissions untuk akses wishlist
3. **Caching**: Pertimbangkan caching untuk performa
4. **Security**: Validasi input dan sanitasi output
5. **Responsive**: Pastikan tampilan responsive di semua device

### 12. TROUBLESHOOTING

- **Wishlist kosong**: Cek apakah user login atau ada cookie wishlist
- **Function tidak ada**: Pastikan plugin aktif dan extension enabled
- **Styling tidak muncul**: Enqueue CSS yang diperlukan
- **AJAX error**: Cek nonce dan permissions

Dokumentasi ini memberikan foundation lengkap untuk implementasi custom wishlist display di Blocksy Companion Pro.
