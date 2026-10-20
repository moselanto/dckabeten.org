<?php
/**
 * Template Name: DCK Our Pastors
 *
 * Names, roles, biographies and photos are editable in DCK Church > About & Pastors.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_ps = dck_pastors();
$dck_wa = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Who We Are', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_pastors_title', __( 'Our Pastors', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_pastors_lead', __( 'Meet the shepherds God has entrusted with the care of our church family.', 'dck-elite' ) ) ); ?></p>
		<nav class="dck-legal-hero__meta" aria-label="<?php esc_attr_e( 'Jump to a pastor', 'dck-elite' ); ?>">
			<?php foreach ( $dck_ps as $p ) : ?>
				<a class="dck-pill dck-pill--link" href="#pastor-<?php echo esc_attr( $p['k'] ); ?>"><?php echo esc_html( $p['name'] ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</section>

<section class="dck-section dck-pastors2">
	<div class="dck-container">
		<?php foreach ( $dck_ps as $i => $p ) : ?>
			<article class="dck-pcard dck-reveal<?php echo $i % 2 ? ' dck-pcard--flip' : ''; ?>" id="pastor-<?php echo esc_attr( $p['k'] ); ?>">
				<div class="dck-pcard__photo">
					<?php echo $p['photo']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in dck_pastor_photo_html. ?>
					<span class="dck-pcard__role"><?php echo esc_html( $p['role'] ); ?></span>
				</div>
				<div class="dck-pcard__body">
					<h2 class="dck-pcard__name"><?php echo esc_html( $p['name'] ); ?></h2>
					<p class="dck-pcard__title"><?php echo esc_html( $p['title'] ); ?></p>
					<p class="dck-pcard__lead"><?php echo esc_html( $p['lead'] ); ?></p>
					<?php if ( trim( wp_strip_all_tags( $p['bio'] ) ) ) : ?>
						<div class="dck-pcard__bio" id="bio-<?php echo esc_attr( $p['k'] ); ?>" hidden><?php echo wp_kses_post( wpautop( $p['bio'] ) ); ?></div>
						<button type="button" class="dck-btn dck-btn--outline dck-bio-toggle" aria-expanded="false" aria-controls="bio-<?php echo esc_attr( $p['k'] ); ?>" data-more="<?php esc_attr_e( 'Read full biography', 'dck-elite' ); ?>" data-less="<?php esc_attr_e( 'Show less', 'dck-elite' ); ?>"><?php esc_html_e( 'Read full biography', 'dck-elite' ); ?></button>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>

<section class="dck-section dck-pastors-cta">
	<div class="dck-container">
		<div class="dck-sermons-cta dck-reveal">
			<div>
				<h3><?php esc_html_e( 'Would you like to speak with a pastor?', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'For prayer, counselling, marriage support or guidance, our pastors are glad to walk with you.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-sermons-cta__btns">
				<?php if ( $dck_wa ) : ?>
					<a class="dck-btn dck-btn--gold" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello, I would like to speak with a pastor.', 'dck-elite' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp a pastor', 'dck-elite' ); ?></a>
				<?php endif; ?>
				<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'prayer-requests' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_prayer_request', 'Prayer Request' ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
