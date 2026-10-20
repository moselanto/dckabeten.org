<?php
/**
 * Single post template.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="dck-page-hero">
		<div class="dck-container">
			<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php echo esc_html( get_the_date() ); ?></p>
			<h1><?php the_title(); ?></h1>
		</div>
	</section>
	<article class="dck-container dck-content">
		<?php
		$dck_img = function_exists( 'dck_post_image_url' ) ? dck_post_image_url() : '';
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'large' );
		} elseif ( $dck_img ) {
			echo '<img src="' . esc_url( $dck_img ) . '" alt="' . esc_attr( get_the_title() ) . '" class="dck-post-hero-img" loading="lazy">';
		}
		the_content();
		wp_link_pages();
		?>
	</article>
	<?php
	if ( 'post' === get_post_type() && function_exists( 'dck_related_posts' ) ) {
		$dck_rel = dck_related_posts( get_the_ID(), 3 );
		if ( $dck_rel ) {
			echo '<div class="dck-section dck-related"><div class="dck-container"><h2 class="dck-center">You may also like</h2><div class="dck-grid-3">';
			foreach ( $dck_rel as $rp ) {
				$rimg = dck_post_image_url( $rp->ID );
				echo '<article class="dck-card dck-reveal">';
				if ( $rimg ) { echo '<a href="' . esc_url( get_permalink( $rp->ID ) ) . '"><img src="' . esc_url( $rimg ) . '" alt="' . esc_attr( get_the_title( $rp->ID ) ) . '" loading="lazy"></a>'; }
				echo '<h3><a href="' . esc_url( get_permalink( $rp->ID ) ) . '">' . esc_html( get_the_title( $rp->ID ) ) . '</a></h3>';
				echo '<p>' . esc_html( wp_trim_words( get_the_excerpt( $rp->ID ), 20 ) ) . '</p></article>';
			}
			echo '</div></div></div>';
		}
	}
	?>
	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="dck-container dck-content">';
		comments_template();
		echo '</div>';
	}
endwhile;

get_footer();
