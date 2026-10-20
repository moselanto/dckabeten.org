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
?>
<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Frequently Asked Questions', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'How can we help you?', 'dck-elite' ); ?></h1>
		<p class="dck-lead"><?php esc_html_e( 'Answers to the questions we hear most often about Deliverance Church Kabete, our services, giving, and how to get connected.', 'dck-elite' ); ?></p>
	</div>
</section>
<section class="dck-section">
	<div class="dck-container dck-faq">
		<?php dck_faq_render(); ?>
		<div class="dck-faq-cta dck-card">
			<h3><?php esc_html_e( 'Still have a question?', 'dck-elite' ); ?></h3>
			<p><?php esc_html_e( 'We would love to hear from you. Reach out and our team will be glad to help.', 'dck-elite' ); ?></p>
			<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( function_exists( 'dck_elite_page_url' ) ? dck_elite_page_url( 'contact' ) : home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();
