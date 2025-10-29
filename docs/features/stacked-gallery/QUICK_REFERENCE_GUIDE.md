# Quick Reference Guide - Gallery Stacked Modification

## TL;DR - What to Build

Transform WooCommerce product gallery from **slider/carousel** to **stacked vertical layout** on desktop (≥1024px), while keeping slider on mobile.

---

## Visual Layout

### Desktop (≥1024px)
```
┌─────────────────────────────────────────┐
│  [Thumb 1] │  ┌──────────────────┐     │
│  [Thumb 2] │  │   Main Image 1   │     │
│  [Thumb 3] │  │   (with badge)   │     │
│  [Thumb 4] │  └──────────────────┘     │
│  [Thumb 5] │                            │
│            │  ← 18px gap →              │
│            │                            │
│            │  ┌──────────────────┐     │
│            │  │   Main Image 2   │     │
│            │  │   (with badge)   │     │
│            │  └──────────────────┘     │
│            │                            │
│            │  ← 18px gap →              │
│            │                            │
│            │  ┌──────────────────┐     │
│            │  │   Main Image 3   │     │
│            │  └──────────────────┘     │
│            │                            │
│   120px    │  ... (all images shown)   │
│            │                            │
└─────────────────────────────────────────┘
```

### Mobile (<1024px)
```
┌─────────────────────────┐
│  ┌──────────────────┐   │
│  │   Main Image 1   │   │ ← Slider (only 1 visible)
│  │   (with badge)   │   │
│  └──────────────────┘   │
│                         │
│  [◉] [○] [○] [○] [○]   │ ← Thumbnails below
│   ← →                   │ ← Arrows
└─────────────────────────┘
```

---

## Key Requirements

| Feature | Desktop (≥1024px) | Mobile (<1024px) |
|---------|-------------------|------------------|
| **Layout** | Thumbnails left, images stacked | Slider with thumbs below |
| **Thumbnail Display** | All visible (no slider) | Slider if many thumbs |
| **Main Images** | All visible (stacked) | Only 1 visible (slider) |
| **Thumbnail Click** | Scroll to image (top + 100px) | Change slide |
| **Image Spacing** | 18px gap | N/A (slider) |
| **Thumbnail Width** | 120px | Auto |
| **Arrows** | Hidden | Visible |
| **Badge** | Yes (on each image) | Yes |
| **Lightbox** | Yes | Yes |
| **Zoom** | Yes | No (parent theme default) |
| **Video** | Inline | Inline |

---

## Files to Create

### 1. Child Theme Root
```
blocksy-child/
├── style.css          ← Required WordPress child theme header
└── functions.php      ← Enqueue scripts, add filters
```

### 2. Assets
```
blocksy-child/assets/
├── css/
│   └── gallery-stacked.css    ← All custom CSS
└── js/
    └── gallery-stacked.js     ← All custom JavaScript
```

---

## Code Snippets

### style.css (Required)
```css
/*
Theme Name: Blocksy Child
Template: blocksy
Version: 1.0.0
*/
```

### functions.php (Core Logic)
```php
<?php
// Enqueue assets
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_assets', 5);
function blocksy_child_enqueue_assets() {
    wp_enqueue_style(
        'blocksy-child-gallery-stacked',
        get_stylesheet_directory_uri() . '/assets/css/gallery-stacked.css',
        array('blocksy-styles'),
        '1.0.0'
    );
    
    if (is_product()) {
        wp_enqueue_script(
            'blocksy-child-gallery-stacked',
            get_stylesheet_directory_uri() . '/assets/js/gallery-stacked.js',
            array(),
            '1.0.0',
            false // Header, not footer
        );
    }
}

// Add body class
add_filter('body_class', 'blocksy_child_add_stacked_gallery_class');
function blocksy_child_add_stacked_gallery_class($classes) {
    if (is_product()) {
        $classes[] = 'ct-has-stacked-gallery';
    }
    return $classes;
}

// Modify flexy args
add_filter('blocksy:woocommerce:single_product:flexy-args', 'blocksy_child_modify_flexy_args', 20);
function blocksy_child_modify_flexy_args($args) {
    $args['class'] = isset($args['class']) ? $args['class'] . ' ct-stacked-desktop' : 'ct-stacked-desktop';
    return $args;
}
```

### gallery-stacked.css (Key Rules)
```css
/* Desktop only */
@media (min-width: 1024px) {
    /* Disable slider transforms */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items {
        display: flex !important;
        flex-direction: column !important;
        gap: 18px !important;
        transform: none !important;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > * {
        flex: 0 0 auto !important;
        width: 100% !important;
        max-width: 100% !important;
        transform: none !important;
        height: auto !important;
        opacity: 1 !important;
    }
    
    /* Thumbnails on left */
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills {
        position: absolute;
        left: 0;
        top: 0;
        height: auto;
        --thumbs-width: 120px;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills ol {
        display: flex;
        flex-direction: column;
        overflow: visible;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy {
        margin-left: calc(120px + var(--thumbs-spacing, 15px));
    }
    
    /* Hide arrows */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-arrow-prev,
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-arrow-next,
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills .flexy-arrow-prev,
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills .flexy-arrow-next {
        display: none !important;
    }
    
    /* Remove height restrictions */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-view {
        height: auto !important;
        overflow: visible !important;
    }
}
```

### gallery-stacked.js (Key Logic)
```javascript
(function($) {
    'use strict';
    
    function isDesktop() {
        return window.innerWidth >= 1024;
    }
    
    function preventFlexyInit() {
        if (!isDesktop()) return;
        
        const gallery = document.querySelector('.woocommerce-product-gallery .flexy-container');
        if (gallery && gallery.hasAttribute('data-flexy')) {
            gallery.removeAttribute('data-flexy');
        }
    }
    
    function initThumbnailScroll() {
        if (!isDesktop()) return;
        
        const pills = document.querySelectorAll('.woocommerce-product-gallery .flexy-pills li');
        const images = document.querySelectorAll('.woocommerce-product-gallery .flexy-items > *');
        
        pills.forEach((pill, index) => {
            pill.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active state
                pills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                
                // Scroll to image
                if (images[index]) {
                    const imageTop = images[index].getBoundingClientRect().top + window.pageYOffset;
                    window.scrollTo({
                        top: imageTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        preventFlexyInit();
        initThumbnailScroll();
    });
    
    // Handle resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            preventFlexyInit();
            initThumbnailScroll();
        }, 250);
    });
    
})(jQuery);
```

---

## Critical CSS Selectors

### Parent Theme Classes (DO NOT MODIFY)
- `.woocommerce-product-gallery` - Main container
- `.flexy-container` - Flexy wrapper
- `.flexy-items` - Images container
- `.flexy-pills` - Thumbnails container
- `.thumbs-left` - Left thumbnail layout

### Child Theme Classes (ADD THESE)
- `.ct-has-stacked-gallery` - Body class (desktop mode)
- `.ct-stacked-desktop` - Gallery class (custom identifier)

---

## Important CSS Variables

| Variable | Default | Desktop Override |
|----------|---------|------------------|
| `--thumbs-width` | 100px | 120px |
| `--thumbs-spacing` | 15px | Keep default |
| `--flexy-item-width` | 100% | auto |
| Gap between images | 0 | 18px |

---

## JavaScript Timing

**Critical**: Prevent Flexy initialization BEFORE parent theme runs

```php
// In functions.php - Priority 5 (before parent's 10)
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_assets', 5);

// Load in header (false), not footer (true)
wp_enqueue_script(..., false);
```

---

## Testing Checklist (Minimal)

### Desktop
- [ ] All thumbnails visible (no slider)
- [ ] All images stacked with 18px gap
- [ ] Click thumbnail → scrolls to image
- [ ] Click image → opens lightbox
- [ ] Badge visible on each image

### Mobile
- [ ] Slider works (swipe/arrows)
- [ ] Thumbnails below image
- [ ] Only 1 image visible at a time

---

## Common Pitfalls

1. **Flexy still initializes** → Script loads too late (use priority 5, header)
2. **Images not stacked** → CSS specificity too low (use `!important`)
3. **Lightbox broken** → HTML structure changed (don't modify HTML)
4. **Mobile broken** → CSS/JS not wrapped in `@media (min-width: 1024px)`
5. **Scroll offset wrong** → Not accounting for admin bar/header

---

## Debugging

### Check if desktop mode active:
```javascript
console.log('Desktop:', window.innerWidth >= 1024);
console.log('Body class:', document.body.classList.contains('ct-has-stacked-gallery'));
```

### Check if Flexy prevented:
```javascript
const gallery = document.querySelector('.flexy-container');
console.log('Has data-flexy:', gallery.hasAttribute('data-flexy'));
console.log('Flexy instance:', gallery.flexy);
```

### Visual debug CSS:
```css
.flexy-items > * { outline: 2px solid red; }
.flexy-pills li { outline: 2px solid blue; }
.flexy-pills li.active { outline: 2px solid green; }
```

---

## Support & Resources

- **Full Documentation**: See `TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md`
- **Parent Theme**: Blocksy (located at `/wp-content/themes/blocksy`)
- **Key Parent Files**:
  - `inc/components/gallery.php` - Gallery HTML
  - `static/js/frontend/flexy.js` - Flexy initialization
  - `static/sass/frontend/4-components/flexy.scss` - Flexy styles

---

## Quick Start Commands

```bash
# Navigate to themes directory
cd wp-content/themes

# Create child theme
mkdir blocksy-child
cd blocksy-child

# Create structure
mkdir -p assets/css assets/js

# Create files
touch style.css functions.php
touch assets/css/gallery-stacked.css
touch assets/js/gallery-stacked.js

# Edit files (use code snippets above)
```

---

## Final Notes

- **DO NOT** modify parent theme files
- **DO** test on real products with multiple images
- **DO** test on mobile devices (not just browser resize)
- **DO** test with WooCommerce variable products
- **DO** remove debug code before production

---

**Quick Reference Version**: 1.0  
**For Full Details**: See `TECHNICAL_DOCUMENTATION_GALLERY_MODIFICATION.md`

