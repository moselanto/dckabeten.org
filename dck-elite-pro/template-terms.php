<?php
/**
 * Template Name: DCK Terms & Photography
 *
 * Terms of use, privacy notice and photography preference form.
 * All text is editable in DCK Church > Terms & Privacy (or Appearance > Customize).
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$dck_sections = function_exists( 'dck_legal_sections' ) ? dck_legal_sections() : array();
$dck_updated  = get_theme_mod( 'dck_legal_updated', '2026-10-20' );
$dck_upd_ts   = strtotime( (string) $dck_updated );
$dck_title    = dck_home_val( 'dck_legal_title', __( 'Terms, Privacy & Photography', 'dck-elite' ) );
$dck_intro    = dck_home_val( 'dck_legal_intro', __( 'How we care for your information, how you may use this website, and how we handle photos and video at church.', 'dck-elite' ) );
$dck_email    = dck_church_email();
$dck_wa       = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
?>

<section class="dck-page-hero dck-legal-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Transparency & Care', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( $dck_title ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( $dck_intro ); ?></p>
		<div class="dck-legal-hero__meta">
			<?php if ( $dck_upd_ts ) : ?>
				<span class="dck-pill"><?php echo esc_html( sprintf( __( 'Last updated %s', 'dck-elite' ), date_i18n( get_option( 'date_format' ), $dck_upd_ts ) ) ); ?></span>
			<?php endif; ?>
			<a class="dck-pill dck-pill--link" href="#dck-optout"><?php esc_html_e( 'Photography preference form', 'dck-elite' ); ?></a>
			<button type="button" class="dck-pill dck-pill--link" onclick="window.print()"><?php esc_html_e( 'Print this page', 'dck-elite' ); ?></button>
		</div>
	</div>
</section>

<section class="dck-section dck-legal-glance">
	<div class="dck-container">
		<div class="dck-glance">
			<div class="dck-glance__item dck-reveal">
				<span class="dck-glance__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></svg></span>
				<h3><?php esc_html_e( 'We never sell your data', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Your details are used only to reply, pray and care for you.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-glance__item dck-reveal">
				<span class="dck-glance__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="7" width="18" height="13" rx="2"/><circle cx="12" cy="13.5" r="3.5"/><path d="M8 7l1.5-3h5L16 7"/></svg></span>
				<h3><?php esc_html_e( 'You control photos', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Prefer not to be photographed? Tell us once and we will honour it.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-glance__item dck-reveal">
				<span class="dck-glance__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M4 12h16M4 17h10"/></svg></span>
				<h3><?php esc_html_e( 'Your rights respected', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Ask to see, correct or delete your information at any time.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-glance__item dck-reveal">
				<span class="dck-glance__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.3A8.5 8.5 0 1 1 21 11.5z"/></svg></span>
				<h3><?php esc_html_e( 'Real people to talk to', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Questions? Our team is a call, WhatsApp or email away.', 'dck-elite' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="dck-section dck-legal-body">
	<div class="dck-container dck-legal-layout">
		<aside class="dck-legal-toc" aria-label="<?php esc_attr_e( 'On this page', 'dck-elite' ); ?>">
			<div class="dck-legal-toc__inner">
				<p class="dck-legal-toc__title"><?php esc_html_e( 'On this page', 'dck-elite' ); ?></p>
				<ol>
					<?php foreach ( $dck_sections as $i => $sec ) : ?>
						<li><a href="#<?php echo esc_attr( $sec['id'] ); ?>"><?php echo esc_html( $sec['title'] ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="#dck-optout"><?php esc_html_e( 'Photography preference form', 'dck-elite' ); ?></a></li>
				</ol>
			</div>
		</aside>

		<article class="dck-legal-doc">
			<?php foreach ( $dck_sections as $i => $sec ) : ?>
				<section class="dck-legal-sec" id="<?php echo esc_attr( $sec['id'] ); ?>">
					<h2><span class="dck-legal-sec__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><?php echo esc_html( $sec['title'] ); ?></h2>
					<div class="dck-legal-sec__body"><?php echo wp_kses_post( $sec['body'] ); ?></div>
				</section>
			<?php endforeach; ?>

			<?php
			/* Extra content added in the page editor (skips the old auto-generated copy). */
			while ( have_posts() ) :
				the_post();
				$dck_pc = get_the_content();
				if ( trim( wp_strip_all_tags( $dck_pc ) ) && false === strpos( $dck_pc, 'Terms of Use' ) ) :
					?>
					<section class="dck-legal-sec"><div class="dck-legal-sec__body"><?php the_content(); ?></div></section>
					<?php
				endif;
			endwhile;
			?>

			<div class="dck-legal-help">
				<div>
					<h3><?php esc_html_e( 'Questions about your privacy?', 'dck-elite' ); ?></h3>
					<p><?php esc_html_e( 'We are glad to help, in English or Kiswahili.', 'dck-elite' ); ?></p>
				</div>
				<div class="dck-legal-help__btns">
					<a class="dck-btn dck-btn--primary" href="mailto:<?php echo esc_attr( $dck_email ); ?>"><?php esc_html_e( 'Email us', 'dck-elite' ); ?></a>
					<a class="dck-btn dck-btn--outline" href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( dck_phone_display() ); ?></a>
					<?php if ( $dck_wa ) : ?>
						<a class="dck-btn dck-btn--outline" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'dck-elite' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</article>
	</div>
</section>

<section id="dck-optout" class="dck-section dck-optout">
	<div class="dck-container dck-optout__wrap">
		<div class="dck-optout__intro">
			<p class="dck-eyebrow"><?php esc_html_e( 'Photography Preference', 'dck-elite' ); ?></p>
			<h2><?php echo esc_html( dck_home_val( 'dck_legal_optout_heading', __( 'I Prefer Not To Be Photographed', 'dck-elite' ) ) ); ?></h2>
			<p class="dck-lead"><?php echo esc_html( dck_home_val( 'dck_legal_optout_text', __( 'Complete the form and sign below. Our media team will be notified and your preference kept on record.', 'dck-elite' ) ) ); ?></p>
			<ul class="dck-checklist">
				<li><?php esc_html_e( 'Takes less than a minute', 'dck-elite' ); ?></li>
				<li><?php esc_html_e( 'Shared only with our media team', 'dck-elite' ); ?></li>
				<li><?php esc_html_e( 'You can change your mind at any time', 'dck-elite' ); ?></li>
			</ul>
		</div>

		<form id="dck-optout-form" class="dck-cform dck-formcard dck-reveal"
			data-ep="<?php echo esc_url( rest_url( 'dck/v1/photo-optout' ) ); ?>"
			data-nc="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" novalidate>

			<div class="dck-cform__row">
				<label class="dck-cform__field">
					<span><?php esc_html_e( 'Full name', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
					<input type="text" name="name" autocomplete="name" required>
				</label>
				<label class="dck-cform__field">
					<span><?php esc_html_e( 'Phone number', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
					<input type="tel" name="phone" autocomplete="tel" required placeholder="<?php esc_attr_e( '+254 7XX XXX XXX', 'dck-elite' ); ?>">
				</label>
			</div>

			<label class="dck-cform__field">
				<span><?php esc_html_e( 'Email address', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
				<input type="email" name="email" autocomplete="email" required placeholder="<?php esc_attr_e( 'you@example.com', 'dck-elite' ); ?>">
			</label>

			<label class="dck-cform__field">
				<span><?php esc_html_e( 'Service you usually attend', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span>
				<select name="service">
					<option value=""><?php esc_html_e( 'Select a service', 'dck-elite' ); ?></option>
					<?php foreach ( dck_elite_service_times() as $s ) : ?>
						<option value="<?php echo esc_attr( $s['name'] ); ?>"><?php echo esc_html( $s['name'] . ' (' . $s['time'] . ')' ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<div class="dck-sig">
				<p class="dck-sig__label"><?php esc_html_e( 'Signature', 'dck-elite' ); ?> <em class="dck-req">*</em></p>
				<div class="dck-sig__box">
					<canvas id="dck-sig-canvas" aria-label="<?php esc_attr_e( 'Signature area', 'dck-elite' ); ?>"></canvas>
					<span class="dck-sig__line" aria-hidden="true"></span>
					<button type="button" id="dck-sig-clear" class="dck-sig__clear"><?php esc_html_e( 'Clear', 'dck-elite' ); ?></button>
				</div>
				<p class="dck-sig__hint"><?php esc_html_e( 'Sign with your mouse, finger or stylus.', 'dck-elite' ); ?></p>
				<p id="dck-sig-err" class="dck-sig__err" hidden><?php esc_html_e( 'Please draw your signature before submitting.', 'dck-elite' ); ?></p>
			</div>

			<label class="dck-prayer__check">
				<input type="checkbox" name="consent" value="1" required>
				<span><?php esc_html_e( 'I prefer not to be photographed or recorded at church, and I would like the media team to be aware.', 'dck-elite' ); ?></span>
			</label>

			<div class="dck-hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

			<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Submit My Preference', 'dck-elite' ); ?></button>
			<p id="dck-optout-status" class="dck-prayer__status" role="status" aria-live="polite"></p>
		</form>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
