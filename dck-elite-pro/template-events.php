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
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'What Is Happening', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Events &amp; Gatherings', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'From our weekly services to Keshas, conferences and outreaches - there is always a place for you at DCK.', 'dck-elite' ); ?></p>
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
<section class="dck-section dck-rhythm">
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

<section class="dck-section" style="background:var(--dck-warm)">
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
							<a class="dck-event__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Details', 'dck-elite' ); ?></a>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="dck-lead dck-center" style="margin-top:26px"><?php esc_html_e( 'No special events are scheduled right now - but our weekly services above are always open to you. Watch this space for conferences and Keshas.', 'dck-elite' ); ?></p>
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
