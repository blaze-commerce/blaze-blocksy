# offcanvas-icons

Lets an admin replace the icon in the heading of the mini cart drawer and the wishlist drawer with an uploaded image
or SVG, from the Customizer. These icons are separate from the header bar icons, which Blocksy's own Customizer sets
(Header > Cart / Wishlist > Icon Source).

Status `stable`. Default off. No plugin requirement. Port of v1 `inc/offcanvas-icons.php`, which `legacy-v1` stops
loading while this module is on.

## Security fix

v1 read the uploaded SVG file and printed it unchanged in the drawer heading on every page. An SVG holding
`<script>`, an `onload` handler or a `javascript:` link therefore ran script for every visitor. This module prints only
the output of `Sanitizer::svg()`:

- Parsed with `DOMDocument` and `LIBXML_NONET`. Entities are never substituted. Any `<!DOCTYPE` or `<!ENTITY`, any
  input over 512 KB, input that does not parse and a root other than `<svg>` all give `''`.
- Elements kept: `svg g path circle rect line polyline polygon ellipse title desc defs use symbol linearGradient
  radialGradient stop clipPath mask`. Any other element (`script`, `style`, `foreignObject`, `a`, `image`, `animate`,
  `set`, ...) is removed with everything inside it. Element names match in any case and only in the SVG namespace, so
  `<SVG>` and `<svg:path>` are kept and `<svg:script>` is not.
- Attributes kept: geometry and presentation (`viewBox`, `d`, `points`, `x y x1 y1 x2 y2 cx cy r rx ry width height`,
  `fill*`, `stroke*`, `clip-*`, `mask`, `opacity`, `transform`, gradient and stop attributes), `class`, `id`,
  `aria-hidden`, `aria-label`, `role`, `focusable`. `href` / `xlink:href` only when the value starts with `#`.
- Always dropped: every `on*` attribute in any case, `style`, any value containing `javascript:`, `vbscript:`, `data:`
  or a backslash, and any `url(...)` that is not `url(#id)`.
- Output has no XML declaration, no comments, and starts with `<svg`.

The file is read only when the attachment's MIME type is `image/svg+xml` and its real path (symlinks and `..`
resolved) is inside the uploads directory. The sanitised SVG is cached in a transient keyed by attachment id and
`post_modified` for 7 days.

A file the sanitiser rejects leaves the drawer's default icon and records `svg_rejected` in the health log, naming
the attachment. **SVGs exported with a `<!DOCTYPE>` line (older Illustrator exports) are rejected:** delete that line,
or re-export without it, and upload again.

## What it does

1. Registers the Customizer section "Off-canvas Panel Icons (Child Theme)" with two media controls, Minicart Drawer
   Icon and Wishlist Drawer Icon (`customize_register`, priority 10). Same section id, options and labels as v1.
2. Defines the v1 helpers that v1's drawer code calls (`compat.php`), so `inc/woocommerce.php` and
   `inc/wishlist-offcanvas.php` render the uploaded icon. An SVG is inlined with `ct-icon` plus the caller's class
   added to its root, so it takes Blocksy's icon size and `currentColor`. Any other image is printed as
   `<img class="ct-icon ..." src="..." alt="" />`. When nothing is set, the drawer keeps its default icon.

## Settings

Set in `site-config.php`:

```php
'settings' => [ 'offcanvas-icons' => [ 'minicart_icon_id' => 123 ] ],
```

| Key | Default | Value |
|---|---|---|
| `minicart_icon_id` | `0` | Attachment id of the mini cart drawer icon |
| `wishlist_icon_id` | `0` | Attachment id of the wishlist drawer icon |

A key set in site config wins, and an explicit `0` there means no custom icon. A key not set falls back to the
Customizer option, as in v1: `blocksy_child_minicart_panel_icon_id` and `blocksy_child_wishlist_panel_icon_id`.
Existing uploads keep working with no change.

## Surfaces

`drawer.cart.icon`, `drawer.wishlist.icon`. Provides `component.icons`.

## Hooks

No new hooks. The v1 functions stay defined while the module is on and now return sanitised markup. They are kept
for v1's own callers; site code should call the module instead:

| v1 function | Use instead |
|---|---|
| `blocksy_child_offcanvas_icon_markup( $which, $extra_class )` | `module( 'offcanvas-icons' )->markup( $which, $extra_class )` |
| `blocksy_child_offcanvas_icon_id( $which )` | `module( 'offcanvas-icons' )->icon_id( $which )` |
| `blocksy_child_offcanvas_icon_options()` | `Blaze\Child\Modules\OffcanvasIcons\Module::OPTIONS` |
| `blocksy_child_register_offcanvas_icon_controls( $wp_customize )` | `module( 'offcanvas-icons' )->register_controls( $wp_customize )` |

`blocksy_child_register_offcanvas_icon_controls` is no longer the hooked callback, so
`remove_action( 'customize_register', 'blocksy_child_register_offcanvas_icon_controls' )` no longer removes the
controls. Use the method below.

## Extend or disable

The Customizer callback is a named public method on the module instance:

```php
use function Blaze\Child\Api\module;

add_action( 'wp_loaded', function () {
	$m = module( 'offcanvas-icons' );
	if ( $m ) {
		remove_action( 'customize_register', [ $m, 'register_controls' ] );
	}
} );
```

Methods: `register_controls`, `markup`, `icon_id`, `sanitised_svg`.

Disable the module with `'offcanvas-icons' => false` in `site-config.php`, or name it in the
`BLAZE_CHILD_DISABLE_MODULES` kill switch. Either way `legacy-v1` loads `inc/offcanvas-icons.php` again, and with it
the unsanitised v1 inlining.

Turning it off any other way after boot (the `blaze_child/module_enabled` filter, a lost surface clash, an exception)
leaves v1's file skipped with no replacement. The drawers then show their default icons, and the health log records
`legacy_files_orphaned` naming the module and the file.
