<?php
/**
 * Church Member Register (private).
 * - Members: wp-admin > Members (administrators only by default; capability dck_manage_members).
 * - Grouped by Ministry Departments (same list as DCK Church > Church Tools > ministries).
 * - Branded Excel export: Summary + All Members + one sheet per department + "Not yet in a department".
 * - CSV import for existing lists. Every export is logged (who / when / how many).
 * Data never leaves wp-admin: no public pages, no REST, nothing cached.
 *
 * @package DCK_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DCK_MEMBERS_CAP', 'dck_manage_members' );

/* Give administrators the capability once (editors can be added from Members > Import / Export). */
add_action(
	'init',
	function () {
		if ( get_option( 'dck_members_cap_v1' ) ) {
			return;
		}
		$r = get_role( 'administrator' );
		if ( $r ) {
			$r->add_cap( DCK_MEMBERS_CAP );
		}
		update_option( 'dck_members_cap_v1', 1, false );
	}
);

add_action(
	'init',
	function () {
		$c = DCK_MEMBERS_CAP;
		register_post_type(
			'dck_member',
			array(
				'labels'              => array(
					'name'          => __( 'Members', 'dck-core' ),
					'singular_name' => __( 'Member', 'dck-core' ),
					'add_new'       => __( 'Add member', 'dck-core' ),
					'add_new_item'  => __( 'Add member', 'dck-core' ),
					'edit_item'     => __( 'Edit member', 'dck-core' ),
					'search_items'  => __( 'Search members', 'dck-core' ),
					'not_found'     => __( 'No members yet', 'dck-core' ),
					'menu_name'     => __( 'Members', 'dck-core' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_rest'        => false,
				'show_in_nav_menus'   => false,
				'menu_icon'           => 'dashicons-id-alt',
				'menu_position'       => 4,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => false,
				'capabilities'        => array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'delete_posts', 'publish_posts', 'read_private_posts', 'create_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts' ), $c ),
			)
		);
	}
);

/* Always stored as private. */
add_filter(
	'wp_insert_post_data',
	function ( $d ) {
		if ( 'dck_member' === $d['post_type'] && ! in_array( $d['post_status'], array( 'trash', 'auto-draft' ), true ) ) {
			$d['post_status'] = 'private';
		}
		return $d;
	}
);

/* ---------- Field definitions ---------- */
if ( ! function_exists( 'dck_mem_departments' ) ) {
	function dck_mem_departments() {
		$raw = get_theme_mod( 'dck_volunteer_ministries', '' );
		if ( '' === trim( (string) $raw ) ) {
			$raw = "Praise and Worship\nUshering and Protocol\nMedia and Sound\nChildren's Ministry (Sunday School)\nYouth Ministry\nIntercessory Prayer\nCare and Welfare\nHospitality\nEvangelism and Outreach\nCleaning and Maintenance";
		}
		return array_values( array_filter( array_map( 'trim', explode( "\n", (string) $raw ) ) ) );
	}
}
if ( ! function_exists( 'dck_mem_options' ) ) {
	function dck_mem_options( $k ) {
		$o = array(
			'gender' => array( 'Female', 'Male' ),
			'age'    => array( 'Child (0-12)', 'Teen (13-17)', 'Youth (18-35)', 'Adult (36-59)', 'Senior (60+)' ),
			'status' => array( 'Member', 'Regular attender', 'New believer', 'Visitor', 'Inactive / moved' ),
			'yesno'  => array( 'Yes', 'No' ),
			'marital' => array( 'Single', 'Married', 'Widowed', 'Prefer not to say' ),
		);
		return isset( $o[ $k ] ) ? $o[ $k ] : array();
	}
}
if ( ! function_exists( 'dck_mem_fields' ) ) {
	/* key => [label, type, options-key]. Kept short on purpose (data minimisation). */
	function dck_mem_fields() {
		return array(
			'phone'      => array( __( 'Phone', 'dck-core' ), 'text', '' ),
			'email'      => array( __( 'Email', 'dck-core' ), 'email', '' ),
			'gender'     => array( __( 'Gender', 'dck-core' ), 'select', 'gender' ),
			'age'        => array( __( 'Age group', 'dck-core' ), 'select', 'age' ),
			'marital'    => array( __( 'Marital status', 'dck-core' ), 'select', 'marital' ),
			'area'       => array( __( 'Area / estate', 'dck-core' ), 'text', '' ),
			'status'     => array( __( 'Membership status', 'dck-core' ), 'select', 'status' ),
			'joined'     => array( __( 'Date joined', 'dck-core' ), 'date', '' ),
			'baptised'   => array( __( 'Water baptised', 'dck-core' ), 'select', 'yesno' ),
			'fellowship' => array( __( 'Home fellowship', 'dck-core' ), 'text', '' ),
			'notes'      => array( __( 'Pastoral notes (staff only, never exported)', 'dck-core' ), 'textarea', '' ),
		);
	}
}
if ( ! function_exists( 'dck_mem_get' ) ) {
	function dck_mem_get( $id ) {
		$m = array( 'name' => get_the_title( $id ) );
		foreach ( array_keys( dck_mem_fields() ) as $k ) {
			$m[ $k ] = (string) get_post_meta( $id, '_dck_m_' . $k, true );
		}
		$d               = get_post_meta( $id, '_dck_m_departments', true );
		$m['departments'] = is_array( $d ) ? $d : array();
		$m['consent']     = (string) get_post_meta( $id, '_dck_m_consent', true );
		return $m;
	}
}

/* ---------- Edit screen ---------- */
add_action(
	'add_meta_boxes_dck_member',
	function () {
		add_meta_box(
			'dck_mem_box',
			__( 'Member details', 'dck-core' ),
			function ( $post ) {
				wp_nonce_field( 'dck_mem_save', 'dck_mem_nonce' );
				$m = dck_mem_get( $post->ID );
				echo '<style>.dck-mg{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px 18px}.dck-mg label{display:block;font-weight:600;margin-bottom:4px}.dck-mg input,.dck-mg select,.dck-mg textarea{width:100%}.dck-mdeps{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:6px;margin-top:6px}.dck-mdeps label{font-weight:400;padding:8px 10px;border:1px solid #dcdcde;border-radius:8px}</style><div class="dck-mg">';
				foreach ( dck_mem_fields() as $k => $f ) {
					$v = $m[ $k ];
					echo '<div' . ( 'textarea' === $f[1] ? ' style="grid-column:1/-1"' : '' ) . '><label for="dckm-' . esc_attr( $k ) . '">' . esc_html( $f[0] ) . '</label>';
					if ( 'select' === $f[1] ) {
						echo '<select id="dckm-' . esc_attr( $k ) . '" name="dck_m[' . esc_attr( $k ) . ']"><option value="">-</option>';
						foreach ( dck_mem_options( $f[2] ) as $o ) {
							echo '<option' . selected( $v, $o, false ) . '>' . esc_html( $o ) . '</option>';
						}
						echo '</select>';
					} elseif ( 'textarea' === $f[1] ) {
						echo '<textarea id="dckm-' . esc_attr( $k ) . '" name="dck_m[' . esc_attr( $k ) . ']" rows="3">' . esc_textarea( $v ) . '</textarea>';
					} else {
						echo '<input type="' . esc_attr( $f[1] ) . '" id="dckm-' . esc_attr( $k ) . '" name="dck_m[' . esc_attr( $k ) . ']" value="' . esc_attr( $v ) . '">';
					}
					echo '</div>';
				}
				echo '</div><p style="margin-top:16px"><strong>' . esc_html__( 'Ministry departments', 'dck-core' ) . '</strong></p><div class="dck-mdeps">';
				foreach ( dck_mem_departments() as $d ) {
					echo '<label><input type="checkbox" name="dck_m_dep[]" value="' . esc_attr( $d ) . '"' . checked( in_array( $d, $m['departments'], true ), true, false ) . '> ' . esc_html( $d ) . '</label>';
				}
				echo '</div><p style="margin-top:16px"><label><input type="checkbox" name="dck_m_consent" value="1"' . checked( '' !== $m['consent'], true, false ) . '> ' . esc_html__( 'Member agreed to the church keeping these details (Kenya Data Protection Act 2019)', 'dck-core' ) . '</label>' . ( $m['consent'] ? ' <em>(' . esc_html( $m['consent'] ) . ')</em>' : '' ) . '</p>';
			},
			'dck_member',
			'normal',
			'high'
		);
	}
);
add_action(
	'save_post_dck_member',
	function ( $post_id ) {
		if ( ! isset( $_POST['dck_mem_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dck_mem_nonce'] ) ), 'dck_mem_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( DCK_MEMBERS_CAP ) ) {
			return;
		}
		$in = isset( $_POST['dck_m'] ) ? (array) wp_unslash( $_POST['dck_m'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		dck_mem_store( $post_id, $in, isset( $_POST['dck_m_dep'] ) ? (array) wp_unslash( $_POST['dck_m_dep'] ) : array(), ! empty( $_POST['dck_m_consent'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}
);

if ( ! function_exists( 'dck_mem_store' ) ) {
	function dck_mem_store( $id, $in, $deps, $consent ) {
		foreach ( dck_mem_fields() as $k => $f ) {
			$v = isset( $in[ $k ] ) ? $in[ $k ] : '';
			if ( 'email' === $f[1] ) {
				$v = sanitize_email( $v );
			} elseif ( 'textarea' === $f[1] ) {
				$v = sanitize_textarea_field( $v );
			} elseif ( 'date' === $f[1] ) {
				$v = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $v ) ? $v : '';
			} elseif ( 'select' === $f[1] ) {
				$v = in_array( $v, dck_mem_options( $f[2] ), true ) ? $v : '';
			} else {
				$v = sanitize_text_field( $v );
			}
			update_post_meta( $id, '_dck_m_' . $k, $v );
		}
		$deps = array_values( array_intersect( array_map( 'sanitize_text_field', (array) $deps ), dck_mem_departments() ) );
		update_post_meta( $id, '_dck_m_departments', $deps );
		$had = get_post_meta( $id, '_dck_m_consent', true );
		update_post_meta( $id, '_dck_m_consent', $consent ? ( $had ? $had : current_time( 'Y-m-d' ) ) : '' );
	}
}

/* ---------- List screen ---------- */
add_filter(
	'manage_dck_member_posts_columns',
	function () {
		return array(
			'cb'          => '<input type="checkbox">',
			'title'       => __( 'Name', 'dck-core' ),
			'dck_phone'   => __( 'Phone', 'dck-core' ),
			'dck_status'  => __( 'Status', 'dck-core' ),
			'dck_deps'    => __( 'Ministry departments', 'dck-core' ),
			'dck_area'    => __( 'Area', 'dck-core' ),
			'dck_consent' => __( 'Consent', 'dck-core' ),
		);
	}
);
add_action(
	'manage_dck_member_posts_custom_column',
	function ( $col, $id ) {
		$m = dck_mem_get( $id );
		if ( 'dck_phone' === $col ) {
			echo esc_html( $m['phone'] );
		} elseif ( 'dck_status' === $col ) {
			echo esc_html( $m['status'] );
		} elseif ( 'dck_deps' === $col ) {
			echo esc_html( implode( ', ', $m['departments'] ) );
		} elseif ( 'dck_area' === $col ) {
			echo esc_html( $m['area'] );
		} elseif ( 'dck_consent' === $col ) {
			echo $m['consent'] ? '&#10003;' : '<span style="color:#b32d2e">' . esc_html__( 'Missing', 'dck-core' ) . '</span>';
		}
	},
	10,
	2
);
add_action(
	'restrict_manage_posts',
	function ( $pt ) {
		if ( 'dck_member' !== $pt ) {
			return;
		}
		$dep = isset( $_GET['dck_dep'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_dep'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$st  = isset( $_GET['dck_st'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_st'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<select name="dck_dep"><option value="">' . esc_html__( 'All departments', 'dck-core' ) . '</option>';
		foreach ( dck_mem_departments() as $d ) {
			echo '<option' . selected( $dep, $d, false ) . '>' . esc_html( $d ) . '</option>';
		}
		echo '</select><select name="dck_st"><option value="">' . esc_html__( 'All statuses', 'dck-core' ) . '</option>';
		foreach ( dck_mem_options( 'status' ) as $s ) {
			echo '<option' . selected( $st, $s, false ) . '>' . esc_html( $s ) . '</option>';
		}
		echo '</select>';
	}
);
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || 'dck_member' !== $q->get( 'post_type' ) ) {
			return;
		}
		$mq  = array();
		$dep = isset( $_GET['dck_dep'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_dep'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$st  = isset( $_GET['dck_st'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_st'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $dep ) {
			$mq[] = array( 'key' => '_dck_m_departments', 'value' => '"' . $dep . '"', 'compare' => 'LIKE' );
		}
		if ( $st ) {
			$mq[] = array( 'key' => '_dck_m_status', 'value' => $st );
		}
		if ( $mq ) {
			$q->set( 'meta_query', $mq );
		}
		if ( ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'title' );
			$q->set( 'order', 'ASC' );
		}
	}
);
add_action(
	'manage_posts_extra_tablenav',
	function ( $which ) {
		$s = get_current_screen();
		if ( 'top' !== $which || ! $s || 'edit-dck_member' !== $s->id || ! current_user_can( DCK_MEMBERS_CAP ) ) {
			return;
		}
		echo '<div class="alignleft actions"><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_mem_xlsx' ), 'dck_mem_xlsx' ) ) . '">' . esc_html__( 'Download Excel register', 'dck-core' ) . '</a> <a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_mem_template' ), 'dck_mem_template' ) ) . '">' . esc_html__( 'Blank Excel template', 'dck-core' ) . '</a> <a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=dck_member&page=dck-members-io' ) ) . '">' . esc_html__( 'Upload / access', 'dck-core' ) . '</a></div>';
	}
);

/* ---------- Excel export ---------- */
add_action(
	'admin_post_dck_mem_xlsx',
	function () {
		check_admin_referer( 'dck_mem_xlsx' );
		if ( ! current_user_can( DCK_MEMBERS_CAP ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-core' ) );
		}
		require_once DCK_CORE_DIR . 'includes/xlsx-writer.php';
		$ids = get_posts( array( 'post_type' => 'dck_member', 'post_status' => array( 'private', 'publish', 'draft' ), 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'title', 'order' => 'ASC' ) );
		$all = array();
		foreach ( $ids as $id ) {
			$all[] = dck_mem_get( $id );
		}
		$head   = array( 'No.', 'Full name', 'Phone', 'Email', 'Gender', 'Age group', 'Marital status', 'Area / estate', 'Status', 'Date joined', 'Baptised', 'Ministry departments', 'Home fellowship' );
		$widths = array( 6, 28, 18, 30, 10, 15, 15, 18, 17, 13, 10, 40, 20 );
		$to_rows = function ( $list ) {
			$r = array();
			foreach ( array_values( $list ) as $i => $m ) {
				$r[] = array( $i + 1, $m['name'], $m['phone'], $m['email'], $m['gender'], $m['age'], $m['marital'], $m['area'], $m['status'], $m['joined'] ? wp_date( 'j M Y', strtotime( $m['joined'] . ' 12:00' ) ) : '', $m['baptised'], implode( ', ', $m['departments'] ), $m['fellowship'] );
			}
			return $r;
		};
		$count = function ( $field, $opts ) use ( $all ) {
			$o = array();
			foreach ( $opts as $x ) {
				$o[] = array( $x, count( array_filter( $all, function ( $m ) use ( $field, $x ) { return $m[ $field ] === $x; } ) ) );
			}
			$none = count( array_filter( $all, function ( $m ) use ( $field ) { return '' === $m[ $field ]; } ) );
			if ( $none ) {
				$o[] = array( 'Not recorded', $none );
			}
			return $o;
		};
		$deps   = dck_mem_departments();
		$by_dep = array();
		foreach ( $deps as $d ) {
			$by_dep[ $d ] = array_filter( $all, function ( $m ) use ( $d ) { return in_array( $d, $m['departments'], true ); } );
		}
		$nodep = array_filter( $all, function ( $m ) { return ! $m['departments']; } );
		$dep_counts = array();
		foreach ( $by_dep as $d => $l ) {
			$dep_counts[] = array( $d, count( $l ) );
		}
		$dep_counts[] = array( 'Not yet in a department', count( $nodep ) );

		$user  = wp_get_current_user();
		$when  = wp_date( 'j F Y, H:i' );
		$x     = new DCK_Xlsx();
		$x->org    = get_bloginfo( 'name' );
		$x->footer = 'Exported by ' . $user->display_name . ' on ' . $when . ' - handle with care, do not share';
		$logo      = function_exists( 'get_template_directory' ) ? get_template_directory() . '/assets/images/logo.png' : '';
		$x->set_logo( apply_filters( 'dck_members_logo_file', $logo ) );
		$x->add_sheet(
			'Summary',
			'Church Membership Register',
			'Summary as at ' . wp_date( 'j F Y' ),
			array(),
			array(),
			array( 38, 4, 16, 18 ),
			array(
				array( 'Overview', array( array( 'Total people on the register', count( $all ) ), array( 'With consent recorded', count( array_filter( $all, function ( $m ) { return '' !== $m['consent']; } ) ) ), array( 'Baptised', count( array_filter( $all, function ( $m ) { return 'Yes' === $m['baptised']; } ) ) ) ) ),
				array( 'By membership status', $count( 'status', dck_mem_options( 'status' ) ) ),
				array( 'By ministry department', $dep_counts ),
				array( 'By gender', $count( 'gender', dck_mem_options( 'gender' ) ) ),
				array( 'By age group', $count( 'age', dck_mem_options( 'age' ) ) ),
			)
		);
		$x->add_sheet( 'All Members', 'All Members', count( $all ) . ' people · ' . wp_date( 'j F Y' ), $head, $to_rows( $all ), $widths );
		foreach ( $by_dep as $d => $l ) {
			$x->add_sheet( $d, $d, 'Ministry department · ' . count( $l ) . ' members', $head, $to_rows( $l ), $widths );
		}
		$x->add_sheet( 'No department yet', 'Not yet in a department', count( $nodep ) . ' people to invite into a ministry', $head, $to_rows( $nodep ), $widths );

		$tmp = wp_tempnam( 'dck-register' );
		if ( ! $x->save( $tmp ) ) {
			wp_die( esc_html__( 'Could not build the Excel file (the server needs the PHP Zip extension).', 'dck-core' ) );
		}
		$log   = (array) get_option( 'dck_members_export_log', array() );
		array_unshift( $log, array( 'user' => $user->display_name, 'time' => $when, 'count' => count( $all ), 'what' => 'Register report', 'ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ) );
		update_option( 'dck_members_export_log', array_slice( $log, 0, 50 ), false );

		nocache_headers();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename="DCK-Member-Register-' . wp_date( 'Y-m-d' ) . '.xlsx"' );
		header( 'Content-Length: ' . filesize( $tmp ) );
		readfile( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		wp_delete_file( $tmp );
		exit;
	}
);

/* ---------- Import / access page ---------- */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=dck_member',
			__( 'Template & upload', 'dck-core' ),
			__( 'Template & upload', 'dck-core' ),
			DCK_MEMBERS_CAP,
			'dck-members-io',
			'dck_mem_io_page'
		);
	}
);

if ( ! function_exists( 'dck_mem_io_page' ) ) {
	function dck_mem_io_page() {
		if ( ! current_user_can( DCK_MEMBERS_CAP ) ) {
			return;
		}
		$msg  = isset( $_GET['dck_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$errs = get_transient( 'dck_mem_import_errs_' . get_current_user_id() );
		$tpl  = wp_nonce_url( admin_url( 'admin-post.php?action=dck_mem_template' ), 'dck_mem_template' );
		$tplw = wp_nonce_url( admin_url( 'admin-post.php?action=dck_mem_template&with=1' ), 'dck_mem_template' );
		$cnt  = (int) wp_count_posts( 'dck_member' )->private;
		?>
		<div class="wrap dck-mio">
			<div class="dck-mio__hero">
				<div><h1><?php esc_html_e( 'Member Register', 'dck-core' ); ?></h1><p><?php echo esc_html( sprintf( _n( '%d person on the register.', '%d people on the register.', $cnt, 'dck-core' ), $cnt ) ); ?> <?php esc_html_e( 'Download the Excel template, add or update members, then upload it back.', 'dck-core' ); ?></p></div>
				<a class="button button-hero" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_mem_xlsx' ), 'dck_mem_xlsx' ) ); ?>"><?php esc_html_e( 'Download full register report', 'dck-core' ); ?></a>
			</div>
			<?php if ( $msg ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>
			<?php if ( $errs ) : delete_transient( 'dck_mem_import_errs_' . get_current_user_id() ); ?>
				<div class="notice notice-warning"><p><strong><?php esc_html_e( 'Some rows were skipped:', 'dck-core' ); ?></strong></p><ul style="list-style:disc;margin-left:20px"><?php foreach ( array_slice( (array) $errs, 0, 20 ) as $e ) : ?><li><?php echo esc_html( $e ); ?></li><?php endforeach; ?></ul></div>
			<?php endif; ?>
			<div class="dck-mio__steps">
				<section class="dck-mio__step">
					<span class="dck-mio__n">1</span>
					<h2><?php esc_html_e( 'Download the template', 'dck-core' ); ?></h2>
					<p><?php esc_html_e( 'A branded Excel file with drop-down lists for gender, age group, status, baptism, consent and a Yes column for each Ministry Department.', 'dck-core' ); ?></p>
					<p><a class="button button-primary" href="<?php echo esc_url( $tpl ); ?>"><?php esc_html_e( 'Blank template', 'dck-core' ); ?></a>
					<a class="button" href="<?php echo esc_url( $tplw ); ?>"><?php esc_html_e( 'Template with current members', 'dck-core' ); ?></a></p>
					<p class="description"><?php esc_html_e( 'Use "with current members" to correct or update people already on the register.', 'dck-core' ); ?></p>
				</section>
				<section class="dck-mio__step">
					<span class="dck-mio__n">2</span>
					<h2><?php esc_html_e( 'Fill it in Excel or Google Sheets', 'dck-core' ); ?></h2>
					<ul>
						<li><?php esc_html_e( 'One person per row, on the "Members" sheet. Full name and phone are required.', 'dck-core' ); ?></li>
						<li><?php esc_html_e( 'Pick from the drop-downs; type "Yes" under each department the person serves in.', 'dck-core' ); ?></li>
						<li><?php esc_html_e( 'Dates as 2024-06-30. Leave the grey Member ID column as it is.', 'dck-core' ); ?></li>
						<li><?php esc_html_e( 'Keep the headings and sheet name unchanged.', 'dck-core' ); ?></li>
					</ul>
				</section>
				<section class="dck-mio__step">
					<span class="dck-mio__n">3</span>
					<h2><?php esc_html_e( 'Upload it back', 'dck-core' ); ?></h2>
					<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="dck_mem_import">
						<?php wp_nonce_field( 'dck_mem_import' ); ?>
						<p><input type="file" name="dck_file" accept=".xlsx,.csv" required></p>
						<p><label><input type="checkbox" name="consent" value="1"> <?php esc_html_e( 'Everyone in this file agreed to the church keeping their details (used where the Consent column is empty)', 'dck-core' ); ?></label></p>
						<p><button class="button button-primary"><?php esc_html_e( 'Upload and update register', 'dck-core' ); ?></button></p>
						<p class="description"><?php esc_html_e( '.xlsx (recommended) or .csv, up to 5 MB. People are matched by Member ID, then phone, then email - nobody is duplicated.', 'dck-core' ); ?></p>
					</form>
				</section>
			</div>
			<?php if ( current_user_can( 'manage_options' ) ) : $ed = get_role( 'editor' ); ?>
				<section class="dck-mio__card"><h2><?php esc_html_e( 'Who can see the register', 'dck-core' ); ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="dck_mem_access"><?php wp_nonce_field( 'dck_mem_access' ); ?>
						<label><input type="checkbox" name="editors" value="1"<?php checked( $ed && $ed->has_cap( DCK_MEMBERS_CAP ) ); ?>> <?php esc_html_e( 'Editors can view, edit, import and download the member register (administrators always can)', 'dck-core' ); ?></label> <button class="button"><?php esc_html_e( 'Save', 'dck-core' ); ?></button></form>
				</section>
			<?php endif; ?>
			<section class="dck-mio__card"><h2><?php esc_html_e( 'Activity log', 'dck-core' ); ?></h2>
				<?php $log = (array) get_option( 'dck_members_export_log', array() ); ?>
				<?php if ( $log ) : ?>
					<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'When', 'dck-core' ); ?></th><th><?php esc_html_e( 'Who', 'dck-core' ); ?></th><th><?php esc_html_e( 'What', 'dck-core' ); ?></th><th><?php esc_html_e( 'People', 'dck-core' ); ?></th><th>IP</th></tr></thead><tbody>
					<?php foreach ( $log as $l ) : ?><tr><td><?php echo esc_html( $l['time'] ); ?></td><td><?php echo esc_html( $l['user'] ); ?></td><td><?php echo esc_html( isset( $l['what'] ) ? $l['what'] : 'Register download' ); ?></td><td><?php echo esc_html( $l['count'] ); ?></td><td><?php echo esc_html( $l['ip'] ); ?></td></tr><?php endforeach; ?>
					</tbody></table>
				<?php else : ?><p><?php esc_html_e( 'No downloads or uploads yet.', 'dck-core' ); ?></p><?php endif; ?>
			</section>
		</div>
		<style>
		.dck-mio{max-width:1180px}
		.dck-mio__hero{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;background:linear-gradient(120deg,#0E1C4D,#24409A);color:#fff;border-radius:18px;padding:24px 28px;margin:14px 0 18px;border-bottom:4px solid #C9A227}
		.dck-mio__hero h1{color:#fff;margin:0 0 6px;padding:0}.dck-mio__hero p{margin:0;color:#d7deff}
		.dck-mio__steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:18px}
		.dck-mio__step,.dck-mio__card{background:#fff;border:1px solid #e3e6ee;border-radius:16px;padding:20px 22px;position:relative}
		.dck-mio__card{margin-bottom:16px}
		.dck-mio__step h2,.dck-mio__card h2{margin:0 0 8px;font-size:16px;color:#0E1C4D}
		.dck-mio__step ul{list-style:disc;margin:0 0 0 18px}
		.dck-mio__n{display:grid;place-items:center;width:34px;height:34px;border-radius:50%;background:#C9A227;color:#0E1C4D;font-weight:800;margin-bottom:10px}
		</style>
		<?php
	}
}

add_action(
	'admin_post_dck_mem_access',
	function () {
		check_admin_referer( 'dck_mem_access' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-core' ) );
		}
		$ed = get_role( 'editor' );
		if ( $ed ) {
			if ( ! empty( $_POST['editors'] ) ) {
				$ed->add_cap( DCK_MEMBERS_CAP );
			} else {
				$ed->remove_cap( DCK_MEMBERS_CAP );
			}
		}
		wp_safe_redirect( add_query_arg( 'dck_msg', rawurlencode( __( 'Access updated.', 'dck-core' ) ), admin_url( 'edit.php?post_type=dck_member&page=dck-members-io' ) ) );
		exit;
	}
);


/* ---------- Template (download -> fill -> upload) ---------- */
if ( ! function_exists( 'dck_mem_template_cols' ) ) {
	function dck_mem_template_cols() {
		$c = array(
			array( 'name', 'Full name *', 28 ),
			array( 'phone', 'Phone *', 18 ),
			array( 'email', 'Email', 28 ),
			array( 'gender', 'Gender', 11 ),
			array( 'age', 'Age group', 15 ),
			array( 'marital', 'Marital status', 15 ),
			array( 'area', 'Area / estate', 18 ),
			array( 'status', 'Membership status', 18 ),
			array( 'joined', 'Date joined (YYYY-MM-DD)', 16 ),
			array( 'baptised', 'Baptised', 10 ),
			array( 'fellowship', 'Home fellowship', 18 ),
			array( 'consent', 'Consent given', 11 ),
		);
		foreach ( dck_mem_departments() as $d ) {
			$c[] = array( 'dep:' . $d, $d, max( 12, min( 22, strlen( $d ) * 0.75 ) ) );
		}
		$c[] = array( 'id', 'Member ID (do not change)', 14 );
		return $c;
	}
}

add_action(
	'admin_post_dck_mem_template',
	function () {
		check_admin_referer( 'dck_mem_template' );
		if ( ! current_user_can( DCK_MEMBERS_CAP ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-core' ) );
		}
		require_once DCK_CORE_DIR . 'includes/xlsx-writer.php';
		$with = ! empty( $_GET['with'] );
		$cols = dck_mem_template_cols();
		$head = array();
		$wid  = array();
		$val  = array();
		$cst  = array();
		$hst  = array();
		foreach ( $cols as $i => $c ) {
			$head[] = $c[1];
			$wid[]  = $c[2];
			$k      = $c[0];
			if ( 0 === strpos( $k, 'dep:' ) ) {
				$val[ $i ] = array( 'Yes' );
				$hst[ $i ] = DCK_Xlsx::S_HEADGOLD;
			} elseif ( in_array( $k, array( 'gender', 'age', 'marital', 'status' ), true ) ) {
				$val[ $i ] = dck_mem_options( $k );
			} elseif ( in_array( $k, array( 'baptised', 'consent' ), true ) ) {
				$val[ $i ] = array( 'Yes', 'No' );
			}
			if ( in_array( $k, array( 'phone', 'joined' ), true ) ) {
				$cst[ $i ] = DCK_Xlsx::S_TEXT;
			}
			if ( 'id' === $k ) {
				$cst[ $i ] = DCK_Xlsx::S_LOCK;
				$hst[ $i ] = DCK_Xlsx::S_LOCK;
			}
		}
		$rows = array();
		if ( $with ) {
			foreach ( get_posts( array( 'post_type' => 'dck_member', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'title', 'order' => 'ASC' ) ) as $id ) {
				$m   = dck_mem_get( $id );
				$row = array();
				foreach ( $cols as $c ) {
					$k = $c[0];
					if ( 0 === strpos( $k, 'dep:' ) ) {
						$row[] = in_array( substr( $k, 4 ), $m['departments'], true ) ? 'Yes' : '';
					} elseif ( 'consent' === $k ) {
						$row[] = $m['consent'] ? 'Yes' : '';
					} elseif ( 'id' === $k ) {
						$row[] = (string) $id;
					} else {
						$row[] = isset( $m[ $k ] ) ? (string) $m[ $k ] : '';
					}
				}
				$rows[] = $row;
			}
		}
		$x      = new DCK_Xlsx();
		$x->org = get_bloginfo( 'name' );
		$user   = wp_get_current_user();
		$x->footer = 'Template from ' . $x->org . ' - ' . wp_date( 'j M Y' ) . ' - confidential';
		$x->set_logo( apply_filters( 'dck_members_logo_file', function_exists( 'get_template_directory' ) ? get_template_directory() . '/assets/images/logo.png' : '' ) );
		$x->add_sheet(
			'Instructions',
			'Member Register Template',
			'How to fill in and upload this file',
			array(),
			array(),
			array( 26, 2, 96, 4 ),
			array(
				array(
					'Steps',
					array(
						array( '1. Open the Members sheet', 'Add one person per row. Full name and Phone are required; everything else is optional but helpful.' ),
						array( '2. Use the drop-downs', 'Gender, Age group, Marital status, Membership status, Baptised and Consent have drop-down lists - click the cell and pick.' ),
						array( '3. Ministry Departments', 'The gold columns are the Ministry Departments. Type or pick "Yes" under every department the person serves in. Leave blank if not.' ),
						array( '4. Dates and phones', 'Write dates as 2024-06-30. Phone numbers can be 0712 345 678 or +254 712 345 678 (the column keeps the leading zero).' ),
						array( '5. Do not change', 'Keep the sheet name "Members", the headings, and the grey Member ID column exactly as they are. New people simply have no Member ID.' ),
						array( '6. Upload', 'wp-admin > Members > Import & access > Upload. People are matched by Member ID, then phone, then email, so nobody is duplicated.' ),
					),
				),
				array(
					'Privacy',
					array(
						array( 'Consent', 'Only add people who agreed to the church keeping their details (Kenya Data Protection Act 2019). Mark "Yes" in the Consent column.' ),
						array( 'Keep it safe', 'Do not email this file around. Store it on a password-protected computer and delete copies after uploading.' ),
					),
				),
			)
		);
		$x->add_sheet( 'Members', 'Members', $with ? count( $rows ) . ' people - edit, add new rows below, then upload' : 'Add one person per row, then upload', $head, $rows, $wid, array(), array( 'validations' => $val, 'col_styles' => $cst, 'head_styles' => $hst, 'blank_rows' => 300 ) );
		$tmp = wp_tempnam( 'dck-template' );
		if ( ! $x->save( $tmp ) ) {
			wp_die( esc_html__( 'Could not build the Excel file (the server needs the PHP Zip extension).', 'dck-core' ) );
		}
		if ( $with ) {
			$log = (array) get_option( 'dck_members_export_log', array() );
			array_unshift( $log, array( 'user' => $user->display_name, 'time' => wp_date( 'j F Y, H:i' ), 'count' => count( $rows ), 'what' => 'Template with members', 'ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ) );
			update_option( 'dck_members_export_log', array_slice( $log, 0, 50 ), false );
		}
		nocache_headers();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename="DCK-Member-Template' . ( $with ? '-with-members' : '' ) . '-' . wp_date( 'Y-m-d' ) . '.xlsx"' );
		header( 'Content-Length: ' . filesize( $tmp ) );
		readfile( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		wp_delete_file( $tmp );
		exit;
	}
);

/* ---------- Import (.xlsx template or .csv) ---------- */
if ( ! function_exists( 'dck_mem_import_rows' ) ) {
	/**
	 * @param array $rows   Row number => [col => value].
	 * @param bool  $consent_all Tick-box fallback.
	 * @return array [added, updated, errors[]]
	 */
	function dck_mem_import_rows( $rows, $consent_all ) {
		$alias = array( 'full name' => 'name', 'name' => 'name', 'phone' => 'phone', 'phone number' => 'phone', 'email' => 'email', 'gender' => 'gender', 'age group' => 'age', 'age' => 'age', 'marital status' => 'marital', 'area / estate' => 'area', 'area' => 'area', 'estate' => 'area', 'membership status' => 'status', 'status' => 'status', 'date joined' => 'joined', 'joined' => 'joined', 'baptised' => 'baptised', 'baptized' => 'baptised', 'home fellowship' => 'fellowship', 'fellowship' => 'fellowship', 'consent given' => 'consent', 'consent' => 'consent', 'departments' => 'departments', 'ministry departments' => 'departments', 'member id' => 'id' );
		$deps  = dck_mem_departments();
		$cols  = array();
		$hrow  = null;
		foreach ( $rows as $rn => $vals ) {
			foreach ( $vals as $ci => $v ) {
				$k = strtolower( trim( preg_replace( '/\s*\(.*\)\s*|\*/', '', preg_replace( '/^\xEF\xBB\xBF/', '', (string) $v ) ) ) );
				if ( 'full name' === $k || 'name' === $k ) {
					$hrow = $rn;
					break 2;
				}
			}
		}
		if ( null === $hrow ) {
			return array( 0, 0, array( __( 'Could not find the heading row (it must contain "Full name").', 'dck-core' ) ) );
		}
		foreach ( $rows[ $hrow ] as $ci => $v ) {
			$raw = trim( (string) $v );
			$k   = strtolower( trim( preg_replace( '/\s*\(.*\)\s*|\*/', '', $raw ) ) );
			if ( isset( $alias[ $k ] ) ) {
				$cols[ $ci ] = $alias[ $k ];
				continue;
			}
			foreach ( $deps as $d ) {
				if ( strtolower( $d ) === strtolower( $raw ) ) {
					$cols[ $ci ] = 'dep:' . $d;
				}
			}
		}
		$yes   = function ( $v ) { return in_array( strtolower( trim( (string) $v ) ), array( 'yes', 'y', '1', 'x', 'true', '✓', '✔' ), true ); };
		$added = 0;
		$upd   = 0;
		$errs  = array();
		$done  = 0;
		foreach ( $rows as $rn => $vals ) {
			if ( $rn <= $hrow ) {
				continue;
			}
			if ( ++$done > 5000 ) {
				$errs[] = __( 'Stopped after 5,000 rows.', 'dck-core' );
				break;
			}
			$d      = array();
			$mydeps = array();
			$hasdep = false;
			foreach ( $cols as $ci => $k ) {
				$v = isset( $vals[ $ci ] ) ? trim( (string) $vals[ $ci ] ) : '';
				if ( 0 === strpos( $k, 'dep:' ) ) {
					$hasdep = true;
					if ( $yes( $v ) ) {
						$mydeps[] = substr( $k, 4 );
					}
				} else {
					$d[ $k ] = $v;
				}
			}
			$name = sanitize_text_field( isset( $d['name'] ) ? $d['name'] : '' );
			if ( '' === $name ) {
				if ( array_filter( $d, 'strlen' ) ) {
					$errs[] = sprintf( __( 'Row %d: missing Full name.', 'dck-core' ), $rn );
				}
				continue;
			}
			// Phone: restore leading 0 Excel may have dropped.
			$ph = isset( $d['phone'] ) ? preg_replace( '/[^\d+ ]/', '', $d['phone'] ) : '';
			if ( preg_match( '/^[17]\d{8}$/', $ph ) ) {
				$ph = '0' . $ph;
			}
			$d['phone'] = $ph;
			if ( '' === $ph ) {
				$errs[] = sprintf( __( 'Row %1$d (%2$s): phone is missing - added anyway, please complete it.', 'dck-core' ), $rn, $name );
			}
			if ( ! empty( $d['email'] ) && ! is_email( $d['email'] ) ) {
				$errs[] = sprintf( __( 'Row %1$d (%2$s): email "%3$s" looks wrong and was left out.', 'dck-core' ), $rn, $name, $d['email'] );
				$d['email'] = '';
			}
			foreach ( array( 'gender', 'age', 'marital', 'status', 'baptised' ) as $sk ) {
				if ( ! empty( $d[ $sk ] ) ) {
					foreach ( dck_mem_options( 'baptised' === $sk ? 'yesno' : $sk ) as $o ) {
						if ( 0 === stripos( $o, $d[ $sk ] ) || 0 === stripos( $d[ $sk ], $o ) ) {
							$d[ $sk ] = $o;
							break;
						}
					}
				}
			}
			if ( isset( $d['joined'] ) ) {
				$d['joined'] = class_exists( 'DCK_Xlsx_Reader' ) ? DCK_Xlsx_Reader::date( $d['joined'] ) : $d['joined'];
			}
			if ( ! empty( $d['departments'] ) ) {
				foreach ( preg_split( '/[;|,]/', $d['departments'] ) as $dp ) {
					foreach ( $deps as $known ) {
						if ( '' !== trim( $dp ) && false !== stripos( $known, trim( $dp ) ) ) {
							$mydeps[] = $known;
						}
					}
				}
				$hasdep = true;
			}
			// Match: Member ID, then phone, then email.
			$exist = 0;
			if ( ! empty( $d['id'] ) && ctype_digit( (string) $d['id'] ) && 'dck_member' === get_post_type( (int) $d['id'] ) ) {
				$exist = (int) $d['id'];
			}
			foreach ( array( 'phone', 'email' ) as $mk ) {
				if ( ! $exist && ! empty( $d[ $mk ] ) ) {
					$f = get_posts( array( 'post_type' => 'dck_member', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_dck_m_' . $mk, 'meta_value' => 'email' === $mk ? sanitize_email( $d[ $mk ] ) : sanitize_text_field( $d[ $mk ] ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
					$exist = $f ? (int) $f[0] : 0;
				}
			}
			$consent = isset( $d['consent'] ) && '' !== $d['consent'] ? $yes( $d['consent'] ) : ( $consent_all || ( $exist && '' !== get_post_meta( $exist, '_dck_m_consent', true ) ) );
			if ( $exist ) {
				$old = dck_mem_get( $exist );
				$in  = array_merge( $old, array_filter( $d, 'strlen' ) );
				$in['notes'] = $old['notes'];
				$dp  = $hasdep ? array_values( array_unique( $mydeps ) ) : $old['departments'];
				wp_update_post( array( 'ID' => $exist, 'post_title' => $name ) );
				dck_mem_store( $exist, $in, $dp, $consent );
				$upd++;
			} else {
				$id = wp_insert_post( array( 'post_type' => 'dck_member', 'post_status' => 'private', 'post_title' => $name ) );
				if ( ! $id || is_wp_error( $id ) ) {
					$errs[] = sprintf( __( 'Row %1$d (%2$s): could not be saved.', 'dck-core' ), $rn, $name );
					continue;
				}
				dck_mem_store( $id, $d, array_values( array_unique( $mydeps ) ), $consent );
				$added++;
			}
		}
		return array( $added, $upd, $errs );
	}
}

add_action(
	'admin_post_dck_mem_import',
	function () {
		check_admin_referer( 'dck_mem_import' );
		if ( ! current_user_can( DCK_MEMBERS_CAP ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-core' ) );
		}
		$back = admin_url( 'edit.php?post_type=dck_member&page=dck-members-io' );
		$f    = isset( $_FILES['dck_file'] ) ? $_FILES['dck_file'] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$ext  = $f ? strtolower( pathinfo( (string) $f['name'], PATHINFO_EXTENSION ) ) : '';
		if ( ! $f || ! empty( $f['error'] ) || ! is_uploaded_file( $f['tmp_name'] ) || $f['size'] > 5 * MB_IN_BYTES || ! in_array( $ext, array( 'xlsx', 'csv' ), true ) ) {
			wp_safe_redirect( add_query_arg( 'dck_msg', rawurlencode( __( 'Please upload the .xlsx template (or a .csv) smaller than 5 MB.', 'dck-core' ) ), $back ) );
			exit;
		}
		$rows = array();
		if ( 'xlsx' === $ext ) {
			require_once DCK_CORE_DIR . 'includes/xlsx-writer.php';
			$fh   = fopen( $f['tmp_name'], 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$sig  = $fh ? fread( $fh, 4 ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( $fh ) {
				fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
			if ( "PK\x03\x04" !== $sig ) {
				wp_safe_redirect( add_query_arg( 'dck_msg', rawurlencode( __( 'That file is not a real Excel (.xlsx) file.', 'dck-core' ) ), $back ) );
				exit;
			}
			$rows = DCK_Xlsx_Reader::rows( $f['tmp_name'], 'Members' );
			if ( $rows instanceof Exception ) {
				wp_safe_redirect( add_query_arg( 'dck_msg', rawurlencode( $rows->getMessage() ), $back ) );
				exit;
			}
		} else {
			$h = fopen( $f['tmp_name'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$n = 0;
			while ( $h && ( $r = fgetcsv( $h ) ) !== false && $n < 5100 ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
				$rows[ ++$n ] = $r;
			}
			if ( $h ) {
				fclose( $h ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
		}
		list( $added, $upd, $errs ) = dck_mem_import_rows( $rows, ! empty( $_POST['consent'] ) );
		if ( $errs ) {
			set_transient( 'dck_mem_import_errs_' . get_current_user_id(), $errs, 10 * MINUTE_IN_SECONDS );
		}
		$user = wp_get_current_user();
		$log  = (array) get_option( 'dck_members_export_log', array() );
		array_unshift( $log, array( 'user' => $user->display_name, 'time' => wp_date( 'j F Y, H:i' ), 'count' => $added + $upd, 'what' => 'Upload (' . strtoupper( $ext ) . '): ' . $added . ' added, ' . $upd . ' updated', 'ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ) );
		update_option( 'dck_members_export_log', array_slice( $log, 0, 50 ), false );
		wp_safe_redirect( add_query_arg( 'dck_msg', rawurlencode( sprintf( __( 'Upload finished: %1$d added, %2$d updated.', 'dck-core' ), $added, $upd ) ), $back ) );
		exit;
	}
);
