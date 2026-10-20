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
<section class="dck-page-hero dck-bible-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( dck_home_val( 'dck_bible_eyebrow', __( 'Grow in the Word', 'dck-elite' ) ) ); ?></p>
		<h1 data-i18n="bible_title"><?php echo esc_html( dck_home_val( 'dck_bible_title', __( 'Bible Study Assistant', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light" data-i18n="bible_lead"><?php echo esc_html( dck_home_val( 'dck_bible_lead', __( 'Read the Bible in English, Kiswahili and Kenyan languages, compare versions side by side, and ask our study assistant about any verse or topic.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<a class="dck-pill dck-pill--link" href="#read"><?php esc_html_e( 'Read the Bible', 'dck-elite' ); ?></a>
			<a class="dck-pill dck-pill--link" href="#ask"><?php esc_html_e( 'Ask the study assistant', 'dck-elite' ); ?></a>
			<span class="dck-pill"><?php echo esc_html( sprintf( _n( '%d version', '%d versions', count( dck_bible_versions() ), 'dck-elite' ), count( dck_bible_versions() ) ) ); ?></span>
		</div>
	</div>
</section>

<section class="dck-section dck-bible-section">
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

		<?php
		$dck_vers   = dck_bible_versions();
		$dck_groups = array();
		foreach ( $dck_vers as $v ) {
			$dck_groups[ $v['group'] ][] = $v;
		}
		?>
		<div class="dck-reader" id="read">
			<div class="dck-votd" id="dck-votd" hidden>
				<span class="dck-votd__k"><?php esc_html_e( 'Verse of the Day', 'dck-elite' ); ?></span>
				<blockquote class="dck-votd__t" id="dck-votd-t"></blockquote>
				<div class="dck-votd__f"><span id="dck-votd-r"></span><button type="button" class="dck-tool" id="dck-votd-open"><?php esc_html_e( 'Read chapter', 'dck-elite' ); ?></button></div>
			</div>

			<div class="dck-rbar" role="toolbar" aria-label="<?php esc_attr_e( 'Bible reader controls', 'dck-elite' ); ?>">
				<label class="dck-rbar__f dck-rbar__f--ver"><span><?php esc_html_e( 'Version', 'dck-elite' ); ?></span>
					<select id="dck-r-tr">
						<?php foreach ( $dck_groups as $g => $list ) : ?>
							<optgroup label="<?php echo esc_attr( $g ); ?>">
								<?php foreach ( $list as $v ) : ?>
									<option value="<?php echo esc_attr( $v['id'] ); ?>"><?php echo esc_html( $v['lang'] . ' - ' . $v['name'] ); ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="dck-rbar__f"><span><?php esc_html_e( 'Book', 'dck-elite' ); ?></span><select id="dck-r-book"></select></label>
				<label class="dck-rbar__f dck-rbar__f--ch"><span><?php esc_html_e( 'Chapter', 'dck-elite' ); ?></span><select id="dck-r-ch"></select></label>
				<label class="dck-rbar__f dck-rbar__f--ver"><span><?php esc_html_e( 'Compare with', 'dck-elite' ); ?></span>
					<select id="dck-r-tr2">
						<option value=""><?php esc_html_e( 'No second version', 'dck-elite' ); ?></option>
						<?php foreach ( $dck_groups as $g => $list ) : ?>
							<optgroup label="<?php echo esc_attr( $g ); ?>">
								<?php foreach ( $list as $v ) : ?>
									<option value="<?php echo esc_attr( $v['id'] ); ?>"><?php echo esc_html( $v['lang'] . ' - ' . $v['name'] ); ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
					</select>
				</label>
				<form class="dck-rbar__go" id="dck-r-go" role="search">
					<label class="screen-reader-text" for="dck-r-ref"><?php esc_html_e( 'Go to a reference', 'dck-elite' ); ?></label>
					<input type="search" id="dck-r-ref" placeholder="<?php esc_attr_e( 'Go to: John 3:16', 'dck-elite' ); ?>" autocomplete="off">
					<button type="submit" class="dck-btn dck-btn--primary"><?php esc_html_e( 'Go', 'dck-elite' ); ?></button>
				</form>
			</div>

			<div class="dck-rpage">
				<div class="dck-rpage__head">
					<button type="button" class="dck-rnav" id="dck-r-prev" aria-label="<?php esc_attr_e( 'Previous chapter', 'dck-elite' ); ?>">&larr;</button>
					<div class="dck-rpage__title"><h2 id="dck-r-title">&nbsp;</h2><p id="dck-r-sub"></p></div>
					<button type="button" class="dck-rnav" id="dck-r-next" aria-label="<?php esc_attr_e( 'Next chapter', 'dck-elite' ); ?>">&rarr;</button>
				</div>
				<div class="dck-rpage__tools">
					<button type="button" class="dck-tool" id="dck-r-smaller" aria-label="<?php esc_attr_e( 'Smaller text', 'dck-elite' ); ?>">A&minus;</button>
					<button type="button" class="dck-tool" id="dck-r-bigger" aria-label="<?php esc_attr_e( 'Larger text', 'dck-elite' ); ?>">A+</button>
					<button type="button" class="dck-tool" id="dck-r-mode"><?php esc_html_e( 'Night mode', 'dck-elite' ); ?></button>
					<button type="button" class="dck-tool" id="dck-r-listen"><?php esc_html_e( 'Listen', 'dck-elite' ); ?></button>
					<button type="button" class="dck-tool" id="dck-r-share"><?php esc_html_e( 'Share chapter', 'dck-elite' ); ?></button>
					<audio id="dck-r-audio" controls preload="none" hidden></audio>
				</div>
				<div class="dck-rtext" id="dck-r-text" aria-live="polite"></div>
				<div class="dck-rpage__foot">
					<button type="button" class="dck-btn dck-btn--outline" id="dck-r-prev2">&larr; <?php esc_html_e( 'Previous', 'dck-elite' ); ?></button>
					<button type="button" class="dck-btn dck-btn--primary" id="dck-r-next2"><?php esc_html_e( 'Next chapter', 'dck-elite' ); ?> &rarr;</button>
				</div>
				<p class="dck-rcredit" id="dck-r-credit"></p>
			</div>

			<div class="dck-vpop" id="dck-vpop" role="dialog" aria-label="<?php esc_attr_e( 'Verse actions', 'dck-elite' ); ?>" hidden>
				<button type="button" data-act="copy"><?php esc_html_e( 'Copy', 'dck-elite' ); ?></button>
				<button type="button" data-act="share"><?php esc_html_e( 'WhatsApp', 'dck-elite' ); ?></button>
				<button type="button" data-act="ask"><?php esc_html_e( 'Ask about this', 'dck-elite' ); ?></button>
				<button type="button" data-act="close" aria-label="<?php esc_attr_e( 'Close', 'dck-elite' ); ?>">&times;</button>
			</div>
		</div>

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
