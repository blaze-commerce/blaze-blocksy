<?php
/**
 * Generic Carousel — Reusable carousel for logos and articles.
 *
 * Usage:
 *   [bc_carousel type="logo" ids="632962,632966,632967" columns="5" dots="1" arrows="1"]
 *   [bc_carousel type="article" count="4" columns="3" dots="1" arrows="1"]
 *
 * Reuses product-slider.js behavior (dots, arrows, swipe, responsive columns).
 * Same CSS classes as bc-product-slider for consistent styling.
 *
 * @package Blocksy_Child
 * @date    2026-04-08
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'bc_carousel', 'blocksy_child_carousel_shortcode' );

/**
 * Render the generic carousel shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function blocksy_child_carousel_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'type'    => 'logo',    // logo | article
		'ids'     => '',        // comma-separated attachment IDs (for logo type)
		'count'   => 4,         // number of posts (for article type)
		'columns' => 4,
		'dots'    => 1,
		'arrows'  => 1,
	], $atts, 'bc_carousel' );

	$type    = sanitize_key( $atts['type'] );
	$columns = absint( $atts['columns'] );

	switch ( $type ) {
		case 'logo':
			return blocksy_child_carousel_render_logos( $atts, $columns );
		case 'article':
			return blocksy_child_carousel_render_articles( $atts, $columns );
		default:
			return '';
	}
}

/**
 * Render logo carousel.
 */
function blocksy_child_carousel_render_logos( $atts, $columns ) {
	if ( empty( $atts['ids'] ) ) {
		return '';
	}

	$ids = array_map( 'absint', explode( ',', $atts['ids'] ) );
	$total = count( $ids );

	if ( $total === 0 ) {
		return '';
	}

	$slider_id  = 'bc-ps-' . wp_rand( 1000, 9999 );
	// Mobile: 5 dots (page-based with 2 visible = ceil(9/2) = 5)
	// Desktop/Tablet: 2 dots (start + end)
	// Render max dots (5 for mobile), JS hides extras at larger viewports.
	$max_pages_mobile = max( 1, (int) ceil( $total / 2 ) );

	ob_start();
	?>
	<div class="bc-product-slider bc-logo-carousel" id="<?php echo esc_attr( $slider_id ); ?>"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		data-columns-tablet="4"
		data-columns-mobile="2"
		data-total="<?php echo esc_attr( $total ); ?>"
		data-slide-by="1">
		<div class="bc-product-slider__track">
			<ul class="bc-logo-carousel__list">
				<?php foreach ( $ids as $id ) :
					$img = wp_get_attachment_image( $id, 'medium', false, [
						'class'   => 'bc-logo-carousel__img',
						'loading' => 'lazy',
					] );
					if ( $img ) :
				?>
				<li class="bc-logo-carousel__item"><?php echo $img; ?></li>
				<?php endif; endforeach; ?>
			</ul>
		</div>

		<?php if ( $atts['arrows'] && $total > $columns ) : ?>
		<button class="bc-product-slider__arrow bc-product-slider__arrow--prev" aria-label="Previous">
			<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
		</button>
		<button class="bc-product-slider__arrow bc-product-slider__arrow--next" aria-label="Next">
			<svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"></polyline></svg>
		</button>
		<?php endif; ?>

		<?php if ( $atts['dots'] && $max_pages_mobile > 1 ) : ?>
		<div class="bc-product-slider__dots">
			<?php for ( $i = 0; $i < $max_pages_mobile; $i++ ) : ?>
			<button class="bc-product-slider__dot<?php echo 0 === $i ? ' active' : ''; ?>"
				aria-label="Page <?php echo $i + 1; ?>"></button>
			<?php endfor; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render article carousel.
 */
function blocksy_child_carousel_render_articles( $atts, $columns ) {
	$count = absint( $atts['count'] );

	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	// If specific IDs provided, use them.
	if ( ! empty( $atts['ids'] ) ) {
		$ids = array_map( 'absint', explode( ',', $atts['ids'] ) );
		$args['post__in'] = $ids;
		$args['orderby']  = 'post__in';
	}

	$posts = new WP_Query( $args );

	if ( ! $posts->have_posts() ) {
		return '';
	}

	$total     = $posts->post_count;
	$slider_id = 'bc-ps-' . wp_rand( 1000, 9999 );
	// Same dot logic as logo carousel: 2 dots desktop (start + end),
	// page-based on mobile (ceil(total / 1) = total dots).
	$max_pages_mobile = max( 1, (int) ceil( $total / 1 ) );

	ob_start();
	?>
	<div class="bc-product-slider bc-article-carousel" id="<?php echo esc_attr( $slider_id ); ?>"
		data-columns="<?php echo esc_attr( $columns ); ?>"
		data-columns-tablet="2"
		data-columns-mobile="1"
		data-total="<?php echo esc_attr( $total ); ?>"
		data-slide-by="1">
		<div class="bc-product-slider__track">
			<ul class="bc-article-carousel__list">
				<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
				<li class="bc-article-carousel__item">
					<div class="bc-article-carousel__link">
						<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="bc-article-carousel__image">
							<?php the_post_thumbnail( 'medium_large', [ 'loading' => 'lazy' ] ); ?>
						</a>
						<?php endif; ?>
						<div class="bc-article-carousel__content">
							<time class="bc-article-carousel__date" datetime="<?php echo get_the_date( 'c' ); ?>">
								<?php echo get_the_date( 'd/m/Y' ); ?>
							</time>
							<h3 class="bc-article-carousel__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="bc-article-carousel__excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?></p>
							<a href="<?php the_permalink(); ?>" class="bc-article-carousel__read-more">Read More →</a>
						</div>
					</div>
				</li>
				<?php endwhile; wp_reset_postdata(); ?>
			</ul>
		</div>

		<?php if ( $atts['arrows'] && $total > $columns ) : ?>
		<button class="bc-product-slider__arrow bc-product-slider__arrow--prev" aria-label="Previous">
			<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
		</button>
		<button class="bc-product-slider__arrow bc-product-slider__arrow--next" aria-label="Next">
			<svg viewBox="0 0 24 24"><polyline points="9 6 15 12 9 18"></polyline></svg>
		</button>
		<?php endif; ?>

		<?php if ( $atts['dots'] && $max_pages_mobile > 1 ) : ?>
		<div class="bc-product-slider__dots">
			<?php for ( $i = 0; $i < $max_pages_mobile; $i++ ) : ?>
			<button class="bc-product-slider__dot<?php echo 0 === $i ? ' active' : ''; ?>"
				aria-label="<?php echo esc_attr( 'Page ' . ( $i + 1 ) ); ?>"></button>
			<?php endfor; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
