# Recently Viewed Products - Simple Implementation

## Overview

Recently Viewed Products adalah fitur yang **otomatis menampilkan** produk-produk yang baru saja dilihat oleh pengunjung website. Fitur ini muncul secara otomatis setelah Related Products di halaman single product dengan format yang identik.

## Cara Kerja

### 1. Tracking Otomatis

- Setiap kali user mengunjungi halaman product, JavaScript otomatis menyimpan product ID ke localStorage
- Data disimpan dengan key `recently_viewed_products` sebagai array
- Sistem menghindari duplikat dan membatasi maksimal 20 produk
- Product yang sedang dilihat tidak akan muncul di list

### 2. Display Otomatis

- Section muncul otomatis setelah Related Products
- Menggunakan format HTML yang sama dengan Related Products
- Mengambil maksimal 6 produk dari localStorage
- Mengirim AJAX request untuk mendapatkan detail produk
- Render dengan Owl Carousel menggunakan konfigurasi yang sama

## Implementasi

### File Structure

```
includes/customization/
└── recently-viewed-products.php    # Main implementation file
```

### Key Features

- **Zero Configuration**: Langsung aktif tanpa setup
- **Same Format**: Menggunakan CSS classes `.related.products`
- **Responsive**: Otomatis responsive (4→2→1 kolom)
- **Performance**: localStorage + AJAX loading
- **Compatible**: Menggunakan Owl Carousel yang sudah ada

### HTML Structure

```html
<section class="recently-viewed-products related products">
  <h2>Recently Viewed Products</h2>
  <div class="products owl-carousel owl-theme">
    <!-- Products rendered via JavaScript -->
  </div>
</section>
```

## Testing

### 1. Basic Testing

1. Kunjungi beberapa halaman produk berbeda
2. Buka halaman produk lain
3. Scroll ke bawah setelah Related Products
4. Recently Viewed Products akan muncul otomatis

### 2. localStorage Check

```javascript
// Check di browser console
console.log(localStorage.getItem("recently_viewed_products"));
// Output: ["123", "456", "789"]
```

### 3. AJAX Endpoint Test

- Endpoint: `/wp-admin/admin-ajax.php`
- Action: `get_recently_viewed_products`
- Method: POST
- Nonce: `recently_viewed_nonce`

## Troubleshooting

### Section Tidak Muncul

- **Penyebab**: Belum ada produk di localStorage
- **Solusi**: Kunjungi beberapa halaman produk dulu

### Carousel Tidak Berfungsi

- **Penyebab**: Owl Carousel tidak loaded
- **Solusi**: Pastikan related-carousel.php aktif dan jQuery tersedia

### AJAX Error

- **Penyebab**: Endpoint atau nonce error
- **Solusi**: Check browser console dan pastikan WordPress AJAX berfungsi

## Browser Compatibility

- **Chrome**: ✅ Full support
- **Firefox**: ✅ Full support
- **Safari**: ✅ Full support
- **Edge**: ✅ Full support
- **IE11**: ⚠️ Basic support (localStorage available)

## Performance Notes

1. **localStorage**: Data disimpan lokal, tidak ada database load
2. **AJAX Efficient**: Hanya load detail saat diperlukan
3. **Carousel Reuse**: Menggunakan library yang sudah loaded
4. **Minimal CSS**: Tidak ada additional CSS, menggunakan existing styles

## Code Highlights

### localStorage Tracking

```javascript
// Auto-track saat visit product page
let recentlyViewed = JSON.parse(
  localStorage.getItem("recently_viewed_products") || "[]"
);
recentlyViewed = recentlyViewed.filter((id) => id != currentProductId);
recentlyViewed.unshift(currentProductId);
localStorage.setItem(
  "recently_viewed_products",
  JSON.stringify(recentlyViewed)
);
```

### AJAX Loading with WooCommerce Templates

```php
// Server-side: Generate HTML menggunakan WooCommerce loop
foreach ( $product_ids as $product_id ) {
    $product = wc_get_product( intval( $product_id ) );
    global $product;
    $GLOBALS['product'] = $product;

    // Render menggunakan WooCommerce content template
    wc_get_template_part( 'content', 'product' );
}

// Return HTML instead of JSON data
wp_send_json_success( array( 'html' => $html ) );
```

```javascript
// Client-side: Insert HTML langsung
.then(data => {
    if (data.success && data.data && data.data.html) {
        container.innerHTML = data.data.html;
    }
})
```

### Carousel Integration

```javascript
// Use same config as related products
$container.owlCarousel({
  loop: false,
  margin: 24,
  nav: false,
  dots: true,
  responsive: {
    0: { items: 1 },
    600: { items: 2 },
    1000: { items: 4 },
  },
});
```

## Advantages

1. **Simple**: Satu file, langsung jalan
2. **Consistent**: Format sama dengan Related Products
3. **Fast**: localStorage + minimal AJAX
4. **Responsive**: Auto-responsive design
5. **Compatible**: Menggunakan existing libraries
6. **Template-based**: Menggunakan `wc_get_template_part()` untuk konsistensi
7. **Auto-sync**: Perubahan design otomatis terefleksi tanpa update code
8. **Theme-compatible**: Mengikuti custom template dari theme/child theme

## Future Enhancements

1. **Database Storage**: Option untuk logged-in users
2. **Category Filter**: Filter berdasarkan kategori
3. **Time Expiry**: Auto-remove setelah waktu tertentu
4. **Admin Settings**: Basic configuration panel
5. **Analytics**: Track viewing patterns
