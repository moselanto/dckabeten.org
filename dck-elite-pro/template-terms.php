<?php
/**
 * Template Name: DCK Terms & Photography
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$dck_ep    = esc_url( rest_url( 'dck/v1/photo-optout' ) );
$dck_nonce = wp_create_nonce( 'wp_rest' );
?>

<section class="dck-page-hero">
	<div class="dck-container">
		<p class="dck-eyebrow" style="color:var(--dck-gold-300)"><?php esc_html_e( 'Transparency & Care', 'dck-elite' ); ?></p>
		<h1><?php esc_html_e( 'Terms, Privacy &amp; Photography', 'dck-elite' ); ?></h1>
		<p class="dck-lead dck-center" style="color:#e7ecff"><?php esc_html_e( 'How we care for your information, and how we handle photos and video at church.', 'dck-elite' ); ?></p>
	</div>
</section>

<section class="dck-section">
	<div class="dck-container dck-content dck-legal">
		<?php
		if ( function_exists( 'dck_elite_terms_content' ) ) {
			echo wp_kses_post( dck_elite_terms_content() );
		}
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
</section>

<section class="dck-section" style="background:var(--dck-blue-100)">
	<div class="dck-container" style="max-width:680px">

		<div class="dck-center">
			<p class="dck-eyebrow"><?php esc_html_e( 'Photography Preference', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'I Prefer Not To Be Photographed', 'dck-elite' ); ?></h2>
			<p class="dck-lead dck-center"><?php esc_html_e( 'Complete all fields and sign below. Our media team will be notified and your preference kept on record.', 'dck-elite' ); ?></p>
		</div>

		<form id="dck-optout-form" class="dck-cform dck-reveal"
		      data-ep="<?php echo esc_url( $dck_ep ); ?>"
		      data-nc="<?php echo esc_attr( $dck_nonce ); ?>"
		      style="margin-top:28px" novalidate>

			<div class="dck-cform__row">
				<label class="dck-cform__field">
					<span><?php esc_html_e( 'Full name', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
					<input type="text" name="name" autocomplete="name" required
					       placeholder="<?php esc_attr_e( 'Full name', 'dck-elite' ); ?>">
				</label>
				<label class="dck-cform__field">
					<span><?php esc_html_e( 'Phone number', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
					<input type="tel" name="phone" autocomplete="tel" required
					       placeholder="<?php esc_attr_e( 'e.g. 0712 345 678', 'dck-elite' ); ?>">
				</label>
			</div>

			<label class="dck-cform__field">
				<span><?php esc_html_e( 'Email address', 'dck-elite' ); ?> <em class="dck-req">*</em></span>
				<input type="email" name="email" autocomplete="email" required
				       placeholder="<?php esc_attr_e( 'your@email.com', 'dck-elite' ); ?>">
			</label>

			<label class="dck-cform__field">
				<span><?php esc_html_e( 'Service you usually attend', 'dck-elite' ); ?></span>
				<input type="text" name="service"
				       placeholder="<?php esc_attr_e( 'Optional', 'dck-elite' ); ?>">
			</label>

			<div class="dck-sig-wrap">
				<p class="dck-sig-label"><?php esc_html_e( 'Signature', 'dck-elite' ); ?> <em class="dck-req">*</em></p>
				<p class="dck-sig-hint" style="font-size:.85rem;color:#555;margin:2px 0 6px"><?php esc_html_e( 'Sign in the box below using your mouse, finger, or stylus.', 'dck-elite' ); ?></p>
				<div class="dck-sig-box" style="position:relative;border:2px solid #c8d0e0;border-radius:6px;background:#fff;overflow:hidden">
					<canvas id="dck-sig-canvas"
					        style="display:block;width:100%;height:140px;cursor:crosshair;touch-action:none"></canvas>
					<button type="button" id="dck-sig-clear"
					        style="position:absolute;top:6px;right:6px;font-size:.75rem;padding:2px 8px;cursor:pointer">
						<?php esc_html_e( 'Clear', 'dck-elite' ); ?>
					</button>
				</div>
				<p id="dck-sig-err" style="display:none;color:#c0392b;font-size:.85rem;margin-top:4px">
					<?php esc_html_e( 'Please draw your signature before submitting.', 'dck-elite' ); ?>
				</p>
			</div>

			<label class="dck-prayer__check" style="margin-top:16px">
				<input type="checkbox" name="consent" value="1" required>
				<span><?php esc_html_e( 'I prefer not to be photographed or recorded at church, and I would like the media team to be aware.', 'dck-elite' ); ?></span>
			</label>

			<div aria-hidden="true" style="display:none">
				<input type="text" name="website" tabindex="-1" autocomplete="off">
			</div>

			<button type="submit" class="dck-btn dck-btn--primary" style="width:100%;margin-top:14px">
				<?php esc_html_e( 'Submit My Preference', 'dck-elite' ); ?>
			</button>

			<p id="dck-optout-status" class="dck-prayer__status" role="status" aria-live="polite" style="margin-top:10px"></p>

		</form>
	</div>
</section>

<?php get_template_part( 'template-parts/join-us' ); ?>

<script>
(function () {

    /* ---- Signature pad ---- */
    var canvas  = document.getElementById('dck-sig-canvas');
    var sigErr  = document.getElementById('dck-sig-err');
    var clrBtn  = document.getElementById('dck-sig-clear');
    var signed  = false;
    var ctx, drawing, lx, ly;

    if (canvas) {
        ctx     = canvas.getContext('2d');
        drawing = false;
        lx      = 0;
        ly      = 0;

        function initCanvas() {
            var rect  = canvas.getBoundingClientRect();
            canvas.width  = Math.round(rect.width);
            canvas.height = Math.round(rect.height);
            ctx.strokeStyle = '#0a1040';
            ctx.lineWidth   = 2;
            ctx.lineCap     = 'round';
            ctx.lineJoin    = 'round';
        }
        initCanvas();

        function getPos(e) {
            var r   = canvas.getBoundingClientRect();
            var src = e.touches ? e.touches[0] : e;
            return { x: src.clientX - r.left, y: src.clientY - r.top };
        }

        canvas.addEventListener('mousedown', function (e) {
            drawing = true;
            var p = getPos(e);
            lx = p.x; ly = p.y;
        });

        canvas.addEventListener('mousemove', function (e) {
            if (!drawing) { return; }
            var p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lx, ly);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            lx = p.x; ly = p.y;
            signed = true;
            if (sigErr) { sigErr.style.display = 'none'; }
        });

        canvas.addEventListener('mouseup',    function () { drawing = false; });
        canvas.addEventListener('mouseleave', function () { drawing = false; });

        canvas.addEventListener('touchstart', function (e) {
            e.preventDefault();
            drawing = true;
            var p = getPos(e);
            lx = p.x; ly = p.y;
        }, { passive: false });

        canvas.addEventListener('touchmove', function (e) {
            e.preventDefault();
            if (!drawing) { return; }
            var p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lx, ly);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            lx = p.x; ly = p.y;
            signed = true;
            if (sigErr) { sigErr.style.display = 'none'; }
        }, { passive: false });

        canvas.addEventListener('touchend', function () { drawing = false; });

        if (clrBtn) {
            clrBtn.addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signed = false;
            });
        }
    }

    /* ---- Form submission ---- */
    var form   = document.getElementById('dck-optout-form');
    var status = document.getElementById('dck-optout-status');
    if (!form) { return; }
    var T0 = Date.now();

    function val(n) {
        return form.elements[n] ? form.elements[n].value.trim() : '';
    }

    function showErr(msg) {
        if (status) {
            status.textContent = msg;
            status.className   = 'dck-prayer__status is-err';
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var name    = val('name');
        var phone   = val('phone');
        var email   = val('email');
        var consent = form.elements['consent'] ? form.elements['consent'].checked : false;
        var sigData = (canvas && signed) ? canvas.toDataURL('image/png') : '';

        if (!name)  { showErr('Please enter your full name.'); return; }
        if (!phone) { showErr('Phone number is required.'); return; }
        if (!email || email.indexOf('@') < 0) { showErr('A valid email address is required.'); return; }
        if (!signed || !sigData) {
            if (sigErr) { sigErr.style.display = ''; }
            showErr('Please draw your signature before submitting.');
            return;
        }
        if (!consent) { showErr('Please tick the confirmation box.'); return; }

        if (status) { status.textContent = ''; status.className = 'dck-prayer__status'; }

        var btn  = form.querySelector('button[type=submit]');
        var prev = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = 'Sending\u2026'; }

        fetch(form.getAttribute('data-ep'), {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce':   form.getAttribute('data-nc')
            },
            body: JSON.stringify({
                name:      name,
                phone:     phone,
                email:     email,
                service:   val('service'),
                signature: sigData,
                website:   val('website'),
                elapsed:   Date.now() - T0
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (status) {
                status.textContent = (res && res.message) ? res.message : 'Thank you.';
                status.className   = 'dck-prayer__status ' + ((res && res.ok) ? 'is-ok' : 'is-err');
            }
            if (res && res.ok) {
                form.reset();
                signed = false;
                if (canvas && ctx) { ctx.clearRect(0, 0, canvas.width, canvas.height); }
            }
            if (btn) { btn.disabled = false; btn.textContent = prev; }
        })
        .catch(function () {
            showErr('Something went wrong. Please speak to an usher or call +254 110 304 521.');
            if (btn) { btn.disabled = false; btn.textContent = prev; }
        });
    });

}());
</script>

<?php get_footer(); ?>
