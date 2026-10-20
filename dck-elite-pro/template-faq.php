<?php
/**
 * Template Name: FAQ
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_groups = function_exists( 'dck_elite_faq_groups' ) ? dck_elite_faq_groups() : array();
$dck_wa     = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
?>
<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Frequently Asked Questions', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_faq_title', __( 'How can we help you?', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_faq_lead', __( 'Answers to the questions we hear most often about our church, services, giving, and how to get connected.', 'dck-elite' ) ) ); ?></p>
	</div>
</section>

<section class="dck-section dck-faq2">
	<div class="dck-container">
		<div class="dck-sfinder dck-reveal">
			<label for="dck-faq-search" class="dck-min2__label"><?php esc_html_e( 'Search the questions', 'dck-elite' ); ?></label>
			<div class="dck-ai-search-bar">
				<input type="search" id="dck-faq-search" placeholder="<?php esc_attr_e( 'Try: service times, giving, baptism, location...', 'dck-elite' ); ?>" autocomplete="off">
				<button type="button" class="dck-btn dck-btn--outline" id="dck-faq-toggle"><?php esc_html_e( 'Expand all', 'dck-elite' ); ?></button>
			</div>
			<nav class="dck-bible-chips" aria-label="<?php esc_attr_e( 'FAQ topics', 'dck-elite' ); ?>">
				<?php foreach ( $dck_groups as $g ) : ?>
					<a class="dck-chip" href="#faq-<?php echo esc_attr( sanitize_title( wp_strip_all_tags( html_entity_decode( $g['heading'] ) ) ) ); ?>"><?php echo esc_html( html_entity_decode( $g['heading'] ) ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>

		<div class="dck-faq2__layout">
			<div class="dck-faq" id="dck-faq-groups">
				<?php
				foreach ( $dck_groups as $g ) :
					$gid = 'faq-' . sanitize_title( wp_strip_all_tags( html_entity_decode( $g['heading'] ) ) );
					?>
					<div class="dck-faq-group" id="<?php echo esc_attr( $gid ); ?>">
						<h2><?php echo esc_html( html_entity_decode( $g['heading'] ) ); ?></h2>
						<div class="dck-faq-list">
							<?php foreach ( $g['items'] as $it ) : ?>
								<details class="dck-faq-item">
									<summary><?php echo esc_html( $it['q'] ); ?></summary>
									<div class="dck-faq-answer"><?php echo wp_kses_post( wpautop( function_exists( 'dck_tokens' ) ? dck_tokens( $it['a'] ) : str_replace( '%HOME%', home_url( '/' ), $it['a'] ) ) ); ?></div>
								</details>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
				<p class="dck-empty" id="dck-faq-none" hidden><?php esc_html_e( 'No questions match that search. Try another word, or ask us directly.', 'dck-elite' ); ?></p>
			</div>

			<aside class="dck-faq2__aside">
				<div class="dck-formcard">
					<h3><?php esc_html_e( 'Still have a question?', 'dck-elite' ); ?></h3>
					<p class="dck-muted"><?php esc_html_e( 'A real person will be glad to help, in English or Kiswahili.', 'dck-elite' ); ?></p>
					<div class="dck-faq2__btns">
						<?php if ( $dck_wa ) : ?>
							<a class="dck-btn dck-btn--gold dck-btn--block" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp us', 'dck-elite' ); ?></a>
						<?php endif; ?>
						<a class="dck-btn dck-btn--outline dck-btn--block" href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( dck_phone_display() ); ?></a>
						<a class="dck-btn dck-btn--outline dck-btn--block" href="<?php echo esc_url( dck_elite_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact page', 'dck-elite' ); ?></a>
					</div>
				</div>
			</aside>
		</div>
	</div>
</section>
<?php
get_footer();
