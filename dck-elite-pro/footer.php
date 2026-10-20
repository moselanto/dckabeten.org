<?php
/**
 * Footer (v2.1.0) - simple, matching the live site.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dck_tel = function_exists( 'dck_home_tel' ) ? dck_home_tel() : '+254110304521';
?>
</main><!-- #dck-main -->

<footer class="dck-footer2">
	<div class="dck-container dck-footer2__row">
		<a class="dck-footer2__item" href="tel:<?php echo esc_attr( $dck_tel ); ?>">
			<span class="dck-footer2__ic"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.24 1Z"/></svg></span>
			<span class="dck-footer2__txt"><span class="dck-footer2__k"><?php esc_html_e( 'Call Us on', 'dck-elite' ); ?></span><strong><?php echo esc_html( dck_home_val( 'dck_top_phone', '+254 110 304521' ) ); ?></strong></span>
		</a>
		<a class="dck-footer2__item" href="mailto:<?php echo esc_attr( dck_home_val( 'dck_top_email', 'info@dckabeten.org' ) ); ?>">
			<span class="dck-footer2__ic"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm0 2 8 6 8-6"/></svg></span>
			<span class="dck-footer2__txt"><span class="dck-footer2__k"><?php esc_html_e( 'Write to us', 'dck-elite' ); ?></span><strong><?php echo esc_html( dck_home_val( 'dck_top_email', 'info@dckabeten.org' ) ); ?></strong></span>
		</a>
		<a class="dck-footer2__item" href="<?php echo esc_url( function_exists( 'dck_home_directions' ) ? dck_home_directions() : 'https://share.google/LJmcXS6D5Nq1aF2R5' ); ?>" target="_blank" rel="noopener">
			<span class="dck-footer2__ic"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg></span>
			<span class="dck-footer2__txt"><span class="dck-footer2__k"><?php esc_html_e( 'Location', 'dck-elite' ); ?></span><strong><?php echo esc_html( dck_home_val( 'dck_top_address', 'Behind N Market, Uthiru, Kabete' ) ); ?></strong></span>
		</a>
	</div>
	<?php if ( function_exists( 'dck_social_icons' ) ) : ?>
	<div class="dck-footer2__social">
		<div class="dck-container"><?php dck_social_icons(); ?></div>
	</div>
	<?php endif; ?>
	<div class="dck-footer2__bottom">
		<div class="dck-container">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( dck_home_val( 'dck_copyright', 'Deliverance Church Kabete N' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'dck-elite' ); ?> &nbsp;&middot;&nbsp; <a class="dck-footer2__legal" href="<?php echo esc_url( function_exists( 'dck_elite_page_url' ) ? dck_elite_page_url( 'terms' ) : home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms &amp; Privacy', 'dck-elite' ); ?></a>
		</div>
	</div>
</footer>

<div class="dck-a11y" id="dck-a11y">
	<button class="dck-a11y__toggle" type="button" aria-expanded="false" aria-controls="dck-a11y-panel" aria-label="<?php esc_attr_e( 'Accessibility options', 'dck-elite' ); ?>" title="<?php esc_attr_e( 'Accessibility', 'dck-elite' ); ?>">
		<svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" aria-hidden="true" focusable="false"><circle cx="12" cy="3.6" r="2"/><path d="M20 7.5c0 .7-3 1.3-6.2 1.5V13l1.9 6.6a1 1 0 0 1-1.9.6L12 15.2l-1.8 5a1 1 0 0 1-1.9-.6L10 13V9c-3.2-.2-6-.8-6-1.5 0-.8 3.6-1.4 8-1.4s8 .6 8 1.4Z"/></svg>
	</button>
	<div class="dck-a11y__panel" id="dck-a11y-panel" role="dialog" aria-label="<?php esc_attr_e( 'Accessibility options', 'dck-elite' ); ?>" hidden>
		<p class="dck-a11y__title"><?php esc_html_e( 'Accessibility', 'dck-elite' ); ?></p>
		<button type="button" data-a11y="text-up"><?php esc_html_e( 'Increase text size', 'dck-elite' ); ?></button>
		<button type="button" data-a11y="text-down"><?php esc_html_e( 'Decrease text size', 'dck-elite' ); ?></button>
		<button type="button" data-a11y="contrast"><?php esc_html_e( 'High contrast', 'dck-elite' ); ?></button>
		<button type="button" data-a11y="links"><?php esc_html_e( 'Highlight links', 'dck-elite' ); ?></button>
		<button type="button" data-a11y="reset"><?php esc_html_e( 'Reset', 'dck-elite' ); ?></button>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
