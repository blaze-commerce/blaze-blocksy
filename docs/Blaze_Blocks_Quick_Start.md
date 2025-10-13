# Blaze WooCommerce Blocks - Quick Start Guide

## 🚀 5-Minute Setup

### Step 1: Verify Installation

Blocks sudah otomatis terintegrasi. Tidak perlu instalasi tambahan!

**Check:**
1. Login ke WordPress Admin
2. Buka Pages > Add New
3. Klik tombol **+** (Add block)
4. Search "Blaze"
5. Anda harus melihat:
   - ✅ Blaze Product Collection
   - ✅ Blaze Product Image

---

## 📦 Blaze Product Collection

### Quick Add

1. **Add Block**
   - Klik **+** button
   - Search "Blaze Product Collection"
   - Klik untuk add

2. **Done!** 
   - Block akan menampilkan 8 produk dalam 4 kolom (desktop)
   - Otomatis responsive untuk tablet (6 produk, 3 kolom) dan mobile (4 produk, 2 kolom)

### Customize (Optional)

**Sidebar kanan > Blaze Responsive Settings:**

```
Desktop:
- Columns: 4 (default)
- Products: 8 (default)

Tablet:
- Columns: 3 (default)
- Products: 6 (default)

Mobile:
- Columns: 2 (default)
- Products: 4 (default)
```

**Tips:**
- Gunakan slider untuk adjust
- Preview akan update otomatis
- Publish untuk lihat di frontend

---

## 🖼️ Blaze Product Image

### Quick Add

1. **Add Block**
   - Klik **+** button
   - Search "Blaze Product Image"
   - Klik untuk add

2. **Done!**
   - Block akan menampilkan product image
   - Wishlist button di top-right
   - Hover image enabled (jika ada gallery)

### Setup Hover Image

**Di WooCommerce Product:**
1. Edit product
2. Scroll ke **Product Gallery**
3. Upload minimal 2 images:
   - Image 1 = Main image
   - Image 2 = Hover image
4. Update product

**Result:**
- Hover pada image akan show image kedua
- Smooth transition effect

### Customize Wishlist Button

**Sidebar kanan > Blaze Wishlist Settings:**

```
Position Options:
- Top Left
- Top Right (default) ✅
- Bottom Left
- Bottom Right
```

**Tips:**
- Pilih position yang tidak overlap dengan badge
- Test di mobile untuk ensure visibility

---

## 💡 Common Use Cases

### Use Case 1: Homepage Product Grid

**Goal:** Tampilkan 8 produk terbaru dalam grid responsive

**Steps:**
1. Add "Blaze Product Collection"
2. Sidebar > Query Settings:
   - Order By: **Date**
   - Order: **Descending**
3. Keep default responsive settings
4. Publish!

**Result:**
- Desktop: 8 produk, 4 kolom
- Tablet: 6 produk, 3 kolom
- Mobile: 4 produk, 2 kolom

---

### Use Case 2: Sale Products Section

**Goal:** Tampilkan produk sale dengan badge

**Steps:**
1. Add "Blaze Product Collection"
2. Sidebar > Query Settings:
   - Toggle ON: **Show Only On Sale Products**
   - Order By: **Price**
   - Order: **Ascending**
3. Publish!

**Result:**
- Hanya produk sale yang ditampilkan
- Sorted by price (murah ke mahal)
- Sale badge otomatis muncul

---

### Use Case 3: Product Card with Wishlist

**Goal:** Product card dengan wishlist button dan hover effect

**Steps:**
1. Add "Blaze Product Image"
2. Sidebar > Blaze Wishlist Settings:
   - Toggle ON: **Show Wishlist Button**
   - Position: **Top Right**
3. Sidebar > Image Settings:
   - Toggle ON: **Enable Hover Image**
4. Ensure product has 2+ gallery images
5. Publish!

**Result:**
- Main image displayed
- Wishlist button di top-right
- Hover shows second image
- Click wishlist to add/remove

---

### Use Case 4: Mobile-Optimized Grid

**Goal:** Lebih banyak produk di mobile

**Steps:**
1. Add "Blaze Product Collection"
2. Sidebar > Blaze Responsive Settings:
   - Mobile Columns: **1**
   - Mobile Products: **6**
3. Publish!

**Result:**
- Mobile: 6 produk, 1 kolom (list view)
- Desktop: tetap 8 produk, 4 kolom

---

## 🎨 Styling Tips

### Custom Colors

Add to **Customizer > Additional CSS:**

```css
/* Wishlist button color */
.blaze-product-image__wishlist {
    background: #ff6b6b;
}

.blaze-product-image__wishlist:hover {
    background: #ff5252;
}

/* Sale badge color */
.blaze-product-image__badge--sale {
    background: #27ae60;
}
```

### Custom Spacing

```css
/* Product grid gap */
.blaze-product-collection {
    --blaze-gap: 30px;
}

/* Mobile gap */
@media (max-width: 767px) {
    .blaze-product-collection {
        --blaze-gap: 15px;
    }
}
```

### Custom Hover Effect

```css
/* Zoom on hover */
.blaze-product-collection .products > li:hover {
    transform: scale(1.05);
}

/* Shadow on hover */
.blaze-product-image:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
```

---

## 🔧 Troubleshooting

### Block tidak muncul di editor

**Fix:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check WooCommerce is active

### Responsive tidak berfungsi

**Fix:**
1. Ensure "Enable Responsive Display" is ON
2. Clear cache
3. Test di incognito mode

### Wishlist button tidak berfungsi

**Fix:**
1. Open browser console (F12)
2. Check for JavaScript errors
3. Ensure jQuery is loaded
4. Clear cache and retry

### Hover image tidak muncul

**Fix:**
1. Ensure product has 2+ gallery images
2. Ensure "Enable Hover Image" is ON
3. Check image URLs in inspector
4. Clear image cache

---

## 📱 Mobile Testing

### Quick Test

1. **Chrome DevTools:**
   - Press F12
   - Click device icon (Ctrl+Shift+M)
   - Select device (iPhone, iPad, etc.)
   - Test responsive behavior

2. **Responsive Design Mode:**
   - Resize browser window
   - Watch columns adjust
   - Verify product count changes

### Real Device Test

1. Get page URL
2. Open on mobile device
3. Test:
   - Layout responsive
   - Wishlist button works
   - Touch events work
   - Images load properly

---

## 🎯 Best Practices

### Product Collection

✅ **DO:**
- Use default settings untuk consistency
- Test responsive di semua devices
- Limit product count untuk performance
- Use appropriate image sizes

❌ **DON'T:**
- Set terlalu banyak products (>24)
- Use terlalu banyak columns (>6)
- Forget to test mobile view
- Ignore loading performance

### Product Image

✅ **DO:**
- Always add 2+ gallery images
- Optimize image sizes
- Test wishlist functionality
- Use consistent button positions

❌ **DON'T:**
- Use huge image files
- Forget alt text
- Overlap wishlist with badge
- Ignore accessibility

---

## 📚 Learn More

**Full Documentation:**
- `docs/Blaze_WooCommerce_Blocks_Documentation.md`

**Testing Checklist:**
- `docs/Blaze_Blocks_Testing_Checklist.md`

**Implementation Details:**
- `docs/Blaze_Blocks_Implementation_Summary.md`

**Code Reference:**
- `includes/customization/wc-blocks/README.md`

---

## 🆘 Need Help?

**Common Questions:**

**Q: Berapa maksimal produk yang bisa ditampilkan?**  
A: Recommended max 24 produk untuk performance.

**Q: Bisa custom breakpoints?**  
A: Ya, via CSS custom properties.

**Q: Wishlist compatible dengan plugin lain?**  
A: Terintegrasi dengan Blocksy Wishlist, fallback ke cookie.

**Q: Bisa disable responsive?**  
A: Ya, toggle OFF "Enable Responsive Display".

**Q: Hover image wajib?**  
A: Tidak, optional. Toggle OFF "Enable Hover Image".

---

**Happy Building! 🚀**

