# Blaze WooCommerce Blocks - Documentation

## Overview

Blaze WooCommerce Blocks adalah custom blocks untuk WooCommerce yang menyediakan fitur-fitur tambahan:

1. **Blaze Product Collection** - Product collection dengan responsive display settings
2. **Blaze Product Image** - Product image dengan wishlist button dan hover effect

## Table of Contents

1. [Installation](#installation)
2. [Blaze Product Collection](#blaze-product-collection)
3. [Blaze Product Image](#blaze-product-image)
4. [Customization](#customization)
5. [Troubleshooting](#troubleshooting)

---

## Installation

Blocks sudah otomatis terintegrasi dengan theme. Tidak perlu instalasi tambahan.

### Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- Blocksy Child Theme
- PHP 7.4+

### File Structure

```
includes/customization/wc-blocks/
├── class-blaze-product-collection.php
└── class-blaze-product-image.php

assets/wc-blocks/
├── blaze-product-collection/
│   ├── index.js
│   ├── script.js
│   ├── style.css
│   └── editor.css
└── blaze-product-image/
    ├── index.js
    ├── script.js
    ├── style.css
    └── editor.css
```

---

## Blaze Product Collection

### Features

✅ **Responsive Display** - Berbeda untuk desktop, tablet, dan mobile
✅ **Custom Product Count** - Atur jumlah produk per device
✅ **Flexible Columns** - Atur jumlah kolom per device
✅ **WooCommerce Integration** - Full compatibility dengan WooCommerce

### Default Settings

| Device  | Columns | Product Count |
|---------|---------|---------------|
| Desktop | 4       | 8             |
| Tablet  | 3       | 6             |
| Mobile  | 2       | 4             |

### How to Use

#### 1. Add Block to Page

1. Buka Gutenberg Editor
2. Klik tombol **+** untuk menambah block
3. Cari "Blaze Product Collection"
4. Klik untuk menambahkan

#### 2. Configure Responsive Settings

Di **Inspector Panel** (sidebar kanan), buka panel **"Blaze Responsive Settings"**:

**Desktop Settings:**
- Desktop Columns: 1-6 (default: 4)
- Desktop Product Count: 1-24 (default: 8)

**Tablet Settings:**
- Tablet Columns: 1-4 (default: 3)
- Tablet Product Count: 1-18 (default: 6)

**Mobile Settings:**
- Mobile Columns: 1-3 (default: 2)
- Mobile Product Count: 1-12 (default: 4)

#### 3. Configure Query Settings

Di panel **"Query Settings"**:

- **Products Per Page**: Total produk yang akan di-query
- **Order By**: Title, Date, Price, Popularity, Rating
- **Order**: Ascending atau Descending
- **Show Only On Sale Products**: Toggle untuk filter produk sale

#### 4. Display Settings

Di panel **"Display Settings"**:

- **Default Columns**: Digunakan saat responsive mode disabled

### Breakpoints

```css
Desktop: > 1024px
Tablet:  768px - 1023px
Mobile:  < 768px
```

### Example Usage

```html
<!-- Contoh output HTML -->
<div class="blaze-product-collection" 
     data-enable-responsive="true"
     data-responsive-columns='{"desktop":4,"tablet":3,"mobile":2}'
     data-responsive-counts='{"desktop":8,"tablet":6,"mobile":4}'>
  <div class="blaze-product-collection__inner">
    <ul class="products columns-4">
      <!-- Product items -->
    </ul>
  </div>
</div>
```

### Customization Hooks

```php
// Filter query arguments
add_filter('blaze_product_collection_query_args', function($args, $attributes) {
    // Modify query args
    return $args;
}, 10, 2);
```

---

## Blaze Product Image

### Features

✅ **Wishlist Button** - Add to wishlist dengan 4 posisi pilihan
✅ **Hover Image** - Tampilkan gambar kedua saat hover
✅ **Sale Badge** - Badge untuk produk sale
✅ **Responsive** - Optimized untuk semua device
✅ **Wishlist Integration** - Terintegrasi dengan Blocksy wishlist

### Wishlist Button Positions

1. **Top Left** - Pojok kiri atas
2. **Top Right** - Pojok kanan atas (default)
3. **Bottom Left** - Pojok kiri bawah
4. **Bottom Right** - Pojok kanan bawah

### How to Use

#### 1. Add Block to Page

1. Buka Gutenberg Editor
2. Klik tombol **+** untuk menambah block
3. Cari "Blaze Product Image"
4. Klik untuk menambahkan

#### 2. Configure Image Settings

Di panel **"Image Settings"**:

- **Show Product Link**: Buat image clickable ke product page
- **Image Size**: Thumbnail, Medium, Large, Full Size
- **Enable Hover Image**: Tampilkan gambar kedua dari gallery saat hover

#### 3. Configure Sale Badge

Di panel **"Sale Badge Settings"**:

- **Show Sale Badge**: Toggle untuk menampilkan badge
- **Badge Position**: Left, Center, Right

#### 4. Configure Wishlist Button

Di panel **"Blaze Wishlist Settings"**:

- **Show Wishlist Button**: Toggle untuk menampilkan button
- **Wishlist Button Position**: Top Left, Top Right, Bottom Left, Bottom Right

### Hover Image Source

Block akan otomatis menggunakan **gambar kedua dari product gallery** sebagai hover image.

**Setup di WooCommerce:**
1. Edit product
2. Scroll ke **Product Gallery**
3. Upload minimal 2 gambar
4. Gambar pertama = main image
5. Gambar kedua = hover image

### Wishlist Integration

Block terintegrasi dengan:
- ✅ Blocksy WooCommerce Extra Extension (jika aktif)
- ✅ Custom wishlist system (fallback)

**AJAX Endpoints:**
- `blaze_add_to_wishlist` - Add product to wishlist
- `blaze_remove_from_wishlist` - Remove product from wishlist

### Example Usage

```html
<!-- Contoh output HTML -->
<div class="blaze-product-image" 
     data-product-id="123"
     data-show-wishlist="true"
     data-wishlist-position="top-right"
     data-enable-hover="true">
  <div class="blaze-product-image__inner">
    <a href="/product/example" class="blaze-product-image__link">
      <div class="blaze-product-image__container">
        <img class="blaze-product-image__img--main" src="main.jpg" />
        <img class="blaze-product-image__img--hover" src="hover.jpg" />
        <span class="blaze-product-image__badge--sale">Sale!</span>
        <button class="blaze-product-image__wishlist--top-right">
          <!-- Wishlist icon -->
        </button>
      </div>
    </a>
  </div>
</div>
```

### JavaScript Events

```javascript
// Listen to wishlist updates
$(document).on('blaze:wishlist:updated', function(event, data) {
    console.log('Product ID:', data.productId);
    console.log('In Wishlist:', data.inWishlist);
});

// Trigger wishlist toggle
document.dispatchEvent(new CustomEvent('blaze:wishlist:toggle', {
    detail: {
        productId: 123,
        action: 'add' // or 'remove'
    }
}));
```

---

## Customization

### CSS Customization

#### Product Collection

```css
/* Custom gap between products */
.blaze-product-collection {
    --blaze-gap: 30px;
}

/* Custom columns for specific breakpoint */
@media screen and (max-width: 1200px) {
    .blaze-product-collection .products {
        --blaze-columns: 3;
    }
}

/* Hover effect customization */
.blaze-product-collection .products > li:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
```

#### Product Image

```css
/* Custom wishlist button style */
.blaze-product-image__wishlist {
    background: #ff6b6b;
    width: 50px;
    height: 50px;
}

.blaze-product-image__wishlist:hover {
    background: #ff5252;
}

/* Custom hover transition */
.blaze-product-image__img--hover {
    transition: opacity 0.5s ease;
}

/* Custom sale badge */
.blaze-product-image__badge--sale {
    background: linear-gradient(45deg, #ff6b6b, #ff5252);
    font-size: 14px;
    padding: 8px 15px;
}
```

### PHP Customization

```php
// Modify Product Collection query
add_filter('blaze_product_collection_query_args', function($args, $attributes) {
    // Only show featured products
    $args['meta_query'][] = array(
        'key' => '_featured',
        'value' => 'yes'
    );
    
    return $args;
}, 10, 2);

// Add custom class to Product Image
add_filter('blaze_product_image_wrapper_class', function($classes, $product) {
    if ($product->is_on_sale()) {
        $classes[] = 'has-sale';
    }
    return $classes;
}, 10, 2);
```

---

## Troubleshooting

### Block tidak muncul di editor

**Solusi:**
1. Clear browser cache
2. Regenerate WordPress permalinks (Settings > Permalinks > Save)
3. Check console untuk JavaScript errors
4. Pastikan WooCommerce aktif

### Responsive tidak berfungsi

**Solusi:**
1. Pastikan "Enable Responsive Display" toggle ON
2. Clear browser cache
3. Check console untuk JavaScript errors
4. Pastikan jQuery loaded

### Wishlist button tidak berfungsi

**Solusi:**
1. Check browser console untuk AJAX errors
2. Pastikan nonce valid (clear cache)
3. Check wishlist integration di `includes/customization/wishlist/wishlist.php`
4. Test dengan browser incognito mode

### Hover image tidak muncul

**Solusi:**
1. Pastikan product memiliki minimal 2 gambar di gallery
2. Pastikan "Enable Hover Image" toggle ON
3. Check image URLs di browser inspector
4. Clear image cache

### JavaScript conflicts

**Solusi:**
1. Check console untuk errors
2. Disable other plugins satu per satu
3. Test dengan default WordPress theme
4. Update jQuery jika perlu

---

## Support

Untuk pertanyaan atau issue, silakan hubungi development team.

### Changelog

**Version 1.0.0** (2024-10-13)
- Initial release
- Blaze Product Collection block
- Blaze Product Image block
- Responsive display settings
- Wishlist integration
- Hover image functionality

---

## Credits

Developed by Blaze Commerce Team
Built with WordPress Block Editor API
Powered by WooCommerce

