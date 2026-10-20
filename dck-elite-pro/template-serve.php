<?php
/**
 * Template Name: DCK Serve With Us
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'dck-forms' );
get_header();
dck_photo_hero(
	dck_theme_image( 'dck_serve_hero_img', 'assets/images/home/joy.webp' ),
	__( 'Serve With Us', 'dck-elite' ),
	__( 'Use your gifts in God\'s house', 'dck-elite' ),
	__( 'Every member has a place to serve. Choose a ministry and the leader will reach out to welcome you to the team.', 'dck-elite' )
);
?>
<section class="dck-section">
	<div class="dck-container dck-gift__grid">
		<div class="dck-gift__intro">
			<p class="dck-eyebrow"><?php esc_html_e( 'Ministries', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Where would you love to serve?', 'dck-elite' ); ?></h2>
			<ul class="dck-gift__points">
				<?php foreach ( array_slice( dck_serve_ministries(), 0, 6 ) as $m ) : ?><li><?php echo esc_html( $m ); ?></li><?php endforeach; ?>
			</ul>
			<p class="dck-muted" style="margin-top:16px"><?php esc_html_e( 'Looking for a home fellowship instead?', 'dck-elite' ); ?> <a href="<?php echo esc_url( dck_elite_page_url( 'fellowships' ) ); ?>"><?php esc_html_e( 'Find one near you', 'dck-elite' ); ?></a></p>
		</div>
		<form class="dck-ctform dck-formcard" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/serve' ) ); ?>" novalidate>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Ministry', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
				<select name="ministry" required><option value=""><?php esc_html_e( 'Choose one', 'dck-elite' ); ?></option><?php foreach ( dck_serve_ministries() as $m ) : ?><option><?php echo esc_html( $m ); ?></option><?php endforeach; ?></select>
			</label>
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Phone / WhatsApp', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" required placeholder="07XX XXX XXX"></label>
			</div>
			<div class="dck-cform__field"><span><?php esc_html_e( 'When are you available?', 'dck-elite' ); ?></span>
				<div class="dck-checks"><?php foreach ( array( 'Sunday mornings', 'Weekday evenings', 'Saturdays', 'Special events' ) as $a ) : ?><label><input type="checkbox" name="avail[]" value="<?php echo esc_attr( $a ); ?>"><span><?php echo esc_html( $a ); ?></span></label><?php endforeach; ?></div>
			</div>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Skills or experience', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><textarea name="note" rows="3" maxlength="1000" placeholder="<?php esc_attr_e( 'e.g. I play keyboard, I can operate cameras, I love working with children', 'dck-elite' ); ?>"></textarea></label>
			<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Sign me up', 'dck-elite' ); ?></button>
			<p class="dck-ctform__status" role="status" aria-live="polite"></p>
		</form>
	</div>
</section>
<?php
get_footer();
