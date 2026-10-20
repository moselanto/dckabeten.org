<?php
/**
 * Template Name: DCK Contact
 *
 * Contact page. All text and details are editable in DCK Church > Church Settings.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_tel   = dck_home_tel();
$dck_phone = dck_phone_display();
$dck_email = dck_church_email();
$dck_addr  = dck_church_address();
$dck_dir   = function_exists( 'dck_home_directions' ) ? dck_home_directions() : dck_home_val( 'dck_directions_url', 'https://share.google/LJmcXS6D5Nq1aF2R5' );
$dck_wa    = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
$dck_map_q = dck_home_val( 'dck_contact_map_query', 'Deliverance Church Kabete N Market, Uthiru, Nairobi, Kenya' );
$dck_topics = array_filter( array_map( 'trim', explode( "\n", (string) dck_home_val( 'dck_contact_topics', "General enquiry\nPlan a visit\nPrayer and pastoral care\nGiving\nMinistries and serving\nMedia and partnerships" ) ) ) );
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( dck_home_val( 'dck_contact_eyebrow', __( 'We Would Love To Hear From You', 'dck-elite' ) ) ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_contact_title', __( 'Contact Us', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_contact_lead', __( 'Have a question, need prayer, or planning a visit from near or far? Reach out and a real person will get back to you.', 'dck-elite' ) ) ); ?></p>
	</div>
</section>

<section class="dck-section dck-contact-quick">
	<div class="dck-container">
		<div class="dck-qc">
			<a class="dck-qc__card dck-reveal" href="tel:<?php echo esc_attr( $dck_tel ); ?>">
				<span class="dck-qc__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.24 1Z"/></svg></span>
				<span class="dck-qc__k"><?php esc_html_e( 'Call us', 'dck-elite' ); ?></span>
				<strong><?php echo esc_html( $dck_phone ); ?></strong>
				<span class="dck-qc__go"><?php esc_html_e( 'Tap to call', 'dck-elite' ); ?> &rarr;</span>
			</a>
			<?php if ( $dck_wa ) : ?>
			<a class="dck-qc__card dck-reveal" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello DCK, I have a question.', 'dck-elite' ) ); ?>" target="_blank" rel="noopener">
				<span class="dck-qc__ic dck-qc__ic--wa" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.3A8.5 8.5 0 1 1 21 11.5z"/></svg></span>
				<span class="dck-qc__k"><?php esc_html_e( 'WhatsApp', 'dck-elite' ); ?></span>
				<strong><?php esc_html_e( 'Chat with us', 'dck-elite' ); ?></strong>
				<span class="dck-qc__go"><?php esc_html_e( 'Open WhatsApp', 'dck-elite' ); ?> &rarr;</span>
			</a>
			<?php endif; ?>
			<a class="dck-qc__card dck-reveal" href="mailto:<?php echo esc_attr( $dck_email ); ?>">
				<span class="dck-qc__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm0 2 8 6 8-6"/></svg></span>
				<span class="dck-qc__k"><?php esc_html_e( 'Email', 'dck-elite' ); ?></span>
				<strong><?php echo esc_html( $dck_email ); ?></strong>
				<span class="dck-qc__go"><?php esc_html_e( 'Write an email', 'dck-elite' ); ?> &rarr;</span>
			</a>
			<a class="dck-qc__card dck-reveal" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener">
				<span class="dck-qc__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg></span>
				<span class="dck-qc__k"><?php esc_html_e( 'Visit us', 'dck-elite' ); ?></span>
				<strong><?php echo esc_html( $dck_addr ); ?></strong>
				<span class="dck-qc__go"><?php esc_html_e( 'Get directions', 'dck-elite' ); ?> &rarr;</span>
			</a>
		</div>
	</div>
</section>

<section class="dck-section dck-contact-main">
	<div class="dck-container dck-contact-grid">
		<div class="dck-formcard dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'Send A Message', 'dck-elite' ); ?></p>
			<h2><?php echo esc_html( dck_home_val( 'dck_contact_form_title', __( 'Write To Us', 'dck-elite' ) ) ); ?></h2>
			<p class="dck-muted"><?php echo esc_html( dck_home_val( 'dck_contact_reply_note', __( 'We usually reply within one working day. For urgent prayer or pastoral care, please call or WhatsApp us.', 'dck-elite' ) ) ); ?></p>

			<form id="dck-contact-form" class="dck-cform" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/contact' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" novalidate>
				<div class="dck-cform__row">
					<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
					<label class="dck-cform__field"><span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="email" name="email" autocomplete="email" required placeholder="<?php esc_attr_e( 'you@example.com', 'dck-elite' ); ?>"></label>
				</div>
				<div class="dck-cform__row">
					<label class="dck-cform__field"><span><?php esc_html_e( 'Phone', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span><input type="tel" name="phone" autocomplete="tel" placeholder="<?php esc_attr_e( 'Include country code, e.g. +254', 'dck-elite' ); ?>"></label>
					<label class="dck-cform__field"><span><?php esc_html_e( 'Topic', 'dck-elite' ); ?></span>
						<select name="topic">
							<?php foreach ( $dck_topics as $t ) : ?>
								<option value="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $t ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>
				<label class="dck-cform__field"><span><?php esc_html_e( 'How can we help?', 'dck-elite' ); ?> <em class="dck-req">*</em></span><textarea name="message" rows="6" required maxlength="5000" placeholder="<?php esc_attr_e( 'Write your message here...', 'dck-elite' ); ?>"></textarea></label>
				<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
				<p class="dck-form-privacy"><?php
					printf(
						/* translators: %s: link to terms page */
						esc_html__( 'We use your details only to reply to you. See our %s.', 'dck-elite' ),
						'<a href="' . esc_url( dck_elite_page_url( 'terms' ) ) . '">' . esc_html__( 'Privacy Notice', 'dck-elite' ) . '</a>'
					);
				?></p>
				<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Send Message', 'dck-elite' ); ?></button>
				<p id="dck-contact-status" class="dck-prayer__status" role="status" aria-live="polite"></p>
			</form>
		</div>

		<aside class="dck-visit dck-reveal">
			<div class="dck-visit__map">
				<iframe title="<?php esc_attr_e( 'Map to the church', 'dck-elite' ); ?>" src="https://www.google.com/maps?q=<?php echo rawurlencode( $dck_map_q ); ?>&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
			</div>
			<div class="dck-visit__body">
				<h3><?php esc_html_e( 'Visit Us On Sunday', 'dck-elite' ); ?></h3>
				<p class="dck-visit__addr"><?php echo esc_html( $dck_addr ); ?></p>
				<ul class="dck-times">
					<?php foreach ( dck_elite_service_times() as $s ) : ?>
						<li><span><?php echo esc_html( $s['name'] ); ?></span><strong><?php echo esc_html( $s['time'] ); ?></strong></li>
					<?php endforeach; ?>
				</ul>
				<p class="dck-visit__tz">
					<?php esc_html_e( 'Times are in East Africa Time (EAT, UTC+3).', 'dck-elite' ); ?>
					<span class="dck-localtime" data-tz="Africa/Nairobi" hidden><?php esc_html_e( 'Our local time now:', 'dck-elite' ); ?> <strong></strong></span>
				</p>
				<div class="dck-visit__btns">
					<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get Directions', 'dck-elite' ); ?></a>
					<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch Online', 'dck-elite' ); ?></a>
				</div>
				<p class="dck-visit__intl"><?php esc_html_e( 'Joining us from abroad? Worship with us live online every Sunday, and reach us anytime on WhatsApp.', 'dck-elite' ); ?></p>
			</div>
		</aside>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
