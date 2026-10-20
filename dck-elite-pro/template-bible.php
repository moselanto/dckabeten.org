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
<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Grow in the Word', 'dck-elite' ); ?></p>
		<h1 data-i18n="bible_title"><?php esc_html_e( 'Bible Study Assistant', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" data-i18n="bible_lead" style="color:#e7ecff"><?php esc_html_e( 'Ask about any verse or topic and receive a clear, Scripture-grounded explanation with cross references, application, and prayer.', 'dck-elite' ); ?></p>
	</div>
</section>
<section class="dck-section">
	<div class="dck-container dck-bible">
		<?php
		while ( have_posts() ) :
			the_post();
			if ( trim( wp_strip_all_tags( get_the_content() ) ) ) :
				?>
				<div class="dck-reveal" style="margin-bottom:24px"><?php the_content(); ?></div>
				<?php
			endif;
		endwhile;
		?>

		<div class="dck-bible-ask dck-reveal">
			<textarea id="dck-bible-input" rows="2" data-i18n-ph="bible_ph" placeholder="<?php esc_attr_e( 'Ask about a verse (Romans 8:28) or a topic (anxiety, forgiveness, hope)...', 'dck-elite' ); ?>"></textarea>
			<button type="button" id="dck-bible-go" class="dck-btn dck-btn--primary" data-i18n="ask"><?php esc_html_e( 'Ask', 'dck-elite' ); ?></button>
		</div>

		<div class="dck-bible-chips dck-reveal">
			<span class="dck-bible-chips__label"><?php esc_html_e( 'Try asking about:', 'dck-elite' ); ?></span>
			<button type="button" class="dck-chip" data-ask="Romans 8:28">Romans 8:28</button>
			<button type="button" class="dck-chip" data-ask="Psalm 23">Psalm 23</button>
			<button type="button" class="dck-chip" data-ask="Philippians 4:6-7">Philippians 4:6-7</button>
			<button type="button" class="dck-chip" data-ask="Overcoming anxiety">Anxiety</button>
			<button type="button" class="dck-chip" data-ask="Forgiveness"><?php esc_html_e( 'Forgiveness', 'dck-elite' ); ?></button>
			<button type="button" class="dck-chip" data-ask="Finding hope in hard times"><?php esc_html_e( 'Hope', 'dck-elite' ); ?></button>
			<button type="button" class="dck-chip" data-ask="Growing in faith"><?php esc_html_e( 'Faith', 'dck-elite' ); ?></button>
			<button type="button" class="dck-chip" data-ask="The armor of God">The Armor of God</button>
		</div>

		<div class="dck-bible-levels dck-reveal">
			<span data-i18n="depth"><?php esc_html_e( 'Depth:', 'dck-elite' ); ?></span>
			<button type="button" class="dck-level is-active" data-level="Balanced" data-i18n="lvl_balanced"><?php esc_html_e( 'Balanced', 'dck-elite' ); ?></button>
			<button type="button" class="dck-level" data-level="Simple" data-i18n="lvl_simple"><?php esc_html_e( 'Simple', 'dck-elite' ); ?></button>
			<button type="button" class="dck-level" data-level="Youth" data-i18n="lvl_youth"><?php esc_html_e( 'Youth', 'dck-elite' ); ?></button>
			<button type="button" class="dck-level" data-level="Children" data-i18n="lvl_children"><?php esc_html_e( 'Children', 'dck-elite' ); ?></button>
			<button type="button" class="dck-level" data-level="Theological" data-i18n="lvl_theological"><?php esc_html_e( 'Theological', 'dck-elite' ); ?></button>
			<button type="button" class="dck-level" data-level="Original" data-i18n="lvl_original"><?php esc_html_e( 'Original Language', 'dck-elite' ); ?></button>
		</div>

		<div id="dck-bible-out" class="dck-bible-out"></div>
		<div class="dck-read dck-read--answer" data-read-scope hidden>
			<button type="button" class="dck-read__btn dck-btn dck-btn--outline" data-target="#dck-bible-out" data-label-idle="<?php esc_attr_e( 'Listen to the answer', 'dck-elite' ); ?>">
				<span class="dck-read__ic" aria-hidden="true">&#9654;</span>
				<span class="dck-read__label"><?php esc_html_e( 'Listen to the answer', 'dck-elite' ); ?></span>
			</button>
			<button type="button" class="dck-read__stop dck-btn dck-btn--outline" hidden><?php esc_html_e( 'Stop', 'dck-elite' ); ?></button>
		</div>
		<div id="dck-bible-sermons" class="dck-bible-sermons"></div>

		<p class="dck-bible-note"><?php esc_html_e( 'This assistant is a study aid to help you engage the Bible. Always read the full passage in context and grow with your local church. For pastoral care, reach us on +254 110 304 521.', 'dck-elite' ); ?></p>

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
	</div>
</section>

<script>
(function(){
	var input = document.getElementById("dck-bible-input");
	var go = document.getElementById("dck-bible-go");
	if (input === null || go === null) { return; }
	var chips = document.querySelectorAll(".dck-bible-chips .dck-chip");
	for (var i = 0; i < chips.length; i++) {
		chips[i].addEventListener("click", function(){
			var q = this.getAttribute("data-ask") || this.textContent;
			input.value = q;
			input.focus();
			go.click();
			var box = document.getElementById("dck-bible-out");
			if (box && box.scrollIntoView) { box.scrollIntoView({ behavior: "smooth", block: "nearest" }); }
		});
	}
})();
</script>
<?php
get_footer();
