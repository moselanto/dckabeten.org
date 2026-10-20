<?php
/**
 * 404 template.
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
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)">404</p>
		<h1><?php esc_html_e( 'This page has wandered off', 'dck-elite' ); ?></h1>
	</div>
</section>
<div class="dck-container dck-content dck-center">
	<p class="dck-lead"><?php esc_html_e( 'The page you are looking for may have moved. Let us help you find your way home.', 'dck-elite' ); ?></p>
	<?php get_search_form(); ?>
	<p style="margin-top:24px"><a class="dck-btn dck-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'dck-elite' ); ?></a></p>
</div>
<?php
get_footer();
