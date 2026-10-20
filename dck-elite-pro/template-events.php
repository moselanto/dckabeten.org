<?php
/**
 * Template Name: DCK Events
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$today = current_time( 'Y-m-d' );

$notices = new WP_Query( array(
	'post_type'      => 'dck_event',
	'posts_per_page' => 6,
	'meta_key'       => '_dck_event_notice',
	'meta_value'     => '1',
) );

$recurring = new WP_Query( array(
	'post_type'      => 'dck_event',
	'posts_per_page' => 100,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
	'meta_key'       => '_dck_event_recurring',
	'meta_value'     => '1',
) );

$upcoming = new WP_Query( array(
	'post_type'      => 'dck_event',
	'posts_per_page' => 100,
	'meta_key'       => '_dck_event_date',
	'orderby'        => 'meta_value',
	'order'          => 'ASC',
	'meta_query'     => array(
		'relation' => 'AND',
		array(
			'key'     => '_dck_event_date',
			'value'   => $today,
			'compare' => '>=',
			'type'    => 'DATE',
		),
		array(
			'relation' => 'OR',
			array( 'key' => '_dck_event_recurring', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_dck_event_recurring', 'value' => '1', 'compare' => '!=' ),
		),
	),
) );

if ( ! function_exists( 'dck_event_gcal' ) ) {
	function dck_event_gcal( $pid, $title, $loc ) {
		$ymd = str_replace( '-', '', (string) get_post_meta( $pid, '_dck_event_date', true ) );
		if ( ! preg_match( '/^\d{8}$/', $ymd ) ) {
			return '';
		}
		$st = preg_replace( '/[^0-9]/', '', (string) get_post_meta( $pid, '_dck_event_start', true ) );
		$en = preg_replace( '/[^0-9]/', '', (string) get_post_meta( $pid, '_dck_event_end', true ) );
		if ( 4 === strlen( $st ) ) {
			$dates = $ymd . 'T' . $st . '00/' . $ymd . 'T' . ( 4 === strlen( $en ) ? $en : $st ) . '00';
		} else {
			$dates = $ymd . '/' . gmdate( 'Ymd', strtotime( $ymd . ' +1 day' ) );
		}
		return add_query_arg(
			array(
				'action'   => 'TEMPLATE',
				'text'     => rawurlencode( $title ),
				'dates'    => $dates,
				'ctz'      => 'Africa/Nairobi',
				'location' => rawurlencode( $loc ? $loc : dck_church_address() ),
				'details'  => rawurlencode( get_permalink( $pid ) ),
			),
			'https://calendar.google.com/calendar/render'
		);
	}
}
if ( ! function_exists( 'dck_event_ts' ) ) {
	/** Event start as a UTC millisecond timestamp (Nairobi time). */
	function dck_event_ts( $pid ) {
		$d = (string) get_post_meta( $pid, '_dck_event_date', true );
		$t = (string) get_post_meta( $pid, '_dck_event_start', true );
		if ( ! $d ) {
			return 0;
		}
		try {
			$dt = new DateTime( $d . ' ' . ( $t ? $t : '00:00' ), new DateTimeZone( 'Africa/Nairobi' ) );
			return $dt->getTimestamp() * 1000;
		} catch ( Exception $e ) {
			return 0;
		}
	}
}

dck_photo_hero(
	dck_theme_image( 'dck_events_hero_img', 'assets/images/gallery/g-115016.webp' ),
	__( 'What Is Happening', 'dck-elite' ),
	dck_home_val( 'dck_events_title', __( 'Events & Gatherings', 'dck-elite' ) ),
	dck_home_val( 'dck_events_lead', __( 'From our weekly services to Keshas, conferences and outreaches, there is always a place for you at DCK.', 'dck-elite' ) )
);
?>

<nav class="dck-subnav" aria-label="<?php esc_attr_e( 'On this page', 'dck-elite' ); ?>">
	<div class="dck-container">
		<a href="#upcoming"><?php esc_html_e( 'Upcoming', 'dck-elite' ); ?></a>
		<a href="#weekly"><?php esc_html_e( 'Weekly rhythm', 'dck-elite' ); ?></a>
		<a href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch live', 'dck-elite' ); ?></a>
		<span class="dck-subnav__note"><?php esc_html_e( 'All times EAT (UTC+3)', 'dck-elite' ); ?></span>
	</div>
</nav>

<?php if ( $notices->have_posts() ) : ?>
<section class="dck-ribbon">
	<div class="dck-container">
		<?php
		while ( $notices->have_posts() ) :
			$notices->the_post();
			$nd = dck_event_format_date( get_post_meta( get_the_ID(), '_dck_event_date', true ) );
			?>
			<a class="dck-ribbon__item" href="<?php the_permalink(); ?>">
				<span class="dck-ribbon__tag"><?php esc_html_e( 'Notice', 'dck-elite' ); ?></span>
				<strong><?php the_title(); ?></strong>
				<?php if ( $nd['full'] ) : ?><span class="dck-ribbon__date"><?php echo esc_html( $nd['full'] ); ?></span><?php endif; ?>
				<span aria-hidden="true">&rarr;</span>
			</a>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
<?php endif; ?>

<section class="dck-section dck-upc" id="upcoming">
	<div class="dck-container">
		<div class="dck-weeksec__head">
			<div>
				<p class="dck-eyebrow"><?php esc_html_e( 'On The Calendar', 'dck-elite' ); ?></p>
				<h2><?php esc_html_e( 'Upcoming Special Events', 'dck-elite' ); ?></h2>
			</div>
		</div>
		<?php if ( $upcoming->have_posts() ) : ?>
			<?php
			$dck_i = 0;
			while ( $upcoming->have_posts() ) :
				$upcoming->the_post();
				$pid  = get_the_ID();
				$d    = dck_event_format_date( get_post_meta( $pid, '_dck_event_date', true ) );
				$st   = dck_event_time_label( get_post_meta( $pid, '_dck_event_start', true ) );
				$en   = dck_event_time_label( get_post_meta( $pid, '_dck_event_end', true ) );
				$loc  = get_post_meta( $pid, '_dck_event_location', true );
				$time = ( $st && $en ) ? $st . ' - ' . $en : $st;
				$gcal = dck_event_gcal( $pid, get_the_title(), $loc );
				$img  = has_post_thumbnail() ? get_the_post_thumbnail_url( $pid, 'large' ) : '';
				if ( 0 === $dck_i ) :
					$ts = dck_event_ts( $pid );
					?>
					<article class="dck-efeat dck-reveal">
						<div class="dck-efeat__media"<?php echo $img ? ' style="background-image:url(' . esc_url( $img ) . ')"' : ''; ?>>
							<div class="dck-efeat__date"><span><?php echo esc_html( $d['mon'] ); ?></span><strong><?php echo esc_html( $d['day'] ); ?></strong></div>
						</div>
						<div class="dck-efeat__body">
							<span class="dck-sfeature__tag"><?php esc_html_e( 'Next up', 'dck-elite' ); ?></span>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<ul class="dck-emeta">
								<?php if ( $d['full'] ) : ?><li><?php echo esc_html( $d['full'] ); ?></li><?php endif; ?>
								<?php if ( $time ) : ?><li><?php echo esc_html( $time ); ?></li><?php endif; ?>
								<?php if ( $loc ) : ?><li><?php echo esc_html( $loc ); ?></li><?php endif; ?>
							</ul>
							<?php if ( has_excerpt() ) : ?><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 34 ) ); ?></p><?php endif; ?>
							<?php if ( $ts > time() * 1000 ) : ?>
								<div class="dck-cd dck-cd--light" data-dck-until="<?php echo esc_attr( $ts ); ?>" data-d="<?php esc_attr_e( 'days', 'dck-elite' ); ?>" data-h="<?php esc_attr_e( 'hrs', 'dck-elite' ); ?>" data-m="<?php esc_attr_e( 'min', 'dck-elite' ); ?>" data-s="<?php esc_attr_e( 'sec', 'dck-elite' ); ?>" data-done="<?php esc_attr_e( 'Happening now', 'dck-elite' ); ?>"></div>
							<?php endif; ?>
							<div class="dck-efeat__btns">
								<a class="dck-btn dck-btn--gold" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Event details', 'dck-elite' ); ?></a>
								<?php if ( $gcal ) : ?><a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( $gcal ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Add to calendar', 'dck-elite' ); ?></a><?php endif; ?>
							</div>
						</div>
					</article>
					<div class="dck-elist">
				<?php else : ?>
					<article class="dck-erow dck-reveal">
						<div class="dck-erow__date"><span><?php echo esc_html( $d['mon'] ); ?></span><strong><?php echo esc_html( $d['day'] ); ?></strong></div>
						<div class="dck-erow__body">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<ul class="dck-emeta">
								<?php if ( $time ) : ?><li><?php echo esc_html( $time ); ?></li><?php endif; ?>
								<?php if ( $loc ) : ?><li><?php echo esc_html( $loc ); ?></li><?php endif; ?>
							</ul>
						</div>
						<div class="dck-erow__act">
							<?php if ( $gcal ) : ?><a class="dck-tool" href="<?php echo esc_url( $gcal ); ?>" target="_blank" rel="noopener"><?php esc_html_e( '+ Calendar', 'dck-elite' ); ?></a><?php endif; ?>
							<a class="dck-tool" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( get_the_title() . ' - ' . get_permalink() ); ?>"><?php esc_html_e( 'Share', 'dck-elite' ); ?></a>
							<a class="dck-erow__go" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">&rarr;</a>
						</div>
					</article>
				<?php endif; ?>
				<?php
				$dck_i++;
			endwhile;
			wp_reset_postdata();
			?>
			</div>
		<?php else : ?>
			<div class="dck-eempty dck-reveal">
				<span class="dck-eempty__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></span>
				<h3><?php esc_html_e( 'No special events announced yet', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Conferences, Keshas and outreaches will appear here as soon as they are announced. Meanwhile, our weekly gatherings below are always open to you.', 'dck-elite' ); ?></p>
				<div class="dck-efeat__btns" style="justify-content:center">
					<a class="dck-btn dck-btn--primary" href="#weekly"><?php esc_html_e( 'See the weekly rhythm', 'dck-elite' ); ?></a>
					<?php $dck_wa = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : ''; ?>
					<?php if ( $dck_wa ) : ?><a class="dck-btn dck-btn--outline" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello DCK, please let me know about upcoming events.', 'dck-elite' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get event updates on WhatsApp', 'dck-elite' ); ?></a><?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php if ( $recurring->have_posts() ) : ?>
<section class="dck-section dck-weeksec" id="weekly">
	<div class="dck-container">
		<div class="dck-weeksec__head">
			<div>
				<p class="dck-eyebrow"><?php esc_html_e( 'Our Weekly Rhythm', 'dck-elite' ); ?></p>
				<h2><?php esc_html_e( 'Regular Services & Gatherings', 'dck-elite' ); ?></h2>
				<p class="dck-muted"><?php esc_html_e( 'Come as you are. Our doors are open through the week for prayer, the Word, worship and fellowship.', 'dck-elite' ); ?></p>
			</div>
			<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'services' ) ); ?>"><?php esc_html_e( 'Service details', 'dck-elite' ); ?></a>
		</div>
		<?php
		$dck_items = array();
		while ( $recurring->have_posts() ) :
			$recurring->the_post();
			$pid = get_the_ID();
			$dl  = (string) get_post_meta( $pid, '_dck_event_daylabel', true );
			$st  = dck_event_time_label( get_post_meta( $pid, '_dck_event_start', true ) );
			$en  = dck_event_time_label( get_post_meta( $pid, '_dck_event_end', true ) );
			$t   = ( $st && $en ) ? $st . ' - ' . $en : ( $st ? sprintf( __( 'From %s', 'dck-elite' ), $st ) : __( 'Overnight', 'dck-elite' ) );
			$days = dck_label_days( $dl );
			$note = preg_match( '/\d(st|nd|rd|th)|month/i', $dl ) ? $dl : '';
			$dck_items[] = array( 'days' => $days ? $days : array( 0 ), 'title' => get_the_title(), 'time' => $t, 'note' => $note );
		endwhile;
		wp_reset_postdata();
		dck_week_calendar( $dck_items );
		?>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
