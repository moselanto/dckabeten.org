<?php
/**
 * Floating WhatsApp chat widget (sits above "Ask Hope").
 * Launcher with pulse + unread badge, timed teaser, office-hours status,
 * quick topics that open WhatsApp with a prefilled message, and a composer.
 * Text: DCK Church > Church Tools > WhatsApp chat.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_waw_topics' ) ) {
	function dck_waw_topics() {
		return apply_filters(
			'dck_waw_topics',
			array(
				array( __( 'Plan my first visit', 'dck-elite' ), __( 'Service times, directions, parking', 'dck-elite' ), 'Hello DCK, I would like to visit this Sunday. Please share service times and directions.' ),
				array( __( 'I need prayer', 'dck-elite' ), __( 'Our prayer team will pray with you', 'dck-elite' ), 'Hello DCK, I would like prayer for: ' ),
				array( __( 'Talk to a pastor', 'dck-elite' ), __( 'Counselling, visits, guidance', 'dck-elite' ), 'Hello DCK, I would like to speak with a pastor about: ' ),
				array( __( 'Giving and M-Pesa help', 'dck-elite' ), __( 'Paybill, bank, giving from abroad', 'dck-elite' ), 'Hello DCK, I need help with giving: ' ),
				array( __( 'Baptism, dedication or wedding', 'dck-elite' ), __( 'Book a church service', 'dck-elite' ), 'Hello DCK, I would like to ask about: ' ),
			)
		);
	}
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$wa = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
		if ( ! $wa || is_page_template( 'template-bible.php' ) ) {
			return;
		}
		wp_enqueue_script( 'dck-waw', get_template_directory_uri() . '/assets/js/wa-widget.js', array(), DCK_ELITE_VERSION, true );
		wp_localize_script(
			'dck-waw',
			'DCK_WAW',
			array(
				'wa'       => $wa,
				'intro'    => dck_home_val( 'dck_waw_intro', __( 'Karibu! 👋 How can we help you today? Choose a topic or type your message and we will reply on WhatsApp.', 'dck-elite' ) ),
				'brand'    => 'DCK',
				'page'     => is_singular() ? get_permalink() : '',
				'tz'       => wp_timezone_string(),
				'open'     => (int) dck_home_val( 'dck_waw_open', 8 ),
				'close'    => (int) dck_home_val( 'dck_waw_close', 20 ),
				'online'   => __( 'Online · usually replies within the hour', 'dck-elite' ),
				'away'     => __( 'Away · we reply from %s', 'dck-elite' ),
			)
		);
	}
);

add_action(
	'wp_footer',
	function () {
		if ( ! wp_script_is( 'dck-waw', 'enqueued' ) ) {
			return;
		}
		$wa_svg = '<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16 3a13 13 0 0 0-11.2 19.6L3 29l6.6-1.7A13 13 0 1 0 16 3Zm0 23.7a10.7 10.7 0 0 1-5.5-1.5l-.4-.2-3.9 1 1-3.8-.2-.4A10.7 10.7 0 1 1 16 26.7Zm5.9-8c-.3-.2-1.9-.9-2.2-1s-.5-.2-.7.2-.8 1-1 1.2-.4.2-.7.1a8.8 8.8 0 0 1-4.3-3.8c-.3-.6.3-.5.9-1.7a.6.6 0 0 0 0-.6l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.9.4 3.8 3.8 0 0 0-1.2 2.8 6.6 6.6 0 0 0 1.4 3.5 15 15 0 0 0 5.8 5.1c2.1.9 3 .1 3.5-.1a3 3 0 0 0 2-1.4 2.5 2.5 0 0 0 .2-1.4c-.1-.2-.3-.3-.5-.4Z"/></svg>';
		$logo   = get_template_directory_uri() . '/assets/images/app/icon-192.png';
		?>
		<div class="dckw" data-dckw hidden>
			<div class="dckw__teaser" data-dckw-teaser hidden>
				<button type="button" class="dckw__teaser-x" data-dckw-teaser-close aria-label="<?php esc_attr_e( 'Dismiss', 'dck-elite' ); ?>">&times;</button>
				<button type="button" class="dckw__teaser-body" data-dckw-toggle>
					<strong><?php echo esc_html( dck_home_val( 'dck_waw_teaser_title', __( 'Need prayer or have a question?', 'dck-elite' ) ) ); ?></strong>
					<span><?php echo esc_html( dck_home_val( 'dck_waw_teaser_text', __( 'Chat with our church team on WhatsApp. A real person will reply.', 'dck-elite' ) ) ); ?></span>
				</button>
			</div>
			<button type="button" class="dckw__launcher" data-dckw-toggle aria-expanded="false" aria-controls="dckw-panel" aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', 'dck-elite' ); ?>">
				<span class="dckw__ico dckw__ico--wa"><?php echo $wa_svg; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></span>
				<span class="dckw__ico dckw__ico--x" aria-hidden="true">&times;</span>
				<span class="dckw__badge" data-dckw-badge aria-hidden="true">1</span>
			</button>
			<section class="dckw__panel" id="dckw-panel" data-dckw-panel role="dialog" aria-modal="false" aria-labelledby="dckw-title" hidden>
				<header class="dckw__head">
					<span class="dckw__avatar"><img src="<?php echo esc_url( $logo ); ?>" alt="" width="46" height="46"><i class="dckw__dot" data-dckw-dot></i></span>
					<span class="dckw__head-text"><strong id="dckw-title"><?php echo esc_html( dck_home_val( 'dck_waw_name', __( 'DCK Church Team', 'dck-elite' ) ) ); ?></strong><small data-dckw-status><?php esc_html_e( 'Deliverance Church Kabete N', 'dck-elite' ); ?></small></span>
					<button type="button" class="dckw__x" data-dckw-toggle aria-label="<?php esc_attr_e( 'Close chat', 'dck-elite' ); ?>">&times;</button>
				</header>
				<div class="dckw__body" data-dckw-log aria-live="polite">
					<div class="dckw__msg dckw__msg--bot"><p data-dckw-intro></p><time data-dckw-time></time></div>
					<div class="dckw__topics">
						<?php foreach ( dck_waw_topics() as $t ) : ?>
							<a class="dckw__topic" href="#" data-dckw-topic="<?php echo esc_attr( $t[2] ); ?>" target="_blank" rel="noopener"><span><strong><?php echo esc_html( $t[0] ); ?></strong><small><?php echo esc_html( $t[1] ); ?></small></span><i aria-hidden="true">&rsaquo;</i></a>
						<?php endforeach; ?>
					</div>
				</div>
				<form class="dckw__form" data-dckw-form>
					<label class="screen-reader-text" for="dckw-input"><?php esc_html_e( 'Your message', 'dck-elite' ); ?></label>
					<input type="text" id="dckw-input" class="dckw__input" data-dckw-input placeholder="<?php esc_attr_e( 'Type a message...', 'dck-elite' ); ?>" autocomplete="off" maxlength="500">
					<button type="submit" class="dckw__send" aria-label="<?php esc_attr_e( 'Send on WhatsApp', 'dck-elite' ); ?>"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>
				</form>
				<p class="dckw__foot"><?php echo esc_html( dck_home_val( 'dck_waw_hours_text', __( 'We reply daily 8am - 8pm (East Africa Time). Emergencies: please call.', 'dck-elite' ) ) ); ?></p>
			</section>
		</div>
		<?php
	},
	20
);
