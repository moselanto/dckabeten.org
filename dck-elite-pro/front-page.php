<?php
/**
 * Homepage (v2.2.2). All content editable via Customizer > DCK Homepage.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$dck_slides = function_exists( 'dck_home_slides' ) ? dck_home_slides() : array();
$dck_dir    = function_exists( 'dck_home_directions' ) ? dck_home_directions() : 'https://share.google/LJmcXS6D5Nq1aF2R5';
?>

<!-- HERO SLIDER -->
<section class="dck-hero2" role="region" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Welcome', 'dck-elite' ); ?>">
	<div class="dck-hero2__track">
		<?php
		$dck_i = 0;
		foreach ( $dck_slides as $s ) :
			$is_first = ( 0 === $dck_i );
			?>
			<div class="dck-hero2__slide<?php echo $is_first ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>">
				<img class="dck-hero2__bg<?php echo $is_first ? ' skip-lazy' : ''; ?>" src="<?php echo esc_url( $s['img'] ); ?>" alt="" aria-hidden="true" width="1600" height="900" decoding="async" <?php echo $is_first ? 'fetchpriority="high" loading="eager" data-no-lazy="1"' : 'loading="lazy"'; ?>>
				<div class="dck-hero2__veil"></div>
				<div class="dck-container dck-hero2__inner">
					<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php echo esc_html( $s['eyebrow'] ); ?></p>
					<?php if ( $is_first ) : ?>
						<h1 class="dck-hero2__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php else : ?>
						<h2 class="dck-hero2__title"><?php echo esc_html( $s['title'] ); ?></h2>
					<?php endif; ?>
					<p class="dck-hero2__text"><?php echo esc_html( $s['text'] ); ?></p>
					<div class="dck-hero__cta">
						<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php echo esc_html( dck_home_val( 'dck_btn_plan_visit', 'Plan Your Visit' ) ); ?></a>
						<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'live' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_watch_live', 'Watch Live' ) ); ?></a>
					</div>
				</div>
			</div>
			<?php
			$dck_i++;
		endforeach;
		?>
	</div>
	<?php if ( count( $dck_slides ) > 1 ) : ?>
		<button class="dck-hero2__nav dck-hero2__prev" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'dck-elite' ); ?>">&#8249;</button>
		<button class="dck-hero2__nav dck-hero2__next" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'dck-elite' ); ?>">&#8250;</button>
		<div class="dck-hero2__dots">
			<?php for ( $d = 0; $d < count( $dck_slides ); $d++ ) : ?>
				<button class="dck-dot<?php echo 0 === $d ? ' is-active' : ''; ?>" type="button" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'dck-elite' ), $d + 1 ) ); ?>"></button>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
</section>

<!-- WELCOME (2 columns, no image) -->
<section class="dck-section">
	<div class="dck-container dck-grid-2">
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php echo esc_html( dck_home_val( 'dck_welcome_eyebrow', 'A Word of Welcome' ) ); ?></p>
			<h2><?php echo esc_html( dck_home_val( 'dck_welcome_heading', 'Karibu Home, You Belong Here' ) ); ?></h2>
			<p class="dck-lead"><?php echo esc_html( dck_home_val( 'dck_lead_text', 'Whether you are visiting for the first time or looking for a church family, there is a place for you at Deliverance Church Kabete N (DCK-N). We are a Christ-centered community founded on prayer, evangelism and revival, believing God for transformed lives and a transformed nation.' ) ); ?></p>
			<p><?php echo esc_html( dck_home_val( 'dck_welcome_pastors', 'Led by Pastor Justus Kubai and Mrs. Clare Kubai, with Assistant Pastors Mr. and Mrs. Henry Onzere.' ) ); ?></p>
			<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'about' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_our_story', 'Our Story' ) ); ?></a>
		</div>
		<div class="dck-reveal">
			<div class="dck-card" style="border-left:5px solid var(--dck-gold)">
				<p class="dck-card__tag"><?php echo esc_html( dck_home_val( 'dck_about_since', 'Since 2017' ) ); ?></p>
				<h3><?php echo esc_html( dck_home_val( 'dck_about_faith_heading', 'Built by Faith' ) ); ?></h3>
				<p><?php echo esc_html( dck_home_val( 'dck_about_faith_text', 'From a first prayer meeting on 9 June 2017 and a launch on 18 June 2017, God has grown Deliverance Church Kabete N (DCK-N) from a small sanctuary in N Market into a thriving church family in Uthiru, building faithfully on ground once undeveloped.' ) ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- STATS (real numbers are server-rendered so search engines, AI tools and no-JS visitors see them; JS only animates) -->
<section class="dck-section dck-stats">
	<div class="dck-container">
		<div class="dck-grid">
			<?php foreach ( ( function_exists( 'dck_home_stats' ) ? dck_home_stats() : array() ) as $st ) : ?>
				<div class="dck-reveal"><div class="dck-stat__num" data-dck-count="<?php echo esc_attr( $st['num'] ); ?>" data-dck-suffix="<?php echo esc_attr( $st['suffix'] ); ?>"><?php echo esc_html( number_format_i18n( $st['num'] ) . $st['suffix'] ); ?></div><div class="dck-stat__label"><?php echo esc_html( $st['label'] ); ?></div></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- MINISTRY DEPARTMENTS (12 shown; full list on View All Ministries) -->
<section class="dck-section dck-min" id="ministries">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php echo esc_html( dck_home_val( 'dck_min_eyebrow', 'Get Involved' ) ); ?></p>
			<h2><?php echo esc_html( dck_home_val( 'dck_min_heading', 'Our Ministry Departments' ) ); ?></h2>
			<p class="dck-lead"><?php echo esc_html( dck_home_val( 'dck_min_lead', 'There is a family for every season of life. These are the departments that drive our growth and impact.' ) ); ?></p>
		</div>
		<div class="dck-min__grid">
			<?php foreach ( array_slice( dck_home_ministries(), 0, 12 ) as $m ) : ?>
				<div class="dck-min__card dck-reveal">
					<div class="dck-min__photo">
						<img src="<?php echo esc_url( $m['img'] ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" loading="lazy" width="180" height="180">
					</div>
					<h3 class="dck-min__name"><?php echo esc_html( $m['name'] ); ?></h3>
					<p class="dck-min__desc"><?php echo esc_html( $m['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="dck-center" style="margin-top:40px">
			<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'ministries' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_all_ministries', 'View All Ministries' ) ); ?></a>
		</div>
	</div>
</section>

<!-- CARE + PROTOCOL -->
<section class="dck-section dck-dept">
	<div class="dck-container dck-dept__grid">
		<div class="dck-dept__card dck-reveal">
			<div class="dck-dept__photo">
				<img src="<?php echo esc_url( dck_home_img( 'dck_care_img', 'careministry.webp' ) ); ?>" alt="<?php echo esc_attr( dck_home_val( 'dck_care_title', 'Care Ministry' ) ); ?>" loading="lazy">
			</div>
			<div class="dck-dept__body">
				<h3><?php echo esc_html( dck_home_val( 'dck_care_title', 'Care Ministry' ) ); ?></h3>
				<p><?php echo esc_html( dck_home_val( 'dck_care_text', 'Handles ushering and hospitality, ensuring a welcoming experience for all.' ) ); ?></p>
			</div>
		</div>
		<div class="dck-dept__card dck-dept__card--accent dck-reveal">
			<div class="dck-dept__photo">
				<img src="<?php echo esc_url( dck_home_img( 'dck_protocol_img', 'protocal.webp' ) ); ?>" alt="<?php echo esc_attr( dck_home_val( 'dck_protocol_title', 'Protocol Department' ) ); ?>" loading="lazy">
			</div>
			<div class="dck-dept__body">
				<h3><?php echo esc_html( dck_home_val( 'dck_protocol_title', 'Protocol Department' ) ); ?></h3>
				<p><?php echo esc_html( dck_home_val( 'dck_protocol_text', 'Oversees the welfare and honor of our pastors and visiting gospel ministers.' ) ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- JOIN US THIS SUNDAY -->
<section class="dck-section dck-sunday2">
	<div class="dck-container dck-center">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php echo esc_html( dck_home_val( 'dck_sunday_eyebrow', 'Join Us This Sunday' ) ); ?></p>
		<h2><?php echo esc_html( dck_home_val( 'dck_sunday_heading', 'A Joyful Sunday Gathering' ) ); ?></h2>
		<p class="dck-sunday2__tag"><?php echo esc_html( dck_home_val( 'dck_sunday_tag2', 'Experience faith, fellowship, and inspiration every Sunday. Wherever you are on your journey, there is a place for you at the table.' ) ); ?></p>
		<div class="dck-sunday2__grid">
			<?php foreach ( dck_elite_service_times() as $s ) : ?>
				<div class="dck-sunday2__card dck-reveal">
					<span class="dck-sunday2__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
					<h3 class="dck-sunday2__name"><?php echo esc_html( $s['name'] ); ?></h3>
					<p class="dck-sunday2__time"><?php echo esc_html( $s['time'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="dck-sunday2__cta">
			<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php echo esc_html( dck_home_val( 'dck_btn_plan_visit', 'Plan Your Visit' ) ); ?></a>
		</div>
	</div>
</section>

<!-- FAMILY CTA -->
<section class="dck-cta2">
	<div class="dck-container">
		<div class="dck-cta2__panel dck-reveal">
			<div class="dck-cta2__copy">
				<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php echo esc_html( dck_home_val( 'dck_cta_eyebrow', 'Come As You Are' ) ); ?></p>
				<h2><?php echo esc_html( dck_home_val( 'dck_cta_title', 'Become Part of Our Family' ) ); ?></h2>
				<p><?php echo esc_html( dck_home_val( 'dck_cta_text', 'Plan your first visit, join a ministry, or give toward the work God is doing at DCK-N. There is a place for you here.' ) ); ?></p>
			</div>
			<div class="dck-cta2__btns">
				<a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php echo esc_html( dck_home_val( 'dck_btn_plan_visit', 'Plan Your Visit' ) ); ?></a>
				<a class="dck-btn dck-btn--ghost" href="<?php echo esc_url( dck_elite_page_url( 'give' ) ); ?>"><?php echo esc_html( dck_home_val( 'dck_btn_give_online', 'Give Online' ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
