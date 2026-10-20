<?php
/**
 * Template Name: DCK Services
 *
 * Sunday times come from DCK Church > Service Times. Weekly schedule and
 * "What to expect" are editable in DCK Church > Page Text.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_sd    = dck_services_defaults();
$dck_sun   = dck_elite_service_times();
$dck_desc  = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_services_sunday_desc', $dck_sd['sunday_desc'] ) ) ) ) );
$dck_week  = dck_lines( 'dck_services_week', $dck_sd['week'], 3 );
$dck_exp   = dck_lines( 'dck_services_expect', $dck_sd['expect'], 2 );
$dck_dir   = function_exists( 'dck_home_directions' ) ? dck_home_directions() : dck_home_val( 'dck_directions_url', '' );
$dck_icons = array(
	'<path d="M12 3v6l4 2"/><circle cx="12" cy="12" r="9"/>',
	'<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M19 3v18"/>',
	'<path d="M12 3l2.5 5 5.5.5-4 3.8 1.2 5.4L12 20l-4.2 2.7 1.2-5.4-4-3.8 5.5-.5z"/>',
	'<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
);
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Gather With Us', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_services_title', __( 'Our Services', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_services_lead', __( 'Consistent spiritual nourishment and fellowship every week, on Sunday and throughout the week.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<a class="dck-pill dck-pill--link" href="#sunday"><?php esc_html_e( 'Sunday', 'dck-elite' ); ?></a>
			<a class="dck-pill dck-pill--link" href="#midweek"><?php esc_html_e( 'Midweek', 'dck-elite' ); ?></a>
			<a class="dck-pill dck-pill--link" href="#first-visit"><?php esc_html_e( 'First visit', 'dck-elite' ); ?></a>
			<a class="dck-pill dck-pill--link" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch online', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>

<section class="dck-section dck-sunday3" id="sunday">
	<div class="dck-container">
		<div class="dck-sunday3__panel dck-reveal">
			<div class="dck-sunday3__head">
				<p class="dck-eyebrow"><?php esc_html_e( 'Every Sunday', 'dck-elite' ); ?></p>
				<h2><?php esc_html_e( "The Lord's Day at DCK", 'dck-elite' ); ?></h2>
				<p class="dck-muted"><?php esc_html_e( 'Our gatherings flow into one another, from intercession to the Word to a glorious celebration of worship.', 'dck-elite' ); ?></p>
				<p class="dck-visit__tz"><?php esc_html_e( 'All times are East Africa Time (EAT, UTC+3).', 'dck-elite' ); ?></p>
				<div class="dck-visit__btns">
					<?php if ( $dck_dir ) : ?><a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get Directions', 'dck-elite' ); ?></a><?php endif; ?>
					<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_watch_live', 'Watch Live' ) ); ?></a>
				</div>
			</div>
			<ol class="dck-agenda">
				<?php foreach ( $dck_sun as $i => $s ) : ?>
					<li class="dck-agenda__item">
						<span class="dck-agenda__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><?php echo $dck_icons[ $i % 4 ]; // phpcs:ignore ?></svg></span>
						<div>
							<span class="dck-agenda__time"><?php echo esc_html( $s['time'] ); ?></span>
							<h3><?php echo esc_html( $s['name'] ); ?></h3>
							<?php if ( ! empty( $dck_desc[ $i ] ) ) : ?><p><?php echo esc_html( $dck_desc[ $i ] ); ?></p><?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>

<?php if ( $dck_week ) : ?>
<section class="dck-section dck-midweek" id="midweek">
	<div class="dck-container dck-grid-2 dck-midweek__grid">
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'During The Week', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Midweek and Home Church', 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php echo esc_html( dck_home_val( 'dck_services_week_lead', __( 'There is a place to meet with God every few days, in the church and in our home churches.', 'dck-elite' ) ) ); ?></p>
			<div class="dck-svc-band__media">
				<img src="<?php echo esc_url( dck_theme_image( 'dck_services_image', 'assets/images/services-worship.jpg' ) ); ?>" alt="<?php esc_attr_e( 'An open Bible in warm morning light', 'dck-elite' ); ?>" loading="lazy" decoding="async">
			</div>
		</div>
		<ul class="dck-week2 dck-reveal">
			<?php foreach ( $dck_week as $w ) : ?>
				<li>
					<span class="dck-week2__day"><?php echo esc_html( $w[0] ); ?></span>
					<span class="dck-week2__act"><?php echo esc_html( $w[2] ); ?></span>
					<span class="dck-week2__time"><?php echo esc_html( $w[1] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php if ( $dck_exp ) : ?>
<section class="dck-section dck-expect" id="first-visit">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Your First Visit', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'What To Expect', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-gp">
			<?php foreach ( $dck_exp as $i => $e ) : ?>
				<div class="dck-gp__item dck-reveal">
					<span class="dck-gp__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<h3><?php echo esc_html( $e[0] ); ?></h3>
					<p><?php echo esc_html( $e[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="dck-section">
	<div class="dck-container">
		<div class="dck-sermons-cta dck-reveal">
			<div>
				<h3><?php esc_html_e( 'Cannot join in person?', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Our Glorious Service streams live every Sunday. Join the family from anywhere in the world.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-sermons-cta__btns">
				<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_watch_live', 'Watch Live' ) ); ?></a>
				<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'dck-elite' ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
