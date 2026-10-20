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
wp_enqueue_script( 'dck-live', get_template_directory_uri() . '/assets/js/live.js', array(), DCK_ELITE_VERSION, true );
$dck_live_day   = (int) dck_home_val( 'dck_live_day', '0' );
$dck_live_start = (string) dck_home_val( 'dck_live_start', '10:40' );
$dck_live_end   = (string) dck_home_val( 'dck_live_end', '13:00' );
wp_localize_script(
	'dck-live',
	'DCK_LIVE',
	array(
		'next'   => dck_elite_next_service_ms(),
		'day'    => $dck_live_day,
		'start'  => $dck_live_start,
		'end'    => $dck_live_end,
		'title'  => dck_home_val( 'dck_live_event_title', __( 'Sunday Glorious Service - Deliverance Church Kabete N', 'dck-elite' ) ),
		'url'    => get_permalink(),
		'addr'   => dck_church_address(),
		'i18n'   => array(
			'live'    => __( 'Live now', 'dck-elite' ),
			'next'    => __( 'Next service', 'dck-elite' ),
			'in'      => __( 'Starts in', 'dck-elite' ),
			'started' => __( 'We are live. Join us now!', 'dck-elite' ),
			'yours'   => __( 'Your time', 'dck-elite' ),
			'copied'  => __( 'Link copied', 'dck-elite' ),
		),
	)
);
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

$dck_live_poster = dck_theme_image( 'dck_live_poster', 'assets/images/home/joy.webp' );
if ( $channel ) {
	$embed_markup = '<div class="dck-embed">' . dck_yt_facade( 'https://www.youtube.com/embed/live_stream?channel=' . rawurlencode( $channel ), __( 'DCK Live Service', 'dck-elite' ), $dck_live_poster ) . '</div>';
} elseif ( $live_url && function_exists( 'dck_elite_video_embed' ) ) {
	$embed_markup = dck_elite_video_embed( $live_url, 'Live Service' );
} else {
	$dck_vid      = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) dck_home_val( 'dck_live_default_video', 'RKItJ4L4sSY' ) );
	$embed_markup = '<div class="dck-embed">' . dck_yt_facade( 'https://www.youtube.com/embed/' . $dck_vid, __( 'DCK Live Service', 'dck-elite' ), 'https://i.ytimg.com/vi/' . $dck_vid . '/hqdefault.jpg' ) . '</div>';
}
?>

<section class="dck-theater" id="watch">
	<div class="dck-theater__glow" aria-hidden="true"></div>
	<div class="dck-theater__inner">
		<header class="dck-theater__head">
			<div>
				<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Digital Ministry', 'dck-elite' ); ?></p>
				<h1><?php echo esc_html( dck_home_val( 'dck_live_title', __( 'Watch Live', 'dck-elite' ) ) ); ?></h1>
				<p class="dck-theater__lead"><?php echo esc_html( dck_home_val( 'dck_live_lead', __( 'Wherever you are in the world, there is a seat for you. Worship with us live every Sunday.', 'dck-elite' ) ) ); ?></p>
			</div>
			<span class="dck-status" id="dck-live-status" data-state="next"><span class="dck-status__dot" aria-hidden="true"></span><span id="dck-live-status-t"><?php esc_html_e( 'Next service', 'dck-elite' ); ?></span></span>
		</header>

		<div class="dck-theater__grid">
			<div class="dck-theater__player">
				<?php echo $embed_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts. ?>
				<div class="dck-theater__under">
					<a class="dck-qa" href="<?php echo esc_url( dck_elite_page_url( 'prayer-requests' ) ); ?>"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-4.4-9.3-9A5.5 5.5 0 0 1 12 6a5.5 5.5 0 0 1 9.3 6c-2.3 4.6-9.3 9-9.3 9z"/></svg><?php esc_html_e( 'Request prayer', 'dck-elite' ); ?></a>
					<a class="dck-qa" href="<?php echo esc_url( dck_elite_page_url( 'give' ) ); ?>"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 12v9H4v-9M2 7h20v5H2zM12 21V7M12 7H7.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7z"/></svg><?php esc_html_e( 'Give online', 'dck-elite' ); ?></a>
					<a class="dck-qa" href="<?php echo esc_url( dck_elite_page_url( 'bible' ) ); ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14z"/></svg><?php esc_html_e( 'Open the Bible', 'dck-elite' ); ?></a>
					<button type="button" class="dck-qa" id="dck-live-share"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg><?php esc_html_e( 'Invite a friend', 'dck-elite' ); ?></button>
				</div>
			</div>

			<aside class="dck-theater__side">
				<div class="dck-tcard dck-tcard--cd">
					<p class="dck-tcard__k" id="dck-live-cdlabel"><?php esc_html_e( 'Starts in', 'dck-elite' ); ?></p>
					<div class="dck-cd" id="dck-live-cd" aria-live="polite">
						<span><b data-u="d">0</b><i><?php esc_html_e( 'days', 'dck-elite' ); ?></i></span>
						<span><b data-u="h">00</b><i><?php esc_html_e( 'hrs', 'dck-elite' ); ?></i></span>
						<span><b data-u="m">00</b><i><?php esc_html_e( 'min', 'dck-elite' ); ?></i></span>
						<span><b data-u="s">00</b><i><?php esc_html_e( 'sec', 'dck-elite' ); ?></i></span>
					</div>
					<div class="dck-tz">
						<div><span><?php esc_html_e( 'Nairobi (EAT)', 'dck-elite' ); ?></span><strong id="dck-live-eat">&nbsp;</strong></div>
						<div><span id="dck-live-yourlbl"><?php esc_html_e( 'Your time', 'dck-elite' ); ?></span><strong id="dck-live-local">&nbsp;</strong></div>
					</div>
					<div class="dck-tcard__btns">
						<a class="dck-btn dck-btn--gold dck-btn--block" id="dck-live-gcal" href="#" target="_blank" rel="noopener"><?php esc_html_e( 'Add to Google Calendar', 'dck-elite' ); ?></a>
						<button type="button" class="dck-btn dck-btn--ghost dck-btn--block" id="dck-live-ics"><?php esc_html_e( 'Apple / Outlook calendar', 'dck-elite' ); ?></button>
					</div>
				</div>
				<div class="dck-tcard">
					<p class="dck-tcard__k"><?php esc_html_e( 'Sunday programme', 'dck-elite' ); ?></p>
					<ul class="dck-tprog">
						<?php foreach ( dck_elite_service_times() as $s ) : ?>
							<li><span><?php echo esc_html( $s['name'] ); ?></span><strong><?php echo esc_html( $s['time'] ); ?></strong></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</aside>
		</div>
	</div>
</section>

<section class="dck-section dck-join">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Join From Anywhere', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Choose How You Worship With Us', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-join__grid">
			<?php if ( $yt_ch ) : ?>
			<a class="dck-jcard dck-reveal" href="<?php echo esc_url( $yt_ch ); ?>" target="_blank" rel="noopener">
				<span class="dck-jcard__ic dck-jcard__ic--yt" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12 31 31 0 0 0 1 16.8a3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8zM9.8 15.1V8.9L15.6 12z"/></svg></span>
				<h3>YouTube</h3><p><?php esc_html_e( 'Subscribe and tap the bell to be notified when we go live.', 'dck-elite' ); ?></p><span class="dck-jcard__go"><?php esc_html_e( 'Subscribe', 'dck-elite' ); ?> &rarr;</span>
			</a>
			<?php endif; ?>
			<?php if ( $fb_url ) : ?>
			<a class="dck-jcard dck-reveal" href="<?php echo esc_url( $fb_url ); ?>" target="_blank" rel="noopener">
				<span class="dck-jcard__ic dck-jcard__ic--fb" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M14 8V6c0-.9.6-1 1-1h3V1h-4c-3.3 0-4 2.4-4 4v3H7v4h3v11h4V12h3.3l.7-4z"/></svg></span>
				<h3>Facebook</h3><p><?php esc_html_e( 'Follow our page to watch, comment and share the service.', 'dck-elite' ); ?></p><span class="dck-jcard__go"><?php esc_html_e( 'Follow', 'dck-elite' ); ?> &rarr;</span>
			</a>
			<?php endif; ?>
			<a class="dck-jcard dck-reveal" href="#watch">
				<span class="dck-jcard__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg></span>
				<h3><?php esc_html_e( 'Right here', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'Bookmark this page. The service plays above automatically when we are live.', 'dck-elite' ); ?></p><span class="dck-jcard__go"><?php esc_html_e( 'Back to the player', 'dck-elite' ); ?> &uarr;</span>
			</a>
			<a class="dck-jcard dck-jcard--gold dck-reveal" href="<?php echo esc_url( dck_home_val( 'dck_directions_url', 'https://share.google/LJmcXS6D5Nq1aF2R5' ) ); ?>" target="_blank" rel="noopener">
				<span class="dck-jcard__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
				<h3><?php esc_html_e( 'In person', 'dck-elite' ); ?></h3><p><?php echo esc_html( dck_church_address() ); ?></p><span class="dck-jcard__go"><?php esc_html_e( 'Get directions', 'dck-elite' ); ?> &rarr;</span>
			</a>
		</div>
	</div>
</section>

<section class="dck-section dck-past">
	<div class="dck-container">
		<div class="dck-past__head">
			<div>
				<p class="dck-eyebrow"><?php esc_html_e( 'Missed a Service?', 'dck-elite' ); ?></p>
				<h2><?php esc_html_e( 'Catch Up Anytime', 'dck-elite' ); ?></h2>
			</div>
			<?php if ( $playlist ) : ?>
				<div class="dck-seg" role="tablist" aria-label="<?php esc_attr_e( 'Past services', 'dck-elite' ); ?>">
					<button type="button" role="tab" class="is-active" aria-selected="true" aria-controls="dck-past-msgs" id="dck-tab-msgs"><?php esc_html_e( 'Recent messages', 'dck-elite' ); ?></button>
					<button type="button" role="tab" aria-selected="false" aria-controls="dck-past-full" id="dck-tab-full"><?php esc_html_e( 'Full services', 'dck-elite' ); ?></button>
				</div>
			<?php endif; ?>
		</div>
		<div id="dck-past-msgs" role="tabpanel">
			<div class="dck-sermons-grid">
				<?php
				$recent = new WP_Query( array( 'post_type' => 'dck_sermon', 'posts_per_page' => 6, 'no_found_rows' => true ) );
				while ( $recent->have_posts() ) :
					$recent->the_post();
					$rimg = function_exists( 'dck_sermon_image_url' ) ? dck_sermon_image_url( get_the_ID() ) : '';
					$rsp  = function_exists( 'dck_sermon_first_term' ) ? dck_sermon_first_term( get_the_ID(), 'dck_sermon_speaker' ) : null;
					?>
					<article class="dck-sermon-card dck-reveal">
						<a class="dck-sermon-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( $rimg ) : ?><img src="<?php echo esc_url( $rimg ); ?>" alt="" loading="lazy" decoding="async" width="800" height="450"><?php endif; ?>
							<span class="dck-sermon-card__play"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
						</a>
						<div class="dck-sermon-card__body">
							<div class="dck-sermon-card__meta"><?php echo esc_html( get_the_date() ); ?><?php echo $rsp ? ' &middot; ' . esc_html( $rsp->name ) : ''; ?></div>
							<h3 class="dck-sermon-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="dck-sermon-card__exc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<div class="dck-center" style="margin-top:30px">
				<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( get_post_type_archive_link( 'dck_sermon' ) ); ?>"><?php esc_html_e( 'Browse the Sermon Library', 'dck-elite' ); ?></a>
			</div>
		</div>
		<?php if ( $playlist && function_exists( 'dck_elite_video_embed' ) ) : ?>
			<div id="dck-past-full" role="tabpanel" hidden>
				<div class="dck-past__playlist"><?php echo dck_elite_video_embed( $playlist, 'Past Services' ); // phpcs:ignore ?></div>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="dck-section dck-online">
	<div class="dck-container">
		<div class="dck-sermons-cta dck-reveal">
			<div>
				<h3><?php esc_html_e( 'Watching online? We would love to know you.', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Whether you are in Nairobi or across the world, say hello so we can pray for you and stay in touch.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-sermons-cta__btns">
				<?php $dck_wa = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : ''; ?>
				<?php if ( $dck_wa ) : ?>
					<a class="dck-btn dck-btn--gold" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello DCK, I watched the service online today from', 'dck-elite' ) . ' ' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Say hello on WhatsApp', 'dck-elite' ); ?></a>
				<?php endif; ?>
				<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'dck-elite' ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
