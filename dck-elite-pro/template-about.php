<?php
/**
 * Template Name: DCK About Us
 *
 * All text is editable in DCK Church > About & Pastors.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_ad    = dck_about_defaults();
$dck_photo = dck_theme_image( 'dck_about_image', 'assets/images/about-front.webp' );
$dck_vals  = dck_lines( 'dck_about_values', $dck_ad['values'], 2 );
$dck_tl    = dck_lines( 'dck_about_timeline', $dck_ad['timeline'], 3 );
$dck_stats = function_exists( 'dck_home_stats' ) ? dck_home_stats() : array();
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( dck_home_val( 'dck_about_eyebrow', __( 'Who We Are', 'dck-elite' ) ) ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_about_title', __( 'About Our Church', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_about_lead', __( 'A Cistern of Hope in the heart of Uthiru, Nairobi, leading every generation into a growing relationship with Christ.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<span class="dck-pill"><?php esc_html_e( 'Est. June 2017', 'dck-elite' ); ?></span>
			<span class="dck-pill"><?php echo esc_html( get_theme_mod( 'dck_seo_locality', 'Uthiru' ) . ', ' . get_theme_mod( 'dck_seo_region', 'Nairobi' ) ); ?></span>
			<span class="dck-pill"><?php esc_html_e( 'Part of the Deliverance Church family', 'dck-elite' ); ?></span>
		</div>
	</div>
</section>

<section class="dck-section dck-about2">
	<div class="dck-container dck-about2__grid">
		<div class="dck-about2__media dck-reveal">
			<img src="<?php echo esc_url( $dck_photo ); ?>" alt="<?php esc_attr_e( 'Deliverance Church Kabete N Market congregation', 'dck-elite' ); ?>" loading="lazy" decoding="async">
			<span class="dck-about2__badge"><strong>2017</strong><?php esc_html_e( 'Planted in faith', 'dck-elite' ); ?></span>
		</div>
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'Our Story', 'dck-elite' ); ?></p>
			<h2><?php echo esc_html( dck_home_val( 'dck_about_story_heading', __( 'Founded in Faith, Growing in Grace', 'dck-elite' ) ) ); ?></h2>
			<div class="dck-about2__story">
				<?php
				echo wp_kses_post(
					wpautop(
						dck_home_val(
							'dck_about_story',
							"Deliverance Church Kabete N Market (Cistern of Hope Center) was founded by Pastor Justus Kubai and Mrs. Clare Kubai, after years of faithful service at Deliverance Church Kangemi and Muthangari from 2002 to 2017.\n\nIn obedience to God's call, they planted a church in N Market, Uthiru. Our journey began with a prayer meeting on 9 June 2017, our first Sunday service on 11 June 2017, and the official launch on 18 June 2017 by Rev. Ben Luvutse.\n\nIn 2020, during the COVID-19 pandemic, we moved to our current site. By God's grace and the faith of our members, we continue to build a house of hope for the whole family."
						)
					)
				);
				?>
			</div>
			<?php if ( $dck_stats ) : ?>
				<div class="dck-about2__stats">
					<?php foreach ( $dck_stats as $st ) : ?>
						<div><strong><?php echo esc_html( number_format_i18n( $st['num'] ) . $st['suffix'] ); ?></strong><span><?php echo esc_html( $st['label'] ); ?></span></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="dck-mvm">
	<div class="dck-container dck-mvm__grid">
		<div class="dck-mvm__card dck-reveal">
			<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Our Mission', 'dck-elite' ); ?></p>
			<p class="dck-mvm__text"><?php echo esc_html( dck_home_val( 'dck_about_mission', __( 'Reaching out and leading generations into a growing relationship with Christ through the Word of God.', 'dck-elite' ) ) ); ?></p>
		</div>
		<div class="dck-mvm__card dck-mvm__card--gold dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'Our Heart', 'dck-elite' ); ?></p>
			<p class="dck-mvm__text"><?php echo esc_html( dck_home_val( 'dck_about_motto', __( 'Small enough to know you, big enough to accommodate you.', 'dck-elite' ) ) ); ?></p>
		</div>
	</div>
</section>

<?php if ( $dck_vals ) : ?>
<section class="dck-section">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'What We Stand For', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Our Core Values', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-values2">
			<?php foreach ( $dck_vals as $i => $v ) : ?>
				<div class="dck-value2 dck-reveal">
					<span class="dck-value2__no"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<h3><?php echo esc_html( $v[0] ); ?></h3>
					<p><?php echo esc_html( $v[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $dck_tl ) : ?>
<section class="dck-section dck-journey">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Our Journey', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Milestones of Grace', 'dck-elite' ); ?></h2>
		</div>
		<ol class="dck-tline">
			<?php foreach ( $dck_tl as $t ) : ?>
				<li class="dck-tline__item dck-reveal">
					<span class="dck-tline__dot" aria-hidden="true"></span>
					<span class="dck-tline__date"><?php echo esc_html( $t[0] ); ?></span>
					<h3><?php echo esc_html( $t[1] ); ?></h3>
					<p><?php echo esc_html( $t[2] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
<?php endif; ?>

<section class="dck-section">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Our Leadership', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Shepherds of the House', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-leaders">
			<?php foreach ( dck_pastors() as $p ) : ?>
				<a class="dck-leader dck-reveal" href="<?php echo esc_url( dck_elite_page_url( 'our-pastors' ) . '#pastor-' . $p['k'] ); ?>">
					<span class="dck-leader__photo"><?php echo $p['photo']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in dck_pastor_photo_html. ?></span>
					<strong><?php echo esc_html( $p['name'] ); ?></strong>
					<span><?php echo esc_html( $p['role'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<div class="dck-center" style="margin-top:32px">
			<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( dck_elite_page_url( 'our-pastors' ) ); ?>"><?php esc_html_e( 'Meet Our Pastors', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
