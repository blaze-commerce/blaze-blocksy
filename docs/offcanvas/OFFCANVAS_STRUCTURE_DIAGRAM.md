# Blocksy Offcanvas - Visual Structure Diagram

## HTML Structure Tree

```
<div id="offcanvas" class="ct-panel" data-behaviour="right-side" role="dialog" inert>
│
├── <div class="ct-panel-inner">
│   │
│   ├── <div class="ct-panel-actions">
│   │   │
│   │   ├── <span class="ct-panel-heading">
│   │   │   └── "Menu" (Customizable Title)
│   │   │
│   │   └── <button class="ct-toggle-close" data-type="type-1">
│   │       └── <svg class="ct-icon">
│   │           └── (Close Icon - Customizable)
│   │
│   └── <div class="ct-panel-content" data-device="desktop">
│       │
│       └── <div class="ct-panel-content-inner">
│           │
│           ├── (Hook: blocksy:header:offcanvas:desktop:top)
│           │
│           ├── Your Custom Content
│           │   ├── Navigation menus
│           │   ├── Widgets
│           │   ├── Custom HTML
│           │   └── Shortcodes
│           │
│           └── (Hook: blocksy:header:offcanvas:desktop:bottom)
```

## State Flow Diagram

```
┌─────────────────┐
│  Initial State  │
│  (Closed)       │
│                 │
│  body: no attr  │
│  panel: hidden  │
│  inert: true    │
└────────┬────────┘
         │
         │ User clicks trigger
         ▼
┌─────────────────┐
│  Opening State  │
│                 │
│  body[data-panel=""]
│  panel.active   │
│  inert: false   │
└────────┬────────┘
         │
         │ requestAnimationFrame (2 frames)
         ▼
┌─────────────────┐
│   Open State    │
│   (Visible)     │
│                 │
│  body[data-panel="in:right"]
│  panel.active   │
│  opacity: 1     │
│  transform: 0   │
└────────┬────────┘
         │
         │ User closes (ESC/Click/Button)
         ▼
┌─────────────────┐
│  Closing State  │
│                 │
│  body[data-panel="out"]
│  panel.active   │
│  inert: true    │
└────────┬────────┘
         │
         │ After 250ms
         ▼
┌─────────────────┐
│  Closed State   │
│                 │
│  body: no attr  │
│  panel: hidden  │
│  opacity: 0     │
└─────────────────┘
```

## Animation Flow (Right Side)

```
Closed Position                    Open Position
(Off-screen right)                 (On-screen)

┌──────────────┐                  ┌──────────────┐
│              │                  │              │
│              │                  │              │
│   Screen     │  ──────────────► │   Screen     │
│              │   transform:     │              │
│              │   translate3d    │              │
│              │   (20% → 0)      │              │
└──────────────┘                  └──────────────┘
                                         │
                                         │
                                  ┌──────┴──────┐
                                  │   Panel     │
                                  │   Content   │
                                  │             │
                                  └─────────────┘
```

## Animation Flow (Left Side)

```
Open Position                      Closed Position
(On-screen)                        (Off-screen left)

┌──────────────┐                  ┌──────────────┐
│              │                  │              │
│              │                  │              │
│   Screen     │  ◄────────────── │   Screen     │
│              │   transform:     │              │
│              │   translate3d    │              │
│              │   (0 → -20%)     │              │
└──────────────┘                  └──────────────┘
│
│
┌──────┴──────┐
│   Panel     │
│   Content   │
│             │
└─────────────┘
```

## CSS Layers Diagram

```
┌─────────────────────────────────────────────────┐
│  .ct-panel (Container)                          │
│  - position: fixed                              │
│  - z-index: 999999                              │
│  - opacity: 0 → 1                               │
│  - transition: opacity 0.25s                    │
│                                                 │
│  ┌───────────────────────────────────────────┐ │
│  │  .ct-panel-inner (Wrapper)                │ │
│  │  - position: absolute                     │ │
│  │  - max-width: var(--side-panel-width)    │ │
│  │  - transform: translate3d(20%, 0, 0) → 0 │ │
│  │  - transition: transform 0.25s            │ │
│  │                                           │ │
│  │  ┌─────────────────────────────────────┐ │ │
│  │  │  .ct-panel-actions (Header)         │ │ │
│  │  │  - display: flex                    │ │ │
│  │  │  - padding: var(--panel-padding)    │ │ │
│  │  │                                     │ │ │
│  │  │  ┌──────────────┐  ┌─────────────┐ │ │ │
│  │  │  │  .heading    │  │  .close-btn │ │ │ │
│  │  │  └──────────────┘  └─────────────┘ │ │ │
│  │  └─────────────────────────────────────┘ │ │
│  │                                           │ │
│  │  ┌─────────────────────────────────────┐ │ │
│  │  │  .ct-panel-content (Content Area)   │ │ │
│  │  │  - flex: 1                          │ │ │
│  │  │  - overflow-y: auto                 │ │ │
│  │  │                                     │ │ │
│  │  │  ┌───────────────────────────────┐ │ │ │
│  │  │  │  .ct-panel-content-inner      │ │ │ │
│  │  │  │  - padding: var(--padding)    │ │ │ │
│  │  │  │  - Your custom content        │ │ │ │
│  │  │  └───────────────────────────────┘ │ │ │
│  │  └─────────────────────────────────────┘ │ │
│  └───────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

## JavaScript Event Flow

```
┌──────────────┐
│ User Action  │
│ (Click/Key)  │
└──────┬───────┘
       │
       ▼
┌──────────────────┐
│ Event Listener   │
│ Triggered        │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ openOffcanvas()  │
│ Function         │
└──────┬───────────┘
       │
       ├─► Update ARIA attributes
       │   (aria-expanded, aria-modal)
       │
       ├─► Remove inert
       │
       ├─► Add body[data-panel]
       │
       ├─► Add .active class
       │
       ├─► Trigger animation
       │   (requestAnimationFrame)
       │
       ├─► Setup close handlers
       │   ├─► ESC key
       │   ├─► Close button
       │   └─► Click outside
       │
       └─► Lock scroll
           └─► Trigger custom event
```

## Customization Points Map

```
┌─────────────────────────────────────────────────────┐
│  Offcanvas Module                                   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  1. HEADING                                 │   │
│  │     • has_offcanvas_heading (yes/no)       │   │
│  │     • offcanvas_heading (text)             │   │
│  │     • CSS: .ct-panel-heading               │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  2. CLOSE ICON                              │   │
│  │     • Filter: blocksy:main:offcanvas:close  │   │
│  │     • data-type: type-1/2/3                │   │
│  │     • --theme-icon-size: 5-50px            │   │
│  │     • --theme-icon-color: custom           │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  3. CONTENT                                 │   │
│  │     • Hook: :desktop:top                   │   │
│  │     • Hook: :desktop:bottom                │   │
│  │     • Hook: :mobile:top                    │   │
│  │     • Hook: :mobile:bottom                 │   │
│  │     • Direct HTML in template              │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  4. BEHAVIOR                                │   │
│  │     • data-behaviour: left-side            │   │
│  │     • data-behaviour: right-side           │   │
│  │     • data-behaviour: modal                │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │  5. STYLING                                 │   │
│  │     • --side-panel-width: 500px            │   │
│  │     • --side-panel-offset: 0px             │   │
│  │     • --side-panel-border-radius: 0px      │   │
│  │     • --panel-padding: 35px                │   │
│  │     • background, colors, etc.             │   │
│  └─────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
```

## File Dependencies

```
Child Theme Implementation
│
├── functions.php
│   ├── Enqueue CSS ──────────► assets/css/offcanvas.css
│   ├── Enqueue JS ───────────► assets/js/offcanvas.js
│   ├── Register widgets
│   ├── Register menus
│   └── Load template ────────► template-parts/offcanvas/custom-offcanvas.php
│
├── assets/css/offcanvas.css
│   ├── Import parent styles (optional)
│   ├── Custom variables
│   └── Custom styles
│
├── assets/js/offcanvas.js
│   ├── Initialize offcanvas
│   ├── Event handlers
│   └── Custom functionality
│
└── template-parts/offcanvas/custom-offcanvas.php
    ├── HTML structure
    ├── Apply filters
    ├── Execute hooks
    └── Render content
```

## Responsive Behavior

```
Desktop (> 768px)
┌────────────────────────────────────────┐
│                                        │
│  Screen                                │
│                                        │
│                              ┌─────────┤
│                              │ Panel   │
│                              │ 500px   │
│                              │ width   │
│                              └─────────┤
└────────────────────────────────────────┘

Tablet (481-768px)
┌────────────────────────────────────────┐
│                                        │
│  Screen                                │
│                                        │
│                    ┌───────────────────┤
│                    │ Panel             │
│                    │ 65vw width        │
│                    └───────────────────┤
└────────────────────────────────────────┘

Mobile (< 480px)
┌────────────────────────────────────────┐
│                                        │
│  Screen                                │
│                                        │
│  ┌────────────────────────────────────┤
│  │ Panel                              │
│  │ 90vw width                         │
│  └────────────────────────────────────┤
└────────────────────────────────────────┘
```

## Accessibility Flow

```
┌─────────────────┐
│  Trigger Click  │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────┐
│  Update ARIA                │
│  • aria-expanded="true"     │
│  • aria-modal="true"        │
│  • Remove inert             │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│  Focus Management           │
│  • Trap focus in panel      │
│  • Set focus to first item  │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│  Keyboard Support           │
│  • ESC to close             │
│  • Tab navigation           │
│  • Enter on buttons         │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│  Screen Reader              │
│  • Announce "Menu opened"   │
│  • Read panel label         │
└────────┬────────────────────┘
         │
         │ User closes
         ▼
┌─────────────────────────────┐
│  Restore State              │
│  • aria-expanded="false"    │
│  • Remove aria-modal        │
│  • Add inert                │
│  • Return focus to trigger  │
└─────────────────────────────┘
```

## Integration Points

```
WordPress Theme
│
├── Header
│   └── Trigger Button ──────► Opens Offcanvas
│
├── Footer
│   └── Offcanvas Template ──► Rendered here
│
├── Hooks
│   ├── wp_enqueue_scripts ──► Load assets
│   ├── widgets_init ────────► Register widget area
│   ├── after_setup_theme ───► Register menu
│   └── wp_footer ───────────► Load template
│
└── Filters
    ├── offcanvas:close:icon ► Customize icon
    └── offcanvas_options ───► Customize config
```

---

## Legend

```
┌─────┐
│ Box │  = Container/Element
└─────┘

  │
  ├──  = Hierarchy/Nesting
  └──

  ──►  = Flow/Direction

  ▼    = Sequence/Next Step
```

---

**Use this diagram alongside the main documentation for visual reference.**

