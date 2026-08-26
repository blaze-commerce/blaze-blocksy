<?php
/**
 * Bonza (client-specific hooks).
 *
 * Loaded via manifest.json when this client is active.
 * Only add hooks specific to THIS client here.
 * Reusable hooks go in inc/woocommerce.php or inc/hooks.php.
 *
 * @package Blocksy_Child
 * @client  Bonza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add client-specific hooks below.

/**
 * Global footer, ClickUp 86eypb6aw.
 *
 * Figma FOOTER component set 564:95449, desktop variant 29399:99534.
 * The component set also has "Tab 2" (17298:196546) and "Mobile 2"
 * (17298:196807) sibling variants in the Figma file itself. The
 * ClickUp task text says the footer "carries no separate mobile or
 * tablet component" and both canvases just reflow the desktop one, so
 * this build follows the task text: media queries in footer.css
 * reflow the one markup, there is no per-variant branching.
 *
 * Registered on `blocksy:builder:footer:custom-output`, a filter the
 * parent theme's own `blocksy_output_footer()` (inc/integrations/
 * theme-builders.php) checks before falling back to its default
 * builder rows: a non-empty return short-circuits those default rows
 * AND is wrapped in the theme's own single `<footer id="footer"
 * class="ct-footer">` landmark (with schema.org attributes) for us.
 * This markup supplies only the content, inside a plain `<div
 * class="bfg-footer">` for CSS scoping, not a second `<footer>`
 * landmark or a competing copyright line.
 *
 * That filter was chosen over Blocksy's Footer Builder rows because
 * this content set (Health Hub cards, an amber sign-up band, a
 * five-column nav + trust-badge block, eight payment marks) is far
 * past what the builder's logo/menu/copyright/social/widget-area
 * elements can express. Blocksy's own `ct_content_block` Content
 * Blocks feature needs either wp-admin or a full-bootstrap `wp`
 * command, and both are broken on this LocalWP install (see CHANGELOG
 * for the WP-CLI gotcha). Every visible string is wrapped in `__()`
 * under the `blocksy-child` text domain so WPML String Translation
 * can pick it up across the five languages.
 *
 * @package Blocksy_Child
 * @client  Bonza
 */

/**
 * URL to a footer-only asset (logo, trust badges, social icons).
 *
 * @param string $file Filename inside clients/bonza/assets/footer/.
 * @return string
 */
function bonza_footer_asset_url( $file ) {
	return BLOCKSY_CHILD_URL . 'clients/bonza/assets/footer/' . $file;
}

/**
 * The four footer navigation columns (SHOP / DISCOVER / SUPPORT +
 * the contact column is built separately, it is not a link list).
 *
 * Hrefs are '#' placeholders: the destination pages (Health Hub,
 * About Us, FAQs, Privacy Policy, etc.) are separate to-do tasks in
 * this same ClickUp folder and do not exist on the site yet. Centralised
 * here so the real URLs can be filled in from one place once those
 * pages ship, without touching the markup below.
 *
 * @return array<string, array{label: string, links: array<int, array{label: string, href: string}>}>
 */
function bonza_footer_nav_columns() {
	return [
		'shop'     => [
			'label' => __( 'Shop', 'blocksy-child' ),
			'links' => [
				[ 'label' => __( 'Plant-based Dog Food', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Supplements', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Create Personalised Meal Plan', 'blocksy-child' ), 'href' => '#' ],
			],
		],
		'discover' => [
			'label' => __( 'Discover', 'blocksy-child' ),
			'links' => [
				[ 'label' => __( 'About Us', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Reviews', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Can Dogs Be Plant-Based', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Science of Nutrition', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Environmental Impact', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Our Impact', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Ingredients', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Recyclable Packaging', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Subscribe & Save', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'VidiVet', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Explore FAQs', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Health Hub for Dogs', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Crash Course In Vegan Pet Food', 'blocksy-child' ), 'href' => '#' ],
			],
		],
		'support'  => [
			'label' => __( 'Support', 'blocksy-child' ),
			'links' => [
				[ 'label' => __( 'My Account', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Track My Order', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Order Shipment', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Subscribe & Save Terms', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Happiness Guarantee', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'VidiVet Terms', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Privacy Policy', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Manage Cookie Settings', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Terms of Service', 'blocksy-child' ), 'href' => '#' ],
				[ 'label' => __( 'Referral Program', 'blocksy-child' ), 'href' => '#' ],
			],
		],
	];
}

/**
 * Payment marks shown in the bottom bar.
 *
 * WHY plain-text pills instead of brand SVGs: ClickUp 86eypb6aw flags
 * that this set (drawn as Visa, Mastercard, Discover, Amex, Apple Pay,
 * Google Pay, PayPal, an unnamed image mark, Klarna, an unnamed
 * instance) must be reconciled against the live store's actually-
 * enabled WooCommerce gateways before it ships, and this LocalWP
 * sandbox has no trustworthy enabled-gateway signal to reconcile
 * against (Stripe and PayPal are both disabled; only a "cheque" test
 * method is on). The pre-rebuild live site (bonza.dog) shows no
 * payment marks in its footer at all either, so there is no ground
 * truth to copy. Trim this list to the confirmed gateway set (and
 * swap in licensed brand SVGs) in one place, here, once that decision
 * is made.
 *
 * @return string[]
 */
function bonza_footer_payment_methods() {
	return [ 'Visa', 'Mastercard', 'Discover', 'Amex', 'Apple Pay', 'Google Pay', 'PayPal', 'Klarna' ];
}

/**
 * Build the global footer markup and return it as a string.
 *
 * Hooked to `blocksy:builder:footer:custom-output` (see below), a
 * filter the parent theme's own `blocksy_output_footer()` checks
 * first: a non-empty return short-circuits Blocksy's default footer
 * builder rows AND is wrapped in the theme's own single `<footer
 * id="footer">` landmark (with its schema.org attributes) for us, so
 * this markup only needs to supply the content, not the landmark.
 *
 * @param string $existing_content Value already on the filter, empty
 *                                 by default. Intentionally not used:
 *                                 this client module owns the entire
 *                                 footer for this site, there is no
 *                                 other registered producer to chain
 *                                 with. Declared so the callback's
 *                                 signature matches what `apply_
 *                                 filters()` actually passes.
 * @return string
 */
function bonza_footer_render_global( $existing_content = '' ) {
	$nav_columns = bonza_footer_nav_columns();
	ob_start();
	?>
	<div class="bfg-footer">

		<section class="bfg-band bfg-band--hub">
			<div class="bfg-hub-intro">
				<p class="bfg-eyebrow"><?php esc_html_e( 'Keep Reading', 'blocksy-child' ); ?></p>
				<h2 class="bfg-h2"><?php esc_html_e( 'Go Deeper on the Science', 'blocksy-child' ); ?></h2>
				<p class="bfg-lead"><?php esc_html_e( 'Evidence-based articles covering dog health and nutrition from our Health Hub, written by our founder and referenced to peer-reviewed research.', 'blocksy-child' ); ?></p>
			</div>
			<div class="bfg-cards">
				<?php
				$articles = [
					[
						'tag'   => __( 'Landmark Research', 'blocksy-child' ),
						/* translators: %s: description that follows the bold "The Waltham Catalogue:" prefix. */
						'title' => sprintf( __( '<strong>The Waltham Catalogue:</strong> %s', 'blocksy-child' ), __( 'the most significant mapping of the dog gut microbiome ever made', 'blocksy-child' ) ),
					],
					[
						'tag'   => __( 'The Microbiome', 'blocksy-child' ),
						/* translators: %s: description that follows the bold "The Gut Microbiome:" prefix. */
						'title' => sprintf( __( '<strong>The Gut Microbiome:</strong> %s', 'blocksy-child' ), __( "your dog's hidden health command centre", 'blocksy-child' ) ),
					],
					[
						'tag'   => __( 'The Biotics Triad', 'blocksy-child' ),
						/* translators: %s: description that follows the bold "Prebiotics, Probiotics and Postbiotics:" prefix. */
						'title' => sprintf( __( '<strong>Prebiotics, Probiotics and Postbiotics:</strong> %s', 'blocksy-child' ), __( 'why all three matter', 'blocksy-child' ) ),
					],
					[
						'tag'   => __( 'The Evidence', 'blocksy-child' ),
						/* translators: %s: description that follows the bold "Plant-Based Dog Food:" prefix. */
						'title' => sprintf( __( '<strong>Plant-Based Dog Food:</strong> %s', 'blocksy-child' ), __( 'the peer-reviewed evidence on gut health and longevity', 'blocksy-child' ) ),
					],
				];
				foreach ( $articles as $article ) :
					?>
					<a class="bfg-card" href="#">
						<span class="bfg-card-content">
							<span class="bfg-card-tag"><?php echo esc_html( $article['tag'] ); ?></span>
							<span class="bfg-card-title"><?php echo wp_kses( $article['title'], [ 'strong' => [] ] ); ?></span>
						</span>
						<span class="bfg-card-arrow" aria-hidden="true">
							<img src="<?php echo esc_url( bonza_footer_asset_url( 'icon-arrow-right.svg' ) ); ?>" alt="" width="18" height="18" loading="lazy" />
						</span>
					</a>
				<?php endforeach; ?>
			</div>
			<a class="bfg-btn bfg-btn--primary" href="#"><?php esc_html_e( 'Explore the health hub', 'blocksy-child' ); ?></a>
		</section>

		<img class="bfg-image-strip" src="<?php echo esc_url( bonza_footer_asset_url( 'image-strip.png' ) ); ?>" alt="" width="1480" height="127" loading="lazy" />

		<section class="bfg-band bfg-band--signup">
			<p class="bfg-eyebrow"><?php esc_html_e( 'A Good Reason to Stay', 'blocksy-child' ); ?></p>
			<h2 class="bfg-h2"><?php esc_html_e( 'Start With the Gut. Support the Whole Dog.', 'blocksy-child' ); ?></h2>
			<div class="bfg-signup-actions">
				<a class="bfg-btn bfg-btn--primary bfg-btn--block" href="#"><?php esc_html_e( 'Shop Superfoods & Ancient Grains', 'blocksy-child' ); ?></a>
				<a class="bfg-btn bfg-btn--outline bfg-btn--block" href="#"><?php esc_html_e( 'Explore Bioactive Bites Supplements', 'blocksy-child' ); ?></a>
			</div>
		</section>

		<img class="bfg-image-strip" src="<?php echo esc_url( bonza_footer_asset_url( 'image-strip.png' ) ); ?>" alt="" width="1480" height="127" loading="lazy" />

		<div class="bfg-main">
			<div class="bfg-trust-row">
				<img class="bfg-badge--bcorp" src="<?php echo esc_url( bonza_footer_asset_url( 'badge-b-corp-pending.svg' ) ); ?>" alt="<?php esc_attr_e( 'Certified B Corporation Pending', 'blocksy-child' ); ?>" width="33" height="62" loading="lazy" />
				<?php /* Figma names this mark only "image 687"; content is not identified, so it is treated as decorative (empty alt) rather than a guessed label. Flag for a real alt once it is identified. */ ?>
				<img src="<?php echo esc_url( bonza_footer_asset_url( 'badge-unnamed-687.png' ) ); ?>" alt="" width="125" height="56" loading="lazy" />
				<img src="<?php echo esc_url( bonza_footer_asset_url( 'badge-1-percent-planet.svg' ) ); ?>" alt="<?php esc_attr_e( '1% for the Planet', 'blocksy-child' ); ?>" width="134" height="56" loading="lazy" />
				<img src="<?php echo esc_url( bonza_footer_asset_url( 'badge-trees-for-the-future.svg' ) ); ?>" alt="<?php esc_attr_e( 'Trees for the Future', 'blocksy-child' ); ?>" width="137" height="56" loading="lazy" />
				<img src="<?php echo esc_url( bonza_footer_asset_url( 'badge-rainforest-trust.svg' ) ); ?>" alt="<?php esc_attr_e( 'Rainforest Trust', 'blocksy-child' ); ?>" width="276" height="56" loading="lazy" />
			</div>

			<nav class="bfg-nav-section" aria-label="<?php esc_attr_e( 'Footer', 'blocksy-child' ); ?>">
					<div class="bfg-intro-col">
						<div class="bfg-logo">
							<img src="<?php echo esc_url( bonza_footer_asset_url( 'bonza-footer-logo.svg' ) ); ?>" alt="<?php esc_attr_e( 'Bonza', 'blocksy-child' ); ?>" width="189" height="64" loading="lazy" />
						</div>
						<div class="bfg-social">
							<a href="#" aria-label="<?php esc_attr_e( 'Instagram', 'blocksy-child' ); ?>"><img src="<?php echo esc_url( bonza_footer_asset_url( 'social-instagram.svg' ) ); ?>" alt="" width="18" height="18" loading="lazy" /></a>
							<a href="#" aria-label="<?php esc_attr_e( 'Facebook', 'blocksy-child' ); ?>"><img src="<?php echo esc_url( bonza_footer_asset_url( 'social-facebook.svg' ) ); ?>" alt="" width="18" height="18" loading="lazy" /></a>
							<a href="#" aria-label="<?php esc_attr_e( 'TikTok', 'blocksy-child' ); ?>"><img src="<?php echo esc_url( bonza_footer_asset_url( 'social-tiktok.svg' ) ); ?>" alt="" width="18" height="18" loading="lazy" /></a>
						</div>
					</div>

					<?php foreach ( $nav_columns as $column ) : ?>
						<div class="bfg-nav-col">
							<h3><?php echo esc_html( $column['label'] ); ?></h3>
							<ul>
								<?php foreach ( $column['links'] as $link ) : ?>
									<li><a href="<?php echo esc_url( $link['href'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>

					<div class="bfg-contact-col">
						<h3><?php esc_html_e( 'Contact', 'blocksy-child' ); ?></h3>
						<div class="bfg-contact-item">
							<span class="bfg-contact-label"><?php esc_html_e( 'Call Us', 'blocksy-child' ); ?></span>
							<a class="bfg-contact-value" href="tel:+441453709677"><?php esc_html_e( '01453 709677 (Mon-Fri 8:30am-5pm)', 'blocksy-child' ); ?></a>
						</div>
						<div class="bfg-contact-item">
							<span class="bfg-contact-label"><?php esc_html_e( 'WhatsApp Us', 'blocksy-child' ); ?></span>
							<a class="bfg-contact-value" href="https://wa.me/447453381766"><?php esc_html_e( '07453 381766', 'blocksy-child' ); ?></a>
						</div>
						<div class="bfg-contact-item">
							<span class="bfg-contact-label"><?php esc_html_e( 'Email', 'blocksy-child' ); ?></span>
							<a class="bfg-contact-value" href="mailto:whistle@bonza.dog">whistle@bonza.dog</a>
						</div>
						<div class="bfg-contact-item">
							<span class="bfg-contact-label"><?php esc_html_e( 'Support', 'blocksy-child' ); ?></span>
							<span class="bfg-contact-value"><?php esc_html_e( 'Chat With Us or Search Our Help Articles', 'blocksy-child' ); ?></span>
						</div>
						<div class="bfg-contact-item">
							<span class="bfg-contact-label"><?php esc_html_e( 'Our Address', 'blocksy-child' ); ?></span>
							<span class="bfg-contact-value"><?php esc_html_e( 'Happea Chappea Ltd, 6 Burgoyne Rd, London, N4 1AD, United Kingdom', 'blocksy-child' ); ?></span>
							<span class="bfg-contact-vat"><?php esc_html_e( 'VAT No. 499 6318 26', 'blocksy-child' ); ?></span>
						</div>
					</div>
				</nav>
		</div>

		<div class="bfg-bottom">
			<p class="bfg-copyright">
				<?php
				printf(
					/* translators: %d: current year. */
					esc_html__( '© Happea Chappea Limited – %d. Store by Blaze Commerce', 'blocksy-child' ),
					(int) gmdate( 'Y' )
				);
				?>
			</p>
			<div class="bfg-payment-row">
				<?php foreach ( bonza_footer_payment_methods() as $method ) : ?>
					<span class="bfg-payment"><?php echo esc_html( $method ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_filter( 'blocksy:builder:footer:custom-output', 'bonza_footer_render_global' );

/**
 * Enqueue the footer stylesheet.
 *
 * Kept out of clients/bonza/bonza.css (loaded by the manifest, shared
 * with other in-flight Bonza work) so this task cannot collide with
 * concurrent edits to that file. Depends on `blocksy-child-style`, the
 * same handle clients/{slug}/{slug}.css itself depends on (see
 * inc/enqueue.php), so both load after Blocksy's own Customizer CSS.
 */
function bonza_footer_enqueue_assets() {
	$file = BLOCKSY_CHILD_PATH . 'clients/bonza/footer.css';

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_style(
		'bonza-footer',
		BLOCKSY_CHILD_URL . 'clients/bonza/footer.css',
		[ 'blocksy-child-style' ],
		filemtime( $file )
	);
}
add_action( 'wp_enqueue_scripts', 'bonza_footer_enqueue_assets' );

/**
 * Login/signup modal skin (86eypb6k6).
 *
 * Restyles Blocksy Companion Pro's native account modal to match the Figma
 * "LOGIN/SIGNUP MODAL" component set. Own file, own handle, isolated from
 * bonza.css and every other client asset so concurrent client-module work
 * on this file cannot collide with it.
 */
function bonza_login_signup_modal_enqueue_assets() {
	$file = BLOCKSY_CHILD_PATH . 'clients/bonza/login-signup-modal.css';

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_style(
		'bonza-login-signup-modal',
		BLOCKSY_CHILD_URL . 'clients/bonza/login-signup-modal.css',
		[ 'blocksy-child-style' ],
		filemtime( $file )
	);
}
add_action( 'wp_enqueue_scripts', 'bonza_login_signup_modal_enqueue_assets' );

/**
 * Shipping panel skin (86eypb6kz).
 *
 * Restyles the shared inc/product-information.php off-canvas panel's
 * Shipping tab to match the Figma mobile spec, scoped to max-width:767px.
 * Only enqueued on single product pages, where the panel actually renders.
 */
function bonza_shipping_panel_enqueue_assets() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$file = BLOCKSY_CHILD_PATH . 'clients/bonza/shipping-panel.css';

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_style(
		'bonza-shipping-panel',
		BLOCKSY_CHILD_URL . 'clients/bonza/shipping-panel.css',
		[ 'blocksy-child-product-information' ],
		filemtime( $file )
	);
}
add_action( 'wp_enqueue_scripts', 'bonza_shipping_panel_enqueue_assets' );
