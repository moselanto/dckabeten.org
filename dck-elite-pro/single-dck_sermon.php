<?php
/**
 * Single sermon.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	$sid       = get_the_ID();
	$video     = get_post_meta( $sid, '_dck_video_url', true );
	$audio     = get_post_meta( $sid, '_dck_audio_url', true );
	$scripture = get_post_meta( $sid, '_dck_scripture', true );
	$download  = get_post_meta( $sid, '_dck_download_url', true );
	$thumb     = has_post_thumbnail() ? get_the_post_thumbnail_url( $sid, 'large' ) : get_post_meta( $sid, '_dck_sermon_image', true );
	$speakers  = get_the_term_list( $sid, 'dck_sermon_speaker', '', ', ' );
	$series    = get_the_term_list( $sid, 'dck_sermon_series', '', ', ' );
	$permalink = get_permalink();
	?>
	<section class="dck-page-hero">
		<div class="dck-container">
			<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php echo esc_html( get_the_date() ); ?></p>
			<h1><?php the_title(); ?></h1>
			<p style="color:#e7ecff">
				<?php if ( $speakers && ! is_wp_error( $speakers ) ) { echo wp_kses_post( $speakers ); } ?>
				<?php if ( $series && ! is_wp_error( $series ) ) { echo ' &middot; ' . wp_kses_post( $series ); } ?>
			</p>
		</div>
	</section>

	<article class="dck-container dck-content dck-sermon-single">
		<?php
		if ( $video ) {
			echo dck_elite_video_embed( $video, get_the_title() ); // phpcs:ignore
		} elseif ( $thumb ) {
			echo '<img class="dck-sermon-single__img" src="' . esc_url( $thumb ) . '" alt="' . esc_attr( get_the_title() ) . '">';
		}
		?>

		<?php if ( $audio ) : ?>
			<p style="margin-top:24px"><audio id="dck-sermon-audio" controls preload="none" style="width:100%"><source src="<?php echo esc_url( $audio ); ?>" type="audio/mpeg"></audio></p>
		<?php endif; ?>

		<?php if ( $scripture ) : ?>
			<p class="dck-sermon-single__scr"><?php esc_html_e( 'Scripture', 'dck-elite' ); ?>: <?php echo esc_html( $scripture ); ?></p>
		<?php endif; ?>

		<?php
		$dck_summary = get_post_meta( $sid, '_dck_summary', true );
		$dck_guide   = get_post_meta( $sid, '_dck_study_guide', true );
		if ( $dck_summary && function_exists( 'dck_ai_render_blocks' ) ) :
			?>
			<div class="dck-summary-card">
				<p class="dck-eyebrow" data-i18n="summary_label"><?php esc_html_e( 'Sermon Summary', 'dck-elite' ); ?></p>
				<?php echo wp_kses_post( dck_ai_render_blocks( $dck_summary ) ); ?>
			</div>
			<?php
		endif;
		?>

		<div class="dck-read" data-reveal>
			<button type="button" class="dck-read__btn dck-btn dck-btn--gold" data-target="#dck-sermon-body" data-label-idle="<?php esc_attr_e( 'Listen to this sermon', 'dck-elite' ); ?>">
				<span class="dck-read__ic" aria-hidden="true">&#9654;</span>
				<span class="dck-read__label"><?php esc_html_e( 'Listen to this sermon', 'dck-elite' ); ?></span>
			</button>
			<button type="button" class="dck-read__stop dck-btn dck-btn--outline" hidden><?php esc_html_e( 'Stop', 'dck-elite' ); ?></button>
		</div>
		<div id="dck-sermon-body" style="margin-top:20px">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>

		<?php if ( ! empty( $dck_guide ) && function_exists( 'dck_ai_render_blocks' ) ) : ?>
			<details class="dck-guide">
				<summary data-i18n="guide_label"><?php esc_html_e( 'Small-Group Study Guide', 'dck-elite' ); ?></summary>
				<div class="dck-guide-body"><?php echo wp_kses_post( dck_ai_render_blocks( $dck_guide ) ); ?></div>
			</details>
		<?php endif; ?>

		<?php if ( function_exists( 'dck_ai_transcript_html' ) ) { echo dck_ai_transcript_html( $sid ); } ?>

		<div class="dck-share" style="margin-top:28px;border-top:1px solid var(--dck-line);padding-top:20px">
			<?php if ( $download ) : ?>
				<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $download ); ?>" download><?php esc_html_e( 'Download Notes', 'dck-elite' ); ?></a>
			<?php endif; ?>
			<a class="dck-btn dck-btn--outline" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( get_the_title() . ' - ' . $permalink ); ?>"><?php esc_html_e( 'Share on WhatsApp', 'dck-elite' ); ?></a>
			<a class="dck-btn dck-btn--outline" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $permalink ); ?>"><?php esc_html_e( 'Share on Facebook', 'dck-elite' ); ?></a>
			<a class="dck-btn dck-btn--outline dck-print-sermon" href="#"><?php esc_html_e( 'Print sermon', 'dck-elite' ); ?></a>
		</div>

		<p class="dck-back-btn" style="margin-top:30px"><a class="dck-btn dck-btn--primary" href="<?php echo esc_url( get_post_type_archive_link( 'dck_sermon' ) ); ?>"><?php esc_html_e( 'Back to Sermons', 'dck-elite' ); ?></a></p>
	</article>
	<?php
endwhile;

get_footer();
