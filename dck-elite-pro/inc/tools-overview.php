<?php
/**
 * Church Tools overview: one screen with what needs attention today
 * (gifts to verify, new visitors, pastoral requests, sign-ups, upcoming registrations),
 * giving totals this month per currency, and quick links. Also a wp-admin Dashboard widget.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_ov_count' ) ) {
	function dck_ov_count( $type, $key, $val, $since = '' ) {
		$args = array( 'post_type' => $type, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false );
		if ( $key ) {
			$args['meta_query'] = array( 'relation' => 'OR', array( 'key' => $key, 'value' => $val ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( 'new' === $val || 'pending' === $val ) {
				$args['meta_query'][] = array( 'key' => $key, 'compare' => 'NOT EXISTS' );
			}
		}
		if ( $since ) {
			$args['date_query'] = array( array( 'after' => $since ) );
		}
		if ( ! post_type_exists( $type ) ) {
			return 0;
		}
		$q = new WP_Query( $args );
		return (int) $q->found_posts;
	}
}

if ( ! function_exists( 'dck_ov_data' ) ) {
	function dck_ov_data() {
		$month = wp_date( 'Y-m-01 00:00:00' );
		$tot   = array();
		if ( post_type_exists( 'dck_gift' ) ) {
			foreach ( get_posts( array( 'post_type' => 'dck_gift', 'post_status' => 'any', 'posts_per_page' => 2000, 'fields' => 'ids', 'date_query' => array( array( 'after' => $month ) ), 'meta_key' => '_dck_gift_status', 'meta_value' => 'verified' ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
				$cur         = get_post_meta( $id, '_dck_gift_currency', true );
				$cur         = $cur ? $cur : 'KES';
				$tot[ $cur ] = ( isset( $tot[ $cur ] ) ? $tot[ $cur ] : 0 ) + (float) get_post_meta( $id, '_dck_gift_amount', true );
			}
		}
		$events = array();
		if ( post_type_exists( 'dck_event' ) && function_exists( 'dck_rsvp_count' ) ) {
			foreach ( get_posts( array( 'post_type' => 'dck_event', 'posts_per_page' => 5, 'meta_query' => array( array( 'key' => '_dck_event_rsvp', 'value' => '1' ), array( 'key' => '_dck_event_date', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=' ) ), 'orderby' => 'meta_value', 'meta_key' => '_dck_event_date', 'order' => 'ASC' ) ) as $e ) { // phpcs:ignore WordPress.DB.SlowDBQuery
				$events[] = array( 'id' => $e->ID, 'title' => $e->post_title, 'date' => get_post_meta( $e->ID, '_dck_event_date', true ), 'count' => dck_rsvp_count( $e->ID ), 'cap' => (int) get_post_meta( $e->ID, '_dck_event_capacity', true ) );
			}
		}
		return array(
			'cards'  => array(
				array( __( 'Gifts to verify', 'dck-elite' ), dck_ov_count( 'dck_gift', '_dck_gift_status', 'pending' ), admin_url( 'edit.php?post_type=dck_gift&dck_status=pending' ), 'money-alt', 'amber' ),
				array( __( 'New visitors to welcome', 'dck-elite' ), dck_ov_count( 'dck_visitor', '_dck_v_status', 'new' ), admin_url( 'edit.php?post_type=dck_visitor&dck_status=new' ), 'groups', 'blue' ),
				array( __( 'Pastoral requests', 'dck-elite' ), dck_ov_count( 'dck_care', '_dck_status', 'new' ), admin_url( 'edit.php?post_type=dck_care&dck_status=new' ), 'heart', 'red' ),
				array( __( 'New volunteers & joiners', 'dck-elite' ), dck_ov_count( 'dck_volunteer', '_dck_status', 'new' ), admin_url( 'edit.php?post_type=dck_volunteer&dck_status=new' ), 'admin-users', 'green' ),
			),
			'week'   => array(
				'visitors' => dck_ov_count( 'dck_visitor', '', '', '7 days ago' ),
				'gifts'    => dck_ov_count( 'dck_gift', '', '', '7 days ago' ),
				'rsvps'    => dck_ov_count( 'dck_rsvp', '', '', '7 days ago' ),
				'care'     => dck_ov_count( 'dck_care', '', '', '7 days ago' ),
			),
			'totals' => $tot,
			'events' => $events,
		);
	}
}

add_action(
	'admin_menu',
	function () {
		$cb = 'dck_ov_render';
		if ( isset( $GLOBALS['admin_page_hooks']['dck-church'] ) ) {
			add_submenu_page( 'dck-church', __( 'Church Tools Overview', 'dck-elite' ), __( 'Overview', 'dck-elite' ), 'edit_pages', 'dck-overview', $cb, 0 );
		} else {
			add_menu_page( __( 'Church Tools Overview', 'dck-elite' ), __( 'Church Tools', 'dck-elite' ), 'edit_pages', 'dck-overview', $cb, 'dashicons-admin-tools', 3 );
		}
	},
	20
);

if ( ! function_exists( 'dck_ov_render' ) ) {
	function dck_ov_render() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$d    = dck_ov_data();
		$user = wp_get_current_user();
		$hour = (int) wp_date( 'G' );
		$greet = $hour < 12 ? __( 'Good morning', 'dck-elite' ) : ( $hour < 17 ? __( 'Good afternoon', 'dck-elite' ) : __( 'Good evening', 'dck-elite' ) );
		?>
		<div class="wrap dck-ov">
			<div class="dck-ov__hero">
				<div>
					<p class="dck-ov__eyebrow"><?php echo esc_html( wp_date( get_option( 'date_format' ) ) ); ?></p>
					<h1><?php echo esc_html( $greet . ', ' . ( $user->first_name ? $user->first_name : $user->display_name ) ); ?></h1>
					<p><?php esc_html_e( 'Here is what needs attention across the church tools today.', 'dck-elite' ); ?></p>
				</div>
				<a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=dck-church&tab=tools' ) ); ?>"><?php esc_html_e( 'Tools settings', 'dck-elite' ); ?></a>
			</div>

			<div class="dck-ov__cards">
				<?php foreach ( $d['cards'] as $c ) : ?>
					<a class="dck-ov__card is-<?php echo esc_attr( $c[4] ); ?>" href="<?php echo esc_url( $c[2] ); ?>">
						<span class="dashicons dashicons-<?php echo esc_attr( $c[3] ); ?>"></span>
						<strong><?php echo esc_html( number_format_i18n( $c[1] ) ); ?></strong>
						<span><?php echo esc_html( $c[0] ); ?></span>
						<em><?php echo esc_html( $c[1] ? __( 'Open list →', 'dck-elite' ) : __( 'All caught up', 'dck-elite' ) ); ?></em>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="dck-ov__grid">
				<section class="dck-ov__panel">
					<h2><?php esc_html_e( 'Verified giving this month', 'dck-elite' ); ?></h2>
					<?php if ( $d['totals'] ) : ?>
						<ul class="dck-ov__totals">
							<?php foreach ( $d['totals'] as $cur => $amt ) : ?>
								<li><span><?php echo esc_html( $cur ); ?></span><strong><?php echo esc_html( dck_ct_money( $amt, $cur ) ); ?></strong></li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'No verified gifts yet this month.', 'dck-elite' ); ?></p>
					<?php endif; ?>
					<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_gift_csv' ), 'dck_gift_csv' ) ); ?>"><?php esc_html_e( 'Download giving CSV', 'dck-elite' ); ?></a></p>
				</section>

				<section class="dck-ov__panel">
					<h2><?php esc_html_e( 'Last 7 days', 'dck-elite' ); ?></h2>
					<ul class="dck-ov__stats">
						<li><strong><?php echo esc_html( number_format_i18n( $d['week']['visitors'] ) ); ?></strong><span><?php esc_html_e( 'New visitor cards', 'dck-elite' ); ?></span></li>
						<li><strong><?php echo esc_html( number_format_i18n( $d['week']['gifts'] ) ); ?></strong><span><?php esc_html_e( 'Gifts recorded', 'dck-elite' ); ?></span></li>
						<li><strong><?php echo esc_html( number_format_i18n( $d['week']['rsvps'] ) ); ?></strong><span><?php esc_html_e( 'Event registrations', 'dck-elite' ); ?></span></li>
						<li><strong><?php echo esc_html( number_format_i18n( $d['week']['care'] ) ); ?></strong><span><?php esc_html_e( 'Pastoral requests', 'dck-elite' ); ?></span></li>
					</ul>
				</section>

				<section class="dck-ov__panel">
					<h2><?php esc_html_e( 'Upcoming events with registration', 'dck-elite' ); ?></h2>
					<?php if ( $d['events'] ) : ?>
						<table class="widefat striped"><tbody>
							<?php foreach ( $d['events'] as $e ) : ?>
								<tr><td><strong><?php echo esc_html( $e['title'] ); ?></strong><br><small><?php echo esc_html( $e['date'] ? wp_date( get_option( 'date_format' ), strtotime( $e['date'] . ' 12:00' ) ) : '' ); ?></small></td>
								<td style="text-align:right"><?php echo esc_html( $e['cap'] ? sprintf( '%d / %d', $e['count'], $e['cap'] ) : (string) $e['count'] ); ?><br><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=dck_rsvp&dck_event=' . $e['id'] ) ); ?>"><?php esc_html_e( 'Check-in list', 'dck-elite' ); ?></a></td></tr>
							<?php endforeach; ?>
						</tbody></table>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'No upcoming events have registration open. Tick "Open registration" on an event.', 'dck-elite' ); ?></p>
					<?php endif; ?>
				</section>

				<section class="dck-ov__panel">
					<h2><?php esc_html_e( 'Setup check', 'dck-elite' ); ?></h2>
					<ul class="dck-ov__check">
						<?php
						$smtp = defined( 'WPMS_PLUGIN_VER' ) || class_exists( 'WPMailSMTP\\Core' ) || class_exists( 'FluentMail\\App\\App' ) || function_exists( 'post_smtp_init' );
						$pg   = function ( $s ) { return (bool) get_page_by_path( $s ); };
						foreach ( array(
							array( is_ssl(), __( 'Site uses HTTPS (needed for the app and secure forms)', 'dck-elite' ), '' ),
							array( (bool) dck_prayer_whatsapp(), __( 'WhatsApp number set', 'dck-elite' ), admin_url( 'admin.php?page=dck-church&tab=church' ) ),
							array( $smtp, __( 'Email sending plugin active (WP Mail SMTP or similar)', 'dck-elite' ), admin_url( 'plugin-install.php?s=wp+mail+smtp&tab=search&type=term' ) ),
							array( $pg( 'new-here' ) && $pg( 'pastoral-care' ) && $pg( 'fellowships' ) && $pg( 'serve' ), __( 'Tool pages exist (I\'m New, Pastoral Care, Fellowships, Serve)', 'dck-elite' ), admin_url( 'edit.php?post_type=page' ) ),
							array( (bool) wp_count_posts( 'dck_fellowship' )->publish, __( 'At least one fellowship group published', 'dck-elite' ), admin_url( 'post-new.php?post_type=dck_fellowship' ) ),
							array( (bool) wp_next_scheduled( 'dck_rsvp_reminders' ), __( 'Event reminder schedule running', 'dck-elite' ), '' ),
							array( class_exists( 'Two_Factor_Core' ), __( 'Two-factor login active', 'dck-elite' ), admin_url( 'plugin-install.php?s=two-factor&tab=search&type=term' ) ),
							array( function_exists( 'dck_pay_ready' ) && dck_pay_ready(), __( 'Paystack key set for instant M-Pesa giving', 'dck-elite' ) . ( function_exists( 'dck_pay_test_mode' ) && dck_pay_test_mode() ? ' (TEST MODE)' : '' ), '' ),
						) as $ck ) {
							echo '<li class="' . ( $ck[0] ? 'is-ok' : 'is-todo' ) . '"><span>' . ( $ck[0] ? '&#10003;' : '!' ) . '</span>' . esc_html( $ck[1] ) . ( ! $ck[0] && $ck[2] ? ' <a href="' . esc_url( $ck[2] ) . '">' . esc_html__( 'Fix', 'dck-elite' ) . '</a>' : '' ) . '</li>';
						}
						?>
					</ul>
					<p class="description"><?php esc_html_e( 'Paystack webhook URL (paste in Paystack > Settings > API Keys & Webhooks):', 'dck-elite' ); ?><br><code style="user-select:all"><?php echo esc_html( rest_url( 'dck/v1/pay/webhook' ) ); ?></code></p>
					<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_ov_testmail' ), 'dck_ov_testmail' ) ); ?>"><?php esc_html_e( 'Send a test email to each team', 'dck-elite' ); ?></a>
					<?php if ( isset( $_GET['dck_mail'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?><br><strong><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['dck_mail'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?></strong><?php endif; ?></p>
				</section>

				<section class="dck-ov__panel">
					<h2><?php esc_html_e( 'Quick links', 'dck-elite' ); ?></h2>
					<div class="dck-ov__links">
						<?php
						foreach ( array(
							array( __( 'Giving Records', 'dck-elite' ), admin_url( 'edit.php?post_type=dck_gift' ) ),
							array( __( 'New Visitors', 'dck-elite' ), admin_url( 'edit.php?post_type=dck_visitor' ) ),
							array( __( 'Pastoral Requests', 'dck-elite' ), admin_url( 'edit.php?post_type=dck_care' ) ),
							array( __( 'Volunteers', 'dck-elite' ), admin_url( 'edit.php?post_type=dck_volunteer' ) ),
							array( __( 'Fellowship Groups', 'dck-elite' ), admin_url( 'edit.php?post_type=dck_fellowship' ) ),
							array( __( 'Events', 'dck-elite' ), admin_url( 'edit.php?post_type=dck_event' ) ),
							array( __( 'Visitor QR code', 'dck-elite' ), admin_url( 'edit.php?post_type=dck_visitor&page=dck-visitor-qr' ) ),
						) as $l ) {
							echo '<a class="button" href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a> ';
						}
						?>
					</div>
				</section>
			</div>
		</div>
		<style>
		.dck-ov{max-width:1200px}
		.dck-ov__hero{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;background:linear-gradient(120deg,#0E1C4D,#24409A);color:#fff;border-radius:18px;padding:26px 30px;margin:14px 0 20px}
		.dck-ov__hero h1{color:#fff;font-size:26px;margin:4px 0 6px;padding:0}
		.dck-ov__hero p{margin:0;color:#d7deff}
		.dck-ov__eyebrow{text-transform:uppercase;letter-spacing:.12em;font-size:11px;color:#E7C86A!important;font-weight:700}
		.dck-ov__cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:20px}
		.dck-ov__card{display:flex;flex-direction:column;gap:4px;padding:18px;border-radius:16px;background:#fff;border:1px solid #e3e6ee;text-decoration:none;color:#1d2327;box-shadow:0 10px 30px -24px rgba(14,28,77,.6);border-top:4px solid #ccc;transition:transform .15s}
		.dck-ov__card:hover{transform:translateY(-2px);color:#1d2327}
		.dck-ov__card .dashicons{font-size:22px;width:22px;height:22px;color:#24409A}
		.dck-ov__card strong{font-size:34px;line-height:1.1;color:#0E1C4D}
		.dck-ov__card em{font-style:normal;font-size:12px;color:#24409A;font-weight:600}
		.dck-ov__card.is-amber{border-top-color:#C9A227}.dck-ov__card.is-blue{border-top-color:#24409A}.dck-ov__card.is-red{border-top-color:#c0392b}.dck-ov__card.is-green{border-top-color:#1f8f4e}
		.dck-ov__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px}
		.dck-ov__panel{background:#fff;border:1px solid #e3e6ee;border-radius:16px;padding:18px 20px}
		.dck-ov__panel h2{margin:0 0 12px;font-size:15px}
		.dck-ov__totals,.dck-ov__stats{list-style:none;margin:0 0 12px;padding:0;display:grid;gap:8px}
		.dck-ov__totals li{display:flex;justify-content:space-between;padding:10px 12px;background:#f6f7fb;border-radius:10px}
		.dck-ov__totals strong{color:#0E1C4D;font-size:16px}
		.dck-ov__stats{grid-template-columns:1fr 1fr}
		.dck-ov__stats li{padding:12px;background:#f6f7fb;border-radius:10px;display:flex;flex-direction:column}
		.dck-ov__stats strong{font-size:24px;color:#0E1C4D}
		.dck-ov__links .button{margin:0 6px 8px 0}
		.dck-ov__check{list-style:none;margin:0 0 12px;padding:0;display:grid;gap:6px}
		.dck-ov__check li{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:10px;background:#f6f7fb}
		.dck-ov__check li span{display:grid;place-items:center;width:22px;height:22px;border-radius:50%;font-weight:700;color:#fff;flex:0 0 auto}
		.dck-ov__check .is-ok span{background:#1f8f4e}.dck-ov__check .is-todo span{background:#C9A227}
		.dck-ov__check a{margin-left:auto}
		</style>
		<?php
	}
}

/* wp-admin Dashboard widget with the four counters. */
add_action(
	'wp_dashboard_setup',
	function () {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'dck_ov_widget',
			__( 'Church Tools - needs attention', 'dck-elite' ),
			function () {
				$d = dck_ov_data();
				echo '<ul style="margin:0">';
				foreach ( $d['cards'] as $c ) {
					echo '<li style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee"><a href="' . esc_url( $c[2] ) . '">' . esc_html( $c[0] ) . '</a><strong>' . esc_html( number_format_i18n( $c[1] ) ) . '</strong></li>';
				}
				echo '</ul><p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=dck-overview' ) ) . '">' . esc_html__( 'Open overview', 'dck-elite' ) . '</a></p>';
			}
		);
	}
);

/* Send one test email per team address so you can confirm delivery. */
add_action(
	'admin_post_dck_ov_testmail',
	function () {
		check_admin_referer( 'dck_ov_testmail' );
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		$to = array_unique( array_filter( array_map( 'dck_ct_email_to', array( 'dck_give_finance_email', 'dck_welcome_email', 'dck_care_email', 'dck_volunteer_email' ) ) ) );
		$ok = 0;
		foreach ( $to as $addr ) {
			if ( wp_mail( $addr, __( 'Test email from the church website', 'dck-elite' ), __( 'If you can read this, notifications from the church tools will reach this inbox. Check your spam folder if other emails are missing.', 'dck-elite' ) ) ) {
				++$ok;
			}
		}
		$msg = sprintf( /* translators: 1: sent, 2: total */ __( 'Test emails sent: %1$d of %2$d (%3$s). Check those inboxes and spam.', 'dck-elite' ), $ok, count( $to ), implode( ', ', $to ) );
		wp_safe_redirect( add_query_arg( 'dck_mail', rawurlencode( $msg ), admin_url( 'admin.php?page=dck-overview' ) ) );
		exit;
	}
);
