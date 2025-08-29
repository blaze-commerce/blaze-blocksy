# Recently Viewed Products Implementation

## Overview

Recently Viewed Products adalah custom element untuk Blocksy theme yang menampilkan produk-produk yang baru saja dilihat oleh user. Element ini menggunakan localStorage untuk menyimpan data dan AJAX untuk mengambil detail produk.

## Features

- **localStorage Tracking**: Menyimpan product IDs di localStorage browser
- **AJAX Loading**: Mengambil detail produk via AJAX untuk performa optimal
- **Owl Carousel Support**: Mendukung carousel mode dengan navigasi dan autoplay
- **Grid Layout**: Alternatif layout grid yang responsive
- **Customizable**: Banyak opsi customization di WordPress Customizer
- **Live Preview**: Real-time preview di WordPress Customizer
- **Cross-browser Compatible**: Bekerja di semua browser modern

## File Structure

```
blocksy/
├── recently-viewed-products.php    # Main PHP class
partials/product/
├── recently-viewed.php             # HTML template
assets/product/recently-viewed/
├── style.css                       # CSS styling
├── script.js                       # Main JavaScript
└── customizer.js                   # Customizer live preview
```

## How It Works

### 1. Product Tracking
Setiap kali user mengunjungi halaman product, JavaScript akan:
- Mengambil product ID dari halaman
- Menyimpan ke localStorage dengan key `recently_viewed_products`
- Menghindari duplikat dan membatasi maksimal 20 produk

### 2. Product Display
Pada halaman product lain, element akan:
- Membaca product IDs dari localStorage
- Mengirim AJAX request ke server untuk mendapatkan detail produk
- Render produk menggunakan template JavaScript
- Inisialisasi Owl Carousel jika diaktifkan

### 3. Customizer Integration
Element terintegrasi dengan WordPress Customizer untuk:
- Live preview perubahan setting
- Real-time update tanpa refresh halaman
- CSS variables untuk styling dinamis

## Customizer Options

### Basic Settings
- **Section Title**: Judul section (default: "Recently Viewed Products")
- **Number of Products**: Jumlah produk yang ditampilkan (2-12)
- **Columns**: Jumlah kolom grid/carousel (2-6)

### Display Options
- **Show Price**: Tampilkan/sembunyikan harga produk
- **Show Rating**: Tampilkan/sembunyikan rating produk

### Carousel Settings
- **Enable Carousel**: Aktifkan mode carousel dengan Owl Carousel
- **Autoplay Carousel**: Autoplay carousel
- **Autoplay Speed**: Kecepatan autoplay dalam milliseconds

### Styling Options
- **Section Margin**: Margin atas/bawah section
- **Title Color**: Warna judul section
- **Title Font Size**: Ukuran font judul

## Usage

### 1. Aktivasi Element
1. Buka WordPress Customizer
2. Navigate ke **WooCommerce > Single Product**
3. Tambahkan element **Recently Viewed Products**
4. Atur posisi element (biasanya setelah Cross Sell atau Related Products)

### 2. Configuration
1. Klik pada element Recently Viewed Products
2. Atur opsi sesuai kebutuhan:
   - Judul section
   - Jumlah produk dan kolom
   - Enable/disable carousel
   - Styling options

### 3. Testing
1. Kunjungi beberapa halaman produk untuk mengisi localStorage
2. Buka halaman produk lain untuk melihat recently viewed products
3. Test responsive design di berbagai ukuran layar

## Technical Implementation

### localStorage Structure
```javascript
// Key: 'recently_viewed_products'
// Value: [123, 456, 789, ...] // Array of product IDs
```

### AJAX Endpoint
```php
// Action: 'get_recently_viewed_products'
// Method: POST
// Data: {
//   nonce: 'security_nonce',
//   product_ids: [123, 456, 789],
//   limit: 6,
//   current_product_id: 123
// }
```

### CSS Variables
```css
:root {
  --recently-viewed-products-count: 6;
  --recently-viewed-columns: 4;
  --recently-viewed-margin: 40px;
  --recently-viewed-title-color: #333333;
  --recently-viewed-title-size: 24px;
  --recently-viewed-show-price: block;
  --recently-viewed-show-rating: block;
}
```

## Responsive Design

### Desktop (1024px+)
- Menggunakan jumlah kolom sesuai setting
- Navigation arrows untuk carousel
- Hover effects pada produk

### Tablet (768px - 1023px)
- Maksimal 3 kolom
- Navigation arrows lebih kecil
- Touch-friendly carousel

### Mobile (< 768px)
- Maksimal 2 kolom
- Dots navigation saja (tanpa arrows)
- Optimized touch interactions

## Browser Compatibility

- **Chrome**: Full support
- **Firefox**: Full support
- **Safari**: Full support
- **Edge**: Full support
- **IE11**: Basic support (tanpa CSS Grid fallback)

## Performance Considerations

1. **localStorage**: Data disimpan lokal, tidak ada database queries
2. **AJAX Loading**: Hanya load detail produk saat diperlukan
3. **Image Lazy Loading**: Menggunakan `loading="lazy"` attribute
4. **CSS Variables**: Efficient styling updates
5. **Carousel Optimization**: Destroy/recreate carousel saat perubahan setting

## Troubleshooting

### Products Not Showing
1. Check browser localStorage untuk `recently_viewed_products`
2. Verify AJAX endpoint working (check Network tab)
3. Ensure products are published and visible

### Carousel Not Working
1. Verify Owl Carousel library loaded
2. Check JavaScript console for errors
3. Ensure jQuery is available

### Customizer Not Updating
1. Check customizer.js loaded
2. Verify wp.customize API available
3. Clear browser cache

## Future Enhancements

1. **Database Storage**: Option untuk simpan di database untuk logged-in users
2. **Product Variations**: Support untuk variable products
3. **Category Filtering**: Filter berdasarkan kategori produk
4. **Time-based Expiry**: Auto-remove produk setelah waktu tertentu
5. **Analytics Integration**: Track viewing patterns
