# Pattern: PageSpeed Modules (`inc/perf/`)

> **Modules:** `inc/perf/loader.php` + `inc/perf/helpers.php` + 13 feature files `inc/perf/<feature>.php`
> **Data:** `inc/perf/data/perfmatters-defaults.json`, `critical-css.php`, `rucss-safelist.php`, `async-style-handles.php`, `dequeue-handles.php`
> **Assets:** `assets/js/perf-hero-facade.js` (inlined by `hero-facade`), `assets/js/perf-lazy-rescan.js` (enqueued by `lazy-rescan`)
> **Opt-in key:** `"perf"` in `clients/<slug>/manifest.json` (separate from `"features"`)
> **Loaded from:** `functions.php`, directly at theme-load time, after `custom/custom.php`
> **Spec:** `W:\BLAZE COMMERCE\pagespeed-docs\RECOMMENDATIONS-90plus-in-3-hours.md`
> **Tests:** `php tests/perf/run.php` (offline, no WordPress install)

These modules take the PageSpeed work we kept redoing per client and ship it with the theme. Nothing loads unless a site opts in. Each module's docblock records where it came from, what it measured on which site, and its per-site caveats. This page is the operator summary. The docblocks are the source of truth.

---

## Opt-in model

Perf modules are **opt-in only**. The legacy `"features"` rule, where a missing key turns everything on, does **not** apply here. The enabled set is the union of three sources (`blocksy_child_perf_enabled_features()`, `inc/perf/helpers.php`):

| Source | Where | Notes |
|---|---|---|
| `"perf"` array | `clients/<slug>/manifest.json` of each active client | The normal place |
| `BLOCKSY_CHILD_PERF_FEATURES` constant | `wp-config.php` or `custom/custom.php` | Must be an array |
| `blocksy_child_perf_features` filter | `custom/custom.php` (or a mu-plugin) | Default `[]` |

- Names are lowercased and deduped. `"*"` enables every **known** feature.
- An unknown name is dropped and logged once: `[blocksy-child][perf] unknown feature: <name>`.
- The set is resolved **once per request** and memoised, so anything added after the first resolution is ignored. `functions.php` requires `custom/custom.php` before `inc/perf/loader.php`, which means a constant or filter defined in `custom/custom.php` is seen.

**Client manifest** (the recommended default: every feature except `hero-facade`, which needs per-site poster assets and wrapper markup):

```json
{
  "perf": [
    "perfmatters-config",
    "perfmatters-filters",
    "rucss-safelist",
    "critical-css-supplements",
    "lcp-image",
    "fonts-critical-path",
    "async-styles",
    "dequeue-assets",
    "media-hygiene",
    "content-visibility",
    "minicart-hydrate",
    "lazy-rescan"
  ]
}
```

**`custom/custom.php`** (deployed from `bc-site-customizations`, lane B below):

```php
// Either: a constant (array).
define( 'BLOCKSY_CHILD_PERF_FEATURES', [ 'hero-facade' ] );

// Or: the filter. Merged with the manifest's "perf" list, not a replacement for it.
add_filter( 'blocksy_child_perf_features', function ( $features ) {
	return array_merge( (array) $features, [ 'hero-facade' ] );
} );

// Per-module tuning goes in the same file, e.g.:
add_filter( 'blocksy_child_perf_leading_images', function () { return 14; } );
```

---

## Perfmatters settings as code (`perfmatters-config`)

Perfmatters keeps its whole configuration in one `wp_options` row, `perfmatters_options`, and a database row does not travel with a PR. This module supplies the row from files through `option_perfmatters_options` and `default_option_perfmatters_options`. The second filter covers a site that has no row at all.

**Layer order, lowest to highest:** stored DB row, then `inc/perf/data/perfmatters-defaults.json` (shipped, lane A), then `custom/perfmatters.json` (per site, lane B). The per-site path can be changed with `blocksy_child_perf_pm_site_config_path` (default `BLOCKSY_CHILD_PATH . 'custom/perfmatters.json'`). The site file is only applied when it decodes to a non-empty array.

### Override contract for `custom/perfmatters.json`

- **Sections merge one level down.** For a section such as `assets`, `lazyload`, `preload` or `fonts`, each key you give replaces that same key and every key you leave out keeps the lower layer's value. A top-level scalar key is replaced as a whole.
- **List keys are replaced wholesale, never merged.** If you set `lazyload.lazy_loading_exclusions`, your list becomes the entire list. The defaults for that key (`blaze-lcp-image`, `bc-perf-lcp`, `bc-perf-hero__poster`) are gone unless you repeat them. The same applies to `assets.delay_js_exclusions` (defaults `greenShift-aos-lib`, `lazyload`) and every other list.
- **Type guard.** The merge runs a type guard afterwards. These keys, if they arrive as a newline-separated string, are split into arrays: `assets.delay_js_exclusions`, `assets.delay_js_inclusions`, `assets.rucss_excluded_stylesheets`, `assets.rucss_excluded_selectors`, `lazyload.lazy_loading_exclusions`, `lazyload.lazy_loading_parent_exclusions`, `lazyload.element_selectors`, `preload.dns_prefetch`, `preload.preconnect`, `preload.preload`, `preload.fetch_priority`, `preload.early_hint_types`, `fonts.subsets`. These keys, if they arrive as an int or float, are cast to strings: `lazyload.threshold`, `lazyload.exclude_leading_images`, `lazyload.css_background_exclude_leading`.
- `assets.rucss_excluded_selectors` ships empty on purpose. The safelist travels as code through the `rucss-safelist` module.

Example `custom/perfmatters.json`:

```json
{
  "lazyload": { "exclude_leading_images": "14" },
  "preload": { "dns_prefetch": [ "//www.googletagmanager.com" ] }
}
```

### Escape hatch, mu-plugin shim, admin notice

- **`BC_PERFMATTERS_CONFIG_AS_CODE`:** define it as `false` in `wp-config.php` and the module returns before registering anything, so the database row applies unchanged.
- **Mu-plugin shim, `inc/perf/mu-plugins/bc-perfmatters-config.php`:** this is **not** autoloaded. You normally don't need it, because the theme registers the filter after `plugins_loaded` and before `init`, and Perfmatters reads its options later than that. Copy the file to `wp-content/mu-plugins/bc-perfmatters-config.php` only if a future Perfmatters version, or some other plugin, reads `perfmatters_options` **on** `plugins_loaded`, before the theme loads. The shim requires `inc/perf/helpers.php` and then `inc/perf/perfmatters-config.php` from the active theme. It defines nothing global, and the module resolves its own paths from `__DIR__`, so it works before `functions.php` has defined `BLOCKSY_CHILD_PATH`. `BLOCKSY_CHILD_PERF_CONFIG_LOADED` records that the module is already loaded. `require_once` with the identical path is what actually prevents a second load.
- **Admin notice:** on Perfmatters' own admin screens, a notice says the settings are managed by the child theme and that saved values are overridden by `perfmatters-defaults.json` + `custom/perfmatters.json`. **The Perfmatters UI shows the stored row, not the effective values.** Don't debug from that screen.

**Verify in served HTML:** `<style id="perfmatters-used-css">` is present (RUCSS on), and the count of `type="pmdelayedscript"` is greater than 0 (Delay JS on).

---

## Per-module reference

Every element id a module prints starts with `bc-perf-`. Blocks printed through `blocksy_child_perf_style()` / `blocksy_child_perf_script()` carry `data-no-optimize="1" data-no-minify="1"`, and the fonts inline style does too. Scripts also carry `data-no-defer="1" data-cfasync="false"`. Every script printed through `blocksy_child_perf_script()` (`bc-perf-hero-facade`, `bc-perf-minicart-hydrate`) is registered in the script-id registry, which `perfmatters-filters` merges into the Delay JS and Defer JS exclusions automatically. The enqueued `bc-perf-lazy-rescan` script and the `wc-cart-fragments` seed are not printed through that helper, so they are not in the registry.

### `perfmatters-filters`

Code-side Perfmatters levers that have no settings field.

| Hook / filter | Default | Effect |
|---|---|---|
| `perfmatters_used_css_below` | `true` (no escape hatch) | Used-CSS block printed just before `</head>` so the LCP preload is discovered first |
| `perfmatters_delay_js_delay_click` | `true` (no escape hatch) | First tap is replayed after the delayed scripts load, not lost |
| `blocksy_child_perf_delay_timeout` | `7000` (**seconds**) | Delay JS effectively waits for interaction |
| `blocksy_child_perf_exclude_analytics_from_delay` | `true` | Keeps `googletagmanager.com/gtag/js`, `gtag(`, `woocommerce-google-analytics-integration`, `gtm4wp`, `/gtm.js` out of Delay JS |
| `blocksy_child_perf_delay_exclusions` | `[]` (called with a fresh `[]`) | Extra Delay JS exclusions |
| `blocksy_child_perf_css_bg_selectors` | `.wp-block-greenshift-blocks-container`, `.wp-block-greenshift-blocks-row` | CSS background lazy-load selectors (only used when `lazyload.css_background_images` is on) |
| `blocksy_child_perf_leading_images` | `0` | Minimum never-lazy leading `<img>` count. The max of this and Perfmatters' own value wins. Count in **HTML source order** |
| `blocksy_child_perf_rucss_stylesheets` | `[]` | Extra RUCSS excluded stylesheets. Any entry matching `main.min.css` is dropped and logged |
| `perfmatters_defer_js_exclusions` | the script-id registry | Same registry as Delay JS |

**Verify:** `<style id="perfmatters-used-css">` sits right before `</head>`. The gtag loader is **not** `type="pmdelayedscript"`. Neither `<script id="bc-perf-hero-facade">` nor `<script id="bc-perf-minicart-hydrate">` is delayed. The header logo `<img>` does not carry `perfmatters-lazy`, and if it does, raise the leading-images count.

### `rucss-safelist`

Merges `inc/perf/data/rucss-safelist.php` (FiboSearch, Blocksy cart panel, WooCommerce cards, carousels and more) into `perfmatters_rucss_excluded_selectors`. That list covers markup that only JS creates, which Perfmatters' no-JS scan can't see. Invalid entries are rejected and logged: empty strings, a trailing combinator, or a trailing `-`. The Perfmatters matcher gives no BEM-descendant coverage, so `.foo` does not cover `.foo__body`.

| Filter | Default | |
|---|---|---|
| `blocksy_child_perf_rucss_selectors` | the shipped list | Add or remove site selectors here, not in the data file |

**Verify:** after **Clear Used CSS** and a warm, the FiboSearch dropdown and the off-canvas cart are styled on first open.

### `critical-css-supplements`

Three inline blocks at `wp_head` priority 1. They restore the rules RUCSS systematically drops: custom-property setters, desktop-only `@media` rules, and JS-gated markup.

| Id | Printed on |
|---|---|
| `bc-perf-critical-global` | every front-end page |
| `bc-perf-critical-archive` | `is_shop() \|\| is_product_taxonomy()` |
| `bc-perf-critical-product` | `is_product()` |

| Filter | Default | |
|---|---|---|
| `blocksy_child_perf_header_min_height` | `null` (never prints) | `['desktop' => px, 'mobile' => px]`, measured per site. A wrong guess causes CLS |
| `blocksy_child_perf_critical_css_extra` | `''` | `( $extra, $bucket )`, per-site CSS appended to a bucket |

**Verify:** the `bc-perf-critical-*` styles are present on their templates. In DevTools, run `for (const s of document.styleSheets) if (s.href) s.disabled = true;`. Body-copy links should stay underlined, the archive grid should keep its column count, and the PDP gallery should stay one slide.

### `lcp-image`

Removes lazy loading from the LCP candidate, adds `fetchpriority="high"`, and prints **one** matching `<link rel="preload" as="image" fetchpriority="high">` at `wp_head` priority 1. It never preloads a `data:` URI. A second candidate in the same request is logged and skipped.

- **PDP gallery** (`woocommerce_single` size, featured image): `loading` is removed, `fetchpriority="high" decoding="sync"` is added, the class gains `blaze-lcp-image bc-perf-lcp`, and `sizes` is rewritten in the **same** filter call. Blocksy's `sizes="auto, …"` is only valid on a lazy image. The preload uses the same attachment, size and `sizes` string. A later render of the same image at another size (the floating add-to-cart bar's copy) is forced lazy.
- **First `core/cover` block** (`render_block` priority 10): the first `<img>` gets `fetchpriority="high"` and loses `loading="lazy"`. If that `<img>` already has a `fetchpriority`, it is left alone.
- **Front-page hero:** the author adds the marker class to the hero image block. The preload is derived from the saved `post_content` (`wp-image-<id>` plus the `-WxH` suffix). The rendered `<img>` is un-lazied at `the_content` priority 20.

| Filter | Default |
|---|---|
| `blocksy_child_perf_lcp_sizes` | `(min-width:1024px) 500px, (min-width:480px) calc(100vw - 48px), calc(100vw - 32px)` |
| `blocksy_child_perf_hero_img_class` | `bc-hero-img` |

**Verify:** on a PDP, the gallery `<img>` has `bc-perf-lcp`, `fetchpriority="high"` and no `loading`. Exactly one `<link rel="preload" as="image">` is present, with `href`/`imagesrcset`/`imagesizes` matching the element. The network panel shows one request for that image, not two.

### `hero-facade` (not in the recommended default)

Poster-first hero inside `<div class="bc-hero-video">`, applied at `the_content` priority 20 on the front page.

- **YouTube `<iframe>`:** replaced by `<img class="bc-perf-hero__poster" fetchpriority="high" decoding="sync">` built from the **repo asset** `custom/images/yt-poster-<id>-800.avif`. The asset is inlined as `data:` when it is ≤ `poster_max_inline_bytes`. An optional `-1920.avif` is used as `data-bc-hires`. The iframe src and title move to `data-bc-yt-src` / `data-bc-yt-title`. If no poster asset exists, the markup is left untouched.
- **`<video autoplay>`:** `autoplay` is removed, `preload="none"` is set, and `src` becomes `data-bc-src`. The poster comes from the media-library `bc_hero_poster` size (640×360, falling back to `medium_large`).
- **Footer script `<script id="bc-perf-hero-facade">`** is printed only when a hero was rewritten. It mounts the media on first interaction or 5 s after `load`, and skips the timer under reduced motion. The iframe is revealed only when the player reports PLAYING, and the media stacks **over** the poster, which is never faded.

| Filter | Default |
|---|---|
| `blocksy_child_perf_hero_facade` | `wrapper_class` `bc-hero-video`, `poster_dir` `BLOCKSY_CHILD_PATH . 'custom/images/'`, `poster_url` `BLOCKSY_CHILD_URL . 'custom/images/'`, `poster_max_inline_bytes` `24576`, `delay_ms` `5000`, `hires_min_width` `900`, `front_page_only` `true` |

The wrapper must be positioned (`position:relative` with a fixed aspect ratio). Poster generation commands (`yt-dlp` + `ffmpeg` frame 0, then AVIF) are in the `inc/perf/hero-facade.php` docblock.

**Verify:** no `<iframe` inside the hero wrapper on load, `img.bc-perf-hero__poster` present, `<script id="bc-perf-hero-facade">` present. A `<video>` hero carries `data-bc-perf-hero="1" preload="none" data-bc-src`.

### `fonts-critical-path`

Replaces Blocksy's Google Fonts `<link>` (handle `blocksy-fonts-font-source-google`) with the Perfmatters-localised CSS inlined as `<style id="bc-perf-fonts-inline">`. It uses `style_loader_tag` priority 10 first, and falls back to `perfmatters_output_buffer` priority 99. It only reads local files, makes no network calls, and caps the read at 50 KB. Anything else fails open to the original `<link>`. Turn off Blocksy's own local-google-fonts extension, because running both means duplicate font loads.

| Filter | Default | |
|---|---|---|
| `blocksy_child_perf_font_handles` | `['blocksy-fonts-font-source-google']` | Handles to inline |
| `blocksy_child_perf_fonts_url_roots` | content dir, uploads, child theme | URL → dir roots for href resolution |
| `blocksy_child_perf_fonts_cache_dir` | `WP_CONTENT_DIR/cache/perfmatters/<host>/fonts` | Newest `*google-fonts*.css` wins |
| `blocksy_child_perf_fonts_max_bytes` | `51200` | Inline cap |
| `blocksy_child_perf_font_preload_faces` | `[]` (off) | `true` = every latin woff2, or an array of URLs. Only worth it when the LCP is text |
| `blocksy_child_perf_font_fallbacks` | `[]` | Metric-matched `@font-face` fallbacks printed as `<style id="bc-perf-font-fallbacks">` at `wp_head` priority 2 |

**Verify:** `<style id="bc-perf-fonts-inline">` is present and no `<link>` to a `google-fonts` stylesheet remains. After `clear-local-fonts`, expect one un-inlined render, after which the module picks the file up again.

### `async-styles`

`style_loader_tag` priority 20 rewrites each listed handle to `media="print" onload="this.media='all'"` plus a `<noscript>` copy of the original. The shipped list is `dgwt-wcas-style` (FiboSearch) and `brb-public-main-css`. The module always refuses `ct-main-styles`, `blocksy-child-style`, `perfmatters-used-css`, any `ct-*` or `blocksy-child-*` handle, and any `main.min.css` href, logging each refusal. It skips tags with `data-pmdelayedstyle`, tags that already have `onload=`, and `media="print"` sheets.

| Filter | Default |
|---|---|
| `blocksy_child_perf_async_style_handles` | `[]`, merged with `inc/perf/data/async-style-handles.php` |

**Verify:** the listed `<link>` carries `media="print" onload=…` followed by `<noscript>`.

### `dequeue-assets`

Dequeues **and** deregisters, at `wp_enqueue_scripts` priority 1000, the handles in `inc/perf/data/dequeue-handles.php`:

- styles: `wp-block-library`, `wp-block-library-theme`, `classic-theme-styles`, `wp-components`, `jquery-ui-styles`, `slick-css`, `slick-css-theme`, `algolia-satellite`
- scripts: `jquery-ui-scripts`

The list is conservative. Each handle was verified unused in the live DOM on a named site. It is still per-site: check the relevant UI with that UI open. The block-library alignment shim ships in `bc-perf-critical-global`. Deregistering a handle also drops any asset that depends on it. The module refuses `ct-*`, `blocksy-child-*`, `jquery-core`, `jquery`, `woocommerce` and `wc-cart-fragments`, logging each refusal.

| Filter | Default |
|---|---|
| `blocksy_child_perf_dequeue_handles` | `['styles' => […], 'scripts' => […]]` from the data file. Runs at request time, so conditional tags work |

**Verify:** no `<link id="<handle>-css">` or `<script id="<handle>-js">` for the listed handles, and the UI they would have styled still works.

### `media-hygiene`

Six image and embed corrections:

1. **`image_downsize` fallback.** When a named size was never generated, the smallest generated size that covers the requested dimensions is served instead of the original.
2. **Logo.** Blocksy logo classes (`default-logo`, `transparent-logo`, `sticky-logo`, `mobile-logo`, `offcanvas-logo`) get an honest `sizes` and lose `fetchpriority`. Priority 999.
3. **Archive thumbnails.** `woocommerce_thumbnail` images on shop and taxonomy archives get `width`/`height`.
4. **Below-fold lazy.** `render_block` priority 20 adds `loading="lazy" decoding="async"` after the first N `<img>`. Every counted `<img>` is stamped `data-bc-perf-n="<position>"`. It skips `fetchpriority="high"`, existing `loading=`, and the classes `blaze-lcp-image`, `bc-perf-lcp`, `bc-perf-hero__poster`, `skip-lazy` and `kb-skip-lazy`.
5. **Stretched height.** A `wp-image-<id>` image with a `width` and no `height` gets `height` from its aspect ratio.
6. **YouTube.** `youtube.com/embed/` URLs are rewritten to `www.youtube-nocookie.com/embed/`.

| Filter | Default |
|---|---|
| `blocksy_child_perf_logo_sizes` | `(max-width: 999px) 220px, 420px` (AW's measured widths, so set your own) |
| `blocksy_child_perf_eager_img_count` | `3` |

**Verify:** the logo `<img>` has the filtered `sizes` and no `fetchpriority`. Block images carry `data-bc-perf-n`, and those past the first 3 carry `loading="lazy"`. No `youtube.com/embed/` URL remains in content. Archive thumbnails have width and height.

### `content-visibility`

`<style id="bc-perf-cv">` at `wp_head` priority 2 prints `{selector}{content-visibility:auto;contain-intrinsic-size:auto {px}px}`. Never apply it to the header, a sticky element, or an open popup. PSI can't resolve the gain, so measure with a DevTools forced-layout A/B instead.

| Filter | Default |
|---|---|
| `blocksy_child_perf_cv_map` | `footer.ct-footer` 400, `.related.products` 600, `.ct-panel.ct-offcanvas-module:not(.active)` 823 |

**Verify:** `<style id="bc-perf-cv">` is present, and nothing jumps when you scroll to the footer.

### `minicart-hydrate`

- `inc/mini-cart-empty.php` wraps the empty-cart suggestions in `<template class="bc-perf-minicart-template">`. `<script id="bc-perf-minicart-hydrate">` clones the template into place on the first sign of intent: cart-trigger hover or focus, first scroll or click, or `requestIdleCallback` with a 4 s timeout. After cloning it triggers `blocksy:frontend:init`.
- A `before` inline script on `wc-cart-fragments` seeds `sessionStorage` with the rendered `div.widget_shopping_cart_content` HTML, so a brand-new, cart-less session skips `?wc-ajax=get_refreshed_fragments`. Three guards (cart cookie, existing session keys, `localStorage` cart hash) plus the no-jQuery and already-hydrated checks mean that when anything is in doubt, the seed is skipped.

| Filter | Default |
|---|---|
| `blocksy_child_perf_minicart_triggers` | `.ct-cart-item, [data-toggle-panel="#woo-cart-panel"], .ct-cart-trigger, .cart-customlocation a` |

**Verify:** the `<template class="bc-perf-minicart-template">` and `<script id="bc-perf-minicart-hydrate">` are in the HTML. On a fresh private window with an empty cart, the first pageview makes no `get_refreshed_fragments` request. Opening the drawer shows the suggestions carousel. Adding to cart still updates the drawer.

### `lazy-rescan`

Enqueues `assets/js/perf-lazy-rescan.js` (handle `bc-perf-lazy-rescan`, footer, versioned by `filemtime`) on `is_shop() || is_product_taxonomy()` only. The script re-runs the Perfmatters lazy loader for product cards injected by Blocksy AJAX filters, Load More or pagination. It has no filters.

**Verify:** after applying an AJAX filter on the shop, no `img.perfmatters-lazy[data-src]` is left showing its placeholder.

---

## Go-live: two deployment lanes

Perf work for a site ships in **two PRs, and both must be deployed before you measure.** The theme repo does not track `custom/`.

| Lane | Repo / PR | Carries |
|---|---|---|
| **A** | `blaze-blocksy` theme PR | `inc/perf/` modules, `inc/perf/data/perfmatters-defaults.json` (and the other data files), the client manifest's `"perf"` list |
| **B** | `bc-site-customizations` PR (`sites/<slug>/custom/`) | `custom/perfmatters.json`, `custom/custom.php` filters (`blocksy_child_perf_*`), `custom/images/` (hero posters `yt-poster-<id>-800.avif` / `-1920.avif`, re-encoded background images) |

**What doesn't travel with either PR** (do these on each environment):

- **Media-library files.** After enabling `hero-facade`, run `wp media regenerate --only-missing` so the `bc_hero_poster` size exists. Missing subsizes in general also need regenerating, because `media-hygiene` (a) is insurance, not the repair.
- **ShortPixel and CDN settings.**
- **Per-host Perfmatters caches.** Regenerate the used-CSS (Clear Used CSS, then warm) and the local-fonts cache. The fonts module recovers on its own after `clear-local-fonts`: one render serves the Google `<link>`, and the next inlines the new file.

---

## Measurement rules

Use the shipped scripts. They live in the `blaze-commerce-mcp` repo and are served as MCP skill scripts.

1. **Before touching anything:** `bash scripts/preflight.sh <home> <cat> <pdp>`. It covers the stop conditions: a tiny options row, a stale hostname, Perfmatters < 2.6.4, and PHP lint of `custom/`.
2. **Before every measurement:** `bash scripts/clear-cache.sh <urls>`, which purges, warms, purges again and warms until HIT.
3. **Measure:** `bash scripts/pagespeed.sh <url> 5`. That is 5 warmed runs, each following warm → purge → warm. The verdict is computed over runs deduped on `fetchTime`: **PASS = median ≥ 90 AND (floor ≥ 85 OR ≥ 80% of unique runs ≥ 90)**. Fewer than 3 unique runs is reported as `LOW CONFIDENCE`. A run with `benchmarkIndex` < 500 is low confidence too.
4. Report the **floor and the distribution**, not only the median.
5. **Never measure right after a deploy or purge.** The first render is unoptimised and gets page-cached (cold 87 vs warm 97 on identical HTML).

---

## Do not do these (measured, repeatedly)

Copied verbatim from §6 of `RECOMMENDATIONS-90plus-in-3-hours.md`:

| Don't | Because |
|---|---|
| `rucss_method: file`, or async/`media=print` the used-CSS or any core Blocksy sheet | Austin: 54 vs 78 and **CLS 1.000**; Kajal R2/R3 CLS ≈1.0; Kajal R4 CLS-safe version: median 89 vs 90 baseline — the whole critical-CSS lever is a measured dead end on this stack |
| Hand-roll critical CSS with penthouse/Critters | Kajal spent 3 rounds; render-blocking 174→21 KB with **no** score change |
| HTTP `Link:` preload header for the LCP **image** | Austin: FCP +500 ms, LCP +1,000 ms on slow 4G (fine for *fonts* — the Kajal/Mettahemp pattern) |
| Exclude `main.min.css` from RUCSS to fix a layout break | substring-matches all 5 Blocksy bundles; AW prod 92→70. Layout break after purge = stale used-CSS → **Clear Used CSS** |
| Set `rucss_excluded_selectors` as a string / `threshold` as int / enable Delay-JS by `update_option` | RUCSS silently aborts (−25 pts, twice); every image blank on live; Delay-JS never engages (Kajal, 2 days) |
| Eager-load all carousel banners / preload with empty `locations` | bandwidth contention (choiceammo sim → 73); phantom preload competed with real LCP on BBC |
| Dequeue Stripe on PDP, webfont trims, brotli-tier chasing, SVG sprite dedupe, `wp_style_add_data(group)` | Apple/Google Pay removed; FCP moved 1 ms; ~17 KB per request only under ~470 KB raw; brotli already collapses it; does nothing for styles |
| Fade the poster out when the video starts; 2 s video delay; interaction-only hero | removes the recorded LCP (metric swings 2.3↔5.9 s); Lighthouse waits 2 s out; non-interacting visitors never see the video — use interaction **or** 5 s, PLAYING-gated |
| Judge a change from sequential PSI runs or 3-run medians | Houston: same bytes scored medians 96 and 88; retracted a "87→96". Use interleaved A/B, warmed, ≥5 runs |
| Measure right after a deploy/purge | first render is un-optimised and gets page-cached (cold 87 vs 97 warmed on identical HTML) |

---

## Writing a new perf module

- Add the name to `blocksy_child_perf_known_features()` (`inc/perf/helpers.php`). The loader maps it to `inc/perf/<name>.php`.
- Start every element id with `bc-perf-`, and every filter name with `blocksy_child_perf_`. Register hooks with named callbacks so a site can `remove_filter()` them.
- Print inline CSS/JS only through `blocksy_child_perf_style( $id, $css, $priority, $condition )` and `blocksy_child_perf_script( $id, $js, $priority, $condition )`. Both accept `string|callable` content. A callable is resolved at **print time**, and **that is the required form for any output that depends on a `blocksy_child_perf_*` filter**. Modules load at theme-load time from `functions.php`, which is after `plugins_loaded` but before anything registered on `after_setup_theme` or `init`, so a string built at load time would miss filters added from those hooks. A plain string is always printed verbatim, even one that matches a function name. Content that resolves to `''` prints nothing, not even the tag. `_script()` registers its id for the Delay JS and Defer JS exclusions.
- Keep request state in `$GLOBALS['blocksy_child_perf_state']`, not in a `static`, so tests can reset it.
- Put data in `inc/perf/data/<name>.php`, which returns an array, and read it with `blocksy_child_perf_data()`.
- Gate hook registration on `blocksy_child_perf_is_frontend_request()` (theme-load time). A callback that rewrites rendered HTML (`render_block`, `the_content`, and similar) must also check `blocksy_child_perf_is_frontend_render()` first and return its input unchanged when that is false. `REST_REQUEST` and `is_feed()` are only known at render time. Guard WooCommerce and Perfmatters calls with `function_exists()`, so the module works with or without them.

### Testing

- `php tests/perf/run.php` runs every `tests/perf/test-*.php` file in one process. It needs no WordPress install. Write each case as `bc_test( 'name', function () { assert_same( $actual, $expected ); } );`.
- **`tests/perf/bootstrap.php` owns every WP stub.** Conditional tags and data getters read from `$GLOBALS['bc_wp_stub']`, so a test sets `$GLOBALS['bc_wp_stub']['is_product'] = true;` instead of declaring its own `is_product()`. `bc_wp_stub_reset()` restores the defaults. A new stub goes into bootstrap.php, wrapped in `function_exists()`, with a default in `bc_wp_stub_defaults()`. Never add a stub to a test file: two declarations in one process are a fatal "Cannot redeclare".
- `run.php` calls `blocksy_child_perf_reset_state()` (only defined when `BC_PERF_TESTING` is true) and `bc_wp_stub_reset()` before each test file.
- Hooks are recorded in `$GLOBALS['bc_test_hooks'][ $hook ][ $priority ]`. `error_log()` output is captured to a temp file and read back with `bc_test_error_log_messages()`.
- Also run `php -l` on every file you touch.
