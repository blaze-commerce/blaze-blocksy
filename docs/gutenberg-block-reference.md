# Gutenberg Block Reference — WordPress Core + WooCommerce

> **Purpose:** Comprehensive reference of every available Gutenberg block for building pages.
> **Rule:** Use native blocks first. See `gutenberg-block-checklist.md` for implementation rules.
> **Last updated:** 2026-04-07

---

## Table of Contents

1. [WordPress Core Blocks](#wordpress-core-blocks)
2. [WooCommerce Blocks](#woocommerce-blocks)
3. [Byron Bay Candles Homepage — Recommended Block Combinations](#homepage-block-plan)

---

## WordPress Core Blocks

### Text Blocks

| Block | Slug | Key Options | Best Use |
|-------|------|-------------|----------|
| Paragraph | `core/paragraph` | Font size, drop cap, color, typography, alignment | Body copy, descriptions |
| Heading | `core/heading` | Level (H1-H6), alignment, color, typography, anchor ID | Section titles, SEO structure |
| List | `core/list` | Bulleted/numbered, indent, color | Features, benefits, ingredients |
| Quote | `core/quote` | Citation field, color, typography, border | Testimonials |
| Pullquote | `core/pullquote` | Citation, color, border style, alignment | Hero quotes, standout testimonials |
| Table | `core/table` | Fixed width, header/footer, stripes, color | Pricing comparisons, scent charts |
| Details | `core/details` | Open by default toggle, summary text | FAQs, expandable info |
| Code | `core/code` | Color, typography | Technical content only |
| Preformatted | `core/preformatted` | Color, typography | Pre-formatted text |
| Verse | `core/verse` | Color, typography, alignment | Poetry, artistic text |
| Footnotes | `core/footnotes` | Auto-generated from inline footnotes | References |

### Media Blocks

| Block | Slug | Key Options | Best Use |
|-------|------|-------------|----------|
| Image | `core/image` | Alt text, caption, link, size, border radius, duotone, aspect ratio, lightbox | Product photos, brand imagery |
| Gallery | `core/gallery` | Columns (1-8), crop, link, image size, gap | Product collections, lifestyle shots |
| Video | `core/video` | Autoplay, loop, muted, controls, poster image, preload | Product demos, brand story |
| Cover | `core/cover` | Background image/video, overlay color/opacity, fixed bg, focal point, min-height, full height | **Hero sections**, category banners, CTAs |
| Media & Text | `core/media-text` | Media position (L/R), stack on mobile, media width %, image fill, vertical align | About sections, feature highlights |
| Audio | `core/audio` | Autoplay, loop, preload | Ambient sounds, podcasts |
| File | `core/file` | Download button, filename, new tab | PDFs, care instructions |

### Design / Layout Blocks

| Block | Slug | Key Options | Best Use |
|-------|------|-------------|----------|
| Group | `core/group` | Layout (flow/flex/grid/constrained), background color/image/gradient, border, padding/margin, min-height, position (sticky) | **Section wrappers**, styled containers |
| Row | `core/group` (variation) | Justify, alignment, wrap, gap | Horizontal arrangements |
| Stack | `core/group` (variation) | Alignment, gap | Vertical stacking |
| Grid | `core/group` (variation) | Column count, min column width | Advanced grid layouts |
| Columns | `core/columns` | Column count (up to 6), vertical align, stack on mobile, color, spacing | Multi-section layouts |
| Column | `core/column` | Width %, vertical alignment, color, padding | Within Columns |
| Buttons | `core/buttons` | Justify content, orientation, wrap | CTA groups |
| Button | `core/button` | Fill/outline, width (25/50/75/100%), border radius, color, typography, link | **"Shop Now"**, CTAs |
| Separator | `core/separator` | Style (default/wide/dots), color, width | Section dividers |
| Spacer | `core/spacer` | Height (px), responsive | Spacing between sections |
| More | `core/more` | Custom text, hide excerpt | Blog post excerpts |
| Page Break | `core/nextpage` | None | Long-form pagination |

### Widget Blocks

| Block | Slug | Key Options | Best Use |
|-------|------|-------------|----------|
| Shortcode | `core/shortcode` | Raw shortcode input | `[bc_product_slider]`, legacy plugins |
| Search | `core/search` | Label, placeholder, button text/position, width | Header, sidebar |
| Social Icons | `core/social-links` | Icon style (default/logos-only/pill), size, justify, orientation, labels | Header, footer social links |
| Latest Posts | `core/latest-posts` | List/grid, columns, order, categories, count, show date/author/excerpt/image | Blog sections on homepage |
| Custom HTML | `core/html` | HTML editor + preview | Custom embeds, tracking code — **avoid if possible** |
| Page List | `core/page-list` | Show submenu indicator | Footer navigation |
| Categories | `core/categories` | Dropdown, hierarchy, post counts | Category navigation |
| Tag Cloud | `core/tag-cloud` | Taxonomy, counts, font sizes | Blog sidebar |
| Archives | `core/archives` | Dropdown, label, counts, group by | Blog sidebar |
| RSS | `core/rss` | Feed URL, count, show author/date/excerpt | Content aggregation |
| Calendar | `core/calendar` | Month/year | Blog sidebar |
| Latest Comments | `core/latest-comments` | Count, show avatar/date/excerpt | Social proof sidebar |

### Theme / FSE Blocks

| Block | Slug | Key Options | Best Use |
|-------|------|-------------|----------|
| Site Title | `core/site-title` | Level, link to home, color, typography | Header |
| Site Logo | `core/site-logo` | Width, link to home, new tab | Header |
| Site Tagline | `core/site-tagline` | Color, typography | Header/footer |
| Navigation | `core/navigation` | Menu, overlay (mobile), submenus on click, layout, colors, typography | Header, footer menus |
| Template Part | `core/template-part` | Area (header/footer/general) | Reusable template areas |
| Query Loop | `core/query` | Post type, order, sticky, per page, offset, category/tag/author, keyword, patterns | Blog pages, related content |
| Post Title | `core/post-title` | Level, link, color, typography | Single post templates |
| Post Content | `core/post-content` | Layout, content/wide width | Single post templates |
| Post Date | `core/post-date` | Format, link, modified date | Post meta |
| Post Excerpt | `core/post-excerpt` | Max words, read more link | Archive templates |
| Post Featured Image | `core/post-featured-image` | Link, scale, aspect ratio, overlay | Archive cards |
| Post Author Name | `core/post-author-name` | Link, color, typography | Post meta |
| Post Terms | `core/post-terms` | Taxonomy, separator, prefix/suffix | Post meta (categories/tags) |
| Read More | `core/read-more` | Custom text, border, color | Archive templates |
| Login/Out | `core/loginout` | Form display, redirect | Utility nav |
| Comments | `core/comments` | Comment template + pagination | Single posts |
| Query Pagination | `core/query-pagination` | Previous/next labels, page numbers | Within Query Loop |

### Embed Blocks

Single block `core/embed` with provider variations:

| Provider | Notes |
|----------|-------|
| YouTube | Video, playlists |
| Vimeo | Video |
| Instagram | Posts, reels |
| Facebook | Posts, videos |
| TikTok | Videos |
| Pinterest | Pins, boards |
| Spotify | Tracks, playlists, podcasts |
| Twitter/X | Tweets, timelines |
| Reddit | Posts |
| WordPress | WP posts |

All embeds support: responsive toggle, alignment, caption, aspect ratio.

---

## WooCommerce Blocks

### Product Collection (PRIMARY — Use This)

**Slug:** `woocommerce/product-collection`
**Status:** The recommended block for ALL product listings. Replaces legacy grid blocks.

**Built-in Presets:**

| Preset | Description |
|--------|-------------|
| Create Your Own | Full catalog, context-aware (adapts to shop/category/tag pages) |
| Featured | Products marked as "featured" |
| Top Rated | Highest-reviewed products |
| On Sale | Currently discounted |
| Best Sellers | Most-purchased products |
| New Arrivals | Last 7 days |
| Hand-Picked | Manually selected product IDs |
| Related Products | Auto-recommended (shared categories/tags) |
| Upsells | Premium/upgraded alternatives |
| Cross-Sells | Complementary cart items |

**Layout:** Stack, Grid (multi-column), Carousel
**Query:** Order by (alpha/newest/price/sales/rating/random), per page, sale/stock/category/tag/attribute filters

**Inner Template Blocks:**
- `woocommerce/product-image` — Product image
- `woocommerce/product-title` — Product name
- `woocommerce/product-price` — Price (incl. sale)
- `woocommerce/product-rating` — Star rating
- `woocommerce/product-button` — Add to cart
- `woocommerce/product-sale-badge` — Sale badge
- `woocommerce/product-summary` — Short description

### Featured Blocks

| Block | Slug | Description | Key Options |
|-------|------|-------------|-------------|
| Featured Product | `woocommerce/featured-product` | Hero-style single product | Product selection, show desc/price, overlay, background image, button text, min height |
| Featured Category | `woocommerce/featured-category` | Hero-style category | Category selection, show desc, overlay, background image, button text, min height |

### Filter Blocks

| Block | Slug | Description |
|-------|------|-------------|
| Product Filters (NEW WC 9.9+) | `woocommerce/product-filters` | Unified filter panel, no-reload, chips, mobile modal |
| Filter by Price | `woocommerce/price-filter` | Price range slider |
| Filter by Attribute | `woocommerce/attribute-filter` | Attribute dropdown/list, AND/OR logic |
| Filter by Stock | `woocommerce/stock-filter` | In stock / out of stock |
| Filter by Rating | `woocommerce/rating-filter` | Star rating filter |
| Active Filters | `woocommerce/active-filters` | Shows current filters with chip removal |

### Cart Blocks

| Block | Slug |
|-------|------|
| Cart (parent) | `woocommerce/cart` |
| Filled Cart | `woocommerce/filled-cart-block` |
| Empty Cart | `woocommerce/empty-cart-block` |
| Cart Items | `woocommerce/cart-items-block` |
| Cart Line Items | `woocommerce/cart-line-items-block` |
| Cart Totals | `woocommerce/cart-totals-block` |
| Cart Cross-Sells | `woocommerce/cart-cross-sells-block` |
| Order Summary | `woocommerce/cart-order-summary-block` |
| Coupon Form | `woocommerce/cart-order-summary-coupon-form-block` |
| Express Checkout | `woocommerce/cart-express-payment-block` |
| Proceed to Checkout | `woocommerce/proceed-to-checkout-block` |

### Checkout Blocks

| Block | Slug |
|-------|------|
| Checkout (parent) | `woocommerce/checkout` |
| Contact Information | `woocommerce/checkout-contact-information-block` |
| Shipping Address | `woocommerce/checkout-shipping-address-block` |
| Billing Address | `woocommerce/checkout-billing-address-block` |
| Shipping Method | `woocommerce/checkout-shipping-method-block` |
| Shipping Options | `woocommerce/checkout-shipping-methods-block` |
| Payment Options | `woocommerce/checkout-payment-block` |
| Express Checkout | `woocommerce/checkout-express-payment-block` |
| Order Note | `woocommerce/checkout-order-note-block` |
| Terms & Conditions | `woocommerce/checkout-terms-block` |
| Place Order | `woocommerce/checkout-actions-block` |
| Order Summary | `woocommerce/checkout-order-summary-block` |

### Mini-Cart Blocks

| Block | Slug |
|-------|------|
| Mini-Cart | `woocommerce/mini-cart` |
| Mini-Cart Contents | `woocommerce/mini-cart-contents` |
| Filled Mini-Cart | `woocommerce/filled-mini-cart-contents-block` |
| Empty Mini-Cart | `woocommerce/empty-mini-cart-contents-block` |
| Mini-Cart Items | `woocommerce/mini-cart-items-block` |
| Mini-Cart Footer | `woocommerce/mini-cart-footer-block` |
| Mini-Cart Title | `woocommerce/mini-cart-title-block` |
| View Cart Button | `woocommerce/mini-cart-cart-button-block` |
| Checkout Button | `woocommerce/mini-cart-checkout-button-block` |

### Other WooCommerce Blocks

| Block | Slug | Best Use |
|-------|------|----------|
| Store Notices | `woocommerce/store-notices` | Customer-facing notifications |
| Customer Account | `woocommerce/customer-account` | Login/logout + account link |
| Store Breadcrumbs | `woocommerce/breadcrumbs` | Navigation trail |
| Catalog Sorting | `woocommerce/catalog-sorting` | Product sort dropdown |
| Product Categories List | `woocommerce/product-categories` | Category navigation list/dropdown |
| Product Search | `woocommerce/product-search` | Product-specific search |
| All Reviews | `woocommerce/all-reviews` | All product reviews display |
| Reviews by Product | `woocommerce/reviews-by-product` | Single product reviews |
| Reviews by Category | `woocommerce/reviews-by-category` | Category reviews |

### Legacy Product Grid Blocks (Superseded by Product Collection)

| Block | Slug | Status |
|-------|------|--------|
| All Products | `woocommerce/all-products` | Use Product Collection instead |
| Best Selling | `woocommerce/product-best-sellers` | Use Product Collection "Best Sellers" preset |
| Newest | `woocommerce/product-new` | Use Product Collection "New Arrivals" preset |
| On Sale | `woocommerce/product-on-sale` | Use Product Collection "On Sale" preset |
| Top Rated | `woocommerce/product-top-rated` | Use Product Collection "Top Rated" preset |
| By Category | `woocommerce/product-category` | Use Product Collection with category filter |
| By Tag | `woocommerce/product-tag` | Use Product Collection with tag filter |
| By Attribute | `woocommerce/product-attribute` | Use Product Collection with attribute filter |
| Hand-picked | `woocommerce/handpicked-products` | Use Product Collection "Hand-Picked" preset |

---

## Homepage Block Plan

Recommended block structure for Byron Bay Candles homepage, using only native blocks.

### Section 1: Hero

**Current:** `[bc_product_slider]` shortcode (CSS-only carousel) — **keep this**, it's a proven custom pattern.

**Alternative if rebuilding:** `core/cover` (full-width, min-height 600px, overlay)
```
core/cover
  -> core/heading (H1)
  -> core/paragraph (tagline)
  -> core/buttons -> core/button ("Shop Now")
```

### Section 2: Category Highlights

```
core/columns (3 columns)
  -> woocommerce/featured-category ("Soy Candles")
  -> woocommerce/featured-category ("Essential Oil Blends")
  -> woocommerce/featured-category ("Gift Sets")
```

### Section 3: Best Sellers

```
core/group (full-width, styled background)
  -> core/heading (H2: "Our Best Sellers")
  -> woocommerce/product-collection (preset: Best Sellers, grid 4-col, 4 products)
```

### Section 4: Brand Story

```
core/media-text (image right, stack on mobile)
  -> core/heading (H2: "Our Story")
  -> core/paragraph (brand story)
  -> core/buttons -> core/button ("Learn More")
```

### Section 5: New Arrivals

```
core/group
  -> core/heading (H2: "New Arrivals")
  -> woocommerce/product-collection (preset: New Arrivals, carousel or grid)
```

### Section 6: Testimonials / Reviews

```
core/group (contrasting background)
  -> core/heading (H2: "What Our Customers Say")
  -> woocommerce/all-reviews (3-6 reviews, show rating + reviewer)
```

**Alternative:** `core/columns` with 3x `core/quote` blocks for curated testimonials.

### Section 7: Featured Product (Signature Candle)

```
woocommerce/featured-product (hero-style, full-width)
```

### Section 8: On Sale / Promotions

```
woocommerce/product-collection (preset: On Sale, grid 4-col)
```

### Section 9: Newsletter / VIP CTA

```
core/group (full-width, contrasting background, centered)
  -> core/heading (H2: "Join the Byron Bay Candle Club")
  -> core/paragraph ("Get 10% off + early access")
  -> core/buttons -> core/button ("Subscribe")
```

### Priority Block List for This Build

| Priority | Block | Why |
|----------|-------|-----|
| Essential | `core/cover` | Hero sections, banners, CTAs |
| Essential | `woocommerce/product-collection` | All product grids |
| Essential | `woocommerce/featured-category` | Category highlight cards |
| Essential | `woocommerce/featured-product` | Signature product showcase |
| Essential | `core/group` | Section containers |
| Essential | `core/columns` | Multi-column layouts |
| Essential | `core/buttons` / `core/button` | All CTAs |
| Essential | `core/media-text` | About/story sections |
| Essential | `core/heading` + `core/paragraph` | All text |
| High | `woocommerce/all-reviews` | Social proof |
| High | `core/image` / `core/gallery` | Lifestyle photography |
| High | `core/social-links` | Footer social icons |
| High | `core/separator` / `core/spacer` | Visual rhythm |
| Medium | `core/quote` / `core/pullquote` | Curated testimonials |
| Medium | `core/details` | FAQs, expandable info |
| Medium | `core/video` | Brand story video |
| Medium | `core/embed` (instagram) | Social feed |

### When to Build a Custom Block

Only create a custom block if ALL of these are true:
1. No native WordPress or WooCommerce block covers the need
2. A shortcode with `core/shortcode` block won't suffice
3. The component will be reused across multiple pages
4. Blocksy Content Blocks can't achieve it

**Current custom shortcode:** `[bc_product_slider]` — this is the right approach for the hero carousel since no native block provides CSS-only auto-advancing carousel behavior with custom pagination.
