<?php
/**
 * Template Name: DCK Give Online
 *
 * Giving page: M-Pesa Paybill / Till, bank transfer and giving from abroad.
 * Every number and line of text is editable in DCK Church > Giving.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'dck-give', get_template_directory_uri() . '/assets/js/give.js', array(), DCK_ELITE_VERSION, true );
wp_localize_script( 'dck-give', 'DCK_GIVE', array( 'copied' => __( 'Copied', 'dck-elite' ), 'copyAll' => __( 'Details copied', 'dck-elite' ) ) );
get_header();

$dck_wa = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '';
$g_pb   = dck_give_val( 'dck_give_paybill' );
$g_acc  = dck_give_val( 'dck_give_paybill_account' );
$g_till = dck_give_val( 'dck_give_till' );
$g_bank = dck_give_val( 'dck_give_bank_name' );
$g_name = dck_give_val( 'dck_give_bank_account_name' );
$g_num  = dck_give_val( 'dck_give_bank_account' );
$g_sw   = dck_give_val( 'dck_give_bank_swift' );
$g_br   = dck_give_val( 'dck_give_bank_branch' );

$dck_purposes = array();
foreach ( explode( "\n", (string) dck_home_val( 'dck_give_purposes', "Tithes|Honour God with the first tenth of your increase, as taught in Malachi 3:10.\nOfferings|Freewill gifts of thanksgiving and worship every Sunday.\nChurch Building|Help us keep building our sanctuary in Uthiru, a house of prayer for many.\nMissions and Evangelism|Carry the Gospel to new places through outreach and soul winning.\nWelfare and Care|Support members and neighbours in need with compassion and practical help." ) ) as $line ) {
	$parts = array_map( 'trim', explode( '|', $line, 2 ) );
	if ( '' !== $parts[0] ) {
		$dck_purposes[] = array( 't' => $parts[0], 'd' => isset( $parts[1] ) ? $parts[1] : '' );
	}
}

/* Tabs: only channels that have details. */
$dck_tabs = array();
if ( $g_pb ) {
	$dck_tabs['paybill'] = __( 'M-Pesa Paybill', 'dck-elite' );
}
if ( $g_till ) {
	$dck_tabs['till'] = __( 'M-Pesa Till', 'dck-elite' );
}
if ( $g_num ) {
	$dck_tabs['bank'] = __( 'Bank Transfer', 'dck-elite' );
	$dck_tabs['abroad'] = __( 'From Abroad', 'dck-elite' );
}
$dck_first = key( $dck_tabs );

if ( ! function_exists( 'dck_give_row' ) ) {
	function dck_give_row( $label, $value, $big = false ) {
		if ( '' === (string) $value ) {
			return;
		}
		echo '<div class="dck-gv-row' . ( $big ? ' dck-gv-row--big' : '' ) . '"><span class="dck-gv-row__k">' . esc_html( $label ) . '</span><span class="dck-gv-row__v">' . esc_html( $value ) . '</span><button type="button" class="dck-copy" data-copy="' . esc_attr( $value ) . '" aria-label="' . esc_attr( sprintf( __( 'Copy %s', 'dck-elite' ), $label ) ) . '">' . esc_html__( 'Copy', 'dck-elite' ) . '</button></div>';
	}
}
?>

<section class="dck-page-hero dck-give-hero">
	<div class="dck-container">
		<p class="dck-eyebrow dck-eyebrow--light"><?php echo esc_html( dck_home_val( 'dck_give_eyebrow', __( 'Partner With Us', 'dck-elite' ) ) ); ?></p>
		<h1><?php echo esc_html( dck_home_val( 'dck_give_title', __( 'Give Online', 'dck-elite' ) ) ); ?></h1>
		<p class="dck-lead dck-center dck-lead--light"><?php echo esc_html( dck_home_val( 'dck_give_lead', __( 'Your generosity advances the Gospel, cares for people, and builds the house of God. Give simply and securely, from Kenya or anywhere in the world.', 'dck-elite' ) ) ); ?></p>
		<div class="dck-legal-hero__meta">
			<span class="dck-pill"><?php esc_html_e( 'Official church accounts only', 'dck-elite' ); ?></span>
			<span class="dck-pill"><?php esc_html_e( 'M-Pesa and bank', 'dck-elite' ); ?></span>
			<span class="dck-pill"><?php esc_html_e( 'Give from anywhere', 'dck-elite' ); ?></span>
		</div>
	</div>
</section>

<?php if ( $dck_tabs ) : ?>
<section class="dck-section dck-give-now" id="give-now">
	<div class="dck-container">
		<div class="dck-gv">
			<div class="dck-gv__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Ways to give', 'dck-elite' ); ?>">
				<?php foreach ( $dck_tabs as $k => $label ) : ?>
					<button type="button" role="tab" class="dck-gv__tab<?php echo $k === $dck_first ? ' is-active' : ''; ?>" id="tab-<?php echo esc_attr( $k ); ?>" aria-controls="panel-<?php echo esc_attr( $k ); ?>" aria-selected="<?php echo $k === $dck_first ? 'true' : 'false'; ?>" tabindex="<?php echo $k === $dck_first ? '0' : '-1'; ?>" data-tab="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>

			<?php if ( $g_pb ) : ?>
			<div class="dck-gv__panel" role="tabpanel" id="panel-paybill" aria-labelledby="tab-paybill"<?php echo 'paybill' === $dck_first ? '' : ' hidden'; ?>>
				<div class="dck-gv__details">
					<span class="dck-card__tag"><?php esc_html_e( 'Tithes and Offerings', 'dck-elite' ); ?></span>
					<h2><?php esc_html_e( 'Lipa na M-Pesa Paybill', 'dck-elite' ); ?></h2>
					<?php dck_give_row( __( 'Business number', 'dck-elite' ), $g_pb, true ); ?>
					<?php dck_give_row( __( 'Account number', 'dck-elite' ), $g_acc, true ); ?>
				</div>
				<ol class="dck-gv__steps">
					<li><?php esc_html_e( 'Open M-Pesa and select Lipa na M-Pesa, then Pay Bill.', 'dck-elite' ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'Enter Business Number %s.', 'dck-elite' ), $g_pb ) ); ?></li>
					<?php if ( $g_acc ) : ?><li><?php echo esc_html( sprintf( __( 'Enter Account Number %s.', 'dck-elite' ), $g_acc ) ); ?></li><?php endif; ?>
					<li><?php esc_html_e( 'Enter the amount and your PIN, check the recipient name, then confirm.', 'dck-elite' ); ?></li>
					<li><?php esc_html_e( 'Keep the M-Pesa confirmation SMS as your record.', 'dck-elite' ); ?></li>
				</ol>
			</div>
			<?php endif; ?>

			<?php if ( $g_till ) : ?>
			<div class="dck-gv__panel" role="tabpanel" id="panel-till" aria-labelledby="tab-till"<?php echo 'till' === $dck_first ? '' : ' hidden'; ?>>
				<div class="dck-gv__details">
					<span class="dck-card__tag"><?php esc_html_e( 'Give To A Project', 'dck-elite' ); ?></span>
					<h2><?php esc_html_e( 'M-Pesa Buy Goods (Till)', 'dck-elite' ); ?></h2>
					<?php dck_give_row( __( 'Till number', 'dck-elite' ), $g_till, true ); ?>
				</div>
				<ol class="dck-gv__steps">
					<li><?php esc_html_e( 'Open M-Pesa and select Lipa na M-Pesa, then Buy Goods and Services.', 'dck-elite' ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'Enter Till Number %s.', 'dck-elite' ), $g_till ) ); ?></li>
					<li><?php esc_html_e( 'Enter the amount and your PIN, check the recipient name, then confirm.', 'dck-elite' ); ?></li>
					<li><?php esc_html_e( 'Keep the M-Pesa confirmation SMS as your record.', 'dck-elite' ); ?></li>
				</ol>
			</div>
			<?php endif; ?>

			<?php if ( $g_num ) : ?>
			<div class="dck-gv__panel" role="tabpanel" id="panel-bank" aria-labelledby="tab-bank"<?php echo 'bank' === $dck_first ? '' : ' hidden'; ?>>
				<div class="dck-gv__details">
					<span class="dck-card__tag"><?php esc_html_e( 'Deposits, Transfers and Cheques', 'dck-elite' ); ?></span>
					<h2><?php esc_html_e( 'Bank Transfer', 'dck-elite' ); ?></h2>
					<?php
					dck_give_row( __( 'Bank', 'dck-elite' ), $g_bank );
					dck_give_row( __( 'Account name', 'dck-elite' ), $g_name );
					dck_give_row( __( 'Account number', 'dck-elite' ), $g_num, true );
					dck_give_row( __( 'Branch', 'dck-elite' ), $g_br );
					?>
					<button type="button" class="dck-btn dck-btn--outline dck-copy-all" data-copy="<?php echo esc_attr( trim( implode( "\n", array_filter( array( $g_bank ? 'Bank: ' . $g_bank : '', $g_name ? 'Account name: ' . $g_name : '', 'Account number: ' . $g_num, $g_br ? 'Branch: ' . $g_br : '', $g_sw ? 'SWIFT: ' . $g_sw : '' ) ) ) ) ); ?>"><?php esc_html_e( 'Copy all bank details', 'dck-elite' ); ?></button>
				</div>
				<ol class="dck-gv__steps">
					<li><?php esc_html_e( 'Use your banking app, internet banking or any branch.', 'dck-elite' ); ?></li>
					<li><?php esc_html_e( 'Pay to the account name and number shown.', 'dck-elite' ); ?></li>
					<?php if ( $g_name ) : ?><li><?php echo esc_html( sprintf( __( 'Make cheques payable to: %s.', 'dck-elite' ), $g_name ) ); ?></li><?php endif; ?>
					<li><?php esc_html_e( 'Add your name as the reference so we can thank you.', 'dck-elite' ); ?></li>
				</ol>
			</div>

			<div class="dck-gv__panel" role="tabpanel" id="panel-abroad" aria-labelledby="tab-abroad"<?php echo 'abroad' === $dck_first ? '' : ' hidden'; ?>>
				<div class="dck-gv__details">
					<span class="dck-card__tag"><?php esc_html_e( 'International Giving', 'dck-elite' ); ?></span>
					<h2><?php esc_html_e( 'Giving From Outside Kenya', 'dck-elite' ); ?></h2>
					<?php
					dck_give_row( __( 'Beneficiary bank', 'dck-elite' ), $g_bank );
					dck_give_row( __( 'SWIFT / BIC', 'dck-elite' ), $g_sw, true );
					dck_give_row( __( 'Account name', 'dck-elite' ), $g_name );
					dck_give_row( __( 'Account number', 'dck-elite' ), $g_num, true );
					dck_give_row( __( 'Country', 'dck-elite' ), 'Kenya' );
					?>
				</div>
				<ol class="dck-gv__steps">
					<li><?php esc_html_e( 'Send an international bank (SWIFT) transfer using the details shown.', 'dck-elite' ); ?></li>
					<li><?php esc_html_e( 'Some international money transfer services can also pay to M-Pesa numbers in Kenya. Check with your provider.', 'dck-elite' ); ?></li>
					<li><?php esc_html_e( 'Your bank may charge transfer and currency fees.', 'dck-elite' ); ?></li>
					<li><?php esc_html_e( 'Let us know on WhatsApp or email once you have sent it, so we can confirm receipt and thank you.', 'dck-elite' ); ?></li>
				</ol>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php if ( function_exists( 'dck_gift_form' ) ) { dck_gift_form(); } ?>

<?php if ( $dck_purposes ) : ?>
<section class="dck-section dck-give-purpose">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Where Your Gift Goes', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Every Gift Makes A Difference', 'dck-elite' ); ?></h2>
			<p class="dck-muted"><?php esc_html_e( 'Choose where your gift goes, pay with M-Pesa or the bank, then tell us so we can record it under the right purpose and thank you.', 'dck-elite' ); ?></p>
		</div>
		<div class="dck-gp">
			<?php foreach ( $dck_purposes as $i => $p ) : ?>
				<?php
				$dck_ic = array(
					'<path d="M12 3v18M5 10h14"/>',
					'<path d="M12 21s-7.5-4.6-9.3-9.2C1.4 8.4 3.6 5 7 5c2 0 3.4 1.1 5 3 1.6-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8C19.5 16.4 12 21 12 21z"/>',
					'<path d="M3 21h18M5 21V10l7-6 7 6v11M10 21v-6h4v6"/>',
					'<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.7 2.6 15.3 0 18M12 3c-2.6 2.7-2.6 15.3 0 18"/>',
					'<path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="M4 11h16v4a8 8 0 0 1-16 0z"/>',
				);
				?>
				<a class="dck-gp__item dck-gp__item--link dck-reveal" href="#confirm-gift" data-purpose="<?php echo esc_attr( $p['t'] ); ?>">
					<span class="dck-gp__top"><span class="dck-gp__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?php echo $dck_ic[ $i % count( $dck_ic ) ]; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></svg></span><span class="dck-gp__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span></span>
					<h3><?php echo esc_html( $p['t'] ); ?></h3>
					<p><?php echo esc_html( $p['d'] ); ?></p>
					<span class="dck-gp__go"><?php echo esc_html( sprintf( __( 'Give to %s', 'dck-elite' ), $p['t'] ) ); ?> &rarr;</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="dck-section dck-give-why">
	<div class="dck-container dck-grid-2">
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'A Heart of Worship', 'dck-elite' ); ?></p>
			<h2><?php echo esc_html( dck_home_val( 'dck_give_why_heading', __( 'Tithes, Offerings and Donations', 'dck-elite' ) ) ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'Tithes, offerings and donations are biblical acts of worship and obedience to God.', 'dck-elite' ); ?></p>
			<p><?php echo esc_html( dck_home_val( 'dck_give_why_text', __( 'The tithe, first mentioned in Genesis 14:20 and commanded in Malachi 3:10, is giving one-tenth of our income to support the work of God through the church. Offerings and donations, seen throughout Scripture (Exodus 35 and Acts 4), are voluntary gifts that reflect gratitude, love, and a willingness to advance the kingdom of God.', 'dck-elite' ) ) ); ?></p>
		</div>
		<div class="dck-reveal">
			<figure class="dck-verse">
				<blockquote><?php echo esc_html( dck_home_val( 'dck_give_verse', __( 'Bring the whole tithe into the storehouse... and see if I will not throw open the floodgates of heaven and pour out so much blessing that there will not be room enough to store it.', 'dck-elite' ) ) ); ?></blockquote>
				<figcaption><?php echo esc_html( dck_home_val( 'dck_give_verse_ref', 'Malachi 3:10' ) ); ?></figcaption>
			</figure>
		</div>
	</div>
</section>

<section class="dck-section dck-give-safe">
	<div class="dck-container">
		<div class="dck-gsafe">
			<div class="dck-gsafe__head">
				<p class="dck-eyebrow"><?php esc_html_e( 'Give With Confidence', 'dck-elite' ); ?></p>
				<h2><?php esc_html_e( 'Your Gift Is Safe', 'dck-elite' ); ?></h2>
			</div>
			<ul class="dck-gsafe__list">
				<li><strong><?php esc_html_e( 'Only these accounts.', 'dck-elite' ); ?></strong> <?php esc_html_e( 'The details on this page are the church\'s only official giving channels.', 'dck-elite' ); ?></li>
				<li><strong><?php esc_html_e( 'Never share your PIN.', 'dck-elite' ); ?></strong> <?php esc_html_e( 'We will never ask for your M-Pesa PIN, bank password or card details by phone, SMS or social media.', 'dck-elite' ); ?></li>
				<li><strong><?php esc_html_e( 'Check before you confirm.', 'dck-elite' ); ?></strong> <?php esc_html_e( 'Make sure the recipient name shown on your phone or banking app is correct before you approve.', 'dck-elite' ); ?></li>
				<li><strong><?php esc_html_e( 'Report anything unusual.', 'dck-elite' ); ?></strong> <?php echo esc_html( sprintf( __( 'If someone asks you to give to a different number, call us on %s first.', 'dck-elite' ), dck_phone_display() ) ); ?></li>
			</ul>
		</div>
	</div>
</section>

<section class="dck-section dck-give-faq">
	<div class="dck-container" style="max-width:820px">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Questions', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Giving FAQ', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-faq-list">
			<details class="dck-faq-item"><summary><?php esc_html_e( 'Which option should I use?', 'dck-elite' ); ?></summary><div class="dck-faq-answer"><p><?php esc_html_e( 'Use the M-Pesa Paybill for tithes and offerings, the Till for project giving, and bank transfer for larger gifts, cheques or giving from outside Kenya.', 'dck-elite' ); ?></p></div></details>
			<details class="dck-faq-item"><summary><?php esc_html_e( 'How do I know my gift was received?', 'dck-elite' ); ?></summary><div class="dck-faq-answer"><p><?php esc_html_e( 'M-Pesa sends you a confirmation SMS immediately. For bank transfers, keep your transfer receipt. You are always welcome to contact us to confirm.', 'dck-elite' ); ?></p></div></details>
			<details class="dck-faq-item"><summary><?php esc_html_e( 'I sent money to the wrong number. What should I do?', 'dck-elite' ); ?></summary><div class="dck-faq-answer"><p><?php echo esc_html( sprintf( __( 'Contact Safaricom (or your bank) straight away to request a reversal, then let us know on %s so we can help.', 'dck-elite' ), dck_phone_display() ) ); ?></p></div></details>
			<details class="dck-faq-item"><summary><?php esc_html_e( 'Can I give from outside Kenya?', 'dck-elite' ); ?></summary><div class="dck-faq-answer"><p><?php esc_html_e( 'Yes. Choose the From Abroad tab above for international bank transfer details, and message us once you have sent your gift.', 'dck-elite' ); ?></p></div></details>
		</div>
	</div>
</section>

<section class="dck-section dck-cta">
	<div class="dck-container dck-center">
		<h2><?php esc_html_e( 'Questions About Giving?', 'dck-elite' ); ?></h2>
		<p class="dck-lead dck-center dck-lead--light"><?php esc_html_e( 'Our team is glad to help. Reach us anytime.', 'dck-elite' ); ?></p>
		<div class="dck-hero__cta" style="margin-top:24px">
			<?php if ( $dck_wa ) : ?>
				<a class="dck-btn dck-btn--gold" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( __( 'Hello, I have a question about giving at DCK.', 'dck-elite' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat on WhatsApp', 'dck-elite' ); ?></a>
			<?php endif; ?>
			<a class="dck-btn dck-btn--ghost" href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( sprintf( __( 'Call %s', 'dck-elite' ), dck_phone_display() ) ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
