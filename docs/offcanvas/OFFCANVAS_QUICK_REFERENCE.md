# Blocksy Offcanvas - Quick Reference Guide

## Essential Code Snippets

### 1. Basic HTML Structure

```html
<div id="my-offcanvas" class="ct-panel" data-behaviour="right-side" role="dialog" aria-label="Menu" inert>
  <div class="ct-panel-inner">
    <div class="ct-panel-actions">
      <span class="ct-panel-heading">Title</span>
      <button class="ct-toggle-close" data-type="type-1" aria-label="Close">
        <svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15">
          <path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/>
        </svg>
      </button>
    </div>
    <div class="ct-panel-content">
      <div class="ct-panel-content-inner">
        <!-- Your content here -->
      </div>
    </div>
  </div>
</div>
```

### 2. Trigger Button

```html
<button data-toggle-panel="#my-offcanvas" aria-controls="my-offcanvas" aria-expanded="false">
  Open Menu
</button>
```

### 3. Essential CSS

```css
/* Base styles */
.ct-panel {
  position: fixed;
  z-index: 999999;
  inset: 0;
  opacity: 0;
  display: none;
  pointer-events: none;
  transition: opacity 0.25s ease-in-out;
}

body[data-panel] .ct-panel.active {
  display: flex;
}

body[data-panel*='in'] .ct-panel.active {
  opacity: 1;
  pointer-events: auto;
}

/* Side panel */
[data-behaviour*='side'] .ct-panel-inner {
  position: absolute;
  max-width: 500px;
  transition: transform 0.25s ease-in-out;
}

[data-behaviour*='right-side'] .ct-panel-inner {
  align-self: flex-end;
  transform: translate3d(20%, 0, 0);
}

[data-behaviour*='left-side'] .ct-panel-inner {
  align-self: flex-start;
  transform: translate3d(-20%, 0, 0);
}

[data-panel*='in'] [data-behaviour*='side'].active .ct-panel-inner {
  transform: translate3d(0, 0, 0);
}
```

### 4. Essential JavaScript

```javascript
// Open
function openOffcanvas(id) {
  const panel = document.querySelector(id);
  document.body.dataset.panel = '';
  panel.classList.add('active');
  panel.inert = false;
  
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      const dir = panel.dataset.behaviour.includes('left') ? ':left' : ':right';
      document.body.dataset.panel = `in${dir}`;
    });
  });
}

// Close
function closeOffcanvas(id) {
  const panel = document.querySelector(id);
  document.body.dataset.panel = 'out';
  panel.inert = true;
  
  setTimeout(() => {
    document.body.removeAttribute('data-panel');
    panel.classList.remove('active');
  }, 250);
}
```

## Key Attributes

| Attribute | Values | Description |
|-----------|--------|-------------|
| `data-behaviour` | `left-side`, `right-side`, `modal` | Direction of slide |
| `data-panel` | `""`, `in`, `in:left`, `in:right`, `out` | Body state |
| `data-type` | `type-1`, `type-2`, `type-3` | Close button style |
| `role` | `dialog` | ARIA role |
| `aria-modal` | `true`/`false` | Modal state |
| `inert` | boolean | Interaction state |

## CSS Variables

```css
--side-panel-width: 500px;
--side-panel-offset: 0px;
--side-panel-border-radius: 0px;
--panel-padding: 35px;
--theme-panel-reveal-right: 20%;
--theme-panel-reveal-left: -20%;
--theme-icon-size: 12px;
--theme-icon-color: rgba(255, 255, 255, 0.7);
```

## WordPress Hooks

### Filters

```php
// Customize close icon
add_filter('blocksy:main:offcanvas:close:icon', function($icon) {
    return '<svg>...</svg>';
});
```

### Actions

```php
// Add content to offcanvas
add_action('blocksy:header:offcanvas:desktop:top', function() {
    echo '<div>Custom content</div>';
});

add_action('blocksy:header:offcanvas:desktop:bottom', function() {
    echo '<div>Footer content</div>';
});

add_action('blocksy:header:offcanvas:mobile:top', function() {
    echo '<div>Mobile content</div>';
});

add_action('blocksy:header:offcanvas:mobile:bottom', function() {
    echo '<div>Mobile footer</div>';
});
```

## Common Customizations

### Change Panel Width

```css
#my-offcanvas {
  --side-panel-width: 600px;
}
```

### Change Animation Speed

```css
.ct-panel,
.ct-panel-inner {
  transition-duration: 0.4s;
}
```

### Add Backdrop

```css
#my-offcanvas::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: -1;
}
```

### Custom Background

```css
#my-offcanvas .ct-panel-inner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}
```

## Accessibility Checklist

- [ ] `role="dialog"` on container
- [ ] `aria-label` on container
- [ ] `aria-modal="true"` when open
- [ ] `aria-expanded` on trigger
- [ ] `aria-controls` on trigger
- [ ] `inert` when closed
- [ ] ESC key closes
- [ ] Focus trap when open
- [ ] Focus returns to trigger when closed
- [ ] Sufficient color contrast
- [ ] Touch targets ≥ 44x44px

## Browser Support

- Chrome/Edge: Last 2 versions
- Firefox: Last 2 versions
- Safari: Last 2 versions
- iOS Safari: Last 2 versions
- Android Chrome: Last 2 versions

## File Structure for Child Theme

```
child-theme/
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

## Minimal Working Example

```php
// functions.php
add_action('wp_footer', function() {
    ?>
    <div id="simple-offcanvas" class="ct-panel" data-behaviour="right-side" role="dialog" inert>
      <div class="ct-panel-inner">
        <div class="ct-panel-actions">
          <button class="ct-toggle-close" aria-label="Close">×</button>
        </div>
        <div class="ct-panel-content">
          <div class="ct-panel-content-inner">
            <h2>Hello World</h2>
          </div>
        </div>
      </div>
    </div>
    
    <button onclick="openOffcanvas('#simple-offcanvas')">Open</button>
    
    <script>
    function openOffcanvas(id) {
      const p = document.querySelector(id);
      document.body.dataset.panel = '';
      p.classList.add('active');
      p.inert = false;
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          document.body.dataset.panel = 'in:right';
        });
      });
    }
    
    document.querySelector('.ct-toggle-close').onclick = function() {
      const p = document.querySelector('#simple-offcanvas');
      document.body.dataset.panel = 'out';
      setTimeout(() => {
        document.body.removeAttribute('data-panel');
        p.classList.remove('active');
        p.inert = true;
      }, 250);
    };
    </script>
    <?php
});
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Offcanvas doesn't slide | Check `data-behaviour` attribute |
| Animation is jerky | Add `transform: translate3d(0,0,0)` for GPU acceleration |
| Can't close with ESC | Add keyup event listener |
| Focus not trapped | Implement focus lock |
| Scrollbar appears | Add `overflow: hidden` to body when open |
| Content not visible | Check z-index and opacity values |

## Performance Tips

1. Use `transform` instead of `left/right` for animations
2. Add `will-change: transform` before animation
3. Remove `will-change` after animation
4. Use `contain: layout style paint`
5. Lazy load JavaScript
6. Minimize repaints/reflows

---

**For complete documentation, see**: `OFFCANVAS_MODULE_DOCUMENTATION.md`

