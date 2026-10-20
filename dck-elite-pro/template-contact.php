<?php
/**
 * Template Name: DCK Contact
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$dck_ep    = esc_url( rest_url( 'dck/v1/contact' ) );
$dck_nonce = wp_create_nonce( 'wp_rest' );
$dck_tel   = function_exists( 'dck_home_tel' ) ? dck_home_tel() : '+254110304521';
$dck_email = function_exists( 'dck_home_val' ) ? dck_home_val( 'dck_top_email', 'info@dckabeten.org' ) : 'info@dckabeten.org';
$dck_addr  = function_exists( 'dck_home_val' ) ? dck_home_val( 'dck_top_address', 'Behind N Market, Uthiru, Kabete' ) : 'Behind N Market, Uthiru, Kabete';
$dck_dir   = function_exists( 'dck_home_directions' ) ? dck_home_directions() : 'https://share.google/LJmcXS6D5Nq1aF2R5';
$dck_wa    = function_exists( 'dck_prayer_whatsapp' ) ? dck_prayer_whatsapp() : '254110304521';
$dck_img   = dck_theme_image( 'dck_contact_image', 'assets/images/contact-ai.webp' );
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'We Would Love To Hear From You', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Contact Us', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'Have a question, need prayer, or want to plan a visit? Reach out - a real person will get back to you.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container dck-grid-2" style="align-items:center">
		<div class="dck-reveal">
			<img src="<?php echo esc_url( $dck_img ); ?>" alt="<?php esc_attr_e( 'Deliverance Church Kabete N Market', 'dck-elite' ); ?>" style="width:100%;border-radius:var(--dck-radius);box-shadow:var(--dck-shadow)">
		</div>
		<div class="dck-reveal">
			<p class="dck-eyebrow"><?php esc_html_e( 'Reach Us', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Get In Touch', 'dck-elite' ); ?></h2>
			<div class="dck-contact-list">
				<a class="dck-cinfo" href="tel:<?php echo esc_attr( $dck_tel ); ?>">
					<span class="dck-cinfo__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.24 1Z"/></svg></span>
					<span><span class="dck-cinfo__k"><?php esc_html_e( 'Call Us', 'dck-elite' ); ?></span><strong>+254 110 304 521</strong></span>
				</a>
				<a class="dck-cinfo" href="https://wa.me/<?php echo esc_attr( $dck_wa ); ?>?text=<?php echo rawurlencode( 'Hello DCK, I have a question.' ); ?>" target="_blank" rel="noopener">
					<span class="dck-cinfo__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.4L3 21l2.2-5.3A8.5 8.5 0 1 1 21 11.5z"/></svg></span>
					<span><span class="dck-cinfo__k"><?php esc_html_e( 'WhatsApp', 'dck-elite' ); ?></span><strong><?php esc_html_e( 'Chat with us', 'dck-elite' ); ?></strong></span>
				</a>
				<a class="dck-cinfo" href="mailto:<?php echo esc_attr( $dck_email ); ?>">
					<span class="dck-cinfo__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm0 2 8 6 8-6"/></svg></span>
					<span><span class="dck-cinfo__k"><?php esc_html_e( 'Email', 'dck-elite' ); ?></span><strong><?php echo esc_html( $dck_email ); ?></strong></span>
				</a>
				<a class="dck-cinfo" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener">
					<span class="dck-cinfo__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg></span>
					<span><span class="dck-cinfo__k"><?php esc_html_e( 'Location', 'dck-elite' ); ?></span><strong><?php echo esc_html( $dck_addr ); ?></strong></span>
				</a>
			</div>
			<p style="margin-top:22px"><a class="dck-btn dck-btn--gold" href="<?php echo esc_url( $dck_dir ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get Directions', 'dck-elite' ); ?></a></p>
		</div>
	</div>
</section>

<section class="dck-section" style="background:var(--dck-blue-100)">
	<div class="dck-container" style="max-width:760px">
		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Send A Message', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'Write To Us', 'dck-elite' ); ?></h2>
		</div>
		<form id="dck-contact-form" class="dck-cform dck-reveal" data-endpoint="<?php echo $dck_ep; ?>" data-nonce="<?php echo esc_attr( $dck_nonce ); ?>" style="margin-top:30px">
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required placeholder="<?php esc_attr_e( 'Full name', 'dck-elite' ); ?>"></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Phone', 'dck-elite' ); ?></span><input type="tel" name="phone" autocomplete="tel" placeholder="<?php esc_attr_e( 'Optional', 'dck-elite' ); ?>"></label>
			</div>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Email', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="email" name="email" autocomplete="email" required placeholder="<?php esc_attr_e( 'So we can reply to you', 'dck-elite' ); ?>"></label>
			<label class="dck-cform__field"><span><?php esc_html_e( 'How can we help?', 'dck-elite' ); ?> <em class="dck-req">*</em></span><textarea name="message" rows="5" required placeholder="<?php esc_attr_e( 'Write your message here...', 'dck-elite' ); ?>"></textarea></label>
			<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<button type="submit" class="dck-btn dck-btn--primary"><?php esc_html_e( 'Send Message', 'dck-elite' ); ?></button>
			<p id="dck-contact-status" class="dck-prayer__status" role="status" aria-live="polite"></p>
		</form>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<script>
(function(){
	var f = document.getElementById("dck-contact-form");
	if (f === null) { return; }
	var T0 = Date.now();
	var status = document.getElementById("dck-contact-status");
	function val(n){ return f.elements[n] ? f.elements[n].value.trim() : ""; }
	f.addEventListener("submit", function(e){
		e.preventDefault();
		var btn = f.querySelector("button[type=submit]");
		var name = val("name"), email = val("email"), mVal = val("message");
		if (name === "" || email === "" || mVal === "") {
			status.textContent = "Please enter your name, email, and a message.";
			status.className = "dck-prayer__status is-err";
			return;
		}
		if (email.indexOf("@") === -1 || email.indexOf(".") === -1) {
			status.textContent = "Please enter a valid email address so we can reply.";
			status.className = "dck-prayer__status is-err";
			return;
		}
		var data = {
			name: name,
			email: email,
			phone: f.elements["phone"] ? f.elements["phone"].value : "",
			message: mVal,
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
			status.textContent = (res && res.message) ? res.message : "Thank you. Your message has been received.";
			status.className = "dck-prayer__status is-ok";
			if (res && res.ok) {
				f.reset();
				if (res.whatsapp) {
					var old = document.getElementById("dck-contact-wa");
					if (old) { old.parentNode.removeChild(old); }
					var wa = document.createElement("a");
					wa.id = "dck-contact-wa";
					wa.className = "dck-btn dck-btn--gold";
					wa.style.marginTop = "14px";
					wa.href = res.whatsapp;
					wa.target = "_blank";
					wa.rel = "noopener";
					wa.textContent = "Also send on WhatsApp";
					status.parentNode.insertBefore(wa, status.nextSibling);
				}
			}
			btn.disabled = false;
			btn.textContent = prev;
		}).catch(function(){
			status.textContent = "Something went wrong. Please call us on +254 110 304 521.";
			status.className = "dck-prayer__status is-err";
			btn.disabled = false;
			btn.textContent = prev;
		});
	});
})();
</script>

<?php
get_footer();
