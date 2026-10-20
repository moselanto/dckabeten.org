<?php
/**
 * Template Name: DCK Photo Gallery
 *
 * Professional unlimited photo gallery with masonry grid, category filter tabs,
 * lightbox (keyboard + touch), and AJAX load-more.
 *
 * To add photos: Gallery Photos > Add New in wp-admin.
 * Set the Featured Image. Use the Title as a caption (optional).
 * Assign Gallery Categories to enable filter tabs.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$dck_cats  = function_exists( 'dck_gallery_categories' ) ? dck_gallery_categories() : array();
$dck_posts = function_exists( 'dck_gallery_get_photos' )  ? dck_gallery_get_photos( 20 ) : array();

/* Total count for has-more detection */
$dck_total_q = new WP_Query( array(
	'post_type'      => 'dck_photo',
	'post_status'    => 'publish',
	'posts_per_page' => 1,
	'no_found_rows'  => false,
) );
$dck_total   = (int) $dck_total_q->found_posts;
$dck_hasmor  = $dck_total > 20;
?>

<section class="dck-page-hero">
	<div class="dck-container dck-center">
		<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( dck_home_val( 'dck_gallery_eyebrow', 'Moments of Grace' ) ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_gallery_title', 'Our Photo Gallery' ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_gallery_lead', 'A window into the life of our church family: worship, fellowship, and the goodness of God.' ) ); ?></p>
		<?php if ( $dck_total ) : ?>
			<div class="dck-legal-hero__meta">
				<span class="dck-pill"><?php echo esc_html( sprintf( _n( '%d photo', '%d photos', $dck_total, 'dck-elite' ), $dck_total ) ); ?></span>
				<span class="dck-pill"><?php esc_html_e( 'Tap any photo to view full size', 'dck-elite' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php if ( ! empty( $dck_cats ) ) : ?>
<div class="dck-gal-filter-wrap dck-gal-filter-wrap--sticky">
	<div class="dck-container">
		<nav class="dck-gal-filter" role="tablist" aria-label="<?php esc_attr_e( 'Filter photos by category', 'dck-elite' ); ?>">
			<button class="dck-gf-btn is-active" type="button" data-cat="" role="tab" aria-selected="true"><?php esc_html_e( 'All', 'dck-elite' ); ?></button>
			<?php foreach ( $dck_cats as $c ) : ?>
			<button class="dck-gf-btn" type="button" data-cat="<?php echo esc_attr( $c->slug ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $c->name ); ?></button>
			<?php endforeach; ?>
		</nav>
	</div>
</div>
<?php endif; ?>

<section class="dck-section dck-gal-section">
	<div class="dck-container">

		<div class="dck-gal-grid" id="dck-gal-grid">
		<?php if ( ! empty( $dck_posts ) ) : ?>
			<?php foreach ( $dck_posts as $dck_ph ) :
				$dck_thumb = get_the_post_thumbnail_url( $dck_ph->ID, 'dck-gallery-thumb' );
				if ( ! $dck_thumb ) {
					$dck_thumb = get_the_post_thumbnail_url( $dck_ph->ID, 'medium_large' );
				}
				$dck_full = get_the_post_thumbnail_url( $dck_ph->ID, 'large' );
				if ( ! $dck_full ) {
					$dck_full = get_the_post_thumbnail_url( $dck_ph->ID, 'full' );
				}
				if ( ! $dck_thumb || ! $dck_full ) { continue; }
				$dck_cap = esc_attr( $dck_ph->post_title );
			?>
			<div class="dck-gal-item dck-gal-visible"
			     data-full="<?php echo esc_url( $dck_full ); ?>"
			     data-caption="<?php echo $dck_cap; ?>"
			     tabindex="0" role="button"
			     aria-label="<?php echo $dck_cap ? $dck_cap : esc_attr__( 'View photo', 'dck-elite' ); ?>">
				<img src="<?php echo esc_url( $dck_thumb ); ?>"
				     alt="<?php echo $dck_cap; ?>"
				     loading="lazy" decoding="async">
				<?php if ( trim( $dck_ph->post_title ) ) : ?>
				<div class="dck-gal-caption"><span><?php echo esc_html( $dck_ph->post_title ); ?></span></div>
				<?php endif; ?>
				<div class="dck-gal-overlay" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
				</div>
			</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="dck-gal-empty">
				<svg viewBox="0 0 24 24" width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
				<h3><?php esc_html_e( 'Gallery coming soon', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Beautiful moments from our church family are on the way. Check back after Sunday!', 'dck-elite' ); ?></p>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
				<a class="dck-btn dck-btn--outline" style="margin-top:20px"
				   href="<?php echo esc_url( admin_url( 'post-new.php?post_type=dck_photo' ) ); ?>">
					<?php esc_html_e( 'Add Your First Photo', 'dck-elite' ); ?>
				</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		</div><!-- .dck-gal-grid -->

		<?php if ( $dck_hasmor ) : ?>
		<div class="dck-gal-more" id="dck-gal-more">
			<button type="button" class="dck-btn dck-btn--outline" id="dck-gal-loadmore">
				<?php esc_html_e( 'Load More Photos', 'dck-elite' ); ?>
			</button>
			<p class="dck-gal-count" id="dck-gal-count"><?php
				/* translators: %1$d = shown count, %2$d = total count */
				printf( esc_html__( 'Showing %1$d of %2$d photos', 'dck-elite' ), min( 20, $dck_total ), $dck_total );
			?></p>
		</div>
		<?php endif; ?>

	</div><!-- .dck-container -->
</section>

<!-- ── LIGHTBOX ─────────────────────────────────────────────────────────── -->
<div id="dck-lb" class="dck-lb" role="dialog" aria-modal="true"
     aria-label="<?php esc_attr_e( 'Photo viewer', 'dck-elite' ); ?>" hidden>
	<div class="dck-lb__back" id="dck-lb-back" aria-hidden="true"></div>
	<div class="dck-lb__wrap">
		<button class="dck-lb__close" id="dck-lb-close" type="button"
		        aria-label="<?php esc_attr_e( 'Close', 'dck-elite' ); ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="M18 6 6 18M6 6l12 12"/></svg>
		</button>
		<button class="dck-lb__nav dck-lb__prev" id="dck-lb-prev" type="button"
		        aria-label="<?php esc_attr_e( 'Previous photo', 'dck-elite' ); ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="M15 18l-6-6 6-6"/></svg>
		</button>
		<div class="dck-lb__stage">
			<div class="dck-lb__img-wrap">
				<img class="dck-lb__img" id="dck-lb-img" src="" alt="">
			</div>
			<p class="dck-lb__cap" id="dck-lb-cap"></p>
		</div>
		<button class="dck-lb__nav dck-lb__next" id="dck-lb-next" type="button"
		        aria-label="<?php esc_attr_e( 'Next photo', 'dck-elite' ); ?>">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" focusable="false"><path d="M9 18l6-6-6-6"/></svg>
		</button>
	</div><!-- .dck-lb__wrap -->
</div><!-- #dck-lb -->

<section class="dck-section" style="padding-top:0">
	<div class="dck-container">
		<div class="dck-sermons-cta dck-reveal">
			<div>
				<h3><?php esc_html_e( 'Be part of the next photo', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Join us this Sunday and experience the joy for yourself. Prefer not to be photographed? Let us know.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-sermons-cta__btns">
				<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( dck_elite_page_url( 'services' ) ); ?>"><?php esc_html_e( 'Service Times', 'dck-elite' ); ?></a>
				<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'terms' ) . '#dck-optout' ); ?>"><?php esc_html_e( 'Photo preference', 'dck-elite' ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
