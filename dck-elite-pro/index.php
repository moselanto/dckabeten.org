<?php
/**
 * Blog / archive / search listing.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$dck_paged = max( 1, (int) get_query_var( 'paged' ) );
$dck_is_blog = is_home() && ! is_front_page();
$dck_feature = $dck_is_blog && 1 === $dck_paged && have_posts();
$dck_cats    = get_categories( array( 'hide_empty' => true, 'number' => 8 ) );
?>
<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'News & Devotions', 'dck-elite' ); ?></p>
		<h1>
			<?php
			if ( $dck_is_blog ) {
				echo esc_html( dck_home_val( 'dck_blog_title', single_post_title( '', false ) ) );
			} elseif ( is_archive() ) {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			} elseif ( is_search() ) {
				echo esc_html( sprintf( __( 'Search: %s', 'dck-elite' ), get_search_query() ) );
			} else {
				esc_html_e( 'News & Articles', 'dck-elite' );
			}
			?>
		</h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_blog_lead', __( 'Faith articles, devotions and church news to encourage you through the week.', 'dck-elite' ) ) ); ?></p>
	</div>
</section>

<section class="dck-section dck-blog2">
	<div class="dck-container">
		<div class="dck-sfinder dck-reveal">
			<form class="dck-ai-search-bar" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="dck-blog-s"><?php esc_html_e( 'Search articles', 'dck-elite' ); ?></label>
				<input type="search" id="dck-blog-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search articles and devotions...', 'dck-elite' ); ?>">
				<input type="hidden" name="post_type" value="post">
				<button class="dck-btn dck-btn--primary" type="submit"><?php esc_html_e( 'Search', 'dck-elite' ); ?></button>
			</form>
			<?php if ( $dck_cats ) : ?>
				<nav class="dck-bible-chips" aria-label="<?php esc_attr_e( 'Categories', 'dck-elite' ); ?>">
					<a class="dck-chip<?php echo $dck_is_blog ? ' is-active' : ''; ?>" href="<?php echo esc_url( dck_elite_page_url( 'blog' ) ); ?>"><?php esc_html_e( 'All', 'dck-elite' ); ?></a>
					<?php foreach ( $dck_cats as $c ) : ?>
						<a class="dck-chip<?php echo is_category( $c->term_id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>

		<?php
		if ( have_posts() ) :
			if ( $dck_feature ) :
				the_post();
				$fimg = function_exists( 'dck_post_image_url' ) ? dck_post_image_url() : '';
				?>
				<article class="dck-sfeature dck-reveal">
					<a class="dck-sfeature__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php if ( $fimg ) : ?><img src="<?php echo esc_url( $fimg ); ?>" alt="" decoding="async" width="1200" height="675"><?php endif; ?>
					</a>
					<div class="dck-sfeature__body">
						<p class="dck-sfeature__tag"><?php esc_html_e( 'Latest Article', 'dck-elite' ); ?></p>
						<h2 class="dck-sfeature__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="dck-sfeature__meta"><span><?php echo esc_html( get_the_date() ); ?></span><span><?php echo esc_html( sprintf( __( '%d min read', 'dck-elite' ), max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) ) ); ?></span></p>
						<p class="dck-sfeature__exc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 36 ) ); ?></p>
						<div class="dck-sfeature__btns"><a class="dck-btn dck-btn--gold" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read Article', 'dck-elite' ); ?></a></div>
					</div>
				</article>
			<?php endif; ?>
			<div class="dck-sermons-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$img = function_exists( 'dck_post_image_url' ) ? dck_post_image_url() : '';
					$cat = get_the_category();
					?>
					<article class="dck-sermon-card dck-reveal">
						<a class="dck-sermon-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" decoding="async" width="800" height="450"><?php endif; ?>
							<?php if ( $cat ) : ?><span class="dck-sermon-card__series"><?php echo esc_html( $cat[0]->name ); ?></span><?php endif; ?>
						</a>
						<div class="dck-sermon-card__body">
							<div class="dck-sermon-card__meta"><?php echo esc_html( get_the_date() ); ?> &middot; <?php echo esc_html( sprintf( __( '%d min read', 'dck-elite' ), max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) ) ); ?></div>
							<h3 class="dck-sermon-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="dck-sermon-card__exc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<a class="dck-sermon-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'dck-elite' ); ?><span class="screen-reader-text"> <?php the_title(); ?></span></a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<div class="dck-empty">
				<h2><?php esc_html_e( 'Nothing found', 'dck-elite' ); ?></h2>
				<p><?php esc_html_e( 'Try another search, or browse all articles.', 'dck-elite' ); ?></p>
				<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( dck_elite_page_url( 'blog' ) ); ?>"><?php esc_html_e( 'All articles', 'dck-elite' ); ?></a>
			</div>
		<?php endif; ?>

		<nav class="dck-pager" aria-label="<?php esc_attr_e( 'Article pages', 'dck-elite' ); ?>">
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => '&larr; ' . __( 'Newer', 'dck-elite' ), 'next_text' => __( 'Older', 'dck-elite' ) . ' &rarr;' ) ); ?>
		</nav>
	</div>
</section>
<?php
get_footer();
