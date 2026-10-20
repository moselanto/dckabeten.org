<?php
/**
 * Fallback template (blog/archive).
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
		<h1>
			<?php
			if ( is_home() && ! is_front_page() ) {
				single_post_title();
			} elseif ( is_archive() ) {
				the_archive_title();
			} elseif ( is_search() ) {
				printf( esc_html__( 'Search: %s', 'dck-elite' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
			} else {
				esc_html_e( 'News & Articles', 'dck-elite' );
			}
			?>
		</h1>
	</div>
</section>

<div class="dck-section">
	<div class="dck-container">
		<div class="dck-grid-3">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article class="dck-card dck-reveal">
					<?php $dck_img = function_exists( 'dck_post_image_url' ) ? dck_post_image_url() : ''; ?>
					<?php if ( $dck_img ) : ?>
						<a href="<?php the_permalink(); ?>"><img src="<?php echo esc_url( $dck_img ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"></a>
					<?php endif; ?>
					<span class="dck-card__tag"><?php echo esc_html( get_the_date() ); ?></span>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
				</article>
				<?php
			endwhile;
		else :
			echo '<p>' . esc_html__( 'Nothing here yet. Please check back soon.', 'dck-elite' ) . '</p>';
		endif;
		?>
		</div>
		<div class="dck-center" style="margin-top:40px">
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		</div>
	</div>
</div>
<?php
get_footer();
