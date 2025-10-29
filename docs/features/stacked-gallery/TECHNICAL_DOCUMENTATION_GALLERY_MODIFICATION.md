# Technical Documentation: WooCommerce Product Gallery Modification

## Project Overview
Modify WooCommerce product gallery in a **child theme** to display all images stacked vertically on desktop with thumbnails on the left, while maintaining the existing flexy slider behavior on mobile.

---

## Requirements Summary

### Desktop (≥1024px)
- **Layout**: Thumbnails on left (vertical), main images on right (stacked vertically)
- **Thumbnail Display**: ALL thumbnails visible (no slider/carousel)
- **Main Images Display**: ALL main images visible (stacked, no slideshow)
- **Thumbnail Click**: Smooth scroll to corresponding main image (top of image + 100px offset from viewport top)
- **Spacing**: 18px between stacked main images
- **Thumbnail Width**: 120px
- **Thumbnail Spacing**: Use existing `--thumbs-spacing` variable (default 15px)
- **Image Ratio**: Follow `product_gallery_ratio` theme setting (default 3/4)
- **Badge**: Maintain existing badge functionality (SALE, SOLD OUT)
- **Lightbox**: Maintain existing PhotoSwipe lightbox functionality
- **Video**: Display inline (not click-to-play)

### Mobile (<1024px)
- **Layout**: Keep existing flexy slider/carousel behavior
- **Thumbnail Position**: Below main image
- **Slideshow**: Active (slider functionality enabled)

---

## Parent Theme Architecture Analysis

### Key Files & Components

#### 1. **Gallery Template**
- **File**: `inc/components/woocommerce/single/woo-gallery-template.php`
- **Function**: Renders product gallery HTML
- **Key Logic**:
  - Lines 245-264: Calls `blocksy_flexy()` when multiple images exist
  - Line 257: `pills_images` parameter controls thumbnail display
  - Filter: `blocksy:woocommerce:single_product:flexy-args` (line 251)

#### 2. **Gallery Component**
- **File**: `inc/components/gallery.php`
- **Functions**:
  - `blocksy_flexy()`: Main gallery container (lines 3-214)
  - `blocksy_flexy_pills()`: Thumbnail pills (lines 216-300)
- **Key Parameters**:
  - `images`: Array of attachment IDs
  - `pills_images`: Array for thumbnails (if null, no thumbs)
  - `has_pills`: Boolean to show/hide pills
  - `enable`: Boolean to enable/disable flexy slider
  - `images_ratio`: Aspect ratio for images

#### 3. **JavaScript - Flexy Slider**
- **File**: `static/js/frontend/flexy.js`
- **Functionality**: Initializes Flexy slider library
- **Key Logic**:
  - Lines 86-92: Pills container selector
  - Lines 115-119: Pills flexy instance integration
  - Lines 132-161: Separate flexy instance for pills slider
  - Lines 152-157: Vertical orientation for `.thumbs-left`

#### 4. **JavaScript - Gallery Interactions**
- **File**: `static/js/frontend/woocommerce/single-product-gallery.js`
- **Functionality**: Lightbox, zoom, and gallery interactions
- **Key Logic**:
  - Lines 9-175: PhotoSwipe lightbox implementation
  - Lines 197-285: Zoom functionality
  - Lines 319-443: Lightbox trigger handlers

#### 5. **SCSS - Gallery Styles**
- **Files**:
  - `static/sass/frontend/4-components/flexy.scss`: Core flexy styles
  - `static/sass/frontend/8-integrations/woocommerce/product-page/default-gallery.scss`: Gallery-specific styles
  - `static/sass/frontend/8-integrations/woocommerce/product-page/gallery-common.scss`: Common gallery styles

- **Key Styles**:
  - `.thumbs-left` class: Positions thumbnails on left (lines 65-105 in default-gallery.scss)
  - `--thumbs-width`: CSS variable for thumbnail width
  - `--thumbs-spacing`: CSS variable for spacing
  - `.flexy-pills`: Thumbnail container
  - `.flexy-items`: Main images container

#### 6. **Dynamic Styles**
- **File**: `inc/dynamic-styles/global/woocommerce/single-product-gallery.php`
- **Functionality**: Outputs dynamic CSS based on theme options
- **Key Variables**:
  - `--product-gallery-width`: Gallery width percentage
  - `--thumbs-spacing`: Thumbnail spacing
  - `product_gallery_ratio`: Image aspect ratio

---

## Implementation Strategy (Child Theme)

### Phase 1: Setup Child Theme Structure

Create the following structure:
```
blocksy-child/
├── functions.php
├── style.css
├── inc/
│   └── woocommerce/
│       └── gallery-stacked.php
├── assets/
│   ├── css/
│   │   └── gallery-stacked.css
│   └── js/
│       └── gallery-stacked.js
```

### Phase 2: PHP Modifications

#### File: `functions.php`
```php
<?php
// Enqueue child theme styles and scripts
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_assets', 20);
function blocksy_child_enqueue_assets() {
    // Enqueue custom gallery CSS
    wp_enqueue_style(
        'blocksy-child-gallery-stacked',
        get_stylesheet_directory_uri() . '/assets/css/gallery-stacked.css',
        array('blocksy-styles'),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue custom gallery JS (only on single product pages)
    if (is_product()) {
        wp_enqueue_script(
            'blocksy-child-gallery-stacked',
            get_stylesheet_directory_uri() . '/assets/js/gallery-stacked.js',
            array('jquery'),
            wp_get_theme()->get('Version'),
            true
        );
    }
}

// Modify flexy args for stacked gallery on desktop
add_filter('blocksy:woocommerce:single_product:flexy-args', 'blocksy_child_modify_flexy_args', 20);
function blocksy_child_modify_flexy_args($args) {
    // Add custom class to identify stacked gallery
    $args['class'] = isset($args['class']) ? $args['class'] . ' ct-stacked-desktop' : 'ct-stacked-desktop';
    
    return $args;
}

// Add custom body class for stacked gallery
add_filter('body_class', 'blocksy_child_add_stacked_gallery_class');
function blocksy_child_add_stacked_gallery_class($classes) {
    if (is_product()) {
        $classes[] = 'ct-has-stacked-gallery';
    }
    return $classes;
}
```

### Phase 3: CSS Implementation

#### File: `assets/css/gallery-stacked.css`

**Key CSS Rules to Implement:**

1. **Desktop Layout (≥1024px)**:
```css
@media (min-width: 1024px) {
    /* Disable flexy slider on desktop */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-container.ct-stacked-desktop {
        /* Remove slider initialization */
    }
    
    /* Thumbnail container - left side, vertical, no slider */
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills {
        position: absolute;
        left: 0;
        top: 0;
        height: auto; /* Allow full height */
        --thumbs-width: 120px;
        --pills-direction: column;
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills ol {
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        overflow: visible; /* Show all thumbnails */
    }
    
    /* Remove flexy slider from pills */
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy-pills [data-flexy] {
        overflow: visible;
        height: auto;
    }
    
    /* Main images container - stacked vertically */
    .ct-has-stacked-gallery .woocommerce-product-gallery.thumbs-left .flexy {
        margin-left: calc(120px + var(--thumbs-spacing, 15px));
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items {
        display: flex;
        flex-direction: column;
        gap: 18px;
        transform: none !important; /* Disable slider transform */
    }
    
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > * {
        flex: 0 0 auto;
        max-width: 100%;
        width: 100%;
        transform: none !important; /* Disable slider transform */
        height: auto !important;
        opacity: 1 !important;
    }
    
    /* Hide slider arrows on desktop */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy .flexy-arrow-prev,
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy .flexy-arrow-next {
        display: none;
    }
    
    /* Hide pills arrows on desktop */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills .flexy-arrow-prev,
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills .flexy-arrow-next {
        display: none;
    }
    
    /* Ensure flexy-view doesn't restrict height */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-view {
        height: auto !important;
        overflow: visible;
    }
    
    /* Thumbnail styling */
    .ct-has-stacked-gallery .woocommerce-product-gallery .flexy-pills li {
        width: 120px;
        flex: 0 0 120px;
        cursor: pointer;
    }
}

/* Mobile - keep flexy slider behavior */
@media (max-width: 1023px) {
    /* No modifications needed - use parent theme behavior */
}
```

### Phase 4: JavaScript Implementation

#### File: `assets/js/gallery-stacked.js`

**Key Functionality to Implement:**

1. **Disable Flexy Initialization on Desktop**:
```javascript
(function($) {
    'use strict';
    
    // Check if desktop
    function isDesktop() {
        return window.innerWidth >= 1024;
    }
    
    // Disable flexy on desktop, enable on mobile
    function handleGalleryMode() {
        if (!isDesktop()) {
            return; // Let parent theme handle mobile
        }
        
        const gallery = document.querySelector('.woocommerce-product-gallery .flexy-container.ct-stacked-desktop');
        if (!gallery) return;
        
        // Remove data-flexy attribute to prevent initialization
        if (gallery.hasAttribute('data-flexy')) {
            gallery.removeAttribute('data-flexy');
        }
        
        // Prevent flexy from initializing
        if (gallery.flexy) {
            // Destroy flexy instance if exists
            gallery.flexy = null;
        }
    }
    
    // Thumbnail click handler - smooth scroll to image
    function initThumbnailScroll() {
        if (!isDesktop()) return;
        
        const pills = document.querySelectorAll('.woocommerce-product-gallery .flexy-pills li');
        const images = document.querySelectorAll('.woocommerce-product-gallery .flexy-items > *');
        
        pills.forEach((pill, index) => {
            pill.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Remove active class from all pills
                pills.forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked pill
                this.classList.add('active');
                
                // Scroll to corresponding image
                if (images[index]) {
                    const imageTop = images[index].getBoundingClientRect().top + window.pageYOffset;
                    const offset = 100; // 100px from top
                    
                    window.scrollTo({
                        top: imageTop - offset,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    // Initialize on DOM ready
    $(document).ready(function() {
        handleGalleryMode();
        initThumbnailScroll();
    });
    
    // Re-initialize on window resize (debounced)
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            handleGalleryMode();
            initThumbnailScroll();
        }, 250);
    });
    
})(jQuery);
```

---

## Important Considerations

### 1. **Flexy Library Integration**
- Parent theme uses external Flexy library (imported from 'flexy' package)
- On desktop, we need to prevent Flexy initialization entirely
- On mobile, let parent theme handle Flexy normally

### 2. **Lightbox Compatibility**
- File: `static/js/frontend/woocommerce/single-product-gallery.js`
- Lightbox queries `.flexy-items .ct-media-container` (line 418)
- Our stacked layout maintains same HTML structure, so lightbox should work
- Test thoroughly: clicking images should open PhotoSwipe lightbox

### 3. **Badge Positioning**
- Badges are rendered in `woo-gallery-template.php` (lines 128-169)
- Badges are positioned on `.ct-media-container`
- With stacked layout, each image will have its own badge
- No modifications needed

### 4. **Video Support**
- Videos are handled by `blocksy_media()` function
- `display_video` parameter set to `true` in flexy args
- Videos should display inline in stacked layout
- Test video playback and ensure no conflicts

### 5. **Zoom Functionality**
- Zoom is handled in `single-product-gallery.js` (lines 240-285)
- Uses jQuery zoom plugin on `.ct-media-container`
- Should work with stacked layout
- Test zoom on each stacked image

---

## Testing Checklist

### Desktop (≥1024px)
- [ ] All thumbnails visible on left (no slider)
- [ ] All main images visible stacked vertically
- [ ] 18px gap between main images
- [ ] Thumbnail width is 120px
- [ ] Clicking thumbnail scrolls to correct image (top + 100px offset)
- [ ] Smooth scroll animation works
- [ ] Active thumbnail has visual indicator
- [ ] No slider arrows visible
- [ ] Badge displays on each image (SALE/SOLD OUT)
- [ ] Lightbox opens when clicking main image
- [ ] Lightbox shows correct image index
- [ ] Zoom functionality works on hover
- [ ] Video displays inline and plays correctly
- [ ] Layout doesn't break with 1 image
- [ ] Layout doesn't break with 10+ images

### Mobile (<1024px)
- [ ] Flexy slider active
- [ ] Thumbnails below main image
- [ ] Slider arrows visible and functional
- [ ] Swipe gesture works
- [ ] Pills slider works (if many thumbnails)
- [ ] Badge displays correctly
- [ ] Lightbox works
- [ ] Video works

### Cross-Browser
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### Edge Cases
- [ ] Product with 1 image (no gallery)
- [ ] Product with video in gallery
- [ ] Variable product (variation image switching)
- [ ] Quick view modal (if enabled)
- [ ] RTL layout (if applicable)

---

## Files to Create in Child Theme

1. **functions.php** - Main child theme functions
2. **style.css** - Child theme header (required)
3. **assets/css/gallery-stacked.css** - Custom gallery styles
4. **assets/js/gallery-stacked.js** - Custom gallery JavaScript

---

## Filters & Hooks Used

- `blocksy:woocommerce:single_product:flexy-args` - Modify flexy arguments
- `body_class` - Add custom body class
- `wp_enqueue_scripts` - Enqueue custom assets

---

## CSS Variables to Respect

- `--thumbs-width`: Thumbnail width (set to 120px on desktop)
- `--thumbs-spacing`: Spacing between thumbnails (use existing value)
- `--product-gallery-width`: Gallery container width
- `product_gallery_ratio`: Image aspect ratio from theme options

---

## Notes for Implementation

1. **Do NOT modify parent theme files** - All changes in child theme
2. **Maintain parent theme HTML structure** - Only override styles and behavior
3. **Use high specificity CSS** - Ensure child theme styles override parent
4. **Test with WooCommerce variations** - Ensure variation switching works
5. **Preserve all parent theme functionality** - Only change layout, not features
6. **Use parent theme's CSS variables** - Maintain consistency
7. **Enqueue scripts with proper dependencies** - Ensure jQuery loads first
8. **Test responsive breakpoints** - Ensure smooth transition at 1024px

---

## Additional Technical Details

### Parent Theme Class Structure

**Gallery Container Classes:**
- `.woocommerce-product-gallery` - Main gallery wrapper
- `.ct-product-gallery-container` - Inner container
- `.flexy-container` - Flexy slider wrapper
- `.flexy` - Flexy main element
- `.flexy-view` - Viewport container
- `.flexy-items` - Images container
- `.flexy-item` - Individual image wrapper
- `.ct-media-container` - Media wrapper (contains image/video)

**Thumbnail Classes:**
- `.flexy-pills` - Pills container
- `.flexy-pills[data-type="thumbs"]` - Thumbnail type pills
- `.flexy-pills ol` - Thumbnail list
- `.flexy-pills li` - Individual thumbnail
- `.flexy-pills li.active` - Active thumbnail

**Layout Classes:**
- `.thumbs-left` - Thumbnails on left layout (desktop)
- `.ct-default-gallery` - Default gallery type
- `.ct-stacked-gallery` - Stacked gallery type (different from our requirement)

### Data Attributes

**Flexy Control:**
- `data-flexy="no"` - Flexy not initialized yet
- `data-flexy=""` - Flexy initialized
- `data-flexy-moving` - Flexy is animating
- `data-flexy-dragging` - User is dragging
- `data-flexy-view="boxed"` - Boxed view mode

**Pills Control:**
- `data-type="thumbs"` - Thumbnail pills
- `data-type="circle"` - Circle pills (pagination dots)

### JavaScript Events

**Flexy Events:**
- `blocksy:frontend:flexy:slide-change` - Triggered when slide changes
  - Payload: `{ instance, payload }`
  - File: `static/js/frontend/flexy.js` line 122

**Usage Example:**
```javascript
import ctEvents from 'ct-events';

ctEvents.on('blocksy:frontend:flexy:slide-change', (data) => {
    console.log('Slide changed', data);
});
```

### Critical CSS Overrides Needed

**Priority 1 - Disable Slider Transform:**
```css
.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items {
    transform: none !important;
    will-change: auto !important;
}

.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > * {
    transform: none !important;
}
```

**Priority 2 - Disable Height Restrictions:**
```css
.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-view {
    height: auto !important;
}

.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > * {
    height: auto !important;
}

.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > *:not(.flexy-item-is-visible) {
    height: auto !important;
}
```

**Priority 3 - Disable Overflow Hidden:**
```css
.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-view[data-flexy-view='boxed'] {
    overflow: visible !important;
}
```

### JavaScript Timing Considerations

**Issue**: Parent theme initializes Flexy on page load
**Solution**: Hook into initialization before Flexy mounts

**Approach 1 - Prevent Initialization:**
```javascript
// Run BEFORE parent theme scripts
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth >= 1024) {
        const gallery = document.querySelector('.woocommerce-product-gallery .flexy-container');
        if (gallery && gallery.hasAttribute('data-flexy')) {
            // Change attribute to prevent Flexy from initializing
            gallery.setAttribute('data-flexy-disabled', gallery.getAttribute('data-flexy'));
            gallery.removeAttribute('data-flexy');
        }
    }
}, { capture: true, once: true });
```

**Approach 2 - Destroy After Initialization:**
```javascript
// Run AFTER parent theme scripts
window.addEventListener('load', function() {
    if (window.innerWidth >= 1024) {
        const gallery = document.querySelector('.woocommerce-product-gallery .flexy-container');
        if (gallery && gallery.flexy) {
            // Destroy flexy instance
            if (typeof gallery.flexy.destroy === 'function') {
                gallery.flexy.destroy();
            }
            gallery.flexy = null;
        }
    }
});
```

**Recommended**: Use Approach 1 with high priority script loading

### Script Enqueue Priority

```php
// In functions.php - enqueue with high priority to run before parent theme
add_action('wp_enqueue_scripts', 'blocksy_child_enqueue_assets', 5); // Priority 5 (before parent's 10)

function blocksy_child_enqueue_assets() {
    wp_enqueue_script(
        'blocksy-child-gallery-stacked',
        get_stylesheet_directory_uri() . '/assets/js/gallery-stacked.js',
        array(), // NO dependencies to run as early as possible
        wp_get_theme()->get('Version'),
        false // Load in header, not footer
    );
}
```

### Handling Variable Products

**Issue**: When variation changes, parent theme may re-initialize gallery
**File**: `static/js/frontend/woocommerce/variable-products.js`

**Solution**: Hook into variation change event
```javascript
jQuery(document).on('found_variation', function(event, variation) {
    if (window.innerWidth >= 1024) {
        // Re-apply stacked gallery after variation change
        setTimeout(function() {
            handleGalleryMode();
            initThumbnailScroll();
        }, 100);
    }
});
```

### Thumbnail Active State Management

**Current Behavior** (Parent Theme):
- Flexy library automatically manages `.active` class on pills
- Active class added to pill corresponding to visible slide

**New Behavior** (Child Theme Desktop):
- Manually manage `.active` class on thumbnail click
- No automatic active state change on scroll

**Implementation:**
```javascript
function setActiveThumbnail(index) {
    const pills = document.querySelectorAll('.woocommerce-product-gallery .flexy-pills li');
    pills.forEach((pill, i) => {
        if (i === index) {
            pill.classList.add('active');
        } else {
            pill.classList.remove('active');
        }
    });
}
```

### Scroll Offset Calculation

**Requirement**: Scroll to top of image + 100px offset

**Consideration**: Account for:
1. Fixed header (if exists)
2. Admin bar (if logged in)
3. Sticky elements

**Robust Implementation:**
```javascript
function scrollToImage(imageElement) {
    const imageTop = imageElement.getBoundingClientRect().top + window.pageYOffset;
    const baseOffset = 100;

    // Check for admin bar
    const adminBar = document.getElementById('wpadminbar');
    const adminBarHeight = adminBar ? adminBar.offsetHeight : 0;

    // Check for sticky header
    const header = document.querySelector('.site-header');
    const headerHeight = header && header.classList.contains('sticky') ? header.offsetHeight : 0;

    const totalOffset = baseOffset + adminBarHeight + headerHeight;

    window.scrollTo({
        top: imageTop - totalOffset,
        behavior: 'smooth'
    });
}
```

### CSS Specificity Strategy

**Issue**: Parent theme has specific selectors that may override child theme
**Solution**: Use higher specificity without !important where possible

**Example:**
```css
/* Parent theme selector */
.woocommerce-product-gallery .flexy-items > * {
    flex: 0 0 var(--flexy-item-width, 100%);
}

/* Child theme - higher specificity */
.ct-has-stacked-gallery.single-product .woocommerce-product-gallery .flexy-items > * {
    flex: 0 0 auto;
}

/* Or use !important as last resort */
.ct-has-stacked-gallery .woocommerce-product-gallery .flexy-items > * {
    flex: 0 0 auto !important;
}
```

### Debugging Helpers

**Add to JavaScript for debugging:**
```javascript
// Debug mode
const DEBUG = true;

function debugLog(message, data) {
    if (DEBUG && window.console) {
        console.log('[Gallery Stacked]', message, data || '');
    }
}

// Usage
debugLog('Gallery mode initialized', { isDesktop: isDesktop() });
debugLog('Thumbnail clicked', { index: index });
debugLog('Scrolling to image', { top: imageTop, offset: offset });
```

**Add to CSS for visual debugging:**
```css
/* Uncomment for debugging */
/*
.ct-has-stacked-gallery .flexy-items > * {
    outline: 2px solid red !important;
}

.ct-has-stacked-gallery .flexy-pills li {
    outline: 2px solid blue !important;
}

.ct-has-stacked-gallery .flexy-pills li.active {
    outline: 2px solid green !important;
}
*/
```

### Performance Considerations

1. **Lazy Loading**: Parent theme supports lazy loading (`lazyload` parameter)
   - Ensure lazy loading still works with stacked layout
   - Images should load as they enter viewport

2. **Image Count**: With all images visible, page may be heavy
   - Consider virtual scrolling for 20+ images (advanced)
   - Or accept performance trade-off for better UX

3. **Scroll Performance**: Smooth scroll may be janky with many images
   - Test with 10+ images
   - Consider using `scroll-behavior: smooth` CSS instead of JS

### Browser Compatibility

**Smooth Scroll:**
- `window.scrollTo({ behavior: 'smooth' })` not supported in Safari < 15.4
- Fallback:
```javascript
function smoothScrollTo(top) {
    if ('scrollBehavior' in document.documentElement.style) {
        window.scrollTo({ top: top, behavior: 'smooth' });
    } else {
        // Fallback for older browsers
        window.scrollTo(0, top);
    }
}
```

**CSS Grid/Flexbox:**
- All modern browsers support flexbox
- No fallback needed

### Accessibility Considerations

**Keyboard Navigation:**
- Ensure thumbnails are keyboard accessible
- Add focus styles

```css
.ct-has-stacked-gallery .flexy-pills li:focus {
    outline: 2px solid var(--theme-palette-color-1, #3366ff);
    outline-offset: 2px;
}
```

**ARIA Labels:**
- Parent theme already has `aria-label` on thumbnails
- Maintain these in child theme

**Screen Readers:**
- Ensure stacked images have proper alt text
- Parent theme handles this via `blocksy_media()` function

### Common Issues & Solutions

**Issue 1**: Flexy still initializes on desktop
- **Cause**: Script runs too late
- **Solution**: Enqueue script in header with no dependencies

**Issue 2**: Lightbox doesn't work
- **Cause**: HTML structure changed
- **Solution**: Maintain exact HTML structure from parent theme

**Issue 3**: Thumbnails not clickable
- **Cause**: Event listener not attached
- **Solution**: Ensure `initThumbnailScroll()` runs after DOM ready

**Issue 4**: Scroll offset incorrect
- **Cause**: Not accounting for fixed elements
- **Solution**: Calculate all fixed element heights

**Issue 5**: Active thumbnail not updating
- **Cause**: Parent theme Flexy overriding
- **Solution**: Remove Flexy instance completely on desktop

**Issue 6**: Gap between images not 18px
- **Cause**: CSS specificity issue
- **Solution**: Use `!important` or higher specificity selector

**Issue 7**: Mobile slider broken
- **Cause**: CSS/JS affecting mobile
- **Solution**: Wrap all custom code in `@media (min-width: 1024px)`

### Final Implementation Checklist

- [ ] Create child theme folder structure
- [ ] Create `style.css` with proper header
- [ ] Create `functions.php` with enqueue functions
- [ ] Create `assets/css/gallery-stacked.css`
- [ ] Create `assets/js/gallery-stacked.js`
- [ ] Test on desktop (≥1024px)
- [ ] Test on mobile (<1024px)
- [ ] Test with 1 image
- [ ] Test with 10+ images
- [ ] Test with video
- [ ] Test variable products
- [ ] Test lightbox
- [ ] Test zoom
- [ ] Test badges
- [ ] Test in all major browsers
- [ ] Test keyboard navigation
- [ ] Test with screen reader
- [ ] Optimize performance
- [ ] Remove debug code
- [ ] Document any custom settings

---

## End of Documentation

**Document Version**: 1.0
**Last Updated**: 2025-10-29
**Target Theme**: Blocksy (Parent)
**Implementation**: Child Theme
**WordPress Version**: 6.0+
**WooCommerce Version**: 7.0+
**PHP Version**: 7.4+

