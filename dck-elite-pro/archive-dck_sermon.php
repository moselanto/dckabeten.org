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

<?php
$dck_paged    = max( 1, (int) get_query_var( 'paged' ) );
$dck_filtered = ! empty( $_GET['dck_sermon_series'] ) || ! empty( $_GET['dck_sermon_speaker'] ) || ! empty( $_GET['dck_sermon_topic'] ); // phpcs:ignore WordPress.Security.NonceVerification
$dck_feature  = ( 1 === $dck_paged && ! $dck_filtered && have_posts() );
$dck_count    = wp_count_posts( 'dck_sermon' );
$dck_total    = isset( $dck_count->publish ) ? (int) $dck_count->publish : 0;
$dck_series_n = wp_count_terms( array( 'taxonomy' => 'dck_sermon_series', 'hide_empty' => true ) );
?>

<section class="dck-page-hero dck-sermons-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( dck_home_val( 'dck_sermons_eyebrow', __( 'Grow in the Word', 'dck-elite' ) ) ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_sermons_title', __( 'Sermon Library', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_sermons_lead', __( 'Catch up on recent messages from Deliverance Church Kabete N Market. Watch, listen, read and grow in the Word, wherever you are in the world.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<span class="dck-pill"><?php echo esc_html( sprintf( _n( '%d message', '%d messages', $dck_total, 'dck-elite' ), $dck_total ) ); ?></span>
			<?php if ( ! is_wp_error( $dck_series_n ) && (int) $dck_series_n > 0 ) : ?>
				<span class="dck-pill"><?php echo esc_html( sprintf( _n( '%d series', '%d series', (int) $dck_series_n, 'dck-elite' ), (int) $dck_series_n ) ); ?></span>
			<?php endif; ?>
			<a class="dck-pill dck-pill--link" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch live on Sunday', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>

<section class="dck-section dck-sermons-page">
	<div class="dck-container">

		<div class="dck-sfinder dck-reveal">
			<div class="dck-ai-search">
				<label for="dck-ss-input" data-i18n="ss_label"><?php esc_html_e( 'Find a message. Try: sermons about forgiveness, or messages on faith and hope', 'dck-elite' ); ?></label>
				<div class="dck-ai-search-bar">
					<input type="text" id="dck-ss-input" data-i18n-ph="ss_ph" placeholder="<?php esc_attr_e( 'Describe what you need...', 'dck-elite' ); ?>" autocomplete="off">
					<button type="button" id="dck-ss-go" class="dck-btn dck-btn--primary" data-i18n="ss_btn"><?php esc_html_e( 'Search', 'dck-elite' ); ?></button>
				</div>
				<div id="dck-ss-results" class="dck-ss-results"></div>
			</div>
			<form class="dck-filter" method="get" action="<?php echo esc_url( $archive_url ); ?>">
				<?php
				dck_elite_tax_select( 'dck_sermon_series', __( 'All Series', 'dck-elite' ) );
				dck_elite_tax_select( 'dck_sermon_speaker', __( 'All Speakers', 'dck-elite' ) );
				dck_elite_tax_select( 'dck_sermon_topic', __( 'All Topics', 'dck-elite' ) );
				?>
				<?php if ( $dck_filtered ) : ?>
					<a class="dck-filter__reset" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Clear filters', 'dck-elite' ); ?></a>
				<?php endif; ?>
				<noscript><button class="dck-btn dck-btn--outline" type="submit"><?php esc_html_e( 'Filter', 'dck-elite' ); ?></button></noscript>
			</form>
		</div>

		<?php
		if ( have_posts() ) :
			if ( $dck_feature ) :
				the_post();
				$fid    = get_the_ID();
				$fimg   = dck_sermon_image_url( $fid );
				$fscr   = get_post_meta( $fid, '_dck_scripture', true );
				$fsp    = dck_sermon_first_term( $fid, 'dck_sermon_speaker' );
				$fser   = dck_sermon_first_term( $fid, 'dck_sermon_series' );
				?>
				<article class="dck-sfeature dck-reveal">
					<a class="dck-sfeature__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php if ( $fimg ) : ?>
							<img src="<?php echo esc_url( $fimg ); ?>" alt="" width="1200" height="675" fetchpriority="high" decoding="async">
						<?php endif; ?>
						<span class="dck-sfeature__play"><svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
					</a>
					<div class="dck-sfeature__body">
						<p class="dck-sfeature__tag"><?php esc_html_e( 'Latest Message', 'dck-elite' ); ?></p>
						<h2 class="dck-sfeature__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="dck-sfeature__meta">
							<span><?php echo esc_html( get_the_date() ); ?></span>
							<?php if ( $fsp ) : ?><span><?php echo esc_html( $fsp->name ); ?></span><?php endif; ?>
							<?php if ( $fser ) : ?><span><?php echo esc_html( $fser->name ); ?></span><?php endif; ?>
						</p>
						<?php if ( $fscr ) : ?><p class="dck-scr-chip"><?php echo esc_html( $fscr ); ?></p><?php endif; ?>
						<p class="dck-sfeature__exc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40 ) ); ?></p>
						<div class="dck-sfeature__btns">
							<a class="dck-btn dck-btn--gold" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Watch, Listen or Read', 'dck-elite' ); ?></a>
							<a class="dck-btn dck-btn--ghost" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( get_the_title() . ' - ' . get_permalink() ); ?>"><?php esc_html_e( 'Share', 'dck-elite' ); ?></a>
						</div>
					</div>
				</article>
				<h2 class="dck-sermons-sub"><?php esc_html_e( 'More Messages', 'dck-elite' ); ?></h2>
				<?php
			endif;
			?>
			<div class="dck-sermons-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$sid  = get_the_ID();
				$img  = dck_sermon_image_url( $sid );
				$scr  = get_post_meta( $sid, '_dck_scripture', true );
				$sp   = dck_sermon_first_term( $sid, 'dck_sermon_speaker' );
				$ser  = dck_sermon_first_term( $sid, 'dck_sermon_series' );
				?>
				<article class="dck-sermon-card dck-reveal">
					<a class="dck-sermon-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php if ( $img ) : ?>
							<img src="<?php echo esc_url( $img ); ?>" alt="" width="800" height="450" loading="lazy" decoding="async">
						<?php else : ?>
							<span class="dck-sermon-card__ph"><svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3v18M5 6h14M5 6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h5M19 6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-5"/></svg></span>
						<?php endif; ?>
						<?php if ( $ser ) : ?><span class="dck-sermon-card__series"><?php echo esc_html( $ser->name ); ?></span><?php endif; ?>
						<span class="dck-sermon-card__play"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
					</a>
					<div class="dck-sermon-card__body">
						<div class="dck-sermon-card__meta"><?php echo esc_html( get_the_date() ); ?><?php echo $sp ? ' &middot; ' . esc_html( $sp->name ) : ''; ?></div>
						<h3 class="dck-sermon-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $scr ) : ?><p class="dck-scr-chip"><?php echo esc_html( $scr ); ?></p><?php endif; ?>
						<p class="dck-sermon-card__exc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<div class="dck-sermon-card__foot">
							<span class="dck-sermon-card__badges">
								<?php foreach ( dck_sermon_badges( $sid ) as $bd ) : ?>
									<span><?php echo esc_html( $bd ); ?></span>
								<?php endforeach; ?>
							</span>
							<a class="dck-sermon-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Open', 'dck-elite' ); ?><span class="screen-reader-text"> <?php the_title(); ?></span></a>
						</div>
					</div>
				</article>
				<?php
			endwhile;
			?>
			</div>
			<?php
		else :
			?>
			<div class="dck-empty">
				<h2><?php esc_html_e( 'No messages found', 'dck-elite' ); ?></h2>
				<p><?php esc_html_e( 'Try a different filter, or browse the full library.', 'dck-elite' ); ?></p>
				<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'See all sermons', 'dck-elite' ); ?></a>
			</div>
			<?php
		endif;
		?>

		<nav class="dck-pager" aria-label="<?php esc_attr_e( 'Sermon pages', 'dck-elite' ); ?>">
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '&larr; ' . __( 'Newer', 'dck-elite' ), 'next_text' => __( 'Older', 'dck-elite' ) . ' &rarr;' ) ); ?>
		</nav>

		<div class="dck-sermons-cta dck-reveal">
			<div>
				<h3><?php esc_html_e( 'Never miss a message', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Join us live every Sunday from anywhere in the world, or ask our Bible assistant about any passage.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-sermons-cta__btns">
				<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_watch_live', 'Watch Live' ) ); ?></a>
				<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'bible' ) ); ?>"><?php esc_html_e( 'Bible Assistant', 'dck-elite' ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
