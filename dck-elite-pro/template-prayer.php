<?php
/**
 * Template Name: DCK Prayer Request
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'dck-prayer', get_template_directory_uri() . '/assets/js/prayer.js', array(), DCK_ELITE_VERSION, true );
wp_localize_script(
	'dck-prayer',
	'DCK_PRAYER',
	array(
		'phone'    => dck_phone_display(),
		'sending'  => __( 'Sending...', 'dck-elite' ),
		'thanks'   => __( 'Thank you. Our prayer team will be praying with you.', 'dck-elite' ),
		'errFill'  => __( 'Please fill in your name, phone, email and prayer request.', 'dck-elite' ),
		'errEmail' => __( 'Please enter a valid email address so we can reach you.', 'dck-elite' ),
		'errNet'   => __( 'Something went wrong. Please call us on', 'dck-elite' ),
	)
);
get_header();
$dck_wa     = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
$dck_topics = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_prayer_topics', "Healing\nFamily\nMarriage\nFinances and work\nSalvation\nGuidance\nThanksgiving\nOther" ) ) ) );
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'We Would Love To Pray With You', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_prayer_title', __( 'Prayer Request', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_prayer_lead', __( 'Whatever you are facing, you do not have to carry it alone. Share your request and our prayer team will stand with you in prayer.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<span class="dck-pill"><?php esc_html_e( 'Received by real people', 'dck-elite' ); ?></span>
			<span class="dck-pill"><?php esc_html_e( 'Confidential option', 'dck-elite' ); ?></span>
			<span class="dck-pill"><?php esc_html_e( 'From anywhere in the world', 'dck-elite' ); ?></span>
		</div>
	</div>
</section>

<section class="dck-section dck-prayer2">
	<div class="dck-container dck-prayer2__grid">
		<div class="dck-formcard dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'Share Your Request', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'How Can We Pray For You?', 'dck-elite' ); ?></h2>
			<form id="dck-prayer-form" class="dck-cform" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/prayer' ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" novalidate>
				<?php if ( $dck_topics ) : ?>
					<div class="dck-cform__field">
						<span><?php esc_html_e( 'What is this about?', 'dck-elite' ); ?> <small class="dck-opt"><?php esc_html_e( 'Optional', 'dck-elite' ); ?></small></span>
						<div class="dck-bible-chips dck-ptopics" role="group">
							<?php foreach ( $dck_topics as $t ) : ?>
								<button type="button" class="dck-chip" data-topic="<?php echo esc_attr( $t ); ?>" aria-pressed="false"><?php echo esc_html( $t ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				<div class="dck-cform__row">
					<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
					<label class="dck-cform__field"><span><?php esc_html_e( 'Phone number', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" required placeholder="<?php esc_attr_e( 'Include country code, e.g. +254', 'dck-elite' ); ?>"></label>
				</div>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="email" name="email" autocomplete="email" required placeholder="<?php esc_attr_e( 'So our pastors can reach you', 'dck-elite' ); ?>"></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Your prayer request', 'dck-elite' ); ?> <em class="dck-req">*</em></span><textarea name="request" rows="6" required maxlength="5000" placeholder="<?php esc_attr_e( 'Share as much or as little as you like...', 'dck-elite' ); ?>"></textarea></label>
				<label class="dck-switch">
					<input type="checkbox" name="private" value="1">
					<span class="dck-switch__ui" aria-hidden="true"></span>
					<span class="dck-switch__txt"><strong><?php esc_html_e( 'Keep this confidential', 'dck-elite' ); ?></strong><?php esc_html_e( 'Only our pastors will see it.', 'dck-elite' ); ?></span>
				</label>
				<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
				<p class="dck-form-privacy"><?php printf( esc_html__( 'Your details are used only to pray for and follow up with you. See our %s.', 'dck-elite' ), '<a href="' . esc_url( dck_elite_page_url( 'terms' ) ) . '">' . esc_html__( 'Privacy Notice', 'dck-elite' ) . '</a>' ); ?></p>
				<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Send Prayer Request', 'dck-elite' ); ?></button>
				<div id="dck-prayer-status" class="dck-prayer__status" role="status" aria-live="polite"></div>
			</form>
		</div>

		<aside class="dck-prayer2__aside">
			<figure class="dck-verse dck-reveal">
				<blockquote><?php echo esc_html( dck_home_val( 'dck_prayer_verse', __( 'Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God.', 'dck-elite' ) ) ); ?></blockquote>
				<figcaption><?php echo esc_html( dck_home_val( 'dck_prayer_verse_ref', 'Philippians 4:6' ) ); ?></figcaption>
			</figure>
			<div class="dck-psteps dck-reveal">
				<h3><?php esc_html_e( 'What happens next', 'dck-elite' ); ?></h3>
				<ol>
					<li><strong><?php esc_html_e( 'Received', 'dck-elite' ); ?></strong> <?php esc_html_e( 'Your request reaches our prayer team, or our pastors only if you marked it confidential.', 'dck-elite' ); ?></li>
					<li><strong><?php esc_html_e( 'Prayed for', 'dck-elite' ); ?></strong> <?php esc_html_e( 'We lift it up in prayer, including at our intercessory gatherings.', 'dck-elite' ); ?></li>
					<li><strong><?php esc_html_e( 'Followed up', 'dck-elite' ); ?></strong> <?php esc_html_e( 'Where helpful, a pastor may reach out to encourage you.', 'dck-elite' ); ?></li>
				</ol>
			</div>
			<div class="dck-purgent dck-reveal">
				<h3><?php esc_html_e( 'Need prayer right now?', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Talk to someone today.', 'dck-elite' ); ?></p>
				<div class="dck-faq2__btns">
					<a class="dck-btn dck-btn--gold dck-btn--block" href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( sprintf( __( 'Call %s', 'dck-elite' ), dck_phone_display() ) ); ?></a>
					<?php if ( $dck_wa ) : ?>
						<a class="dck-btn dck-btn--ghost dck-btn--block" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello, I would like someone to pray with me.', 'dck-elite' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp a pastor', 'dck-elite' ); ?></a>
					<?php endif; ?>
				</div>
				<p class="dck-purgent__note"><?php esc_html_e( 'If you or someone else is in danger, please contact your local emergency services immediately.', 'dck-elite' ); ?></p>
			</div>
		</aside>
	</div>
</section>

<?php
get_footer();
