# Blaze WooCommerce Blocks - Testing Checklist

## Pre-Testing Setup

- [ ] Clear browser cache
- [ ] Clear WordPress cache (if using cache plugin)
- [ ] Regenerate permalinks (Settings > Permalinks > Save)
- [ ] Ensure WooCommerce is active
- [ ] Ensure products exist with images

---

## Blaze Product Collection - Testing

### Block Registration

- [ ] Block muncul di block inserter
- [ ] Block dapat ditambahkan ke page/post
- [ ] Block icon dan title benar
- [ ] Block category "WooCommerce" benar

### Editor Functionality

- [ ] Inspector panel "Blaze Responsive Settings" muncul
- [ ] Toggle "Enable Responsive Display" berfungsi
- [ ] Range controls untuk Desktop settings berfungsi
  - [ ] Desktop Columns (1-6)
  - [ ] Desktop Product Count (1-24)
- [ ] Range controls untuk Tablet settings berfungsi
  - [ ] Tablet Columns (1-4)
  - [ ] Tablet Product Count (1-18)
- [ ] Range controls untuk Mobile settings berfungsi
  - [ ] Mobile Columns (1-3)
  - [ ] Mobile Product Count (1-12)
- [ ] Query Settings panel berfungsi
  - [ ] Products Per Page
  - [ ] Order By (Title, Date, Price, etc.)
  - [ ] Order (ASC/DESC)
  - [ ] Show Only On Sale toggle
- [ ] Display Settings panel berfungsi
  - [ ] Default Columns setting

### Frontend Display

- [ ] Products ditampilkan dengan benar
- [ ] Default columns (4) ditampilkan di desktop
- [ ] Product count sesuai setting (8 di desktop)
- [ ] Product images muncul
- [ ] Product titles muncul
- [ ] Product prices muncul
- [ ] Product links berfungsi

### Responsive Behavior

**Desktop (> 1024px):**
- [ ] Menampilkan 4 kolom
- [ ] Menampilkan 8 produk
- [ ] Grid layout rapi

**Tablet (768px - 1023px):**
- [ ] Menampilkan 3 kolom
- [ ] Menampilkan 6 produk
- [ ] Grid layout rapi
- [ ] Transisi smooth saat resize

**Mobile (< 768px):**
- [ ] Menampilkan 2 kolom
- [ ] Menampilkan 4 produk
- [ ] Grid layout rapi
- [ ] Transisi smooth saat resize

### JavaScript Functionality

- [ ] Device detection berfungsi
- [ ] Auto-adjust columns saat resize window
- [ ] Auto-adjust product count saat resize
- [ ] No JavaScript errors di console
- [ ] Smooth transitions

### Performance

- [ ] Page load time acceptable
- [ ] No layout shift (CLS)
- [ ] Images lazy load (if applicable)
- [ ] Resize performance smooth

---

## Blaze Product Image - Testing

### Block Registration

- [ ] Block muncul di block inserter
- [ ] Block dapat ditambahkan ke page/post
- [ ] Block icon dan title benar
- [ ] Block category "WooCommerce" benar

### Editor Functionality

- [ ] Inspector panel "Image Settings" muncul
- [ ] Toggle "Show Product Link" berfungsi
- [ ] Select "Image Size" berfungsi
- [ ] Toggle "Enable Hover Image" berfungsi
- [ ] Inspector panel "Sale Badge Settings" muncul
- [ ] Toggle "Show Sale Badge" berfungsi
- [ ] Select "Badge Position" berfungsi
- [ ] Inspector panel "Blaze Wishlist Settings" muncul
- [ ] Toggle "Show Wishlist Button" berfungsi
- [ ] Select "Wishlist Button Position" berfungsi
  - [ ] Top Left
  - [ ] Top Right
  - [ ] Bottom Left
  - [ ] Bottom Right
- [ ] Preview mockup ditampilkan di editor
- [ ] Settings summary ditampilkan

### Frontend Display

- [ ] Product image ditampilkan
- [ ] Image link ke product page berfungsi
- [ ] Image size sesuai setting
- [ ] Placeholder image muncul jika no image

### Wishlist Button

**Display:**
- [ ] Wishlist button muncul
- [ ] Button position sesuai setting (top-right default)
- [ ] Button icon (heart outline) muncul
- [ ] Button styling benar (white background, rounded)

**Functionality:**
- [ ] Click button menambahkan ke wishlist
- [ ] Icon berubah ke filled heart
- [ ] Button class "is-in-wishlist" ditambahkan
- [ ] Click lagi menghapus dari wishlist
- [ ] Icon kembali ke outline
- [ ] Loading state muncul saat AJAX
- [ ] No JavaScript errors

**AJAX:**
- [ ] AJAX request ke `blaze_add_to_wishlist` berhasil
- [ ] AJAX request ke `blaze_remove_from_wishlist` berhasil
- [ ] Nonce validation berfungsi
- [ ] Response success/error ditangani
- [ ] Wishlist count updated (if applicable)

**Integration:**
- [ ] Blocksy wishlist integration berfungsi (if active)
- [ ] Fallback cookie storage berfungsi
- [ ] Wishlist persists setelah refresh

### Hover Image

**Setup:**
- [ ] Product memiliki minimal 2 gambar di gallery
- [ ] Main image (pertama) ditampilkan
- [ ] Hover image (kedua) loaded

**Functionality:**
- [ ] Hover pada image menampilkan gambar kedua
- [ ] Transisi smooth (opacity)
- [ ] Mouse leave kembali ke gambar pertama
- [ ] No layout shift saat hover
- [ ] Hover berfungsi di desktop
- [ ] Hover disabled di mobile (touch)

### Sale Badge

- [ ] Badge muncul untuk produk on sale
- [ ] Badge tidak muncul untuk produk regular
- [ ] Badge position sesuai setting
- [ ] Badge text "Sale!" benar
- [ ] Badge styling benar (red background)

### Responsive Behavior

**Desktop:**
- [ ] Wishlist button 40px x 40px
- [ ] Icon 20px x 20px
- [ ] Hover effects berfungsi
- [ ] All features berfungsi

**Tablet:**
- [ ] Layout tetap rapi
- [ ] Wishlist button visible
- [ ] Hover image berfungsi

**Mobile:**
- [ ] Wishlist button 36px x 36px
- [ ] Icon 18px x 18px
- [ ] Touch events berfungsi
- [ ] No hover image (touch device)
- [ ] Badge font size lebih kecil

### Accessibility

- [ ] Wishlist button memiliki aria-label
- [ ] Aria-label berubah saat toggle
- [ ] Focus outline visible
- [ ] Keyboard navigation berfungsi
- [ ] Screen reader friendly

---

## Cross-Browser Testing

### Chrome
- [ ] Blaze Product Collection berfungsi
- [ ] Blaze Product Image berfungsi
- [ ] Responsive berfungsi
- [ ] No console errors

### Firefox
- [ ] Blaze Product Collection berfungsi
- [ ] Blaze Product Image berfungsi
- [ ] Responsive berfungsi
- [ ] No console errors

### Safari
- [ ] Blaze Product Collection berfungsi
- [ ] Blaze Product Image berfungsi
- [ ] Responsive berfungsi
- [ ] No console errors

### Edge
- [ ] Blaze Product Collection berfungsi
- [ ] Blaze Product Image berfungsi
- [ ] Responsive berfungsi
- [ ] No console errors

---

## Mobile Device Testing

### iOS (iPhone/iPad)
- [ ] Blocks ditampilkan dengan benar
- [ ] Touch events berfungsi
- [ ] Wishlist button berfungsi
- [ ] Responsive layout benar
- [ ] No layout issues

### Android
- [ ] Blocks ditampilkan dengan benar
- [ ] Touch events berfungsi
- [ ] Wishlist button berfungsi
- [ ] Responsive layout benar
- [ ] No layout issues

---

## Integration Testing

### WooCommerce
- [ ] Compatible dengan WooCommerce products
- [ ] Product data ditampilkan benar
- [ ] Product links berfungsi
- [ ] Sale status detected
- [ ] Stock status handled

### Blocksy Theme
- [ ] Compatible dengan Blocksy theme
- [ ] Styling konsisten dengan theme
- [ ] No CSS conflicts
- [ ] Wishlist integration berfungsi

### WordPress
- [ ] Compatible dengan WordPress 6.0+
- [ ] Block editor berfungsi
- [ ] No PHP errors
- [ ] No JavaScript errors

---

## Performance Testing

- [ ] Page Speed Insights score acceptable
- [ ] First Contentful Paint (FCP) < 2s
- [ ] Largest Contentful Paint (LCP) < 2.5s
- [ ] Cumulative Layout Shift (CLS) < 0.1
- [ ] Time to Interactive (TTI) < 3.8s
- [ ] No render-blocking resources
- [ ] Images optimized
- [ ] CSS/JS minified (production)

---

## Error Handling

### Product Collection
- [ ] No products: Menampilkan "No products found"
- [ ] Invalid query: Handled gracefully
- [ ] AJAX error: Handled gracefully

### Product Image
- [ ] No image: Menampilkan placeholder
- [ ] No gallery: Hover disabled
- [ ] AJAX error: User notified
- [ ] Invalid product ID: Handled gracefully

---

## Security Testing

- [ ] Nonce validation berfungsi
- [ ] AJAX requests authenticated
- [ ] XSS protection implemented
- [ ] SQL injection protected
- [ ] CSRF protection implemented
- [ ] Input sanitization berfungsi
- [ ] Output escaping berfungsi

---

## Final Checks

- [ ] All files committed to repository
- [ ] Documentation complete
- [ ] No TODO comments in code
- [ ] Code follows WordPress coding standards
- [ ] PHPDoc comments complete
- [ ] JSDoc comments complete
- [ ] No debug code left
- [ ] No console.log statements
- [ ] Version numbers updated
- [ ] Changelog updated

---

## Sign-off

**Tested by:** ___________________

**Date:** ___________________

**Environment:**
- WordPress Version: ___________________
- WooCommerce Version: ___________________
- PHP Version: ___________________
- Browser(s): ___________________

**Notes:**
___________________________________________
___________________________________________
___________________________________________

**Status:** [ ] PASS [ ] FAIL

**Issues Found:** ___________________

