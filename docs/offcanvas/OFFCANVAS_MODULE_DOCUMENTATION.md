# Blocksy Theme - Reusable Offcanvas Module Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [HTML Structure](#html-structure)
4. [CSS Styling & Animations](#css-styling--animations)
5. [JavaScript API](#javascript-api)
6. [Customization Guide](#customization-guide)
7. [Implementation in Child Theme](#implementation-in-child-theme)
8. [Accessibility Features](#accessibility-features)
9. [Best Practices](#best-practices)

---

## Overview

The Blocksy theme implements a robust offcanvas/drawer panel system that can slide in from the left or right side of the screen, or appear as a centered modal. This documentation provides a complete technical specification for implementing a reusable offcanvas module in a child theme that maintains structural and stylistic consistency with the parent theme.

### Key Features
- ✅ Slide from left or right with smooth transitions
- ✅ Modal mode (center screen)
- ✅ Customizable heading, close icon, and content
- ✅ Full accessibility support (ARIA, keyboard navigation, focus management)
- ✅ Click-outside-to-close functionality
- ✅ ESC key to close
- ✅ Scroll lock when open
- ✅ Responsive design
- ✅ Animation transitions (0.25s duration)

---

## Architecture

### Core Components

The offcanvas system consists of three main layers:

1. **HTML Structure Layer**
   - Container: `.ct-panel`
   - Inner wrapper: `.ct-panel-inner` (for side panels)
   - Actions bar: `.ct-panel-actions` (heading + close button)
   - Content area: `.ct-panel-content` → `.ct-panel-content-inner`

2. **CSS/SCSS Layer**
   - Main styles: `static/sass/frontend/5-modules/off-canvas/main.scss`
   - Panel animations: `static/sass/frontend/5-modules/off-canvas/panel.scss`
   - Modal animations: `static/sass/frontend/5-modules/off-canvas/modal.scss`
   - Transition duration: `$transition-duration: 0.25s`

3. **JavaScript Layer**
   - Fast overlay handler: `static/js/frontend/fast-overlay.js`
   - Full overlay logic: `static/js/frontend/lazy/overlay.js`
   - Event system for open/close/focus management

### File Locations Reference

```
Parent Theme Structure:
├── inc/
│   ├── components/builder/header-elements.php (offcanvas HTML generation)
│   └── panel-builder/header/
│       ├── offcanvas/options.php (configuration options)
│       ├── offcanvas/dynamic-styles.php (CSS variables)
│       └── trigger/view.php (trigger button)
├── static/
│   ├── js/frontend/
│   │   ├── fast-overlay.js (initial mount)
│   │   └── lazy/overlay.js (full functionality)
│   └── sass/frontend/5-modules/off-canvas/
│       ├── main.scss (base styles)
│       ├── panel.scss (side panel animations)
│       └── modal.scss (modal animations)
```

---

## HTML Structure

### Basic Structure

```html
<div id="offcanvas" 
     class="ct-panel" 
     data-behaviour="right-side"
     role="dialog"
     aria-label="Offcanvas modal"
     inert>
  
  <div class="ct-panel-inner">
    
    <!-- Header/Actions Bar -->
    <div class="ct-panel-actions">
      <span class="ct-panel-heading">Menu</span>
      <button class="ct-toggle-close" 
              data-type="type-1" 
              aria-label="Close drawer">
        <svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15">
          <path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/>
        </svg>
      </button>
    </div>
    
    <!-- Content Area -->
    <div class="ct-panel-content" data-device="desktop">
      <div class="ct-panel-content-inner">
        <!-- Your custom content here -->
      </div>
    </div>
    
  </div>
</div>
```

### Trigger Button Structure

```html
<button class="ct-header-trigger ct-toggle" 
        data-toggle-panel="#offcanvas"
        aria-controls="offcanvas"
        aria-expanded="false"
        data-design="simple"
        aria-label="Open menu">
  <span>Menu</span>
  <svg class="ct-icon" width="18" height="14" viewBox="0 0 18 14">
    <rect y="0.00" width="18" height="1.7" rx="1"/>
    <rect y="6.15" width="18" height="1.7" rx="1"/>
    <rect y="12.3" width="18" height="1.7" rx="1"/>
  </svg>
</button>
```

### Behavior Types

The `data-behaviour` attribute controls how the offcanvas appears:

- `data-behaviour="left-side"` - Slides from left
- `data-behaviour="right-side"` - Slides from right  
- `data-behaviour="modal"` - Appears in center (modal style)

### State Management

The offcanvas state is managed through:

1. **Body attribute**: `data-panel` on `<body>`
   - Empty value `""` = Opening/transition state
   - `"in"` = Fully opened
   - `"in:left"` = Opened from left
   - `"in:right"` = Opened from right
   - `"out"` = Closing state

2. **Panel class**: `.active` on `.ct-panel`
   - Added when panel is visible
   - Removed when panel is hidden

3. **ARIA attributes**:
   - `aria-expanded` on trigger button
   - `aria-modal="true"` when open
   - `inert` attribute when closed

---

## CSS Styling & Animations

### Core SCSS Variables

```scss
$transition-duration: 0.25s;

// CSS Custom Properties
--side-panel-width: 500px;        // Desktop default
--side-panel-offset: 0px;         // Offset from edges
--side-panel-border-radius: 0px;  // Border radius
--panel-padding: 35px;            // Internal padding (25px on mobile)
--theme-panel-reveal-right: 20%;  // Initial transform offset (right)
--theme-panel-reveal-left: -20%;  // Initial transform offset (left)
```

### Base Panel Styles

```scss
.ct-panel {
  flex-direction: column;
  position: fixed;
  z-index: 999999;
  inset: var(--admin-bar, 0px) 0 0 0;
  opacity: 0;
  display: none;
  pointer-events: none;
  transition: opacity $transition-duration ease-in-out;
  
  .ct-panel-inner {
    display: flex;
    flex-direction: column;
  }
}

// Active state
body[data-panel] .ct-panel.active {
  display: flex;
}

body[data-panel*='in'] .ct-panel.active {
  opacity: 1;
  pointer-events: auto;
}
```

### Side Panel Animations (Left/Right)

```scss
[data-behaviour*='side'] {
  .ct-panel-inner {
    position: absolute;
    inset-block: 0px;
    height: calc(100% - var(--side-panel-offset, 0px) * 2);
    width: calc(100% - var(--side-panel-offset, 0px) * 2);
    max-width: var(--side-panel-width, 500px);
    margin: var(--side-panel-offset, 0px);
    box-shadow: var(--theme-box-shadow);
    border-radius: var(--side-panel-border-radius, 0px);
    transition: transform $transition-duration ease-in-out;
  }
}

// Right side panel
[data-behaviour*='right-side'] .ct-panel-inner {
  align-self: flex-end;
  transform: translate3d(var(--theme-panel-reveal-right, 20%), 0, 0);
}

// Left side panel
[data-behaviour*='left-side'] .ct-panel-inner {
  align-self: flex-start;
  transform: translate3d(var(--theme-panel-reveal-left, -20%), 0, 0);
}

// Active state (slides in)
[data-panel*='in'] [data-behaviour*='side'].active .ct-panel-inner {
  transform: translate3d(0, 0, 0);
}
```

### Modal Animations (Center)

```scss
// Modal animations
[data-panel*='in'] [data-behaviour='modal'].active,
[data-panel*='out'] [data-behaviour='modal'] {
  .ct-panel-content {
    animation-duration: $transition-duration;
    animation-fill-mode: both;
  }
}

[data-panel*='in'] [data-behaviour='modal'].active {
  .ct-panel-content {
    animation-name: move-in;
  }
}

[data-panel*='out'] [data-behaviour='modal'] {
  .ct-panel-content {
    animation-name: move-out;
  }
}

@keyframes move-in {
  0% {
    opacity: 0;
    transform: translate3d(0, -40px, 0);
  }
  100% {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}

@keyframes move-out {
  0% {
    transform: translate3d(0, 0, 0);
  }
  100% {
    transform: translate3d(0, 40px, 0);
  }
}
```

### Panel Actions & Content

```scss
.ct-panel-actions {
  display: flex;
  align-items: center;
  padding-inline: var(--panel-padding, 35px);
  padding-top: 30px; // 20px on mobile
  
  .ct-panel-heading {
    font-size: 15px;
    font-weight: 600;
    color: var(--theme-text-color);
  }
  
  .ct-toggle-close {
    --toggle-button-padding: 10px;
    --toggle-button-margin-end: -10px;
    --toggle-button-margin-block: -10px;
    margin-inline-start: auto;
  }
}

.ct-panel-content {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  justify-content: var(--vertical-alignment, flex-start);
}

.ct-panel-content-inner {
  display: flex;
  flex-direction: column;
  align-items: var(--horizontal-alignment, flex-start);
  height: var(--panel-content-height, auto);
  overflow-y: auto;
  padding: var(--panel-padding, 35px);
}
```

### Responsive Breakpoints

```scss
@include media-breakpoint-down(sm) {
  --panel-padding: 25px;
  
  .ct-panel-actions {
    padding-top: 20px;
  }
}

@include media-breakpoint-up(md) {
  .ct-panel-actions {
    padding-top: 30px;
  }
}
```

---

## JavaScript API

### Opening the Offcanvas

```javascript
// Method 1: Using the fast overlay handler (recommended)
import { fastOverlayHandleClick } from './fast-overlay';

const openOffcanvas = (event) => {
  fastOverlayHandleClick(event, {
    container: document.querySelector('#offcanvas'),
    isModal: true,
    clickOutside: true,
    focus: false,
    openStrategy: 'full' // 'full' | 'fast' | 'skip'
  });
};

// Method 2: Using data attributes (automatic)
// Add data-toggle-panel="#offcanvas" to any button
```

### Closing the Offcanvas

```javascript
// Method 1: Trigger custom event
import ctEvents from './ct-events';

ctEvents.trigger('ct:offcanvas:force-close', {
  container: document.querySelector('#offcanvas')
});

// Method 2: Click close button (automatic)
// Method 3: Press ESC key (automatic)
// Method 4: Click outside panel (automatic if clickOutside: true)
```

### Full JavaScript Implementation

```javascript
/**
 * Complete offcanvas handler implementation
 * Based on Blocksy theme's overlay.js
 */

// Settings object structure
const offcanvasSettings = {
  container: null,              // DOM element of the offcanvas
  onClose: () => {},            // Callback when closed
  focus: false,                 // Auto-focus on open
  clickOutside: true,           // Close on outside click
  isModal: true,                // Treat as modal
  closeWhenLinkInside: false,   // Close when link clicked inside
  shouldBeInert: true,          // Use inert attribute
  openStrategy: 'full'          // 'full' | 'fast' | 'skip'
};

// Show offcanvas function
const showOffcanvas = (settings) => {
  const container = settings.container;

  // Update trigger aria-expanded
  document.querySelectorAll(`[data-toggle-panel*="${container.id}"]`)
    .forEach(trigger => {
      trigger.setAttribute('aria-expanded', 'true');
    });

  // Remove inert
  if (settings.shouldBeInert) {
    container.inert = false;
    container.setAttribute('aria-modal', 'true');
  }

  // Add active class and body attribute
  document.body.dataset.panel = '';
  container.classList.add('active');

  // Trigger animation after 2 frames (for Firefox compatibility)
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      const behaviour = container.dataset.behaviour;
      const direction = behaviour.indexOf('left') > -1 ? ':left'
                      : behaviour.indexOf('right') > -1 ? ':right'
                      : '';
      document.body.dataset.panel = `in${direction}`;
    });
  });

  // Setup close handlers
  setupCloseHandlers(settings);

  // Trigger custom event
  ctEvents.trigger('ct:modal:opened', container);
};

// Hide offcanvas function
const hideOffcanvas = (settings) => {
  const container = settings.container;

  // Update trigger aria-expanded
  document.querySelectorAll(`[data-toggle-panel*="${container.id}"]`)
    .forEach(trigger => {
      trigger.setAttribute('aria-expanded', 'false');
    });

  // Add inert
  if (settings.shouldBeInert) {
    container.inert = true;
    container.removeAttribute('aria-modal');
  }

  // Trigger closing animation
  document.body.dataset.panel = 'out';

  // Wait for animation to complete
  setTimeout(() => {
    document.body.removeAttribute('data-panel');
    container.classList.remove('active');
    settings.onClose();
  }, 250); // Match $transition-duration
};

// Setup close event handlers
const setupCloseHandlers = (settings) => {
  const container = settings.container;

  // ESC key handler
  const onKeyUp = (event) => {
    if (event.keyCode === 27) {
      event.preventDefault();
      hideOffcanvas(settings);
      document.removeEventListener('keyup', onKeyUp);
    }
  };
  document.addEventListener('keyup', onKeyUp);

  // Close button handler
  const closeButton = container.querySelector('.ct-toggle-close');
  if (closeButton) {
    closeButton.addEventListener('click', (event) => {
      event.preventDefault();
      hideOffcanvas(settings);
    }, { once: true });
  }

  // Click outside handler
  if (settings.clickOutside) {
    const handleWindowClick = (e) => {
      setTimeout(() => {
        if (!container.contains(e.target) &&
            e.target !== document.body &&
            e.target.closest('body')) {
          hideOffcanvas(settings);
        }
      });
    };
    window.addEventListener('click', handleWindowClick, { capture: true });
  }
};
```

### Event System

```javascript
// Listen for offcanvas opened
ctEvents.on('ct:modal:opened', (container) => {
  console.log('Offcanvas opened:', container.id);
});

// Force close offcanvas
ctEvents.on('ct:offcanvas:force-close', (settings) => {
  hideOffcanvas(settings);
});
```

---

## Customization Guide

### 1. Customizing the Heading/Title

#### Enable/Disable Heading

```php
// In your child theme's functions.php

// Method 1: Using filter (if implementing custom offcanvas)
add_filter('your_offcanvas_options', function($options) {
    $options['has_heading'] = true;
    $options['heading_text'] = 'My Custom Title';
    return $options;
});
```

#### HTML Structure with Heading

```html
<div class="ct-panel-actions">
  <span class="ct-panel-heading">My Custom Title</span>
  <button class="ct-toggle-close" aria-label="Close drawer">
    <!-- Close icon -->
  </button>
</div>
```

#### Styling the Heading

```css
.ct-panel-heading {
  font-size: 15px;
  font-weight: 600;
  color: var(--theme-text-color);
  /* Add your custom styles */
  text-transform: uppercase;
  letter-spacing: 1px;
}
```

### 2. Customizing the Close Icon

#### Method 1: Using WordPress Filter (Recommended)

```php
// In child theme's functions.php

add_filter('blocksy:main:offcanvas:close:icon', function($default_icon) {
    // Return your custom SVG icon
    return '<svg class="ct-icon" width="12" height="12" viewBox="0 0 24 24">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>';
});

// For custom offcanvas (not the main one), create your own filter
add_filter('your_custom_offcanvas:close:icon', function($default_icon) {
    return '<svg><!-- Your icon --></svg>';
});
```

#### Method 2: Direct HTML Replacement

```php
function render_custom_close_button() {
    $icon = apply_filters('your_custom_offcanvas:close:icon',
        '<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15">
            <path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/>
        </svg>'
    );

    return '<button class="ct-toggle-close" data-type="type-1" aria-label="' .
           esc_attr__('Close drawer', 'your-textdomain') . '">' .
           $icon .
           '</button>';
}
```

#### Close Button Types

The close button supports 3 visual types via `data-type` attribute:

```html
<!-- Type 1: Simple (icon only) -->
<button class="ct-toggle-close" data-type="type-1">...</button>

<!-- Type 2: With border -->
<button class="ct-toggle-close" data-type="type-2">...</button>

<!-- Type 3: With background -->
<button class="ct-toggle-close" data-type="type-3">...</button>
```

#### Styling Close Button

```css
/* Icon size */
.ct-toggle-close {
  --theme-icon-size: 12px; /* Default, range: 5-50px */
}

/* Icon color */
.ct-toggle-close {
  --theme-icon-color: rgba(255, 255, 255, 0.7);
}

.ct-toggle-close:hover {
  --theme-icon-color: #ffffff;
}

/* For type-2 (border) */
.ct-toggle-close[data-type="type-2"] {
  --toggle-button-border-color: rgba(0, 0, 0, 0.5);
  --toggle-button-radius: 5px;
}

/* For type-3 (background) */
.ct-toggle-close[data-type="type-3"] {
  --toggle-button-background: rgba(0, 0, 0, 0.5);
  --toggle-button-radius: 5px;
}
```

### 3. Customizing Content

#### Method 1: Using WordPress Action Hooks

```php
// Add content to desktop view (top)
add_action('blocksy:header:offcanvas:desktop:top', function() {
    echo '<div class="custom-offcanvas-header">';
    echo '<h2>Welcome!</h2>';
    echo '</div>';
});

// Add content to desktop view (bottom)
add_action('blocksy:header:offcanvas:desktop:bottom', function() {
    echo '<div class="custom-offcanvas-footer">';
    echo '<p>Footer content</p>';
    echo '</div>';
});

// Add content to mobile view (top)
add_action('blocksy:header:offcanvas:mobile:top', function() {
    echo '<div class="mobile-custom-content">Mobile specific content</div>';
});

// Add content to mobile view (bottom)
add_action('blocksy:header:offcanvas:mobile:bottom', function() {
    echo '<div class="mobile-footer">Mobile footer</div>';
});
```

#### Method 2: Direct HTML in Custom Offcanvas

```php
function render_custom_offcanvas_content() {
    ob_start();
    ?>
    <div class="ct-panel-content" data-device="desktop">
        <div class="ct-panel-content-inner">

            <!-- Your custom content -->
            <nav class="custom-navigation">
                <ul>
                    <li><a href="#">Link 1</a></li>
                    <li><a href="#">Link 2</a></li>
                    <li><a href="#">Link 3</a></li>
                </ul>
            </nav>

            <!-- Widget area -->
            <?php if (is_active_sidebar('offcanvas-sidebar')) : ?>
                <div class="offcanvas-widgets">
                    <?php dynamic_sidebar('offcanvas-sidebar'); ?>
                </div>
            <?php endif; ?>

            <!-- Custom HTML -->
            <div class="custom-section">
                <h3>Contact Info</h3>
                <p>Email: info@example.com</p>
            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
```

#### Method 3: Using Shortcodes

```php
// Register shortcode for offcanvas content
add_shortcode('offcanvas_content', function($atts, $content = null) {
    return '<div class="shortcode-offcanvas-content">' .
           do_shortcode($content) .
           '</div>';
});

// Use in action hook
add_action('blocksy:header:offcanvas:desktop:top', function() {
    echo do_shortcode('[offcanvas_content]Your content here[/offcanvas_content]');
});
```

### 4. Customizing Behavior & Position

```php
// Set offcanvas to slide from left
$behaviour = 'left-side';

// Set offcanvas to slide from right
$behaviour = 'right-side';

// Set offcanvas as centered modal
$behaviour = 'modal';

// Apply to HTML
echo '<div id="custom-offcanvas"
           class="ct-panel"
           data-behaviour="' . esc_attr($behaviour) . '"
           role="dialog"
           aria-label="Custom offcanvas"
           inert>';
```

### 5. Customizing Panel Width

```css
/* Desktop: 500px (default) */
#custom-offcanvas {
  --side-panel-width: 600px;
}

/* Tablet: 65vw (default) */
@media (max-width: 768px) {
  #custom-offcanvas {
    --side-panel-width: 70vw;
  }
}

/* Mobile: 90vw (default) */
@media (max-width: 480px) {
  #custom-offcanvas {
    --side-panel-width: 95vw;
  }
}
```

### 6. Customizing Panel Offset & Border Radius

```css
/* Add offset from screen edges */
#custom-offcanvas {
  --side-panel-offset: 20px;
  --side-panel-border-radius: 10px;
}
```

### 7. Customizing Background & Backdrop

```css
/* Panel background (for modal mode) */
#custom-offcanvas {
  background: rgba(18, 21, 25, 0.98);
}

/* Panel inner background (for side panels) */
#custom-offcanvas .ct-panel-inner {
  background: #ffffff;
}

/* Backdrop overlay */
#custom-offcanvas::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: -1;
}
```

---

## Implementation in Child Theme

### Step 1: Create Directory Structure

```
your-child-theme/
├── functions.php
├── assets/
│   ├── css/
│   │   └── offcanvas.css
│   └── js/
│       └── offcanvas.js
└── template-parts/
    └── offcanvas/
        └── custom-offcanvas.php
```

### Step 2: Enqueue Assets

```php
// functions.php

function child_theme_enqueue_offcanvas_assets() {
    // Enqueue CSS
    wp_enqueue_style(
        'child-offcanvas-style',
        get_stylesheet_directory_uri() . '/assets/css/offcanvas.css',
        array(), // Dependencies (add parent theme handle if needed)
        '1.0.0'
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'child-offcanvas-script',
        get_stylesheet_directory_uri() . '/assets/js/offcanvas.js',
        array('jquery'), // Dependencies
        '1.0.0',
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_offcanvas_assets');
```

### Step 3: Create Offcanvas HTML Template

```php
// template-parts/offcanvas/custom-offcanvas.php

<?php
/**
 * Custom Offcanvas Template
 *
 * @package YourChildTheme
 */

// Offcanvas configuration
$offcanvas_config = array(
    'id' => 'custom-offcanvas',
    'behaviour' => 'right-side', // 'left-side', 'right-side', or 'modal'
    'has_heading' => true,
    'heading_text' => __('Custom Menu', 'your-textdomain'),
    'close_button_type' => 'type-1', // 'type-1', 'type-2', or 'type-3'
);

// Close icon (can be filtered)
$close_icon = apply_filters(
    'child_theme:offcanvas:close:icon',
    '<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15">
        <path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/>
    </svg>'
);
?>

<div id="<?php echo esc_attr($offcanvas_config['id']); ?>"
     class="ct-panel custom-offcanvas"
     data-behaviour="<?php echo esc_attr($offcanvas_config['behaviour']); ?>"
     role="dialog"
     aria-label="<?php echo esc_attr($offcanvas_config['heading_text']); ?>"
     inert>

  <div class="ct-panel-inner">

    <!-- Header/Actions Bar -->
    <div class="ct-panel-actions">
      <?php if ($offcanvas_config['has_heading']) : ?>
        <span class="ct-panel-heading">
          <?php echo esc_html($offcanvas_config['heading_text']); ?>
        </span>
      <?php endif; ?>

      <button class="ct-toggle-close"
              data-type="<?php echo esc_attr($offcanvas_config['close_button_type']); ?>"
              aria-label="<?php esc_attr_e('Close drawer', 'your-textdomain'); ?>">
        <?php echo $close_icon; ?>
      </button>
    </div>

    <!-- Content Area -->
    <div class="ct-panel-content" data-device="desktop">
      <div class="ct-panel-content-inner">

        <?php
        // Hook: Before content
        do_action('child_theme:offcanvas:before_content');
        ?>

        <!-- Your Custom Content Here -->
        <nav class="custom-offcanvas-navigation">
          <?php
          wp_nav_menu(array(
              'theme_location' => 'offcanvas-menu',
              'container' => false,
              'menu_class' => 'offcanvas-menu',
              'fallback_cb' => false,
          ));
          ?>
        </nav>

        <!-- Widget Area -->
        <?php if (is_active_sidebar('offcanvas-widgets')) : ?>
          <div class="offcanvas-widget-area">
            <?php dynamic_sidebar('offcanvas-widgets'); ?>
          </div>
        <?php endif; ?>

        <?php
        // Hook: After content
        do_action('child_theme:offcanvas:after_content');
        ?>

      </div>
    </div>

  </div>
</div>
```

### Step 4: Register Widget Area (Optional)

```php
// functions.php

function child_theme_register_offcanvas_widgets() {
    register_sidebar(array(
        'name'          => __('Offcanvas Widgets', 'your-textdomain'),
        'id'            => 'offcanvas-widgets',
        'description'   => __('Widgets in this area will be shown in the offcanvas panel.', 'your-textdomain'),
        'before_widget' => '<div id="%1$s" class="widget offcanvas-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'child_theme_register_offcanvas_widgets');
```

### Step 5: Register Menu Location (Optional)

```php
// functions.php

function child_theme_register_offcanvas_menu() {
    register_nav_menus(array(
        'offcanvas-menu' => __('Offcanvas Menu', 'your-textdomain'),
    ));
}
add_action('after_setup_theme', 'child_theme_register_offcanvas_menu');
```

### Step 6: Include Template in Theme

```php
// functions.php

function child_theme_load_offcanvas_template() {
    get_template_part('template-parts/offcanvas/custom-offcanvas');
}
add_action('wp_footer', 'child_theme_load_offcanvas_template');
```

### Step 7: Create Trigger Button

```php
// Add this where you want the trigger button to appear
// For example, in header.php or via a hook

function child_theme_offcanvas_trigger() {
    ?>
    <button class="ct-header-trigger ct-toggle custom-offcanvas-trigger"
            data-toggle-panel="#custom-offcanvas"
            aria-controls="custom-offcanvas"
            aria-expanded="false"
            data-design="simple"
            aria-label="<?php esc_attr_e('Open menu', 'your-textdomain'); ?>">
      <span class="trigger-label"><?php _e('Menu', 'your-textdomain'); ?></span>
      <svg class="ct-icon" width="18" height="14" viewBox="0 0 18 14">
        <rect y="0.00" width="18" height="1.7" rx="1"/>
        <rect y="6.15" width="18" height="1.7" rx="1"/>
        <rect y="12.3" width="18" height="1.7" rx="1"/>
      </svg>
    </button>
    <?php
}

// Hook it to appropriate location
add_action('blocksy:header:middle:end', 'child_theme_offcanvas_trigger');
// Or use in template: <?php child_theme_offcanvas_trigger(); ?>
```

### Step 8: Create CSS File

```css
/* assets/css/offcanvas.css */

/* Custom offcanvas variables */
#custom-offcanvas {
  --side-panel-width: 500px;
  --side-panel-offset: 0px;
  --side-panel-border-radius: 0px;
  --panel-padding: 35px;
}

/* Responsive widths */
@media (max-width: 768px) {
  #custom-offcanvas {
    --side-panel-width: 70vw;
    --panel-padding: 25px;
  }
}

@media (max-width: 480px) {
  #custom-offcanvas {
    --side-panel-width: 90vw;
  }
}

/* Custom navigation styles */
.custom-offcanvas-navigation {
  width: 100%;
}

.offcanvas-menu {
  list-style: none;
  margin: 0;
  padding: 0;
}

.offcanvas-menu li {
  margin-bottom: 10px;
}

.offcanvas-menu a {
  display: block;
  padding: 10px 15px;
  color: var(--theme-text-color);
  text-decoration: none;
  transition: background-color 0.2s ease;
}

.offcanvas-menu a:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

/* Widget area styles */
.offcanvas-widget-area {
  margin-top: 30px;
  padding-top: 30px;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.offcanvas-widget {
  margin-bottom: 25px;
}

.offcanvas-widget:last-child {
  margin-bottom: 0;
}

/* Trigger button styles */
.custom-offcanvas-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 10px;
  color: var(--theme-text-color);
}

.custom-offcanvas-trigger:hover {
  opacity: 0.8;
}
```

### Step 9: Create JavaScript File

```javascript
// assets/js/offcanvas.js

(function($) {
  'use strict';

  /**
   * Initialize custom offcanvas
   */
  function initCustomOffcanvas() {
    const trigger = document.querySelector('[data-toggle-panel="#custom-offcanvas"]');
    const offcanvas = document.querySelector('#custom-offcanvas');

    if (!trigger || !offcanvas) {
      return;
    }

    // Click handler for trigger
    trigger.addEventListener('click', function(e) {
      e.preventDefault();
      openOffcanvas();
    });

    // Close button handler
    const closeButton = offcanvas.querySelector('.ct-toggle-close');
    if (closeButton) {
      closeButton.addEventListener('click', function(e) {
        e.preventDefault();
        closeOffcanvas();
      });
    }

    // ESC key handler
    document.addEventListener('keyup', function(e) {
      if (e.keyCode === 27 && offcanvas.classList.contains('active')) {
        closeOffcanvas();
      }
    });

    // Click outside handler
    window.addEventListener('click', function(e) {
      if (offcanvas.classList.contains('active') &&
          !offcanvas.contains(e.target) &&
          !trigger.contains(e.target)) {
        closeOffcanvas();
      }
    }, true);
  }

  /**
   * Open offcanvas
   */
  function openOffcanvas() {
    const offcanvas = document.querySelector('#custom-offcanvas');
    const trigger = document.querySelector('[data-toggle-panel="#custom-offcanvas"]');

    // Update ARIA
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'true');
    }
    offcanvas.inert = false;
    offcanvas.setAttribute('aria-modal', 'true');

    // Add classes
    document.body.dataset.panel = '';
    offcanvas.classList.add('active');

    // Trigger animation
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        const behaviour = offcanvas.dataset.behaviour;
        const direction = behaviour.indexOf('left') > -1 ? ':left'
                        : behaviour.indexOf('right') > -1 ? ':right'
                        : '';
        document.body.dataset.panel = `in${direction}`;
      });
    });

    // Lock scroll
    document.body.style.overflow = 'hidden';

    // Trigger custom event
    $(document).trigger('customOffcanvas:opened', [offcanvas]);
  }

  /**
   * Close offcanvas
   */
  function closeOffcanvas() {
    const offcanvas = document.querySelector('#custom-offcanvas');
    const trigger = document.querySelector('[data-toggle-panel="#custom-offcanvas"]');

    // Update ARIA
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
    }
    offcanvas.inert = true;
    offcanvas.removeAttribute('aria-modal');

    // Trigger closing animation
    document.body.dataset.panel = 'out';

    // Remove classes after animation
    setTimeout(() => {
      document.body.removeAttribute('data-panel');
      offcanvas.classList.remove('active');
      document.body.style.overflow = '';

      // Trigger custom event
      $(document).trigger('customOffcanvas:closed', [offcanvas]);
    }, 250);
  }

  // Initialize on DOM ready
  $(document).ready(function() {
    initCustomOffcanvas();
  });

})(jQuery);
```

### Step 10: Complete Example - Full Integration

```php
// functions.php - Complete example

<?php
/**
 * Child Theme Functions
 */

// Enqueue assets
function child_theme_enqueue_assets() {
    wp_enqueue_style(
        'child-offcanvas-style',
        get_stylesheet_directory_uri() . '/assets/css/offcanvas.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'child-offcanvas-script',
        get_stylesheet_directory_uri() . '/assets/js/offcanvas.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_assets');

// Register widget area
function child_theme_register_widgets() {
    register_sidebar(array(
        'name'          => __('Offcanvas Widgets', 'your-textdomain'),
        'id'            => 'offcanvas-widgets',
        'description'   => __('Widgets in offcanvas panel', 'your-textdomain'),
        'before_widget' => '<div class="widget offcanvas-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'child_theme_register_widgets');

// Register menu
function child_theme_register_menus() {
    register_nav_menus(array(
        'offcanvas-menu' => __('Offcanvas Menu', 'your-textdomain'),
    ));
}
add_action('after_setup_theme', 'child_theme_register_menus');

// Load offcanvas template
function child_theme_load_offcanvas() {
    get_template_part('template-parts/offcanvas/custom-offcanvas');
}
add_action('wp_footer', 'child_theme_load_offcanvas');

// Add trigger button to header
function child_theme_add_trigger() {
    ?>
    <button class="ct-toggle custom-offcanvas-trigger"
            data-toggle-panel="#custom-offcanvas"
            aria-controls="custom-offcanvas"
            aria-expanded="false">
      <span>Menu</span>
      <svg width="18" height="14" viewBox="0 0 18 14">
        <rect y="0" width="18" height="1.7" rx="1"/>
        <rect y="6.15" width="18" height="1.7" rx="1"/>
        <rect y="12.3" width="18" height="1.7" rx="1"/>
      </svg>
    </button>
    <?php
}
add_action('blocksy:header:middle:end', 'child_theme_add_trigger');

// Customize close icon (optional)
add_filter('child_theme:offcanvas:close:icon', function($icon) {
    return '<svg class="ct-icon" width="12" height="12" viewBox="0 0 24 24">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>';
});
?>
```

---

## Accessibility Features

The Blocksy offcanvas implementation includes comprehensive accessibility features that must be maintained in your custom implementation.

### 1. ARIA Attributes

#### Required ARIA Attributes

```html
<!-- Offcanvas container -->
<div id="custom-offcanvas"
     class="ct-panel"
     role="dialog"
     aria-label="Offcanvas menu"
     aria-modal="true"
     inert>
  <!-- Content -->
</div>

<!-- Trigger button -->
<button data-toggle-panel="#custom-offcanvas"
        aria-controls="custom-offcanvas"
        aria-expanded="false"
        aria-label="Open menu">
  Menu
</button>

<!-- Close button -->
<button class="ct-toggle-close"
        aria-label="Close drawer">
  <svg aria-hidden="true">...</svg>
</button>
```

#### Dynamic ARIA Updates

```javascript
// When opening
trigger.setAttribute('aria-expanded', 'true');
offcanvas.setAttribute('aria-modal', 'true');
offcanvas.inert = false;

// When closing
trigger.setAttribute('aria-expanded', 'false');
offcanvas.removeAttribute('aria-modal');
offcanvas.inert = true;
```

### 2. Keyboard Navigation

#### Required Keyboard Support

| Key | Action |
|-----|--------|
| `ESC` | Close offcanvas |
| `Enter` | Activate trigger/close button |
| `Tab` | Navigate through focusable elements |
| `Shift + Tab` | Navigate backwards |

#### Implementation

```javascript
// ESC key handler
document.addEventListener('keyup', (e) => {
  if (e.keyCode === 27 && offcanvas.classList.contains('active')) {
    closeOffcanvas();
  }
});

// Enter key on close button
closeButton.addEventListener('keyup', (e) => {
  if (e.keyCode === 13) {
    e.preventDefault();
    closeOffcanvas();
  }
});
```

### 3. Focus Management

#### Focus Trap

When offcanvas is open, focus should be trapped within the panel:

```javascript
// Pseudo-code for focus trap
function trapFocus(container) {
  const focusableElements = container.querySelectorAll(
    'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
  );

  const firstElement = focusableElements[0];
  const lastElement = focusableElements[focusableElements.length - 1];

  container.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      if (e.shiftKey && document.activeElement === firstElement) {
        e.preventDefault();
        lastElement.focus();
      } else if (!e.shiftKey && document.activeElement === lastElement) {
        e.preventDefault();
        firstElement.focus();
      }
    }
  });
}
```

#### Return Focus to Trigger

```javascript
function closeOffcanvas() {
  const trigger = document.querySelector('[data-toggle-panel="#custom-offcanvas"]');

  // Close animation...

  setTimeout(() => {
    // Return focus to trigger button
    if (trigger && !trigger.focusDisabled) {
      trigger.focus();
    }
  }, 50);
}
```

### 4. Inert Attribute

The `inert` attribute prevents interaction with the offcanvas when closed:

```javascript
// When closed
offcanvas.inert = true;

// When open
offcanvas.inert = false;
```

### 5. Screen Reader Announcements

```html
<!-- Add visually hidden live region for announcements -->
<div class="sr-only" role="status" aria-live="polite" aria-atomic="true">
  <span id="offcanvas-status"></span>
</div>
```

```javascript
// Announce state changes
function announceToScreenReader(message) {
  const status = document.getElementById('offcanvas-status');
  if (status) {
    status.textContent = message;
    setTimeout(() => {
      status.textContent = '';
    }, 1000);
  }
}

// Usage
announceToScreenReader('Menu opened');
announceToScreenReader('Menu closed');
```

### 6. Reduced Motion Support

```css
/* Respect user's motion preferences */
@media (prefers-reduced-motion: reduce) {
  .ct-panel,
  .ct-panel-inner,
  .ct-panel-content {
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
  }
}
```

### 7. Color Contrast

Ensure sufficient color contrast for WCAG compliance:

```css
/* Minimum contrast ratio 4.5:1 for normal text */
.ct-panel-heading {
  color: #000000; /* On white background */
}

.ct-toggle-close {
  --theme-icon-color: rgba(0, 0, 0, 0.8);
}

/* For dark backgrounds */
.ct-panel-inner {
  background: #ffffff;
  color: #000000;
}
```

### 8. Touch Target Size

Ensure touch targets are at least 44x44px:

```css
.ct-toggle-close,
.custom-offcanvas-trigger {
  min-width: 44px;
  min-height: 44px;
  padding: 10px;
}
```

---

## Best Practices

### 1. Performance Optimization

#### Lazy Loading

```javascript
// Load offcanvas JavaScript only when needed
let offcanvasLoaded = false;

document.querySelector('[data-toggle-panel]').addEventListener('click', function(e) {
  if (!offcanvasLoaded) {
    import('./offcanvas-module.js').then(module => {
      module.initOffcanvas();
      offcanvasLoaded = true;
    });
  }
}, { once: true });
```

#### CSS Containment

```css
.ct-panel {
  contain: layout style paint;
}
```

#### GPU Acceleration

```css
.ct-panel-inner {
  transform: translate3d(0, 0, 0);
  will-change: transform;
}

/* Remove will-change after animation */
.ct-panel-inner.animation-complete {
  will-change: auto;
}
```

### 2. Scroll Lock

Prevent body scroll when offcanvas is open:

```javascript
function lockScroll() {
  const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
  document.body.style.overflow = 'hidden';
  document.body.style.paddingRight = `${scrollbarWidth}px`;
}

function unlockScroll() {
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
}
```

### 3. Multiple Offcanvas Instances

```javascript
// Support multiple offcanvas panels
function initOffcanvas(selector) {
  const panels = document.querySelectorAll(selector);

  panels.forEach(panel => {
    const id = panel.id;
    const trigger = document.querySelector(`[data-toggle-panel="#${id}"]`);

    if (trigger) {
      trigger.addEventListener('click', (e) => {
        e.preventDefault();
        openOffcanvas(panel);
      });
    }
  });
}

// Initialize all offcanvas panels
initOffcanvas('.ct-panel');
```

### 4. Event Delegation

```javascript
// Use event delegation for dynamic content
document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-toggle-panel]');

  if (trigger) {
    e.preventDefault();
    const targetId = trigger.dataset.togglePanel;
    const offcanvas = document.querySelector(targetId);

    if (offcanvas) {
      openOffcanvas(offcanvas);
    }
  }
});
```

### 5. Error Handling

```javascript
function openOffcanvas(offcanvas) {
  if (!offcanvas) {
    console.error('Offcanvas element not found');
    return;
  }

  try {
    // Opening logic...
  } catch (error) {
    console.error('Error opening offcanvas:', error);
    // Cleanup
    document.body.removeAttribute('data-panel');
    offcanvas.classList.remove('active');
  }
}
```

### 6. Responsive Behavior

```javascript
// Close offcanvas on window resize (optional)
let resizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    const offcanvas = document.querySelector('.ct-panel.active');
    if (offcanvas && window.innerWidth > 768) {
      closeOffcanvas(offcanvas);
    }
  }, 250);
});
```

### 7. Content Loading

```javascript
// Load content dynamically
function loadOffcanvasContent(offcanvas, url) {
  const contentContainer = offcanvas.querySelector('.ct-panel-content-inner');

  fetch(url)
    .then(response => response.text())
    .then(html => {
      contentContainer.innerHTML = html;
      // Re-initialize any scripts
      initContentScripts(contentContainer);
    })
    .catch(error => {
      console.error('Error loading content:', error);
      contentContainer.innerHTML = '<p>Error loading content</p>';
    });
}
```

### 8. State Persistence (Optional)

```javascript
// Remember offcanvas state
function saveOffcanvasState(isOpen) {
  sessionStorage.setItem('offcanvas-state', isOpen ? 'open' : 'closed');
}

function restoreOffcanvasState() {
  const state = sessionStorage.getItem('offcanvas-state');
  if (state === 'open') {
    openOffcanvas(document.querySelector('#custom-offcanvas'));
  }
}

// Restore on page load
window.addEventListener('load', restoreOffcanvasState);
```

### 9. Testing Checklist

- [ ] Offcanvas opens and closes correctly
- [ ] Animations are smooth (60fps)
- [ ] ESC key closes the offcanvas
- [ ] Click outside closes the offcanvas
- [ ] Focus is trapped within offcanvas when open
- [ ] Focus returns to trigger when closed
- [ ] ARIA attributes update correctly
- [ ] Screen readers announce state changes
- [ ] Works on mobile devices (touch events)
- [ ] Works on tablets
- [ ] Works on desktop
- [ ] Works with keyboard only
- [ ] Respects prefers-reduced-motion
- [ ] Color contrast meets WCAG standards
- [ ] Touch targets are at least 44x44px
- [ ] No console errors
- [ ] No layout shifts
- [ ] Scroll is locked when open
- [ ] Multiple instances work independently

### 10. Common Pitfalls to Avoid

#### ❌ Don't

```javascript
// Don't use display: none for animations
.ct-panel {
  display: none; // This breaks animations
}

// Don't forget to clean up event listeners
function openOffcanvas() {
  window.addEventListener('click', handleClick); // Memory leak!
}

// Don't use fixed positioning without considering mobile browsers
.ct-panel {
  position: fixed; // Can cause issues on iOS
}
```

#### ✅ Do

```javascript
// Use opacity and pointer-events
.ct-panel {
  opacity: 0;
  pointer-events: none;
}

// Clean up event listeners
function closeOffcanvas() {
  window.removeEventListener('click', handleClick);
}

// Use inset for better mobile support
.ct-panel {
  position: fixed;
  inset: 0;
}
```

---

## Summary

This documentation provides a complete specification for implementing a reusable offcanvas module in a Blocksy child theme. The implementation maintains:

1. **Structural Consistency**: Same HTML structure as parent theme
2. **Visual Consistency**: Same animations and transitions
3. **Accessibility**: Full ARIA support and keyboard navigation
4. **Customization**: Flexible heading, icon, and content options
5. **Performance**: Optimized animations and lazy loading
6. **Best Practices**: Error handling, event delegation, and testing

### Quick Start Checklist

1. ✅ Create directory structure
2. ✅ Copy HTML template
3. ✅ Copy CSS styles
4. ✅ Copy JavaScript functionality
5. ✅ Enqueue assets
6. ✅ Add trigger button
7. ✅ Test accessibility
8. ✅ Test responsiveness
9. ✅ Customize as needed

### Support & Resources

- **Parent Theme**: Blocksy WordPress Theme
- **CSS Framework**: SCSS with CSS Custom Properties
- **JavaScript**: Vanilla JS with optional jQuery
- **Accessibility**: WCAG 2.1 Level AA compliant
- **Browser Support**: Modern browsers (last 2 versions)

---

**Document Version**: 1.0.0
**Last Updated**: 2025-11-07
**Compatibility**: Blocksy Theme v2.0+


