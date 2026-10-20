<?php
/**
 * Template Name: DCK Ministries
 *
 * Departments are edited in DCK Church > Ministries.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_all = function_exists( 'dck_all_departments' ) ? dck_all_departments() : ( function_exists( 'dck_home_ministries' ) ? dck_home_ministries() : array() );
$dck_wa  = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Get Involved', 'dck-elite' ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_ministries_title', __( 'Our Ministries', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_ministries_lead', __( 'There is a family for every season of life. Explore the departments that drive our growth, service and impact.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<span class="dck-pill"><?php echo esc_html( sprintf( _n( '%d department', '%d departments', count( $dck_all ), 'dck-elite' ), count( $dck_all ) ) ); ?></span>
			<span class="dck-pill"><?php esc_html_e( 'From age 3 to adults', 'dck-elite' ); ?></span>
		</div>
	</div>
</section>

<section class="dck-section dck-min2">
	<div class="dck-container">
		<div class="dck-sfinder dck-min2__finder dck-reveal">
			<label for="dck-min-search" class="dck-min2__label"><?php esc_html_e( 'Find your place', 'dck-elite' ); ?></label>
			<div class="dck-ai-search-bar">
				<input type="search" id="dck-min-search" placeholder="<?php esc_attr_e( 'Try: children, youth, women, worship, choir...', 'dck-elite' ); ?>" autocomplete="off">
			</div>
			<div class="dck-bible-chips" role="group" aria-label="<?php esc_attr_e( 'Quick filters', 'dck-elite' ); ?>">
				<?php
				$dck_quick = array(
					''         => __( 'All', 'dck-elite' ),
					'child'    => __( 'Children', 'dck-elite' ),
					'teen'     => __( 'Teens', 'dck-elite' ),
					'youth'    => __( 'Youth', 'dck-elite' ),
					'women'    => __( 'Women', 'dck-elite' ),
					'men'      => __( 'Men', 'dck-elite' ),
					'unmarried' => __( 'Singles', 'dck-elite' ),
					'worship'  => __( 'Worship', 'dck-elite' ),
				);
				foreach ( $dck_quick as $q => $l ) :
					?>
					<button type="button" class="dck-chip<?php echo '' === $q ? ' is-active' : ''; ?>" data-q="<?php echo esc_attr( $q ); ?>"><?php echo esc_html( $l ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="dck-mgrid" id="dck-mgrid">
			<?php foreach ( $dck_all as $m ) : ?>
				<article class="dck-mcard dck-reveal" data-s="<?php echo esc_attr( strtolower( $m['name'] . ' ' . $m['desc'] ) ); ?>" id="<?php echo esc_attr( 'ministry-' . sanitize_title( $m['name'] ) ); ?>">
					<div class="dck-mcard__photo"><img src="<?php echo esc_url( $m['img'] ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" loading="lazy" decoding="async" width="400" height="300"></div>
					<div class="dck-mcard__body">
						<h3><?php echo esc_html( $m['name'] ); ?></h3>
						<p><?php echo esc_html( $m['desc'] ); ?></p>
						<?php if ( $dck_wa ) : ?>
							<a class="dck-mcard__join" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( sprintf( __( 'Hello DCK, I would like to join %s.', 'dck-elite' ), $m['name'] ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'I want to join', 'dck-elite' ); ?> &rarr;</a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<p class="dck-empty dck-min2__none" id="dck-min-none" hidden><?php esc_html_e( 'No ministry matches that search. Try another word, or contact us and we will help you find your place.', 'dck-elite' ); ?></p>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
