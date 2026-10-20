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
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'What Is Happening', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_events_title', __( 'Events & Gatherings', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_events_lead', __( 'From our weekly services to Keshas, conferences and outreaches, there is always a place for you at DCK.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<a class="dck-pill dck-pill--link" href="#upcoming"><?php esc_html_e( 'Upcoming', 'dck-elite' ); ?></a>
			<a class="dck-pill dck-pill--link" href="#weekly"><?php esc_html_e( 'Weekly rhythm', 'dck-elite' ); ?></a>
			<span class="dck-pill"><?php esc_html_e( 'Times in EAT (UTC+3)', 'dck-elite' ); ?></span>
		</div>
	</div>
</section>

<?php if ( $notices->have_posts() ) : ?>
<section class="dck-section dck-notice-wrap" style="background:var(--dck-blue-100)">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Please Note', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Notices &amp; Announcements', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-notices">
			<?php while ( $notices->have_posts() ) : $notices->the_post(); $nid = get_the_ID(); $nd = dck_event_format_date( get_post_meta( $nid, '_dck_event_date', true ) ); ?>
				<div class="dck-notice dck-reveal">
					<span class="dck-notice__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3a6 6 0 0 0-6 6c0 4-2 5-2 5h16s-2-1-2-5a6 6 0 0 0-6-6z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg></span>
					<div>
						<h3 class="dck-notice__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $nd['full'] ) : ?><p class="dck-notice__date"><?php echo esc_html( $nd['full'] ); ?></p><?php endif; ?>
						<p class="dck-notice__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
					</div>
				</div>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $recurring->have_posts() ) : ?>
<section class="dck-section dck-rhythm" id="weekly">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Our Weekly Rhythm', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Regular Services &amp; Gatherings', 'dck-elite' ); ?></h2>
			<p class="dck-lead dck-center"><?php esc_html_e( 'Come as you are. Our doors are open through the week for prayer, the Word, worship and fellowship.', 'dck-elite' ); ?></p>
		</div>
		<?php
		$groups = array();
		$order  = array();
		while ( $recurring->have_posts() ) :
			$recurring->the_post();
			$pid = get_the_ID();
			$dl  = get_post_meta( $pid, '_dck_event_daylabel', true );
			if ( '' === $dl ) {
				$dl = 'Weekly';
			}
			$st = dck_event_time_label( get_post_meta( $pid, '_dck_event_start', true ) );
			$en = dck_event_time_label( get_post_meta( $pid, '_dck_event_end', true ) );
			if ( $st && $en ) {
				$t = $st . ' - ' . $en;
			} elseif ( $st ) {
				$t = 'From ' . $st;
			} else {
				$t = 'Overnight';
			}
			if ( ! isset( $groups[ $dl ] ) ) {
				$groups[ $dl ] = array();
				$order[]       = $dl;
			}
			$groups[ $dl ][] = array(
				'title'   => get_the_title(),
				'time'    => $t,
				'excerpt' => get_the_excerpt(),
			);
		endwhile;
		wp_reset_postdata();
		?>
		<div class="dck-rhythm-grid">
			<?php foreach ( $order as $dl ) : ?>
				<div class="dck-rday dck-reveal">
					<p class="dck-rday__day"><?php echo esc_html( $dl ); ?></p>
					<ul class="dck-rday__list">
						<?php foreach ( $groups[ $dl ] as $it ) : ?>
							<li class="dck-rday__item">
								<span class="dck-rday__name"><?php echo esc_html( $it['title'] ); ?></span>
								<?php if ( $it['time'] ) : ?><span class="dck-rday__time"><?php echo esc_html( $it['time'] ); ?></span><?php endif; ?>
								<?php if ( $it['excerpt'] ) : ?><span class="dck-rday__desc"><?php echo esc_html( $it['excerpt'] ); ?></span><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="dck-section dck-upcoming" id="upcoming">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'On The Calendar', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Upcoming Special Events', 'dck-elite' ); ?></h2>
		</div>
		<?php if ( $upcoming->have_posts() ) : ?>
			<div class="dck-events">
				<?php
				while ( $upcoming->have_posts() ) :
					$upcoming->the_post();
					$pid  = get_the_ID();
					$d    = dck_event_format_date( get_post_meta( $pid, '_dck_event_date', true ) );
					$st   = dck_event_time_label( get_post_meta( $pid, '_dck_event_start', true ) );
					$en   = dck_event_time_label( get_post_meta( $pid, '_dck_event_end', true ) );
					$loc  = get_post_meta( $pid, '_dck_event_location', true );
					$isn  = get_post_meta( $pid, '_dck_event_notice', true );
					$time = $st;
					if ( $st && $en ) {
						$time = $st . ' - ' . $en;
					}
					?>
					<article class="dck-event dck-reveal">
						<div class="dck-event__date">
							<span class="dck-event__mon"><?php echo esc_html( $d['mon'] ); ?></span>
							<span class="dck-event__day"><?php echo esc_html( $d['day'] ); ?></span>
						</div>
						<div class="dck-event__body">
							<?php if ( $isn ) : ?><span class="dck-event__flag"><?php esc_html_e( 'Notice', 'dck-elite' ); ?></span><?php endif; ?>
							<h3 class="dck-event__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="dck-event__meta">
								<?php if ( $d['full'] ) : ?><span><?php echo esc_html( $d['full'] ); ?></span><?php endif; ?>
								<?php if ( $time ) : ?><span><?php echo esc_html( $time ); ?></span><?php endif; ?>
								<?php if ( $loc ) : ?><span><?php echo esc_html( $loc ); ?></span><?php endif; ?>
							</p>
							<?php if ( has_excerpt() ) : ?><p class="dck-event__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p><?php endif; ?>
							<div class="dck-event__actions">
								<a class="dck-event__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Details', 'dck-elite' ); ?></a>
								<?php
								$dck_ymd = str_replace( '-', '', (string) get_post_meta( $pid, '_dck_event_date', true ) );
								if ( preg_match( '/^\d{8}$/', $dck_ymd ) ) :
									$dck_s = preg_replace( '/[^0-9]/', '', (string) get_post_meta( $pid, '_dck_event_start', true ) );
									$dck_e = preg_replace( '/[^0-9]/', '', (string) get_post_meta( $pid, '_dck_event_end', true ) );
									if ( 4 === strlen( $dck_s ) ) {
										$dck_dates = $dck_ymd . 'T' . $dck_s . '00/' . $dck_ymd . 'T' . ( 4 === strlen( $dck_e ) ? $dck_e : $dck_s ) . '00';
									} else {
										$dck_next  = gmdate( 'Ymd', strtotime( $dck_ymd . ' +1 day' ) );
										$dck_dates = $dck_ymd . '/' . $dck_next;
									}
									$dck_gcal = add_query_arg(
										array(
											'action'   => 'TEMPLATE',
											'text'     => rawurlencode( get_the_title() ),
											'dates'    => $dck_dates,
											'ctz'      => 'Africa/Nairobi',
											'location' => rawurlencode( $loc ? $loc : dck_church_address() ),
											'details'  => rawurlencode( get_permalink() ),
										),
										'https://calendar.google.com/calendar/render'
									);
									?>
									<a class="dck-tool" href="<?php echo esc_url( $dck_gcal ); ?>" target="_blank" rel="noopener"><?php esc_html_e( '+ Add to calendar', 'dck-elite' ); ?></a>
								<?php endif; ?>
								<a class="dck-tool" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( get_the_title() . ' - ' . get_permalink() ); ?>"><?php esc_html_e( 'Share', 'dck-elite' ); ?></a>
							</div>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="dck-empty" style="margin-top:26px"><h3><?php esc_html_e( 'No special events scheduled yet', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'Our weekly services are always open to you. Watch this space for conferences and Keshas.', 'dck-elite' ); ?></p></div>
			<div class="dck-hero__cta" style="justify-content:center;margin-top:18px">
				<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( dck_elite_page_url( 'services' ) ); ?>"><?php esc_html_e( 'See Service Times', 'dck-elite' ); ?></a>
				<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch Live', 'dck-elite' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
