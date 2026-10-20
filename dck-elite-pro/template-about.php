<?php
/**
 * Template Name: DCK About Us
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_about_photo = dck_theme_image( 'dck_about_image', 'assets/images/about-front.webp' );
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Who We Are', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'About Our Church', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'A Cistern of Hope in the heart of Uthiru, Nairobi, leading every generation into a growing relationship with Christ.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container dck-grid-2 dck-about__intro">
		<div class="dck-reveal dck-about__media">
			<img src="<?php echo esc_url( $dck_about_photo ); ?>" alt="<?php esc_attr_e( 'Deliverance Church Kabete N Market congregation', 'dck-elite' ); ?>" loading="lazy">
			<span class="dck-about__badge"><?php esc_html_e( 'Est. 2017', 'dck-elite' ); ?></span>
		</div>
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'Our Story', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Founded in Faith, Growing in Grace', 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'Deliverance Church Kabete N Market (Cistern of Hope Center) was founded by Pastor Justus Kubai and Mrs. Clare Kubai, after years of faithful service at Deliverance Church Kangemi and Muthangari from 2002 to 2017.', 'dck-elite' ); ?></p>
			<p>In obedience to God's call, they planted a church in N Market, Uthiru. Our journey began with a prayer meeting on 9 June 2017, our first Sunday service on 11 June 2017, and the official launch on 18 June 2017 by Rev. Ben Luvutse.</p>
			<p>In 2020, during the COVID-19 pandemic, we moved to our current site. By God's grace and the faith of our members, we continue to build a house of hope for the whole family.</p>
		</div>
	</div>
</section>

<section class="dck-mission">
	<div class="dck-container dck-center">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Our Mission', 'dck-elite' ); ?></p>
		<p class="dck-mission__text">&ldquo;<?php esc_html_e( 'Reaching out and leading generations into a growing relationship with Christ through the Word of God.', 'dck-elite' ); ?>&rdquo;</p>
		<p class="dck-mission__motto"><?php esc_html_e( 'Small enough to know you, big enough to accommodate you.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'What We Stand For', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Our Core Values', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-values">
			<div class="dck-value dck-reveal"><span class="dck-value__no">01</span><h3><?php esc_html_e( 'Bible Teaching', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'Sound, Scripture-based teaching that equips every believer to grow and serve.', 'dck-elite' ); ?></p></div>
			<div class="dck-value dck-reveal"><span class="dck-value__no">02</span><h3><?php esc_html_e( 'Prayer', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'A praying church that seeks God together through intercession and overnight Keshas.', 'dck-elite' ); ?></p></div>
			<div class="dck-value dck-reveal"><span class="dck-value__no">03</span><h3><?php esc_html_e( 'Evangelism', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'Reaching our community and the nations with the hope of the Gospel.', 'dck-elite' ); ?></p></div>
			<div class="dck-value dck-reveal"><span class="dck-value__no">04</span><h3><?php esc_html_e( 'Worship', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'Joyful, heartfelt worship that exalts the name of Jesus Christ.', 'dck-elite' ); ?></p></div>
			<div class="dck-value dck-reveal"><span class="dck-value__no">05</span><h3><?php esc_html_e( 'Excellence', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'Giving God our very best in all that we do, with diligence and care.', 'dck-elite' ); ?></p></div>
			<div class="dck-value dck-reveal"><span class="dck-value__no">06</span><h3><?php esc_html_e( 'Integrity', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'Living honestly and faithfully before God and before people.', 'dck-elite' ); ?></p></div>
		</div>
	</div>
</section>

<section class="dck-section" style="background:var(--dck-blue-100)">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Our Journey', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Milestones of Grace', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-timeline">
			<div class="dck-tl dck-reveal"><span class="dck-tl__date">9 Jun 2017</span><h3><?php esc_html_e( 'First Prayer Meeting', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'A small gathering seeking God for a new work in Uthiru.', 'dck-elite' ); ?></p></div>
			<div class="dck-tl dck-reveal"><span class="dck-tl__date">11 Jun 2017</span><h3><?php esc_html_e( 'First Sunday Service', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'The congregation held its very first Sunday worship service.', 'dck-elite' ); ?></p></div>
			<div class="dck-tl dck-reveal"><span class="dck-tl__date">18 Jun 2017</span><h3><?php esc_html_e( 'Official Launch', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'The church was officially launched by Rev. Ben Luvutse.', 'dck-elite' ); ?></p></div>
			<div class="dck-tl dck-reveal"><span class="dck-tl__date">2020</span><h3><?php esc_html_e( 'A New Home', 'dck-elite' ); ?></h3><p><?php esc_html_e( 'We moved to our current site behind N Market and kept building by faith.', 'dck-elite' ); ?></p></div>
		</div>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container dck-center">
		<p class="dck-eyebrow"><?php esc_html_e( 'Our Leadership', 'dck-elite' ); ?></p>
		<h2><?php esc_html_e( 'Shepherds of the House', 'dck-elite' ); ?></h2>
		<p class="dck-lead dck-center"><?php esc_html_e( 'Lead Pastors Justus and Clare Kubai, together with Assistant Pastors Henry and Nancy Onzere, faithfully shepherd our church family.', 'dck-elite' ); ?></p>
		<div class="dck-center" style="margin-top:24px">
			<a class="dck-btn dck-btn--primary" href="<?php echo esc_url( dck_elite_page_url( 'our-pastors' ) ); ?>"><?php esc_html_e( 'Meet Our Pastors', 'dck-elite' ); ?></a>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<?php
get_footer();
