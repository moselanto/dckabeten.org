<?php
/**
 * Template Name: DCK Services
 *
 * Sunday times: DCK Church > Service Times. Weekly schedule, descriptions,
 * photos and "What to expect": DCK Church > Services & More.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_sd   = dck_services_defaults();
$dck_sun  = dck_elite_service_times();
$dck_desc = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_services_sunday_desc', $dck_sd['sunday_desc'] ) ) ) ) );
$dck_week = dck_lines( 'dck_services_week', $dck_sd['week'], 3 );
$dck_exp  = dck_lines( 'dck_services_expect', $dck_sd['expect'], 2 );
$dck_dir  = function_exists( 'dck_home_directions' ) ? dck_home_directions() : dck_home_val( 'dck_directions_url', '' );
$dck_base = get_template_directory_uri() . '/assets/images/';
$dck_imgs = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_services_images', '' ) ) ) ) );
if ( ! $dck_imgs ) {
	$dck_imgs = array( $dck_base . 'home/church-slide-4.webp', $dck_base . 'blog/study.webp', $dck_base . 'home/joy.webp', $dck_base . 'home/church-slide-3.webp' );
}
$dck_main = isset( $dck_sun[0] ) ? $dck_sun[0] : null;
foreach ( $dck_sun as $s ) {
	if ( false !== stripos( $s['name'], 'glorious' ) ) {
		$dck_main = $s;
	}
}

/* Hero side card: next Sunday gathering with live countdown. */
ob_start();
?>
<div class="dck-nextcard">
	<p class="dck-nextcard__k"><?php esc_html_e( 'Next Sunday gathering', 'dck-elite' ); ?></p>
	<p class="dck-nextcard__t"><?php echo esc_html( $dck_main ? $dck_main['name'] : __( 'Sunday Service', 'dck-elite' ) ); ?></p>
	<p class="dck-nextcard__d"><?php echo esc_html( date_i18n( 'l, j F', (int) ( dck_elite_next_service_ms() / 1000 ) + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . ( $dck_main ? ' · ' . $dck_main['time'] : '' ) ); ?></p>
	<div class="dck-cd dck-cd--light" data-dck-until="<?php echo esc_attr( dck_elite_next_service_ms() ); ?>" data-d="<?php esc_attr_e( 'days', 'dck-elite' ); ?>" data-h="<?php esc_attr_e( 'hrs', 'dck-elite' ); ?>" data-m="<?php esc_attr_e( 'min', 'dck-elite' ); ?>" data-s="<?php esc_attr_e( 'sec', 'dck-elite' ); ?>" data-done="<?php esc_attr_e( 'Happening now', 'dck-elite' ); ?>"></div>
	<div class="dck-nextcard__btns">
		<?php if ( $dck_dir ) : ?><a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'dck-elite' ); ?></a><?php endif; ?>
		<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch online', 'dck-elite' ); ?></a>
	</div>
	<p class="dck-nextcard__tz"><?php esc_html_e( 'East Africa Time (EAT, UTC+3)', 'dck-elite' ); ?></p>
</div>
<?php
$dck_aside = ob_get_clean();
dck_photo_hero(
	dck_theme_image( 'dck_services_hero_img', 'assets/images/home/church-slide-3.webp' ),
	__( 'Gather With Us', 'dck-elite' ),
	dck_home_val( 'dck_services_title', __( 'Our Services', 'dck-elite' ) ),
	dck_home_val( 'dck_services_lead', __( 'Consistent spiritual nourishment and fellowship every week, on Sunday and throughout the week. Come as you are, you belong here.', 'dck-elite' ) ),
	$dck_aside
);
?>

<nav class="dck-subnav" aria-label="<?php esc_attr_e( 'On this page', 'dck-elite' ); ?>">
	<div class="dck-container">
		<a href="#sunday"><?php esc_html_e( 'Sunday', 'dck-elite' ); ?></a>
		<a href="#week"><?php esc_html_e( 'This week', 'dck-elite' ); ?></a>
		<a href="#first-visit"><?php esc_html_e( 'First visit', 'dck-elite' ); ?></a>
		<a href="#visit"><?php esc_html_e( 'Find us', 'dck-elite' ); ?></a>
	</div>
</nav>

<section class="dck-section dck-sun4" id="sunday">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Every Sunday', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( "The Lord's Day at DCK", 'dck-elite' ); ?></h2>
			<p class="dck-lead dck-center"><?php esc_html_e( 'Our gatherings flow into one another, from intercession to the Word to a glorious celebration of worship.', 'dck-elite' ); ?></p>
		</div>
		<div class="dck-sun4__grid">
			<?php foreach ( $dck_sun as $i => $s ) : ?>
				<article class="dck-scard2 dck-reveal<?php echo ( $dck_main && $s['name'] === $dck_main['name'] ) ? ' is-main' : ''; ?>">
					<div class="dck-scard2__img" style="background-image:url(<?php echo esc_url( $dck_imgs[ $i % count( $dck_imgs ) ] ); ?>)">
						<span class="dck-scard2__step"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<?php if ( $dck_main && $s['name'] === $dck_main['name'] ) : ?><span class="dck-scard2__live"><?php esc_html_e( 'Streamed live', 'dck-elite' ); ?></span><?php endif; ?>
					</div>
					<div class="dck-scard2__body">
						<span class="dck-scard2__time"><?php echo esc_html( $s['time'] ); ?></span>
						<h3><?php echo esc_html( $s['name'] ); ?></h3>
						<?php if ( ! empty( $dck_desc[ $i ] ) ) : ?><p><?php echo esc_html( $dck_desc[ $i ] ); ?></p><?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="dck-section dck-weeksec" id="week">
	<div class="dck-container">
		<div class="dck-weeksec__head">
			<div>
				<p class="dck-eyebrow"><?php esc_html_e( 'During The Week', 'dck-elite' ); ?></p>
				<h2><?php esc_html_e( 'Your Week With DCK', 'dck-elite' ); ?></h2>
				<p class="dck-muted"><?php echo esc_html( dck_home_val( 'dck_services_week_lead', __( 'There is a place to meet with God every few days, in the church and in our home churches.', 'dck-elite' ) ) ); ?></p>
			</div>
			<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'events' ) ); ?>"><?php esc_html_e( 'See all events', 'dck-elite' ); ?></a>
		</div>
		<?php
		$dck_items = array();
		foreach ( $dck_week as $w ) {
			$dck_items[] = array( 'days' => dck_label_days( $w[0] ), 'title' => $w[2], 'time' => $w[1], 'note' => ( count( dck_label_days( $w[0] ) ) ? '' : $w[0] ) );
		}
		foreach ( $dck_sun as $s ) {
			if ( preg_match( '/week|month|kesha/i', $s['time'] . ' ' . $s['name'] ) ) {
				continue;
			}
			$dck_items[] = array( 'days' => array( 0 ), 'title' => $s['name'], 'time' => $s['time'] );
		}
		dck_week_calendar( $dck_items );
		?>
		<p class="dck-weeksec__note"><?php esc_html_e( 'Keshas (overnight prayer) are held on the 1st and 3rd week of every month.', 'dck-elite' ); ?></p>
	</div>
</section>

<?php if ( $dck_exp ) : ?>
<section class="dck-section dck-expect2" id="first-visit">
	<div class="dck-container dck-expect2__grid">
		<div class="dck-expect2__intro dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'Your First Visit', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'What To Expect', 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'Visiting a church for the first time can feel like a big step. Here is what your Sunday with us looks like.', 'dck-elite' ); ?></p>
			<img src="<?php echo esc_url( dck_theme_image( 'dck_services_image', 'assets/images/services-worship.jpg' ) ); ?>" alt="" loading="lazy" decoding="async">
		</div>
		<ol class="dck-expect2__list">
			<?php foreach ( $dck_exp as $e ) : ?>
				<li class="dck-reveal"><strong><?php echo esc_html( $e[0] ); ?></strong><span><?php echo esc_html( $e[1] ); ?></span></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
<?php endif; ?>

<section class="dck-section dck-findus" id="visit">
	<div class="dck-container dck-findus__card">
		<div class="dck-findus__map"><iframe title="<?php esc_attr_e( 'Map to the church', 'dck-elite' ); ?>" src="https://www.google.com/maps?q=<?php echo rawurlencode( dck_home_val( 'dck_contact_map_query', 'Deliverance Church Kabete N Market, Uthiru, Nairobi, Kenya' ) ); ?>&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
		<div class="dck-findus__body">
			<p class="dck-eyebrow"><?php esc_html_e( 'Find Us', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Plan Your Visit', 'dck-elite' ); ?></h2>
			<p class="dck-findus__addr"><?php echo esc_html( dck_church_address() ); ?></p>
			<div class="dck-visit__btns">
				<?php if ( $dck_dir ) : ?><a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get Directions', 'dck-elite' ); ?></a><?php endif; ?>
				<a class="dck-btn dck-btn--outline" href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( dck_phone_display() ); ?></a>
			</div>
			<p class="dck-findus__alt"><?php esc_html_e( 'Cannot join in person? Our Glorious Service streams live every Sunday.', 'dck-elite' ); ?> <a href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch live', 'dck-elite' ); ?> &rarr;</a></p>
		</div>
	</div>
</section>

<?php
get_footer();
