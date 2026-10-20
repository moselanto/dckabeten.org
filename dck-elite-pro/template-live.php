<?php
/**
 * Template Name: DCK Live Stream
 *
 * Premium Watch Live page. Features the church broadcast (Customizer override,
 * or the default featured stream), a live badge, service times, subscribe CTA,
 * a countdown to the next service, and recent recorded services.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$channel  = get_theme_mod( 'dck_youtube_channel_id', '' );
$live_url = get_theme_mod( 'dck_youtube_live_url', '' );
$playlist = get_theme_mod( 'dck_youtube_playlist_url', '' );
$yt_ch    = get_theme_mod( 'dck_yt_url', '' );
$fb_url   = get_theme_mod( 'dck_fb_url', '' );

$attrs = 'title="DCK Live Service" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"';
if ( $channel ) {
	$embed_markup = '<div class="dck-embed"><iframe src="https://www.youtube.com/embed/live_stream?channel=' . esc_attr( $channel ) . '" ' . $attrs . '></iframe></div>';
} elseif ( $live_url && function_exists( 'dck_elite_video_embed' ) ) {
	$embed_markup = dck_elite_video_embed( $live_url, 'Live Service' );
} else {
	$embed_markup = '<div class="dck-embed"><iframe src="https://www.youtube.com/embed/RKItJ4L4sSY" ' . $attrs . '></iframe></div>';
}
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Digital Ministry', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Watch Live', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'Wherever you are, there is a seat for you. Join our worship live every Sunday.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section dck-live-wrap">
	<div class="dck-container">
		<div class="dck-live-frame dck-reveal">
			<div class="dck-live-top">
				<span class="dck-live-badge"><?php esc_html_e( 'LIVE / LATEST', 'dck-elite' ); ?></span>
				<span class="dck-live-when"><?php esc_html_e( 'Sunday Glorious Service - 10:40 AM (EAT)', 'dck-elite' ); ?></span>
			</div>
			<?php echo $embed_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts. ?>
			<div class="dck-live-actions">
				<?php if ( $yt_ch ) : ?>
					<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $yt_ch ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Subscribe on YouTube', 'dck-elite' ); ?></a>
				<?php endif; ?>
				<?php if ( $fb_url ) : ?>
					<a class="dck-btn dck-btn--ghost dck-btn--on-dark" href="<?php echo esc_url( $fb_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Follow on Facebook', 'dck-elite' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<div class="dck-card dck-center" data-dck-countdown="<?php echo esc_attr( dck_elite_next_service_ms() ); ?>" style="margin-top:36px;border-top:5px solid var(--dck-gold)">
			<p class="dck-eyebrow"><?php esc_html_e( 'Next Live Service Begins In', 'dck-elite' ); ?></p>
			<div class="dck-countdown" data-dck-cd-out><?php esc_html_e( 'Loading...', 'dck-elite' ); ?></div>
			<p style="margin-top:10px;color:var(--dck-gray)"><?php esc_html_e( 'See you Sunday, in person or online.', 'dck-elite' ); ?></p>
		</div>

		<div class="dck-service-grid" style="margin-top:40px">
			<?php foreach ( dck_elite_service_times() as $s ) : ?>
				<div class="dck-service" style="background:var(--dck-blue-100);border-color:var(--dck-line)">
					<h3 style="color:var(--dck-blue-700)"><?php echo esc_html( $s['name'] ); ?></h3>
					<p style="color:var(--dck-ink)"><?php echo esc_html( $s['time'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="dck-section dck-stats">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Missed a Service?', 'dck-elite' ); ?></p>
			<h2 style="color:#fff"><?php esc_html_e( 'Watch Past Services', 'dck-elite' ); ?></h2>
			<p class="dck-lead dck-center" style="color:#c6cff0"><?php esc_html_e( 'Catch up on recent messages and be refreshed in the Word any time.', 'dck-elite' ); ?></p>
		</div>
		<div style="margin-top:32px">
		<?php if ( $playlist && function_exists( 'dck_elite_video_embed' ) ) : ?>
			<?php echo dck_elite_video_embed( $playlist, 'Past Services' ); // phpcs:ignore ?>
		<?php else : ?>
			<div class="dck-grid-3">
			<?php
			$recent = new WP_Query( array( 'post_type' => 'dck_sermon', 'posts_per_page' => 3, 'no_found_rows' => true ) );
			if ( $recent->have_posts() ) :
				while ( $recent->have_posts() ) :
					$recent->the_post();
					?>
					<article class="dck-card">
						<span class="dck-card__tag"><?php esc_html_e( 'Sermon', 'dck-elite' ); ?></span>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				echo '<p class="dck-center" style="grid-column:1/-1;color:#c6cff0">' . esc_html__( 'Recorded services will appear here soon.', 'dck-elite' ) . '</p>';
			endif;
			?>
			</div>
			<div class="dck-center" style="margin-top:30px">
				<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( get_post_type_archive_link( 'dck_sermon' ) ); ?>"><?php esc_html_e( 'Browse the Sermon Library', 'dck-elite' ); ?></a>
			</div>
		<?php endif; ?>
		</div>
	</div>
</section>

<?php
get_footer();
