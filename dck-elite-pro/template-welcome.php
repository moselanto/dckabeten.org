<?php
/**
 * Template Name: DCK I'm New
 *
 * First-time visitor card. Submissions go to DCK > New Visitors and the welcome team email.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'dck-forms' );
get_header();

dck_photo_hero(
	dck_theme_image( 'dck_welcome_img', 'assets/images/home/joy.webp' ),
	__( 'Welcome Home', 'dck-elite' ),
	dck_home_val( 'dck_welcome_title', __( "We're so glad you came", 'dck-elite' ) ),
	dck_home_val( 'dck_welcome_lead', __( 'Whether this was your first Sunday or you watched online, we would love to know you. It takes one minute.', 'dck-elite' ) )
);
?>
<section class="dck-section dck-newhere">
	<div class="dck-container dck-gift__grid">
		<div class="dck-gift__intro">
			<p class="dck-eyebrow"><?php esc_html_e( 'What happens next', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'A real person will reach out', 'dck-elite' ); ?></h2>
			<ol class="dck-newhere__steps">
				<li><strong><?php esc_html_e( 'This week', 'dck-elite' ); ?></strong> <?php esc_html_e( 'Someone from our welcome team will send you a short message or call.', 'dck-elite' ); ?></li>
				<li><strong><?php esc_html_e( 'Your questions', 'dck-elite' ); ?></strong> <?php esc_html_e( 'Ask anything about the church, fellowships, baptism or membership.', 'dck-elite' ); ?></li>
				<li><strong><?php esc_html_e( 'Next Sunday', 'dck-elite' ); ?></strong> <?php esc_html_e( 'We will look out for you and help you find your place.', 'dck-elite' ); ?></li>
			</ol>
		</div>
		<form class="dck-ctform dck-formcard" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/visitor' ) ); ?>" novalidate>
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Phone / WhatsApp', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" required placeholder="07XX XXX XXX"></label>
			</div>
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><input type="email" name="email" autocomplete="email"></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Where do you live?', 'dck-elite' ); ?></span><input type="text" name="area" placeholder="<?php esc_attr_e( 'e.g. Uthiru, Kabete, Kangemi', 'dck-elite' ); ?>"></label>
			</div>
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'How did you hear about us?', 'dck-elite' ); ?></span>
					<select name="heard">
						<option value=""><?php esc_html_e( 'Choose one', 'dck-elite' ); ?></option>
						<?php foreach ( array( 'Friend or family', 'Walked past the church', 'Facebook', 'YouTube', 'TikTok / Instagram', 'Google search', 'Event or outreach', 'Other' ) as $o ) : ?>
							<option><?php echo esc_html( $o ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Date of your visit', 'dck-elite' ); ?></span><input type="date" name="visit_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"></label>
			</div>
			<div class="dck-cform__field">
				<span><?php esc_html_e( "I'd like...", 'dck-elite' ); ?></span>
				<div class="dck-checks">
					<?php foreach ( dck_visitor_interests() as $k => $l ) : ?>
						<label><input type="checkbox" name="interests[]" value="<?php echo esc_attr( $k ); ?>"><span><?php echo esc_html( $l ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Anything we can pray with you about?', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><textarea name="prayer" rows="3" maxlength="2000"></textarea></label>
			<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<p class="dck-form-privacy"><?php printf( esc_html__( 'Only our welcome team and pastors see your details. See our %s.', 'dck-elite' ), '<a href="' . esc_url( dck_elite_page_url( 'terms' ) ) . '">' . esc_html__( 'Privacy Notice', 'dck-elite' ) . '</a>' ); ?></p>
			<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Send my welcome card', 'dck-elite' ); ?></button>
			<p class="dck-ctform__status" role="status" aria-live="polite"></p>
		</form>
	</div>
</section>
<?php
get_footer();
