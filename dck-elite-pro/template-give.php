<?php
/**
 * Template Name: DCK Give Online
 *
 * Secure giving page with M-Pesa Paybill/Till and bank details.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_wa = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '254110304521';
$g_pb   = dck_give_val( 'dck_give_paybill' );
$g_acc  = dck_give_val( 'dck_give_paybill_account' );
$g_till = dck_give_val( 'dck_give_till' );
$g_bank = dck_give_val( 'dck_give_bank_name' );
$g_name = dck_give_val( 'dck_give_bank_account_name' );
$g_num  = dck_give_val( 'dck_give_bank_account' );
$g_sw   = dck_give_val( 'dck_give_bank_swift' );
$g_br   = dck_give_val( 'dck_give_bank_branch' );
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Partner With Us', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Give Online', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'Your generosity advances the Gospel, cares for people, and builds the house of God.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container">
		<div class="dck-give-ways">
			<a class="dck-way dck-reveal" href="#dck-mpesa">
				<span class="dck-way__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M10 18h4"/></svg></span>
				<span class="dck-way__t"><?php esc_html_e( 'M-Pesa Paybill', 'dck-elite' ); ?></span>
				<span class="dck-way__d"><?php esc_html_e( 'Tithes &amp; Offerings', 'dck-elite' ); ?></span>
			</a>
			<a class="dck-way dck-reveal" href="#dck-mpesa">
				<span class="dck-way__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 10h18M7 15h4M6 5h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/></svg></span>
				<span class="dck-way__t"><?php esc_html_e( 'M-Pesa Till', 'dck-elite' ); ?></span>
				<span class="dck-way__d"><?php esc_html_e( 'Give To A Project', 'dck-elite' ); ?></span>
			</a>
			<a class="dck-way dck-reveal" href="#dck-bank">
				<span class="dck-way__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 10l9-6 9 6M5 10v9h14v-9M9 19v-5h6v5"/></svg></span>
				<span class="dck-way__t"><?php esc_html_e( 'Bank Transfer', 'dck-elite' ); ?></span>
				<span class="dck-way__d"><?php esc_html_e( 'Deposits &amp; Cheques', 'dck-elite' ); ?></span>
			</a>
		</div>
	</div>
</section>

<section class="dck-section" style="padding-top:8px">
	<div class="dck-container dck-grid-2">
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'A Heart of Worship', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Tithes, Offerings &amp; Donations', 'dck-elite' ); ?></h2>
			<p class="dck-lead"><?php esc_html_e( 'Tithes, offerings and donations are biblical acts of worship and obedience to God.', 'dck-elite' ); ?></p>
			<p><?php esc_html_e( 'The tithe, first mentioned in Genesis 14:20 and commanded in Malachi 3:10, is giving one-tenth of our income to support the work of God through the church. Offerings and donations, seen throughout Scripture (Exodus 35 and Acts 4), are voluntary gifts that reflect gratitude, love, and a willingness to advance the kingdom of God.', 'dck-elite' ); ?></p>
		</div>
		<div class="dck-reveal">
			<div class="dck-card" style="border-top:5px solid var(--dck-gold)">
				<span class="dck-card__tag"><?php esc_html_e( 'Malachi 3:10', 'dck-elite' ); ?></span>
				<p style="font-family:var(--dck-font-display);font-size:1.25rem;color:var(--dck-blue-900);line-height:1.5;margin:0">
					<?php esc_html_e( '"Bring the whole tithe into the storehouse... and see if I will not throw open the floodgates of heaven and pour out so much blessing that there will not be room enough to store it."', 'dck-elite' ); ?>
				</p>
			</div>
		</div>
	</div>
</section>

<section id="dck-mpesa" class="dck-section" style="background:var(--dck-blue-100)">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Give Via M-Pesa', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Fast, Secure Mobile Giving', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-grid-2" style="margin-top:40px;align-items:stretch">

			<div class="dck-card dck-reveal">
				<span class="dck-card__tag"><?php esc_html_e( 'Tithes &amp; Offerings', 'dck-elite' ); ?></span>
				<h3><?php esc_html_e( 'M-Pesa Paybill', 'dck-elite' ); ?></h3>
				<p class="dck-give-lbl"><?php esc_html_e( 'Paybill Number', 'dck-elite' ); ?></p>
				<div class="dck-give-row"><span class="dck-give-num"><?php echo esc_html( $g_pb ); ?></span><button type="button" class="dck-copy" data-copy="<?php echo esc_attr( $g_pb ); ?>"><?php esc_html_e( 'Copy', 'dck-elite' ); ?></button></div>
				<p class="dck-give-lbl"><?php esc_html_e( 'Account Number', 'dck-elite' ); ?></p>
				<div class="dck-give-row"><span class="dck-give-num"><?php echo esc_html( $g_acc ); ?></span><button type="button" class="dck-copy" data-copy="<?php echo esc_attr( $g_acc ); ?>"><?php esc_html_e( 'Copy', 'dck-elite' ); ?></button></div>
				<ol class="dck-give-steps">
					<li><?php esc_html_e( 'Go to M-Pesa, then Lipa na M-Pesa, then Pay Bill.', 'dck-elite' ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'Enter Business Number %s.', 'dck-elite' ), $g_pb ) ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'Enter Account Number %s.', 'dck-elite' ), $g_acc ) ); ?></li>
					<li><?php esc_html_e( 'Enter amount, your PIN, and confirm.', 'dck-elite' ); ?></li>
				</ol>
			</div>

			<div class="dck-card dck-reveal">
				<span class="dck-card__tag"><?php esc_html_e( 'Give To A Project', 'dck-elite' ); ?></span>
				<h3><?php esc_html_e( 'M-Pesa Buy Goods (Till)', 'dck-elite' ); ?></h3>
				<p class="dck-give-lbl"><?php esc_html_e( 'Till Number', 'dck-elite' ); ?></p>
				<div class="dck-give-row"><span class="dck-give-num"><?php echo esc_html( $g_till ); ?></span><button type="button" class="dck-copy" data-copy="<?php echo esc_attr( $g_till ); ?>"><?php esc_html_e( 'Copy', 'dck-elite' ); ?></button></div>
				<ol class="dck-give-steps">
					<li><?php esc_html_e( 'Go to M-Pesa, then Lipa na M-Pesa, then Buy Goods and Services.', 'dck-elite' ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'Enter Till Number %s.', 'dck-elite' ), $g_till ) ); ?></li>
					<li><?php esc_html_e( 'Enter amount, your PIN, and confirm.', 'dck-elite' ); ?></li>
				</ol>
			</div>

		</div>
	</div>
</section>

<section id="dck-bank" class="dck-section">
	<div class="dck-container">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Bank Transfer', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Bank Deposit / Cheques', 'dck-elite' ); ?></h2>
		</div>
		<div class="dck-card dck-reveal" style="max-width:640px;margin:36px auto 0">
			<table class="dck-bank">
				<tr><th><?php esc_html_e( 'Bank', 'dck-elite' ); ?></th><td><?php echo esc_html( $g_bank ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Account Name', 'dck-elite' ); ?></th><td><?php echo esc_html( $g_name ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Account Number', 'dck-elite' ); ?></th><td><span class="dck-give-row" style="justify-content:flex-start"><span><?php echo esc_html( $g_num ); ?></span><button type="button" class="dck-copy" data-copy="<?php echo esc_attr( $g_num ); ?>"><?php esc_html_e( 'Copy', 'dck-elite' ); ?></button></span></td></tr>
				<tr><th><?php esc_html_e( 'SWIFT Code', 'dck-elite' ); ?></th><td><?php echo esc_html( $g_sw ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Branch', 'dck-elite' ); ?></th><td><?php echo esc_html( $g_br ); ?></td></tr>
			</table>
			<p style="margin-top:18px;color:var(--dck-gray)"><?php echo esc_html( sprintf( __( 'Make cheques payable to: %s.', 'dck-elite' ), $g_name ) ); ?></p>
		</div>
	</div>
</section>

<section class="dck-section dck-cta">
	<div class="dck-container dck-center">
		<h2><?php esc_html_e( 'Questions About Giving?', 'dck-elite' ); ?></h2>
		<p class="dck-lead dck-center" style="color:#d7deff"><?php esc_html_e( 'Our team is glad to help. Reach us anytime.', 'dck-elite' ); ?></p>
		<div class="dck-hero__cta" style="margin-top:24px">
			<a class="dck-btn dck-btn--gold" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( 'Hello, I have a question about giving at DCK.' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat on WhatsApp', 'dck-elite' ); ?></a>
			<a class="dck-btn dck-btn--ghost" href="tel:<?php echo esc_attr( dck_home_tel() ); ?>"><?php echo esc_html( sprintf( __( 'Call %s', 'dck-elite' ), dck_phone_display() ) ); ?></a>
		</div>
	</div>
</section>

<script>
(function(){
	document.addEventListener('click',function(e){
		var b=e.target.closest('.dck-copy');
		if(!b){return;}
		var v=b.getAttribute('data-copy')||'';
		var done=function(){var o=b.textContent;b.textContent='Copied';b.classList.add('is-copied');setTimeout(function(){b.textContent=o;b.classList.remove('is-copied');},1400);};
		if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(v).then(done).catch(done);}else{done();}
	});
})();
</script>

<?php
get_footer();
