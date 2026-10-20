<?php
/**
 * Template Name: DCK Our Pastors
 *
 * Leadership page. Names, roles, focus areas, biographies and photos are
 * editable in DCK Church > About & Pastors.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_ps     = dck_pastors();
$dck_wa     = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
$dck_couple = dck_theme_image( 'dck_pastors_couple_img', 'assets/images/pastors/lead-couple.webp' );
$dck_years  = (int) wp_date( 'Y' ) - 2017 - ( (int) wp_date( 'n' ) < 6 ? 1 : 0 );
$dck_lead   = isset( $dck_ps[0] ) ? $dck_ps[0] : null;
$dck_co     = isset( $dck_ps[1] ) ? $dck_ps[1] : null;

/* Person structured data for search engines and AI assistants. */
$dck_ld = array();
foreach ( $dck_ps as $p ) {
	$dck_ld[] = array(
		'@type'       => 'Person',
		'name'        => $p['name'],
		'jobTitle'    => $p['role'],
		'description' => $p['lead'],
		'image'       => $p['img'],
		'worksFor'    => array( '@type' => 'Church', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ),
		'url'         => get_permalink() . '#pastor-' . $p['k'],
	);
}
?>
<script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $dck_ld ) ); ?></script>

<section class="dck-lhero">
	<div class="dck-lhero__inner">
		<div class="dck-lhero__copy">
			<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Our Leadership', 'dck-elite' ); ?></p>
			<h1><?php echo esc_html( dck_home_val( 'dck_pastors_title', __( 'Meet Our Pastors', 'dck-elite' ) ) ); ?></h1>
			<p class="dck-lhero__lead"><?php echo esc_html( dck_home_val( 'dck_pastors_lead', __( 'Shepherds with a heart for God and for people, faithfully leading Deliverance Church Kabete N Market since 2017.', 'dck-elite' ) ) ); ?></p>
			<ul class="dck-lhero__facts">
				<li><strong><?php echo esc_html( count( $dck_ps ) ); ?></strong><span><?php esc_html_e( 'Pastors', 'dck-elite' ); ?></span></li>
				<li><strong><?php echo esc_html( max( 1, $dck_years ) ); ?>+</strong><span><?php esc_html_e( 'Years leading DCK-N', 'dck-elite' ); ?></span></li>
				<li><strong>2017</strong><span><?php esc_html_e( 'Church planted', 'dck-elite' ); ?></span></li>
			</ul>
			<div class="dck-lhero__btns">
				<a class="dck-btn dck-btn--gold" href="#team"><?php esc_html_e( 'Meet the team', 'dck-elite' ); ?></a>
				<?php if ( $dck_wa ) : ?>
					<a class="dck-btn dck-btn--ghost" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello, I would like to speak with a pastor.', 'dck-elite' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Talk to a pastor', 'dck-elite' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<figure class="dck-lhero__photo">
			<img src="<?php echo esc_url( $dck_couple ); ?>" alt="<?php echo esc_attr( $dck_lead && $dck_co ? $dck_lead['name'] . ' & ' . $dck_co['name'] : get_bloginfo( 'name' ) ); ?>" width="550" height="654" fetchpriority="high" decoding="async">
			<?php if ( $dck_lead && $dck_co ) : ?>
				<figcaption><strong><?php echo esc_html( $dck_lead['name'] . ' & ' . $dck_co['name'] ); ?></strong><span><?php esc_html_e( 'Founders & Lead Pastors', 'dck-elite' ); ?></span></figcaption>
			<?php endif; ?>
		</figure>
	</div>
</section>

<?php if ( $dck_lead ) : ?>
<section class="dck-section dck-lstory">
	<div class="dck-container dck-lstory__grid">
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'A Calling Answered', 'dck-elite' ); ?></p>
			<h2><?php echo esc_html( dck_home_val( 'dck_pastors_story_title', __( 'From Kangemi to Uthiru, by faith', 'dck-elite' ) ) ); ?></h2>
			<p class="dck-lead"><?php echo esc_html( dck_home_val( 'dck_pastors_story', __( 'After years of faithful service at Deliverance Church Kangemi and Muthangari, Pastor Justus and Pastor Clare obeyed God\'s call to plant a church in N Market, Uthiru. What began with a prayer meeting on 9 June 2017 has grown into a vibrant church family with fourteen ministry departments.', 'dck-elite' ) ) ); ?></p>
		</div>
		<blockquote class="dck-lquote dck-reveal">
			<p><?php echo esc_html( dck_home_val( 'dck_about_mission', __( 'Reaching out and leading generations into a growing relationship with Christ through the Word of God.', 'dck-elite' ) ) ); ?></p>
			<cite><?php esc_html_e( 'Our mission', 'dck-elite' ); ?></cite>
		</blockquote>
	</div>
</section>
<?php endif; ?>

<section class="dck-section dck-team" id="team">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Pastoral Team', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'The Shepherds of Our House', 'dck-elite' ); ?></h2>
			<p class="dck-lead dck-center"><?php esc_html_e( 'Tap a profile to read the full story.', 'dck-elite' ); ?></p>
		</div>
		<div class="dck-team__grid">
			<?php foreach ( $dck_ps as $i => $p ) : ?>
				<article class="dck-pro dck-reveal<?php echo $i < 2 ? ' dck-pro--lead' : ''; ?>" id="pastor-<?php echo esc_attr( $p['k'] ); ?>">
					<button type="button" class="dck-pro__open" data-pastor="<?php echo esc_attr( $p['k'] ); ?>" aria-haspopup="dialog" aria-label="<?php echo esc_attr( sprintf( __( 'Read %s\'s profile', 'dck-elite' ), $p['name'] ) ); ?>">
						<span class="dck-pro__photo"><?php echo $p['photo']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in dck_pastor_photo_html. ?></span>
						<span class="dck-pro__role"><?php echo esc_html( $p['role'] ); ?></span>
					</button>
					<div class="dck-pro__body">
						<h3><?php echo esc_html( $p['name'] ); ?></h3>
						<p class="dck-pro__title"><?php echo esc_html( $p['title'] ); ?></p>
						<p class="dck-pro__lead"><?php echo esc_html( $p['lead'] ); ?></p>
						<?php if ( $p['focus'] ) : ?>
							<ul class="dck-pro__tags">
								<?php foreach ( $p['focus'] as $f ) : ?><li><?php echo esc_html( $f ); ?></li><?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<button type="button" class="dck-pro__more" data-pastor="<?php echo esc_attr( $p['k'] ); ?>" aria-haspopup="dialog"><?php esc_html_e( 'Read full profile', 'dck-elite' ); ?> <span aria-hidden="true">&rarr;</span></button>
					</div>
					<template id="dck-bio-<?php echo esc_attr( $p['k'] ); ?>">
						<div class="dck-pdlg__head">
							<img src="<?php echo esc_url( $p['img'] ); ?>" alt="" class="dck-pdlg__img" loading="lazy" decoding="async">
							<div>
								<span class="dck-pro__role dck-pro__role--static"><?php echo esc_html( $p['role'] ); ?></span>
								<h2 id="dck-pdlg-title"><?php echo esc_html( $p['name'] ); ?></h2>
								<p class="dck-pro__title"><?php echo esc_html( $p['title'] ); ?></p>
								<?php if ( $p['focus'] ) : ?>
									<ul class="dck-pro__tags"><?php foreach ( $p['focus'] as $f ) : ?><li><?php echo esc_html( $f ); ?></li><?php endforeach; ?></ul>
								<?php endif; ?>
							</div>
						</div>
						<div class="dck-pdlg__bio"><?php echo wp_kses_post( wpautop( $p['bio'] ? $p['bio'] : $p['lead'] ) ); ?></div>
						<div class="dck-pdlg__foot">
							<?php if ( $dck_wa ) : ?>
								<a class="dck-btn dck-btn--gold" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( sprintf( __( 'Hello, I would like to speak with %s.', 'dck-elite' ), $p['name'] ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Request a conversation', 'dck-elite' ); ?></a>
							<?php endif; ?>
							<a class="dck-btn dck-btn--outline" href="<?php echo esc_url( dck_elite_page_url( 'prayer-requests' ) ); ?>"><?php esc_html_e( 'Send a prayer request', 'dck-elite' ); ?></a>
						</div>
					</template>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( $dck_lead ) : ?>
<section class="dck-section dck-cred">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Equipped To Serve', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Training, Experience and Impact', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-cred__grid">
			<?php
			$dck_creds = dck_lines(
				'dck_pastors_credentials',
				"Diploma in Theology|KAG East University\nCertified Church Planting Trainer|Dynamic Church Planting International (DCPI)\nLeadership Training|Sentinels for Christ, Colorado, USA\nMission Work|Life Ministry\nFounder & Executive Director|Cistern of Hope Foundation\nMarriage Therapist & Counselor|Helping couples and families build healthy homes",
				2
			);
			foreach ( $dck_creds as $c ) :
				?>
				<div class="dck-cred__item dck-reveal">
					<span class="dck-cred__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 15l-3.5 2 1-4-3-2.7 4-.3L12 6l1.5 4 4 .3-3 2.7 1 4z"/><circle cx="12" cy="12" r="10"/></svg></span>
					<div><strong><?php echo esc_html( $c[0] ); ?></strong><span><?php echo esc_html( $c[1] ); ?></span></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="dck-section dck-found">
	<div class="dck-container">
		<div class="dck-found__card dck-reveal">
			<div>
				<p class="dck-eyebrow dck-eyebrow--light"><?php esc_html_e( 'Beyond The Pulpit', 'dck-elite' ); ?></p>
				<h2><?php echo esc_html( dck_home_val( 'dck_pastors_found_title', __( 'Cistern of Hope Foundation', 'dck-elite' ) ) ); ?></h2>
				<p><?php echo esc_html( dck_home_val( 'dck_pastors_found_text', __( 'Founded by Pastor Justus Kubai, the foundation uplifts marginalised communities in Kenya through technical skills training, vocational counselling for youth, and small-business programmes that empower women and persons with disabilities.', 'dck-elite' ) ) ); ?></p>
			</div>
			<ul class="dck-found__list">
				<li><?php esc_html_e( 'Technical skills training', 'dck-elite' ); ?></li>
				<li><?php esc_html_e( 'Vocational counselling for youth', 'dck-elite' ); ?></li>
				<li><?php esc_html_e( 'Small-business programmes for women', 'dck-elite' ); ?></li>
				<li><?php esc_html_e( 'Empowering persons with disabilities', 'dck-elite' ); ?></li>
			</ul>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="dck-section dck-care">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Pastoral Care', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'How Our Pastors Can Walk With You', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-gp">
			<?php
			$dck_care = dck_lines( 'dck_pastors_care', "Prayer|We will pray with you, in person, by phone or on WhatsApp.\nCounselling|Marriage, family and personal counselling grounded in Scripture.\nNew believers|Guidance for your first steps with Jesus, including baptism.\nLife events|Weddings, dedications, hospital visits and times of loss.", 2 );
			foreach ( $dck_care as $i => $c ) :
				?>
				<div class="dck-gp__item dck-reveal">
					<span class="dck-gp__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<h3><?php echo esc_html( $c[0] ); ?></h3>
					<p><?php echo esc_html( $c[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="dck-sermons-cta dck-reveal" style="margin-top:40px">
			<div>
				<h3><?php esc_html_e( 'Would you like to speak with a pastor?', 'dck-elite' ); ?></h3>
				<p><?php esc_html_e( 'Wherever you are in the world, reach out. A real person will respond.', 'dck-elite' ); ?></p>
			</div>
			<div class="dck-sermons-cta__btns">
				<?php if ( $dck_wa ) : ?>
					<a class="dck-btn dck-btn--gold" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello, I would like to speak with a pastor.', 'dck-elite' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp a pastor', 'dck-elite' ); ?></a>
				<?php endif; ?>
				<a class="dck-btn dck-btn--ghost" href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( sprintf( __( 'Call %s', 'dck-elite' ), dck_phone_display() ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<dialog class="dck-pdlg" id="dck-pdlg" aria-labelledby="dck-pdlg-title">
	<button type="button" class="dck-pdlg__x" id="dck-pdlg-x" aria-label="<?php esc_attr_e( 'Close', 'dck-elite' ); ?>">&times;</button>
	<div class="dck-pdlg__content" id="dck-pdlg-content"></div>
	<nav class="dck-pdlg__nav">
		<button type="button" class="dck-tool" id="dck-pdlg-prev">&larr; <?php esc_html_e( 'Previous', 'dck-elite' ); ?></button>
		<button type="button" class="dck-tool" id="dck-pdlg-next"><?php esc_html_e( 'Next', 'dck-elite' ); ?> &rarr;</button>
	</nav>
</dialog>

<?php
get_footer();
