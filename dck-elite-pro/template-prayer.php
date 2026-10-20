<?php
/**
 * Template Name: DCK Prayer Request
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_ep    = esc_url( rest_url( 'dck/v1/prayer' ) );
$dck_nonce = wp_create_nonce( 'wp_rest' );
$dck_tel   = function_exists( 'dck_home_tel' ) ? dck_home_tel() : '+254110304521';
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'We Would Love To Pray With You', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Prayer Request', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'Whatever you are facing, you do not have to carry it alone. Share your request and our prayer team will stand with you in prayer.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container dck-prayer">

		<div class="dck-prayer__form-wrap dck-reveal">
			<form id="dck-prayer-form" class="dck-prayer__form" data-endpoint="<?php echo $dck_ep; ?>" data-nonce="<?php echo esc_attr( $dck_nonce ); ?>" novalidate>
				<div class="dck-prayer__row">
					<label class="dck-prayer__field">
						<span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
						<input type="text" name="name" autocomplete="name" required placeholder="<?php esc_attr_e( 'Full name', 'dck-elite' ); ?>">
					</label>
					<label class="dck-prayer__field">
						<span><?php esc_html_e( 'Phone number', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
						<input type="tel" name="phone" autocomplete="tel" required placeholder="<?php esc_attr_e( 'e.g. 0712 345 678', 'dck-elite' ); ?>">
					</label>
				</div>
				<label class="dck-prayer__field">
					<span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
					<input type="email" name="email" autocomplete="email" required placeholder="<?php esc_attr_e( 'So our pastors can reach you', 'dck-elite' ); ?>">
				</label>
				<label class="dck-prayer__field">
					<span><?php esc_html_e( 'How can we pray for you?', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
					<textarea name="request" rows="6" required placeholder="<?php esc_attr_e( 'Share your prayer request here...', 'dck-elite' ); ?>"></textarea>
				</label>
				<label class="dck-prayer__check">
					<input type="checkbox" name="private" value="1">
					<span><?php esc_html_e( 'Keep this request confidential (pastors only).', 'dck-elite' ); ?></span>
				</label>
				<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
				<button type="submit" class="dck-btn dck-btn--primary"><?php esc_html_e( 'Send Prayer Request', 'dck-elite' ); ?></button>
				<p id="dck-prayer-status" class="dck-prayer__status" role="status" aria-live="polite"></p>
			</form>
		</div>

		<aside class="dck-prayer__aside dck-reveal">
			<div class="dck-prayer__verse">
				<p class="dck-prayer__verse-text">&ldquo;<?php esc_html_e( 'Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God.', 'dck-elite' ); ?>&rdquo;</p>
				<span class="dck-prayer__verse-ref"><?php esc_html_e( 'Philippians 4:6', 'dck-elite' ); ?></span>
			</div>
			<h3><?php esc_html_e( 'You Are Not Alone', 'dck-elite' ); ?></h3>
			<p><?php esc_html_e( 'Every request is received with care and lifted up by our prayer team. If you prefer, tick the confidential box and it will be seen by our pastors only. Your details are kept private and used only to follow up with you.', 'dck-elite' ); ?></p>
			<p class="dck-prayer__call"><?php esc_html_e( 'Need prayer right now?', 'dck-elite' ); ?></p>
			<a class="dck-btn dck-btn--gold" href="tel:<?php echo esc_attr( $dck_tel ); ?>"><?php esc_html_e( 'Call Our Prayer Line', 'dck-elite' ); ?></a>
		</aside>

	</div>
</section>

<script>
(function(){
	var f = document.getElementById("dck-prayer-form");
	if (!f) { return; }
	var T0 = Date.now();
	var status = document.getElementById("dck-prayer-status");
	function val(n){ return f.elements[n] ? f.elements[n].value.trim() : ""; }
	f.addEventListener("submit", function(e){
		e.preventDefault();
		var btn = f.querySelector("button[type=submit]");
		var name = val("name"), phone = val("phone"), email = val("email"), req = val("request");
		if (name === "" || phone === "" || email === "" || req === "") {
			status.textContent = "Please fill in your name, phone, email, and prayer request.";
			status.className = "dck-prayer__status is-err";
			return;
		}
		if (email.indexOf("@") === -1 || email.indexOf(".") === -1) {
			status.textContent = "Please enter a valid email address so we can reach you.";
			status.className = "dck-prayer__status is-err";
			return;
		}
		var data = {
			name: name, phone: phone, email: email, request: req,
			private: f.elements["private"] ? f.elements["private"].checked : false,
			website: f.elements["website"] ? f.elements["website"].value : "",
			elapsed: Date.now() - T0
		};
		var prev = btn.textContent;
		btn.disabled = true;
		btn.textContent = "Sending...";
		fetch(f.getAttribute("data-endpoint"), {
			method: "POST",
			headers: { "Content-Type": "application/json", "X-WP-Nonce": f.getAttribute("data-nonce") },
			body: JSON.stringify(data)
		}).then(function(r){ return r.json(); }).then(function(res){
			status.textContent = (res && res.message) ? res.message : "Thank you. Our prayer team will be praying with you.";
			status.className = "dck-prayer__status " + (res && res.ok ? "is-ok" : "is-err");
			if (res && res.ok) { f.reset(); }
			btn.disabled = false;
			btn.textContent = prev;
		}).catch(function(){
			status.textContent = "Something went wrong. Please call us on <?php echo esc_js( dck_phone_display() ); ?>.";
			status.className = "dck-prayer__status is-err";
			btn.disabled = false;
			btn.textContent = prev;
		});
	});
})();
</script>

<?php
get_footer();
