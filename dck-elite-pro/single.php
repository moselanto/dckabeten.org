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
	<section class="dck-page-hero dck-post-hero">
		<div class="dck-container">
			<?php $dck_cat = get_the_category(); ?>
			<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( $dck_cat ? $dck_cat[0]->name : __( 'Article', 'dck-elite' ) ); ?></p>
			<h1><?php the_title(); ?></h1>
			<div class="dck-legal-hero__meta">
				<span class="dck-pill"><?php echo esc_html( get_the_date() ); ?></span>
				<span class="dck-pill"><?php echo esc_html( sprintf( __( '%d min read', 'dck-elite' ), max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) ) ); ?></span>
			</div>
		</div>
	</section>
	<article class="dck-container dck-content dck-post">
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
		<div class="dck-live2__share dck-post__share">
			<span><?php esc_html_e( 'Share this article:', 'dck-elite' ); ?></span>
			<a class="dck-tool" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( get_the_title() . ' - ' . get_permalink() ); ?>">WhatsApp</a>
			<a class="dck-tool" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>">Facebook</a>
			<a class="dck-tool" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode( get_permalink() ); ?>&amp;text=<?php echo rawurlencode( get_the_title() ); ?>">X</a>
		</div>
		<p class="dck-back-btn"><a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'blog' ) ); ?>">&larr; <?php esc_html_e( 'All articles', 'dck-elite' ); ?></a></p>
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
