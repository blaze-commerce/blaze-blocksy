# Visual Diagrams & Architecture

Visual representations of the gallery modification architecture, flow, and structure.

---

## 1. Layout Comparison

### Current (Parent Theme) - Desktop
```
┌─────────────────────────────────────────────┐
│                                             │
│         ┌──────────────────────┐           │
│         │                      │           │
│         │   Main Image 1       │  ← Only 1 visible
│         │   [SALE Badge]       │           │
│         │                      │           │
│         └──────────────────────┘           │
│                                             │
│         ← →  (Arrows)                      │
│                                             │
│    [T1] [T2] [T3] [T4] [T5]  ← →          │
│    Thumbnails (slider if many)             │
│                                             │
└─────────────────────────────────────────────┘
```

### New (Child Theme) - Desktop
```
┌─────────────────────────────────────────────┐
│                                             │
│  [T1]    ┌──────────────────────┐          │
│          │                      │          │
│  [T2]    │   Main Image 1       │          │
│          │   [SALE Badge]       │          │
│  [T3]    │                      │          │
│          └──────────────────────┘          │
│  [T4]                                       │
│          ↕ 18px gap                        │
│  [T5]                                       │
│          ┌──────────────────────┐          │
│  120px   │                      │          │
│  width   │   Main Image 2       │          │
│          │                      │          │
│          └──────────────────────┘          │
│                                             │
│          ↕ 18px gap                        │
│                                             │
│          ┌──────────────────────┐          │
│          │                      │          │
│          │   Main Image 3       │          │
│          │                      │          │
│          └──────────────────────┘          │
│                                             │
│          ... (all images visible)          │
│                                             │
└─────────────────────────────────────────────┘
```

### Mobile (<1024px) - No Change
```
┌──────────────────────┐
│                      │
│  ┌────────────────┐  │
│  │                │  │
│  │  Main Image 1  │  │ ← Slider (1 visible)
│  │  [SALE Badge]  │  │
│  │                │  │
│  └────────────────┘  │
│                      │
│      ← →             │
│                      │
│  [T1][T2][T3][T4]    │
│                      │
└──────────────────────┘
```

---

## 2. HTML Structure

### Parent Theme Structure (Maintained)
```html
<div class="woocommerce-product-gallery thumbs-left">
  <div class="ct-product-gallery-container">
    
    <!-- Badges -->
    <span class="onsale">SALE!</span>
    
    <!-- Lightbox Trigger -->
    <a href="#" class="woocommerce-product-gallery__trigger">🔍</a>
    
    <!-- Main Gallery Container -->
    <div class="flexy-container ct-stacked-desktop" data-flexy="no">
      <div class="flexy">
        <div class="flexy-view" data-flexy-view="boxed">
          
          <!-- Main Images -->
          <div class="flexy-items">
            <div class="flexy-item">
              <figure class="ct-media-container" data-src="image1.jpg">
                <img src="image1.jpg" alt="Product">
              </figure>
            </div>
            <div class="flexy-item">
              <figure class="ct-media-container" data-src="image2.jpg">
                <img src="image2.jpg" alt="Product">
              </figure>
            </div>
            <!-- ... more images ... -->
          </div>
          
        </div>
        
        <!-- Arrows (hidden on desktop in child theme) -->
        <span class="flexy-arrow-prev">←</span>
        <span class="flexy-arrow-next">→</span>
      </div>
      
      <!-- Thumbnails -->
      <div class="flexy-pills" data-type="thumbs">
        <ol>
          <li class="active">
            <span class="ct-media-container">
              <img src="thumb1.jpg" alt="Slide 1">
            </span>
          </li>
          <li>
            <span class="ct-media-container">
              <img src="thumb2.jpg" alt="Slide 2">
            </span>
          </li>
          <!-- ... more thumbnails ... -->
        </ol>
      </div>
      
    </div>
  </div>
</div>
```

---

## 3. CSS Architecture

### CSS Cascade & Specificity
```
┌─────────────────────────────────────────────┐
│  Parent Theme CSS                           │
│  (Lower Specificity)                        │
│                                             │
│  .flexy-items {                             │
│    display: flex;                           │
│    transform: translateX(...);              │
│  }                                          │
└─────────────────────────────────────────────┘
                    ↓ Overridden by
┌─────────────────────────────────────────────┐
│  Child Theme CSS                            │
│  (Higher Specificity + !important)          │
│                                             │
│  @media (min-width: 1024px) {               │
│    .ct-has-stacked-gallery                  │
│    .woocommerce-product-gallery             │
│    .flexy-items {                           │
│      flex-direction: column !important;     │
│      gap: 18px !important;                  │
│      transform: none !important;            │
│    }                                        │
│  }                                          │
└─────────────────────────────────────────────┘
```

### CSS Variables Flow
```
Parent Theme Sets:
--thumbs-width: 100px
--thumbs-spacing: 15px
--flexy-item-width: 100%

        ↓

Child Theme Overrides (Desktop):
--thumbs-width: 120px
--thumbs-spacing: 15px (keep)
--flexy-item-width: auto (via flex: 0 0 auto)
```

---

## 4. JavaScript Flow

### Desktop (≥1024px)
```
Page Load
    ↓
DOMContentLoaded Event
    ↓
Child Theme JS Runs (Priority: Early)
    ↓
preventFlexyInitialization()
    ↓
Remove data-flexy attribute
    ↓
Parent Theme JS Runs
    ↓
Flexy checks for data-flexy
    ↓
Not found → Flexy NOT initialized ✓
    ↓
initializeThumbnailScroll()
    ↓
Attach click handlers to thumbnails
    ↓
User clicks thumbnail
    ↓
Calculate scroll position
    ↓
Smooth scroll to image
    ↓
Update active thumbnail
```

### Mobile (<1024px)
```
Page Load
    ↓
DOMContentLoaded Event
    ↓
Child Theme JS Runs
    ↓
isDesktop() → false
    ↓
Skip preventFlexyInitialization()
    ↓
Skip initializeThumbnailScroll()
    ↓
Parent Theme JS Runs
    ↓
Flexy initializes normally ✓
    ↓
Slider works as expected
```

---

## 5. File Dependencies

```
WordPress Core
    ↓
Blocksy Parent Theme
    ├── inc/components/gallery.php
    ├── static/js/frontend/flexy.js
    └── static/sass/frontend/4-components/flexy.scss
    ↓
Blocksy Child Theme
    ├── style.css (depends on: blocksy-styles)
    ├── functions.php
    ├── assets/css/gallery-stacked.css (depends on: blocksy-styles)
    └── assets/js/gallery-stacked.js (no dependencies)
```

---

## 6. Enqueue Priority

```
Priority 5:  Child Theme JS (header)
             ↓
Priority 10: Parent Theme CSS
             ↓
Priority 10: Parent Theme JS
             ↓
Priority 10: Child Theme CSS (after parent)
```

**Why Priority 5 for Child JS?**
- Runs BEFORE parent theme JS
- Can prevent Flexy initialization
- Critical for desktop stacked layout

---

## 7. Responsive Breakpoints

```
Mobile          Tablet          Desktop
0px ────────── 768px ────────── 1024px ──────────→

├─────────────────────┤├──────────────────────────→
   Flexy Slider         Stacked Layout
   (Parent Theme)       (Child Theme)
```

**Breakpoint: 1024px**
- Below: Mobile slider (parent theme)
- Above: Stacked layout (child theme)

---

## 8. Event Flow - Thumbnail Click

```
User clicks thumbnail #3
    ↓
Event listener triggered
    ↓
e.preventDefault()
    ↓
Remove 'active' from all thumbnails
    ↓
Add 'active' to thumbnail #3
    ↓
Get image #3 element
    ↓
Calculate position:
  imageTop = element.getBoundingClientRect().top
  pageYOffset = window.pageYOffset
  offset = 100 + adminBar + stickyHeader
    ↓
Calculate scroll target:
  scrollTop = imageTop + pageYOffset - offset
    ↓
window.scrollTo({
  top: scrollTop,
  behavior: 'smooth'
})
    ↓
Page smoothly scrolls to image #3
```

---

## 9. CSS Selector Hierarchy

```
Body Class
.ct-has-stacked-gallery
    ↓
Gallery Container
.woocommerce-product-gallery
    ↓
Flexy Container
.flexy-container.ct-stacked-desktop
    ↓
┌─────────────────┬─────────────────┐
│                 │                 │
Main Images       Thumbnails
.flexy            .flexy-pills
    ↓                 ↓
.flexy-view       ol
    ↓                 ↓
.flexy-items      li
    ↓                 ↓
.flexy-item       .ct-media-container
    ↓
.ct-media-container
    ↓
img / video
```

---

## 10. Data Flow - Variable Product

```
User selects variation
    ↓
WooCommerce triggers 'found_variation' event
    ↓
Parent theme updates gallery images
    ↓
Parent theme may re-initialize Flexy
    ↓
Child theme listens to 'found_variation'
    ↓
setTimeout(100ms)
    ↓
Re-run preventFlexyInitialization()
    ↓
Re-run initializeThumbnailScroll()
    ↓
Stacked layout maintained ✓
```

---

## 11. Lightbox Integration

```
User clicks main image
    ↓
Parent theme JS detects click
    ↓
Queries: .flexy-items .ct-media-container
    ↓
Finds all images (including stacked ones)
    ↓
Gets clicked image index
    ↓
Opens PhotoSwipe lightbox
    ↓
Lightbox shows correct image ✓
```

**Why it works:**
- HTML structure unchanged
- Parent theme queries still work
- All images have data-src attribute

---

## 12. Scroll Offset Calculation

```
Image Top Position
    ↓
getBoundingClientRect().top
    ↓
+ window.pageYOffset
    ↓
= Absolute position from document top
    ↓
- Base offset (100px)
    ↓
- Admin bar height (if logged in)
    ↓
- Sticky header height (if exists)
    ↓
= Final scroll target position
```

**Example:**
```
Image at 2000px from top
+ 500px current scroll
= 2500px absolute position
- 100px base offset
- 32px admin bar
- 80px sticky header
= 2288px scroll target
```

---

## 13. CSS Transform Override

### Parent Theme (Slider)
```css
.flexy-items {
  transform: translateX(calc(-100% * var(--current-item, 0)));
}
```

### Child Theme (Stacked)
```css
@media (min-width: 1024px) {
  .ct-has-stacked-gallery .flexy-items {
    transform: none !important;
  }
}
```

**Result:** No horizontal sliding on desktop

---

## 14. Flexbox Layout Change

### Parent Theme (Horizontal)
```css
.flexy-items {
  display: flex;
  flex-direction: row; /* default */
}
```

### Child Theme (Vertical)
```css
@media (min-width: 1024px) {
  .ct-has-stacked-gallery .flexy-items {
    flex-direction: column !important;
    gap: 18px !important;
  }
}
```

**Result:** Vertical stacking with gaps

---

## 15. Implementation Phases

```
Phase 1: Setup
├── Create child theme folder
├── Create file structure
└── Create core files (style.css, functions.php)

Phase 2: Code
├── Write CSS (gallery-stacked.css)
├── Write JS (gallery-stacked.js)
└── Write PHP (functions.php)

Phase 3: Activate
├── Activate child theme
└── Verify no errors

Phase 4: Test Desktop
├── Layout verification
├── Thumbnail interaction
├── Image features
└── Edge cases

Phase 5: Test Mobile
├── Slider verification
├── Thumbnail interaction
└── Image features

Phase 6: Test Responsive
├── Breakpoint transition
└── Different screen sizes

Phase 7: Browser Testing
├── Desktop browsers
└── Mobile browsers

Phase 8: WooCommerce
├── Variable products
└── Product types

Phase 9: Performance
├── Page load
├── Scroll performance
└── Lazy loading

Phase 10: Accessibility
├── Keyboard navigation
├── Screen reader
└── Focus management

Phase 11: Cleanup
├── Remove debug code
├── Optimize files
└── Documentation

Phase 12: Deployment
├── Backup
├── Upload
└── Verify
```

---

## 16. Troubleshooting Decision Tree

```
Issue: Stacked layout not working
    ↓
Is viewport ≥1024px?
    ├── No → Expected (mobile slider)
    └── Yes ↓
        Is .ct-has-stacked-gallery on body?
            ├── No → Check functions.php body_class filter
            └── Yes ↓
                Is CSS file loaded?
                    ├── No → Check enqueue in functions.php
                    └── Yes ↓
                        Is CSS being overridden?
                            ├── Yes → Increase specificity or use !important
                            └── No ↓
                                Is Flexy still initializing?
                                    ├── Yes → Check JS runs before parent theme
                                    └── No → Check browser console for errors
```

---

**End of Visual Diagrams**

These diagrams provide visual understanding of:
- Layout differences
- HTML structure
- CSS architecture
- JavaScript flow
- Dependencies
- Event handling
- Troubleshooting

Refer to these diagrams alongside the technical documentation for complete understanding.

