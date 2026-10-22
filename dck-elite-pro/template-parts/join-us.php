<?php
/**
 * Reusable Join Us + Family CTA block for inner pages.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dck_dir = function_exists( 'dck_home_directions' ) ? dck_home_directions() : 'https://share.google/LJmcXS6D5Nq1aF2R5';
$dck_times = function_exists( 'dck_elite_service_times' ) ? dck_elite_service_times() : array();
?>

<section class="dck-section dck-sunday2">
	<div class="dck-container dck-center">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php echo esc_html( dck_home_val( 'dck_sunday_eyebrow', 'Join Us This Sunday' ) ); ?></p>
		<h2><?php echo esc_html( dck_home_val( 'dck_sunday_heading', 'A Joyful Sunday Gathering' ) ); ?></h2>
		<p class="dck-sunday2__tag"><?php echo esc_html( dck_home_val( 'dck_sunday_tag2', 'Experience faith, fellowship, and inspiration every Sunday. Wherever you are on your journey, there is a place for you at the table.' ) ); ?></p>
		<div class="dck-sunday2__grid">
			<?php foreach ( $dck_times as $s ) : ?>
				<div class="dck-sunday2__card dck-reveal">
					<span class="dck-sunday2__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
					<h3 class="dck-sunday2__name"><?php echo esc_html( $s['name'] ); ?></h3>
					<p class="dck-sunday2__time"><?php echo esc_html( $s['time'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="dck-sunday2__cta">
			<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( dck_elite_page_url( 'plan-your-visit' ) ); ?>"><?php esc_html_e( 'Plan Your Visit', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>

<section class="dck-cta2">
	<div class="dck-container">
		<div class="dck-cta2__panel dck-reveal">
			<div class="dck-cta2__copy">
				<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php echo esc_html( dck_home_val( 'dck_cta_eyebrow', 'Come As You Are' ) ); ?></p>
				<h2><?php echo esc_html( dck_home_val( 'dck_cta_title', 'Become Part of Our Family' ) ); ?></h2>
				<p><?php echo esc_html( dck_home_val( 'dck_cta_text', 'Plan your first visit, join a ministry, or give toward the work God is doing at DCK-N. There is a place for you here.' ) ); ?></p>
			</div>
			<div class="dck-cta2__btns">
				<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( dck_elite_page_url( 'prayer-requests' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_prayer_request', 'Prayer Request' ) ); ?></a>
				<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'give' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_give_online', 'Give Online' ) ); ?></a>
			</div>
		</div>
	</div>
</section>
