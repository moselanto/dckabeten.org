<?php
/**
 * Sermon library (archive).
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$archive_url = get_post_type_archive_link( 'dck_sermon' );

if ( ! function_exists( 'dck_elite_tax_select' ) ) {
	function dck_elite_tax_select( $tax, $label ) {
		$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}
		$current = isset( $_GET[ $tax ] ) ? sanitize_title( wp_unslash( $_GET[ $tax ] ) ) : '';
		echo '<select name="' . esc_attr( $tax ) . '" onchange="this.form.submit()">';
		echo '<option value="">' . esc_html( $label ) . '</option>';
		foreach ( $terms as $t ) {
			echo '<option value="' . esc_attr( $t->slug ) . '" ' . selected( $current, $t->slug, false ) . '>' . esc_html( $t->name ) . '</option>';
		}
		echo '</select>';
	}
}
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Grow in the Word', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Sermon Library', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'Catch up on recent messages from Deliverance Church Kabete N Market - watch, listen, and grow in the Word.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container">
		<div class="dck-ai-search dck-reveal">
			<label for="dck-ss-input" data-i18n="ss_label"><?php esc_html_e( 'Ask for a message. Try: sermons about forgiveness, or messages on faith and hope', 'dck-elite' ); ?></label>
			<div class="dck-ai-search-bar">
				<input type="text" id="dck-ss-input" data-i18n-ph="ss_ph" placeholder="<?php esc_attr_e( 'Describe what you need...', 'dck-elite' ); ?>" autocomplete="off">
				<button type="button" id="dck-ss-go" class="dck-btn dck-btn--primary" data-i18n="ss_btn"><?php esc_html_e( 'AI Search', 'dck-elite' ); ?></button>
			</div>
			<div id="dck-ss-results" class="dck-ss-results"></div>
		</div>

		<form class="dck-filter" method="get" action="<?php echo esc_url( $archive_url ); ?>">
			<?php
			dck_elite_tax_select( 'dck_sermon_series', __( 'All Series', 'dck-elite' ) );
			dck_elite_tax_select( 'dck_sermon_speaker', __( 'All Speakers', 'dck-elite' ) );
			dck_elite_tax_select( 'dck_sermon_topic', __( 'All Topics', 'dck-elite' ) );
			?>
			<noscript><button class="dck-btn dck-btn--outline" type="submit"><?php esc_html_e( 'Filter', 'dck-elite' ); ?></button></noscript>
		</form>

		<div class="dck-sermons-grid">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				$sid      = get_the_ID();
				$thumb    = has_post_thumbnail() ? get_the_post_thumbnail_url( $sid, 'large' ) : get_post_meta( $sid, '_dck_sermon_image', true );
				$scr      = get_post_meta( $sid, '_dck_scripture', true );
				$speakers = get_the_term_list( $sid, 'dck_sermon_speaker', '', ', ' );
				?>
				<article class="dck-sermon-card dck-reveal">
					<a class="dck-sermon-card__media" href="<?php the_permalink(); ?>">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
						<?php else : ?>
							<span class="dck-sermon-card__ph" aria-hidden="true"><svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3v18M5 6h14M5 6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h5M19 6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-5"/></svg></span>
						<?php endif; ?>
						<span class="dck-sermon-card__play" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
					</a>
					<div class="dck-sermon-card__body">
						<div class="dck-sermon-card__meta"><?php echo esc_html( get_the_date() ); ?><?php if ( $speakers && ! is_wp_error( $speakers ) ) { echo ' &middot; ' . wp_kses_post( $speakers ); } ?></div>
						<h3 class="dck-sermon-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $scr ) : ?><p class="dck-sermon-card__scr"><?php echo esc_html( $scr ); ?></p><?php endif; ?>
						<p class="dck-sermon-card__exc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<a class="dck-sermon-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Watch / Listen', 'dck-elite' ); ?></a>
					</div>
				</article>
				<?php
			endwhile;
		else :
			echo '<p style="grid-column:1/-1">' . esc_html__( 'No sermons found. Try a different filter, or check back soon.', 'dck-elite' ) . '</p>';
		endif;
		?>
		</div>

		<div class="dck-center" style="margin-top:40px">
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		</div>
	</div>
</section>

<?php
get_footer();
