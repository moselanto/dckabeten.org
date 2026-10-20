<?php
/**
 * Single page template.
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
			<h1><?php the_title(); ?></h1>
		</div>
	</section>
	<article class="dck-container dck-content">
		<?php
		the_content();
		wp_link_pages();
		?>
	</article>
	<?php
endwhile;

get_footer();
