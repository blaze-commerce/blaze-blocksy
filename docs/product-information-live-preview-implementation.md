# Product Information Element - Live Preview Implementation

## Overview

Implementasi live preview untuk Product Information element telah diperbarui untuk mendukung real-time preview di WordPress Customizer. Perubahan ini memungkinkan user melihat perubahan border width, border color, padding, dan layout secara langsung tanpa perlu refresh halaman.

## Perubahan yang Dilakukan

### 1. Update Field Sync Configuration

**File:** `blocksy/product-information.php`

Mengubah semua field dari `'sync' => array('id' => 'woo_single_layout_skip')` menjadi `'sync' => 'live'`:

```php
// Border Width
'ct-information-border_width' => array(
    'label' => __( 'Border Width', 'textdomain' ),
    'type' => 'ct-slider',
    'min' => 0,
    'max' => 10,
    'value' => 1,
    'sync' => 'live', // ✅ Diubah untuk real-time preview
),

// Border Color
'ct-information-border_color' => array(
    'label' => __( 'Border Color', 'textdomain' ),
    'type' => 'ct-color-picker',
    'design' => 'inline',
    'sync' => 'live', // ✅ Diubah untuk real-time preview
    // ... rest of configuration
),

// Padding, Justify Content, Gap Between, Gap Inside
// Semua menggunakan 'sync' => 'live'
```

### 2. Enhanced HTML Output dengan Data Attributes

**File:** `blocksy/product-information.php` - Method `renderLayer()`

```php
echo blocksy_html_tag(
    'div',
    array(
        'class' => 'ct-product-information',
        'data-element' => 'product_information', // ✅ Untuk JavaScript targeting
        'data-id' => uniqid( 'product-info-' ), // ✅ Unique ID
    ),
    $content
);
```

### 3. Customizer JavaScript Implementation

**File:** `assets/product/information/customizer.js` (Baru)

JavaScript untuk enhanced live preview yang:

- Listen untuk perubahan pada `woo_single_layout`
- Update CSS variables secara real-time
- Handle semua field: border width, border color, padding, justify content, gaps

Key functions:

- `updateProductInformationPreview()` - Main handler
- `updateProductInformationStyles()` - Update CSS variables
- `getBorderColor()` - Extract border color dari configuration
- `updateCSSVariable()` - Helper untuk update CSS variables

### 4. Enhanced CSS dengan CSS Variables

**File:** `assets/product/information/style.css`

Updated CSS untuk menggunakan CSS variables:

```css
.ct-product-information .info-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: var(--product-information-justify-content, center);
  gap: var(--product-information-gap-between, 20px);
}

.ct-product-information .info-item {
  display: flex;
  align-items: center;
  padding: 0 1rem;
  gap: var(--product-information-gap-inside, 20px);
  cursor: pointer;
  transition: all 0.3s ease;
  justify-content: center;
}
```

### 5. Customizer Scripts Hook

**File:** `blocksy/product-information.php` - Constructor

```php
// Enqueue customizer scripts untuk live preview
add_action( 'customize_preview_init', array( $this, 'enqueueCustomizerScripts' ) );
```

**Method:** `enqueueCustomizerScripts()`

```php
public function enqueueCustomizerScripts() {
    if ( ! is_customize_preview() ) {
        return;
    }

    wp_enqueue_script(
        'product-information-customizer',
        get_stylesheet_directory_uri() . '/assets/product/information/customizer.js',
        array( 'customize-preview' ),
        '1.0.0',
        true
    );
}
```

## CSS Variables yang Digunakan

1. `--product-information-border-width` - Border width dalam px
2. `--product-information-border-color` - Border color
3. `--product-information-padding` - Vertical padding dalam px
4. `--product-information-justify-content` - Flex justify-content value
5. `--product-information-gap-between` - Gap between items dalam px
6. `--product-information-gap-inside` - Gap inside items dalam px

## Cara Kerja Live Preview

1. **User mengubah setting di Customizer** (border width, color, dll)
2. **WordPress Customizer API** mendeteksi perubahan karena `'sync' => 'live'`
3. **JavaScript customizer** (`customizer.js`) menerima update via `wp.customize()`
4. **CSS Variables diupdate** secara real-time di `document.documentElement`
5. **Visual changes** langsung terlihat di preview tanpa refresh

## Testing

Untuk menguji implementasi:

1. Buka WordPress Customizer
2. Navigate ke WooCommerce → Single Product → Product Elements
3. Enable "Product Information" element
4. Ubah Border Width, Border Color, Padding, dll
5. Perubahan harus langsung terlihat di preview panel

## Troubleshooting

Jika live preview tidak bekerja:

1. **Check browser console** untuk JavaScript errors
2. **Verify CSS variables** di browser dev tools
3. **Ensure Blocksy functions** tersedia (`blocksy_html_tag`, `blocksy_akg`)
4. **Clear cache** jika menggunakan caching plugins
5. **Test dengan default theme** untuk isolasi masalah

## Update: Perbaikan Masalah Refresh

### Masalah yang Ditemukan

- CSS variables tidak ter-generate ulang setelah live preview refresh
- Method `enqueueStyles` tidak terpanggil dengan parameter yang benar
- Styles hilang ketika halaman di-refresh di customizer

### Perbaikan yang Dilakukan

#### 1. **Refactor Method `enqueueStyles`**

- Mengubah method untuk tidak memerlukan parameter `$layer`
- Menambahkan logic untuk mengambil current layout dari `get_theme_mod()`
- Menambahkan fallback untuk default values

#### 2. **Enhanced JavaScript Customizer**

- Menambahkan `ensureBaseCSSLoaded()` untuk inject base CSS
- Menambahkan periodic check untuk memastikan CSS variables ter-set
- Menambahkan event listener untuk customizer refresh
- Menambahkan fallback `setDefaultCSSVariables()`

#### 3. **Robust CSS Injection**

- Base CSS di-inject langsung via JavaScript jika tidak ter-load
- CSS variables di-set dengan fallback values
- Periodic monitoring untuk memastikan styles tetap aktif

#### 4. **Additional Hooks**

- Menambahkan `wp_head` hook dengan priority 999
- Menambahkan `enqueueCustomizerStyles()` method

### Key Features Perbaikan

1. **Auto-Recovery**: Jika CSS variables hilang, sistem akan otomatis restore
2. **Base CSS Injection**: CSS dasar di-inject via JavaScript sebagai backup
3. **Periodic Monitoring**: Check setiap 2 detik untuk memastikan styles aktif
4. **Multiple Event Listeners**: Listen untuk berbagai events (load, refresh, active)
5. **Fallback Values**: Default values selalu tersedia

## Update: Perbaikan Masalah Offcanvas CSS

### Masalah yang Ditemukan

- CSS offcanvas tidak ter-load setelah live preview refresh
- Offcanvas elements tidak ter-style dengan benar
- File CSS tidak ter-include dalam JavaScript injection

### Perbaikan yang Dilakukan

#### 1. **Complete CSS Injection dalam JavaScript**

- Menambahkan semua CSS offcanvas ke dalam `ensureBaseCSSLoaded()`
- Include styles untuk overlay, offcanvas panel, header, tabs, form elements
- Menambahkan responsive styles untuk mobile

#### 2. **Enhanced Debugging & Monitoring**

- Menambahkan `checkOffcanvasElements()` untuk debug offcanvas
- Periodic check untuk memastikan offcanvas CSS ter-load
- Console logging untuk tracking CSS injection

#### 3. **Force Refresh Mechanism**

- `forceRefreshOffcanvasStyles()` untuk force reflow offcanvas elements
- Auto-detection jika offcanvas CSS tidak ter-load (position !== 'fixed')
- Re-injection CSS jika diperlukan

#### 4. **PHP CSS Loading Fix**

- Mengubah `include` menjadi `file_get_contents` untuk CSS file
- Memastikan CSS file ter-load dengan benar dalam PHP

### CSS Offcanvas yang Di-inject

```css
/* Offcanvas Overlay */
.ct-information-canvas.offcanvas-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

/* Offcanvas Panel */
.ct-information-canvas.offcanvas {
  position: fixed;
  top: 0;
  right: -400px;
  width: 400px;
  height: 100%;
  background: white;
  z-index: 1001;
  transition: right 0.3s ease;
  box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
}

/* Active States */
.ct-information-canvas.offcanvas-overlay.active {
  opacity: 1;
  visibility: visible;
}

.ct-information-canvas.offcanvas.active {
  right: 0;
}
```

## Update: Solusi Efisien untuk CSS Loading

### Masalah Sebelumnya

- Hardcode CSS di JavaScript sangat tidak efisien
- Setiap perubahan di `style.css` harus update `customizer.js`
- Duplikasi CSS code antara file CSS dan JavaScript

### Solusi Baru: Dynamic CSS Loading

#### 1. **Smart CSS Detection & Loading**

- Auto-detect theme URL dari berbagai sources
- Dynamic loading CSS file dengan `<link>` element
- Fallback ke minimal inline CSS jika file tidak ter-load

#### 2. **Efficient CSS Management**

```javascript
// Load CSS file secara dynamic - NO HARDCODED CSS!
loadCSSFromURL(cssUrl);

// Jika gagal load, hanya warning - tidak ada fallback CSS
console.warn("CSS not loaded - styles may not work correctly");
```

#### 3. **Cache Busting**

- CSS di-load dengan timestamp untuk avoid cache issues
- Auto-reload CSS jika terdeteksi tidak ter-load

#### 4. **No More Hardcoded CSS**

- **REMOVED** semua hardcoded CSS dari JavaScript
- **PURE** dynamic loading dari file CSS
- **ZERO** duplikasi antara CSS dan JavaScript

### Keuntungan Pendekatan Baru

1. **100% Efisien**: ZERO hardcoded CSS di JavaScript
2. **Single Source of Truth**: Hanya file CSS yang perlu di-edit
3. **Performance**: Load actual CSS file dengan cache busting
4. **Clean Architecture**: Separation of concerns yang benar
5. **Developer Friendly**: Edit CSS → Auto reload di customizer

## Update Final: No-Refresh Live Preview

### Masalah yang Ditemukan

- Refresh preview menghilangkan CSS file yang sudah ter-load
- CSS variables sudah berubah real-time tanpa perlu refresh
- Refresh menyebabkan style.css tidak terbaca

### Solusi Final: Pure CSS Variables Update

#### **REMOVED** Semua Logic Refresh

- ❌ Periodic checking
- ❌ Force refresh functions
- ❌ Offcanvas debugging
- ❌ CSS reloading pada setiap perubahan

#### **SIMPLIFIED** ke Core Functionality

- ✅ Load CSS file SEKALI saat init
- ✅ Update CSS variables real-time
- ✅ Handle class changes (separator)
- ✅ NO refresh preview

### Cara Kerja Sekarang

1. **Initial Load**: CSS file di-load sekali saat customizer init
2. **Field Changes**: Hanya update CSS variables, TIDAK refresh
3. **Real-time**: Perubahan langsung terlihat tanpa reload
4. **Clean**: Tidak ada interference dengan CSS loading

### Debugging Function

Hanya tersedia satu function untuk emergency:

- `window.reloadProductInfoCSS()` - Reload CSS file jika benar-benar diperlukan

## Files Modified/Created

- ✅ `blocksy/product-information.php` - Updated sync configuration & hooks + perbaikan refresh
- ✅ `assets/product/information/customizer.js` - Enhanced dengan auto-recovery & monitoring
- ✅ `assets/product/information/style.css` - Enhanced CSS variables
- ✅ `docs/product-information-live-preview-implementation.md` - This documentation
