# Product Carousel Dots — Bottom Pagination for PDP Carousels

Replaces Blocksy's overlapping side arrows on the **Related Products** ("You May Also Like") and **Recently Viewed** carousels with bottom dot pagination. Reusable across any client using this child theme.

QA reference: ClickUp [86exbzf96](https://app.clickup.com/t/86exbzf96).

---

## Why this pattern exists

Blocksy renders the Related Products carousel using its proprietary **Flexy** slider. Out of the box, the prev/next arrows are absolutely positioned 20px from the carousel edge and overlap the first/last visible product card — covering the wishlist heart, badges, and image hover state. There is no Customizer toggle to switch arrows for dots on this specific carousel.

## Why we don't use Flexy's native `.flexy-pills`

Flexy's bundled pill code (`wp-content/themes/blocksy/static/bundle/71.*.js`) assumes **one pill per slide-item** — the gallery model. The active-state updater does:

```js
pillsContainerSelector.children[previousCurrentIndex].classList.add('active')
```

For an 8-item product row with 4 visible columns, the user-meaningful pill count is `8 - 4 + 1 = 5` (one per valid leading-item position). Flexy's `currentIndex` still ranges 0..7 internally, so the moment it advances past 4 it reads `children[5..7]` — which is `undefined` — and crashes the draw loop. This is also why Blocksy's own related-products renderer (`blocksy/inc/components/archive/helpers.php:174-185`) ships arrows-only.

We sidestep this by naming our container `.bc-carousel-dots` (NOT `.flexy-pills`). Flexy's early-out (`if (!options.pillsContainerSelector) return`) means it never touches our dots, and we own click navigation entirely.

## How navigation works

1. Render `<ul class="bc-carousel-dots">` next to `.flexy`.
2. Pre-mount Flexy on init via `container.forcedMount()` (Blocksy's lazy-load helper attached to every `.flexy-container[data-flexy*="no"]`). This ensures Flexy's arrow click listeners are wired by the time the user clicks a dot.
3. On dot click: read current slide index, compute `diff = target - current`, click the off-screen `.flexy-arrow-prev` or `.flexy-arrow-next` button `abs(diff)` times. Each click advances the Flexy state by one position; Flexy itself handles the animation queue.
4. Active dot syncs via `blocksy:frontend:flexy:slide-change` event (also fires on swipe gestures).

## Why arrows are positioned off-screen instead of `display: none`

Programmatic `.click()` works on `display: none` elements in some browsers but not others, and event-handling specs are inconsistent. Off-screen positioning (`position: absolute; left: -9999px; opacity: 0; pointer-events: none`) keeps the elements in the layout tree and reliably clickable from JS, while remaining invisible and inaccessible to user pointer events.

---

## Files

```
blocksy-child/
├── assets/
│   ├── css/components/
│   │   └── product-carousel-dots.css    ← arrow hide + dot row layout
│   └── js/
│       └── product-carousel-dots.js     ← injects .bc-carousel-dots + click-to-arrow nav
├── inc/
│   └── enqueue.php                      ← MODIFIED: enqueues both under is_product()
└── docs/patterns/
    └── product-carousel-dots.md         ← this file
```

---

## Selectors targeted

The CSS+JS scope themselves to **only** these two carousels:

| Selector | Source |
|----------|--------|
| `.related.products.is-layout-slider` | Blocksy parent — Related Products / Upsells on PDP |
| `.bc-recently-viewed` | Custom — `inc/recently-viewed.php` |

If you need another carousel (e.g., Wishlist drawer suggested products, homepage product slider) to have dots, add its selector to:

- `product-carousel-dots.css` — to the arrow-hide rule.
- `product-carousel-dots.js` — to the `SELECTOR` constant at the top.

---

## How dot count is computed

`dotCount = max(1, totalItems - visibleCols + 1)`

`visibleCols` is read from the live `--flexy-item-width` CSS variable Blocksy sets on `.flexy-items` (resolves to `--grid-columns-width` in slider layout, e.g. `25%` for 4-column desktop). On window resize, the value is re-read and dots are rebuilt. This means:

| Breakpoint | Cols visible | 8 items → dots |
|-----------|-------------|----------------|
| Desktop (1400+) | 4 | 5 |
| Tablet (768) | 3 | 6 |
| Mobile (375) | 2 | 7 |

If `totalItems <= visibleCols` (everything already fits), the JS sets `data-bc-empty="1"` on the container and CSS hides it — no orphan dots.

---

## How active state stays in sync

1. **Initial render** — first `<li>` gets `class="active"`.
2. **User clicks a dot** — JS reads current slide index, computes diff, clicks `.flexy-arrow-prev/next` `abs(diff)` times. Active class set immediately for snappy feel; `blocksy:frontend:flexy:slide-change` event re-confirms it.
3. **User swipes / touches** — Blocksy emits `blocksy:frontend:flexy:slide-change`; our listener reads the current index and re-applies `.active`.

---

## How to disable on a specific client

Add to that client's CSS (`clients/{slug}/{slug}.css`):

```css
/* Restore Blocksy default arrows on PDP carousels for this client */
.related.products.is-layout-slider .flexy > .flexy-arrow-prev,
.related.products.is-layout-slider .flexy > .flexy-arrow-next,
.bc-recently-viewed .flexy > .flexy-arrow-prev,
.bc-recently-viewed .flexy > .flexy-arrow-next {
    position: static !important;
    left: auto !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}
.bc-carousel-dots {
    display: none !important;
}
```

To prevent the JS from running at all, dequeue it in client-specific PHP:

```php
add_action( 'wp_enqueue_scripts', function () {
    wp_dequeue_script( 'blocksy-child-product-carousel-dots' );
}, 100 );
```

---

## Dependencies

- **Blocksy parent theme** ≥ 2.1 — provides Flexy library, `.flexy-pills[data-type='circle']` styling, and the `blocksy:frontend:flexy:slide-change` event.
- **`inc/recently-viewed.php`** — provides the `.bc-recently-viewed` markup. If that file is removed, the dots component still works on Related Products only.

---

## Testing checklist

- [ ] Visit a product with ≥5 related products. No side arrows visible. Dots render below carousel, centered.
- [ ] Click a dot → carousel scrolls to that page. Active dot updates.
- [ ] Swipe on touch device → active dot updates.
- [ ] Resize across 320 / 375 / 768 / 1400 / 2560px → dot count adjusts at each breakpoint.
- [ ] Visit 3+ products in sequence, return to first — Recently Viewed renders with dots.
- [ ] Browser console clean. No PHP errors in `WP_DEBUG` log.
- [ ] Regression: Blocksy product gallery dots still work. `[bc_product_slider]` shortcode arrows still render (it uses `.bc-product-slider__arrow`, untouched).
