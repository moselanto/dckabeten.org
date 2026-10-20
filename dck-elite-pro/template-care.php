<?php
/**
 * Template Name: DCK Pastoral Care
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'dck-forms' );
get_header();
dck_photo_hero(
	dck_theme_image( 'dck_care_hero_img', 'assets/images/home/joy.webp' ),
	__( 'Pastoral Care', 'dck-elite' ),
	__( 'We are here for you', 'dck-elite' ),
	__( 'Book time with a pastor, or ask about baptism, baby dedication, weddings, the membership class, visits and bereavement support.', 'dck-elite' )
);
$dck_pre = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
?>
<section class="dck-section">
	<div class="dck-container dck-gift__grid">
		<div class="dck-gift__intro">
			<p class="dck-eyebrow"><?php esc_html_e( 'How it works', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Tell us what you need', 'dck-elite' ); ?></h2>
			<ol class="dck-newhere__steps">
				<li><strong><?php esc_html_e( 'Choose a request', 'dck-elite' ); ?></strong> <?php esc_html_e( 'and the day and time that suit you.', 'dck-elite' ); ?></li>
				<li><strong><?php esc_html_e( 'We confirm', 'dck-elite' ); ?></strong> <?php esc_html_e( 'a pastor calls or WhatsApps you to agree on a time.', 'dck-elite' ); ?></li>
				<li><strong><?php esc_html_e( 'In confidence', 'dck-elite' ); ?></strong> <?php esc_html_e( 'tick "Keep this confidential" and only our pastors will see it.', 'dck-elite' ); ?></li>
			</ol>
			<p class="dck-muted"><?php esc_html_e( 'Urgent? Call us now:', 'dck-elite' ); ?> <a href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><strong><?php echo esc_html( dck_phone_display() ); ?></strong></a></p>
		</div>
		<form class="dck-ctform dck-formcard" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/care' ) ); ?>" novalidate>
			<label class="dck-cform__field"><span><?php esc_html_e( 'What would you like?', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
				<select name="type" required>
					<option value=""><?php esc_html_e( 'Choose one', 'dck-elite' ); ?></option>
					<?php foreach ( dck_care_types() as $k => $l ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $dck_pre, $k ); ?>><?php echo esc_html( $l ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'Preferred day', 'dck-elite' ); ?></span><input type="date" name="date" min="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Preferred time', 'dck-elite' ); ?></span>
					<select name="slot"><?php foreach ( dck_care_times() as $k => $l ) : ?><option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></option><?php endforeach; ?></select>
				</label>
			</div>
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Phone / WhatsApp', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" required placeholder="07XX XXX XXX"></label>
			</div>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><input type="email" name="email" autocomplete="email"></label>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Anything we should know?', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><textarea name="details" rows="4" maxlength="3000"></textarea></label>
			<label class="dck-switch"><input type="checkbox" name="private" value="1"><span class="dck-switch__ui" aria-hidden="true"></span><span class="dck-switch__txt"><strong><?php esc_html_e( 'Keep this confidential', 'dck-elite' ); ?></strong><?php esc_html_e( 'Only our pastors will see the details.', 'dck-elite' ); ?></span></label>
			<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Send request', 'dck-elite' ); ?></button>
			<p class="dck-ctform__status" role="status" aria-live="polite"></p>
		</form>
	</div>
</section>
<?php
get_footer();
