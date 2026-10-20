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
$ig_url = get_theme_mod( 'dck_ig_url', '' );
$tt_url = get_theme_mod( 'dck_tiktok_url', '' );
$main   = null;
foreach ( dck_elite_service_times() as $dck_st ) {
	if ( false !== stripos( $dck_st['name'], 'glorious' ) ) {
		$main = $dck_st;
	}
}

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
	$embed_markup = '<div class="dck-embed"><iframe src="https://www.youtube.com/embed/' . esc_attr( dck_home_val( 'dck_live_default_video', 'RKItJ4L4sSY' ) ) . '" ' . $attrs . '></iframe></div>';
}
?>

<section class="dck-page-hero dck-live-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Digital Ministry', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_live_title', __( 'Watch Live', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_live_lead', __( 'Wherever you are in the world, there is a seat for you. Join our worship live every Sunday.', 'dck-elite' ) ) ); ?></p>
	</div>
</section>

<section class="dck-section dck-live2">
	<div class="dck-container dck-live2__grid">
		<div class="dck-live2__player dck-reveal">
			<div class="dck-live2__bar">
				<span class="dck-live2__badge"><span class="dck-live2__pulse" aria-hidden="true"></span><?php esc_html_e( 'Live / Latest', 'dck-elite' ); ?></span>
				<span class="dck-live2__when"><?php echo esc_html( $main ? sprintf( __( 'Sunday %1$s, %2$s (EAT, UTC+3)', 'dck-elite' ), $main['name'], $main['time'] ) : __( 'Sundays (EAT, UTC+3)', 'dck-elite' ) ); ?></span>
			</div>
			<?php echo $embed_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts. ?>
			<div class="dck-live2__share">
				<span><?php esc_html_e( 'Invite someone to watch:', 'dck-elite' ); ?></span>
				<a class="dck-tool" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( __( 'Join me to worship live with Deliverance Church Kabete:', 'dck-elite' ) . ' ' . get_permalink() ); ?>">WhatsApp</a>
				<a class="dck-tool" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>">Facebook</a>
				<button type="button" class="dck-tool dck-copy" data-copy="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'Copy link', 'dck-elite' ); ?></button>
			</div>
		</div>

		<aside class="dck-live2__side">
			<div class="dck-live2__cd dck-reveal" data-dck-countdown="<?php echo esc_attr( dck_elite_next_service_ms() ); ?>">
				<p class="dck-eyebrow"><?php esc_html_e( 'Next Live Service In', 'dck-elite' ); ?></p>
				<div class="dck-countdown" data-dck-cd-out><?php esc_html_e( 'Loading...', 'dck-elite' ); ?></div>
				<p class="dck-localtime" data-tz="Africa/Nairobi" hidden><?php esc_html_e( 'Church time now:', 'dck-elite' ); ?> <strong></strong></p>
			</div>
			<div class="dck-live2__times dck-reveal">
				<h3><?php esc_html_e( 'Sunday Programme', 'dck-elite' ); ?></h3>
				<ul class="dck-times">
					<?php foreach ( dck_elite_service_times() as $s ) : ?>
						<li><span><?php echo esc_html( $s['name'] ); ?></span><strong><?php echo esc_html( $s['time'] ); ?></strong></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="dck-live2__follow dck-reveal">
				<h3><?php esc_html_e( 'Never miss a service', 'dck-elite' ); ?></h3>
				<div class="dck-faq2__btns">
					<?php if ( $yt_ch ) : ?><a class="dck-btn dck-btn--gold dck-btn--block" href="<?php echo esc_url( $yt_ch ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Subscribe on YouTube', 'dck-elite' ); ?></a><?php endif; ?>
					<?php if ( $fb_url ) : ?><a class="dck-btn dck-btn--outline dck-btn--block" href="<?php echo esc_url( $fb_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Follow on Facebook', 'dck-elite' ); ?></a><?php endif; ?>
					<?php if ( $ig_url ) : ?><a class="dck-btn dck-btn--outline dck-btn--block" href="<?php echo esc_url( $ig_url ); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
					<?php if ( $tt_url ) : ?><a class="dck-btn dck-btn--outline dck-btn--block" href="<?php echo esc_url( $tt_url ); ?>" target="_blank" rel="noopener">TikTok</a><?php endif; ?>
				</div>
			</div>
		</aside>
	</div>
</section>

<section class="dck-section dck-live2__past">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Missed a Service?', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Watch and Read Past Messages', 'dck-elite' ); ?></h2>
		</div>
		<?php if ( $playlist && function_exists( 'dck_elite_video_embed' ) ) : ?>
			<div class="dck-live2__playlist"><?php echo dck_elite_video_embed( $playlist, 'Past Services' ); // phpcs:ignore ?></div>
		<?php endif; ?>
		<div class="dck-sermons-grid" style="margin-top:32px">
			<?php
			$recent = new WP_Query( array( 'post_type' => 'dck_sermon', 'posts_per_page' => 3, 'no_found_rows' => true ) );
			if ( $recent->have_posts() ) :
				while ( $recent->have_posts() ) :
					$recent->the_post();
					$rimg = function_exists( 'dck_sermon_image_url' ) ? dck_sermon_image_url( get_the_ID() ) : '';
					?>
					<article class="dck-sermon-card dck-reveal">
						<a class="dck-sermon-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( $rimg ) : ?><img src="<?php echo esc_url( $rimg ); ?>" alt="" loading="lazy" decoding="async" width="800" height="450"><?php endif; ?>
							<span class="dck-sermon-card__play"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
						</a>
						<div class="dck-sermon-card__body">
							<div class="dck-sermon-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
							<h3 class="dck-sermon-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="dck-sermon-card__exc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
		<div class="dck-center" style="margin-top:30px">
			<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( get_post_type_archive_link( 'dck_sermon' ) ); ?>"><?php esc_html_e( 'Browse the Sermon Library', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
