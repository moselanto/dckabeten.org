<?php
/**
 * Template Name: DCK Services
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$img = get_template_directory_uri() . '/assets/images/gallery/';
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Gather With Us', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Our Services', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'Consistent spiritual nourishment and fellowship every week - on Sunday and throughout the week.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container dck-grid-2 dck-svc-band">
		<div class="dck-reveal dck-svc-band__media">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/services-worship.jpg' ); ?>" alt="<?php esc_attr_e( 'An open Bible in warm morning light', 'dck-elite' ); ?>" loading="lazy">
		</div>
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'A Rhythm of Worship', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Midweek &amp; Monthly Services', 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'Our midweek and monthly services provide consistent spiritual nourishment and fellowship beyond Sunday gatherings.', 'dck-elite' ); ?></p>
			<p>Every Tuesday and Thursday we hold impactful midweek services focused on prayer, Bible study, and worship - strengthening believers in their daily walk with Christ. Whatever day you join us, you will find a warm family and a living encounter with God.</p>
		</div>
	</div>
</section>

<section class="dck-section" style="background:var(--dck-blue-100)">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Every Sunday', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( "The Lord's Day at DCK", 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'Three gatherings flow into one another, from intercession to the Word to a glorious celebration of worship.', 'dck-elite' ); ?></p>
		</div>
		<div class="dck-schedule">
			<div class="dck-svc dck-reveal">
				<span class="dck-svc__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v6l4 2"/><circle cx="12" cy="12" r="9"/></svg></span>
				<h3 class="dck-svc__name"><?php esc_html_e( 'Intensive Prayers', 'dck-elite' ); ?></h3>
				<p class="dck-svc__time">8:00 - 9:00 AM</p>
				<p class="dck-svc__desc"><?php esc_html_e( 'Starting the day seeking God together in fervent prayer.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-svc dck-reveal">
				<span class="dck-svc__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M19 3v18"/></svg></span>
				<h3 class="dck-svc__name"><?php esc_html_e( 'Intensive Bible Study', 'dck-elite' ); ?></h3>
				<p class="dck-svc__time">9:30 - 10:30 AM</p>
				<p class="dck-svc__desc"><?php esc_html_e( 'Digging deep into the Word for understanding and growth.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-svc dck-reveal">
				<span class="dck-svc__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3l2.5 5 5.5.5-4 3.8 1.2 5.4L12 20l-4.2 2.7 1.2-5.4-4-3.8 5.5-.5z"/></svg></span>
				<h3 class="dck-svc__name"><?php esc_html_e( 'Glorious Service', 'dck-elite' ); ?></h3>
				<p class="dck-svc__time">10:40 AM - 1:00 PM</p>
				<p class="dck-svc__desc"><?php esc_html_e( 'Joyful worship, Spirit-filled preaching, and celebration.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-svc dck-reveal">
				<span class="dck-svc__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg></span>
				<h3 class="dck-svc__name"><?php esc_html_e( 'Keshas', 'dck-elite' ); ?></h3>
				<p class="dck-svc__time"><?php esc_html_e( '1st &amp; 3rd Week', 'dck-elite' ); ?></p>
				<p class="dck-svc__desc"><?php esc_html_e( 'Overnight prayer on the first and third week of each month.', 'dck-elite' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'During The Week', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Midweek Services', 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'There is a place to meet with God every few days - in the church and in our home churches.', 'dck-elite' ); ?></p>
		</div>
		<div class="dck-week">
			<div class="dck-week__row dck-reveal"><span class="dck-week__day"><?php esc_html_e( 'Tuesday', 'dck-elite' ); ?></span><span class="dck-week__time">6:00 - 7:00 PM</span><span class="dck-week__act"><?php esc_html_e( 'Prayers in the Church', 'dck-elite' ); ?></span></div>
			<div class="dck-week__row dck-reveal"><span class="dck-week__day"><?php esc_html_e( 'Thursday', 'dck-elite' ); ?></span><span class="dck-week__time">6:00 - 7:00 PM</span><span class="dck-week__act"><?php esc_html_e( 'Midweek Service in Church', 'dck-elite' ); ?></span></div>
			<div class="dck-week__row dck-reveal"><span class="dck-week__day"><?php esc_html_e( 'Friday &amp; Saturday', 'dck-elite' ); ?></span><span class="dck-week__time">6:00 - 7:00 PM</span><span class="dck-week__act"><?php esc_html_e( 'Home-Based Church Services', 'dck-elite' ); ?></span></div>
			<div class="dck-week__row dck-reveal"><span class="dck-week__day"><?php esc_html_e( 'Saturday', 'dck-elite' ); ?></span><span class="dck-week__time"><?php esc_html_e( 'From 4:00 PM', 'dck-elite' ); ?></span><span class="dck-week__act"><?php esc_html_e( 'Worship Team Practice in Church', 'dck-elite' ); ?></span></div>
		</div>
	</div>
</section>

<section class="dck-section dck-stats">
	<div class="dck-container dck-center">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Cannot Join In Person?', 'dck-elite' ); ?></p>
		<h2 style="color:#fff"><?php esc_html_e( 'Worship With Us Online', 'dck-elite' ); ?></h2>
		<p class="dck-lead dck-center" style="color:#d7deff"><?php esc_html_e( 'Our Glorious Service streams live on YouTube every Sunday. Join the family wherever you are.', 'dck-elite' ); ?></p>
		<div class="dck-hero__cta" style="margin-top:24px;justify-content:center">
			<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php esc_html_e( 'Watch Live', 'dck-elite' ); ?></a>
			<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
