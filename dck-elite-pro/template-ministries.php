<?php
/**
 * Template Name: DCK Ministries
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_all = function_exists( 'dck_all_departments' ) ? dck_all_departments() : ( function_exists( 'dck_home_ministries' ) ? dck_home_ministries() : array() );
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Get Involved', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Our Ministries', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'There is a family for every season of life. Explore the departments that drive our growth, service and impact.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section dck-min">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'There Is A Place For You', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Our Ministry Departments', 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'Every department is a doorway to belong, to serve, and to grow in Christ alongside others.', 'dck-elite' ); ?></p>
		</div>
		<div class="dck-min__grid">
			<?php foreach ( $dck_all as $m ) : ?>
				<div class="dck-min__card dck-reveal">
					<div class="dck-min__photo">
						<img src="<?php echo esc_url( $m['img'] ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" loading="lazy" width="180" height="180">
					</div>
					<h3 class="dck-min__name"><?php echo esc_html( $m['name'] ); ?></h3>
					<p class="dck-min__desc"><?php echo esc_html( $m['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
