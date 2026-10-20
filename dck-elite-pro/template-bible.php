<?php
/**
 * Template Name: DCK Bible Assistant
 *
 * AI Bible study assistant page.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<?php
$dck_chips = array_filter( array_map( 'trim', explode( "\n", (string) dck_home_val( 'dck_bible_suggestions', "Romans 8:28\nPsalm 23\nPhilippians 4:6-7\nOvercoming anxiety\nForgiveness\nFinding hope in hard times\nGrowing in faith\nThe armor of God" ) ) ) );
?>
<section class="dck-page-hero dck-bible-hero dck-bible-hero--compact">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( dck_home_val( 'dck_bible_eyebrow', __( 'Grow in the Word', 'dck-elite' ) ) ); ?></p>
		<h1 data-i18n="bible_title"><?php echo esc_html( dck_home_val( 'dck_bible_title', __( 'The Holy Bible', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light" data-i18n="bible_lead"><?php echo esc_html( dck_home_val( 'dck_bible_lead', __( 'Read in English, Kiswahili and Kenyan languages, compare versions side by side, and ask our study assistant about any verse.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<a class="dck-pill dck-pill--link" href="#read"><?php esc_html_e( 'Start reading', 'dck-elite' ); ?></a>
			<a class="dck-pill dck-pill--link" href="#ask"><?php esc_html_e( 'Ask the study assistant', 'dck-elite' ); ?></a>
			<span class="dck-pill"><?php echo esc_html( sprintf( _n( '%d version', '%d versions', count( dck_bible_versions() ), 'dck-elite' ), count( dck_bible_versions() ) ) ); ?></span>
		</div>
	</div>
</section>

<?php
$dck_vers   = dck_bible_versions();
$dck_groups = array();
foreach ( $dck_vers as $v ) {
	$dck_groups[ $v['group'] ][] = $v;
}
if ( ! function_exists( 'dck_reader_version_options' ) ) {
	function dck_reader_version_options( $groups ) {
		foreach ( $groups as $g => $list ) {
			echo '<optgroup label="' . esc_attr( $g ) . '">';
			foreach ( $list as $v ) {
				echo '<option value="' . esc_attr( $v['id'] ) . '">' . esc_html( $v['lang'] . ' · ' . $v['name'] ) . '</option>';
			}
			echo '</optgroup>';
		}
	}
}
?>
<section class="dck-rwrap" id="read">
	<div class="dck-reader" id="dck-reader">

		<aside class="dck-rside" id="dck-r-side" aria-label="<?php esc_attr_e( 'Books and chapters', 'dck-elite' ); ?>">
			<div class="dck-rside__head">
				<div class="dck-rside__tabs" role="tablist">
					<button type="button" role="tab" class="dck-rside__tab is-active" data-test="all" aria-selected="true"><?php esc_html_e( 'All', 'dck-elite' ); ?></button>
					<button type="button" role="tab" class="dck-rside__tab" data-test="ot" aria-selected="false"><?php esc_html_e( 'Old Testament', 'dck-elite' ); ?></button>
					<button type="button" role="tab" class="dck-rside__tab" data-test="nt" aria-selected="false"><?php esc_html_e( 'New Testament', 'dck-elite' ); ?></button>
				</div>
				<button type="button" class="dck-rside__close" id="dck-r-sideclose" aria-label="<?php esc_attr_e( 'Close', 'dck-elite' ); ?>">&times;</button>
			</div>
			<label class="screen-reader-text" for="dck-r-bfilter"><?php esc_html_e( 'Find a book', 'dck-elite' ); ?></label>
			<input type="search" class="dck-rside__filter" id="dck-r-bfilter" placeholder="<?php esc_attr_e( 'Find a book...', 'dck-elite' ); ?>" autocomplete="off">
			<div class="dck-rside__list" id="dck-r-blist"></div>
		</aside>
		<div class="dck-rscrim" id="dck-r-scrim" hidden></div>

		<div class="dck-rmain">
			<div class="dck-rtop" id="dck-r-top">
				<div class="dck-rtop__progress"><span id="dck-r-progress"></span></div>
				<div class="dck-rtop__row">
					<button type="button" class="dck-rtop__nav" id="dck-r-navbtn" aria-controls="dck-r-side" aria-expanded="false">
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14z"/></svg>
						<span id="dck-r-cur"><?php esc_html_e( 'Books', 'dck-elite' ); ?></span>
						<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
					</button>
					<label class="dck-rtop__sel"><span class="screen-reader-text"><?php esc_html_e( 'Version', 'dck-elite' ); ?></span>
						<select id="dck-r-tr"><?php dck_reader_version_options( $dck_groups ); ?></select>
					</label>
					<button type="button" class="dck-rtop__more" id="dck-r-morebtn" aria-controls="dck-r-more" aria-expanded="false"><?php esc_html_e( 'More', 'dck-elite' ); ?></button>
					<div class="dck-rtop__extra" id="dck-r-more">
						<label class="dck-rtop__sel"><span class="dck-rtop__lbl"><?php esc_html_e( 'Compare', 'dck-elite' ); ?></span>
							<select id="dck-r-tr2"><option value=""><?php esc_html_e( 'No second version', 'dck-elite' ); ?></option><?php dck_reader_version_options( $dck_groups ); ?></select>
						</label>
						<form class="dck-rtop__go" id="dck-r-go" role="search">
							<label class="screen-reader-text" for="dck-r-ref"><?php esc_html_e( 'Go to a reference', 'dck-elite' ); ?></label>
							<input type="search" id="dck-r-ref" placeholder="<?php esc_attr_e( 'Go to: John 3:16', 'dck-elite' ); ?>" autocomplete="off">
							<button type="submit" aria-label="<?php esc_attr_e( 'Go', 'dck-elite' ); ?>"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></button>
						</form>
						<div class="dck-rtop__tools">
							<button type="button" class="dck-ibtn" id="dck-r-smaller" title="<?php esc_attr_e( 'Smaller text', 'dck-elite' ); ?>" aria-label="<?php esc_attr_e( 'Smaller text', 'dck-elite' ); ?>">A&minus;</button>
							<button type="button" class="dck-ibtn" id="dck-r-bigger" title="<?php esc_attr_e( 'Larger text', 'dck-elite' ); ?>" aria-label="<?php esc_attr_e( 'Larger text', 'dck-elite' ); ?>">A+</button>
							<button type="button" class="dck-ibtn" id="dck-r-mode" title="<?php esc_attr_e( 'Night mode', 'dck-elite' ); ?>" aria-label="<?php esc_attr_e( 'Night mode', 'dck-elite' ); ?>"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg></button>
							<button type="button" class="dck-ibtn" id="dck-r-listen" title="<?php esc_attr_e( 'Listen', 'dck-elite' ); ?>" aria-label="<?php esc_attr_e( 'Listen', 'dck-elite' ); ?>"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg></button>
							<button type="button" class="dck-ibtn" id="dck-r-share" title="<?php esc_attr_e( 'Share chapter', 'dck-elite' ); ?>" aria-label="<?php esc_attr_e( 'Share chapter', 'dck-elite' ); ?>"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg></button>
							<button type="button" class="dck-ibtn" id="dck-r-full" title="<?php esc_attr_e( 'Focus mode', 'dck-elite' ); ?>" aria-label="<?php esc_attr_e( 'Focus mode', 'dck-elite' ); ?>"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/></svg></button>
						</div>
					</div>
				</div>
			</div>

			<div class="dck-votd" id="dck-votd" hidden>
				<span class="dck-votd__k"><?php esc_html_e( 'Verse of the Day', 'dck-elite' ); ?></span>
				<blockquote class="dck-votd__t" id="dck-votd-t"></blockquote>
				<div class="dck-votd__f"><span id="dck-votd-r"></span><button type="button" class="dck-tool" id="dck-votd-open"><?php esc_html_e( 'Read chapter', 'dck-elite' ); ?></button><button type="button" class="dck-votd__x" id="dck-votd-x" aria-label="<?php esc_attr_e( 'Hide', 'dck-elite' ); ?>">&times;</button></div>
			</div>

			<article class="dck-rpage">
				<header class="dck-rpage__head">
					<button type="button" class="dck-rnav" id="dck-r-prev" aria-label="<?php esc_attr_e( 'Previous chapter', 'dck-elite' ); ?>">&larr;</button>
					<div class="dck-rpage__title"><h2 id="dck-r-title">&nbsp;</h2><p id="dck-r-sub"></p></div>
					<button type="button" class="dck-rnav" id="dck-r-next" aria-label="<?php esc_attr_e( 'Next chapter', 'dck-elite' ); ?>">&rarr;</button>
				</header>
				<audio id="dck-r-audio" class="dck-raudio" controls preload="none" hidden></audio>
				<div class="dck-rtext" id="dck-r-text" aria-live="polite"></div>
				<footer class="dck-rpage__foot">
					<button type="button" class="dck-btn dck-btn--outline" id="dck-r-prev2">&larr; <span id="dck-r-prevlbl"><?php esc_html_e( 'Previous', 'dck-elite' ); ?></span></button>
					<button type="button" class="dck-btn dck-btn--primary" id="dck-r-next2"><span id="dck-r-nextlbl"><?php esc_html_e( 'Next chapter', 'dck-elite' ); ?></span> &rarr;</button>
				</footer>
				<p class="dck-rcredit" id="dck-r-credit"></p>
				<p class="dck-rhint"><?php esc_html_e( 'Tip: tap any verse to copy, share or ask about it. On a keyboard, use the arrow keys to change chapter.', 'dck-elite' ); ?></p>
			</article>

			<div class="dck-vpop" id="dck-vpop" role="dialog" aria-label="<?php esc_attr_e( 'Verse actions', 'dck-elite' ); ?>" hidden>
				<button type="button" data-act="copy"><?php esc_html_e( 'Copy', 'dck-elite' ); ?></button>
				<button type="button" data-act="share"><?php esc_html_e( 'WhatsApp', 'dck-elite' ); ?></button>
				<button type="button" data-act="ask"><?php esc_html_e( 'Ask about this', 'dck-elite' ); ?></button>
				<button type="button" data-act="close" aria-label="<?php esc_attr_e( 'Close', 'dck-elite' ); ?>">&times;</button>
			</div>
		</div>
	</div>

	<nav class="dck-rbottom" id="dck-r-bottom" aria-label="<?php esc_attr_e( 'Chapter navigation', 'dck-elite' ); ?>">
		<button type="button" id="dck-r-bprev" aria-label="<?php esc_attr_e( 'Previous chapter', 'dck-elite' ); ?>">&larr;</button>
		<button type="button" id="dck-r-bcur" class="dck-rbottom__cur"><?php esc_html_e( 'Books', 'dck-elite' ); ?></button>
		<button type="button" id="dck-r-bnext" aria-label="<?php esc_attr_e( 'Next chapter', 'dck-elite' ); ?>">&rarr;</button>
	</nav>
</section>

<section class="dck-section dck-bible-section dck-ask-section">
	<div class="dck-container dck-bible">
		<?php
		while ( have_posts() ) :
			the_post();
			if ( trim( wp_strip_all_tags( get_the_content() ) ) ) :
				?>
				<div class="dck-bible-intro dck-reveal"><?php the_content(); ?></div>
				<?php
			endif;
		endwhile;
		?>

		<div class="dck-center dck-ask-head" id="ask">
			<p class="dck-eyebrow"><?php esc_html_e( 'Go Deeper', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Ask the Bible Study Assistant', 'dck-elite' ); ?></h2>
		</div>

		<div class="dck-studio dck-reveal">
			<label class="screen-reader-text" for="dck-bible-input"><?php esc_html_e( 'Your Bible question', 'dck-elite' ); ?></label>
			<div class="dck-bible-ask">
				<span class="dck-studio__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14zM4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg></span>
				<textarea id="dck-bible-input" rows="2" data-i18n-ph="bible_ph" placeholder="<?php esc_attr_e( 'Ask about a verse (Romans 8:28) or a topic (anxiety, forgiveness, hope)...', 'dck-elite' ); ?>"></textarea>
				<button type="button" id="dck-bible-go" class="dck-btn dck-btn--primary" data-i18n="ask"><?php esc_html_e( 'Ask', 'dck-elite' ); ?></button>
			</div>
			<p class="dck-studio__hint"><?php esc_html_e( 'Press Enter to ask. Shift + Enter for a new line.', 'dck-elite' ); ?></p>

			<div class="dck-bible-levels" role="group" aria-label="<?php esc_attr_e( 'Explanation depth', 'dck-elite' ); ?>">
				<span data-i18n="depth"><?php esc_html_e( 'Depth:', 'dck-elite' ); ?></span>
				<button type="button" class="dck-level is-active" data-level="Balanced" data-i18n="lvl_balanced"><?php esc_html_e( 'Balanced', 'dck-elite' ); ?></button>
				<button type="button" class="dck-level" data-level="Simple" data-i18n="lvl_simple"><?php esc_html_e( 'Simple', 'dck-elite' ); ?></button>
				<button type="button" class="dck-level" data-level="Youth" data-i18n="lvl_youth"><?php esc_html_e( 'Youth', 'dck-elite' ); ?></button>
				<button type="button" class="dck-level" data-level="Children" data-i18n="lvl_children"><?php esc_html_e( 'Children', 'dck-elite' ); ?></button>
				<button type="button" class="dck-level" data-level="Theological" data-i18n="lvl_theological"><?php esc_html_e( 'Theological', 'dck-elite' ); ?></button>
				<button type="button" class="dck-level" data-level="Original" data-i18n="lvl_original"><?php esc_html_e( 'Original Language', 'dck-elite' ); ?></button>
			</div>

			<div class="dck-bible-chips">
				<span class="dck-bible-chips__label"><?php esc_html_e( 'Try asking about:', 'dck-elite' ); ?></span>
				<?php foreach ( $dck_chips as $c ) : ?>
					<button type="button" class="dck-chip" data-ask="<?php echo esc_attr( $c ); ?>"><?php echo esc_html( $c ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="dck-answer" id="dck-answer" hidden>
			<div class="dck-answer__bar">
				<span class="dck-answer__q" id="dck-answer-q"></span>
				<span class="dck-answer__tools">
					<button type="button" class="dck-tool" id="dck-bible-copy"><?php esc_html_e( 'Copy', 'dck-elite' ); ?></button>
					<button type="button" class="dck-tool" id="dck-bible-print"><?php esc_html_e( 'Print', 'dck-elite' ); ?></button>
				</span>
			</div>
			<div id="dck-bible-out" class="dck-bible-out" aria-live="polite"></div>
			<div class="dck-read dck-read--answer" data-read-scope hidden>
				<button type="button" class="dck-read__btn dck-btn dck-btn--outline" data-target="#dck-bible-out" data-label-idle="<?php esc_attr_e( 'Listen to the answer', 'dck-elite' ); ?>">
					<span class="dck-read__ic" aria-hidden="true">&#9654;</span>
					<span class="dck-read__label"><?php esc_html_e( 'Listen to the answer', 'dck-elite' ); ?></span>
				</button>
				<button type="button" class="dck-read__stop dck-btn dck-btn--outline" hidden><?php esc_html_e( 'Stop', 'dck-elite' ); ?></button>
			</div>
		</div>
		<div id="dck-bible-sermons" class="dck-bible-sermons"></div>

		<div class="dck-bible-ways">
			<div class="dck-bway dck-reveal">
				<span class="dck-bway__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 4v16M5 6h14M5 6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h5M19 6a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-5"/></svg></span>
				<h3><?php esc_html_e( 'Understand a Verse', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Ask about any passage and receive its context, cross references, and how to apply it to your life.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-bway dck-reveal">
				<span class="dck-bway__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></span>
				<h3><?php esc_html_e( 'Explore a Topic', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Anxiety, forgiveness, hope, marriage, purpose - get Scripture-grounded guidance for real life.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-bway dck-reveal">
				<span class="dck-bway__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
				<h3><?php esc_html_e( 'Prepare for Group', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Use the depth settings for simple, youth, or theological explanations to lead a home church or class.', 'dck-elite' ); ?></p>
			</div>
		</div>

		<p class="dck-bible-note"><?php echo esc_html( sprintf( __( 'This assistant is a study aid to help you engage the Bible. It can make mistakes, so always read the full passage in context and grow with your local church. For pastoral care, reach us on %s.', 'dck-elite' ), dck_phone_display() ) ); ?></p>
	</div>
</section>
<?php
get_footer();
