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
		echo '<div class="alignleft actions"><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_mem_xlsx' ), 'dck_mem_xlsx' ) ) . '">' . esc_html__( 'Download Excel register', 'dck-core' ) . '</a> <a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=dck_member&page=dck-members-io' ) ) . '">' . esc_html__( 'Import / access', 'dck-core' ) . '</a></div>';
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
		array_unshift( $log, array( 'user' => $user->display_name, 'time' => $when, 'count' => count( $all ), 'ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ) );
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
			__( 'Import & access', 'dck-core' ),
			__( 'Import & access', 'dck-core' ),
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
		$msg = isset( $_GET['dck_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="wrap"><h1>' . esc_html__( 'Member register - import & access', 'dck-core' ) . '</h1>';
		if ( $msg ) {
			echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
		}
		echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Download', 'dck-core' ) . '</h2><p>' . esc_html__( 'A branded Excel workbook: Summary, All Members, one sheet per ministry department, and people not yet in a department. Pastoral notes are never included.', 'dck-core' ) . '</p><p><a class="button button-primary button-hero" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_mem_xlsx' ), 'dck_mem_xlsx' ) ) . '">' . esc_html__( 'Download Excel register', 'dck-core' ) . '</a></p></div>';

		echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Import an existing list (CSV)', 'dck-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Save your Excel list as "CSV UTF-8". First row must be headings. Recognised headings: Name, Phone, Email, Gender, Age group, Marital status, Area, Status, Date joined (YYYY-MM-DD), Baptised, Departments (separate with ;), Fellowship. People already on the register (same phone or email) are updated, not duplicated.', 'dck-core' ) . '</p>';
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="dck_mem_import">';
		wp_nonce_field( 'dck_mem_import' );
		echo '<p><input type="file" name="dck_csv" accept=".csv,text/csv" required> <label><input type="checkbox" name="consent" value="1"> ' . esc_html__( 'These people have agreed to the church keeping their details', 'dck-core' ) . '</label></p><p><button class="button button-primary">' . esc_html__( 'Import', 'dck-core' ) . '</button></p></form></div>';

		if ( current_user_can( 'manage_options' ) ) {
			$ed = get_role( 'editor' );
			echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Who can see the register', 'dck-core' ) . '</h2><p>' . esc_html__( 'Administrators always have access. Only give Editors access if they handle membership.', 'dck-core' ) . '</p>';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="dck_mem_access">';
			wp_nonce_field( 'dck_mem_access' );
			echo '<label><input type="checkbox" name="editors" value="1"' . checked( $ed && $ed->has_cap( DCK_MEMBERS_CAP ), true, false ) . '> ' . esc_html__( 'Editors can view, edit and download the member register', 'dck-core' ) . '</label> <button class="button">' . esc_html__( 'Save', 'dck-core' ) . '</button></form></div>';
		}

		$log = (array) get_option( 'dck_members_export_log', array() );
		echo '<div class="card" style="max-width:820px"><h2>' . esc_html__( 'Download history', 'dck-core' ) . '</h2>';
		if ( $log ) {
			echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'When', 'dck-core' ) . '</th><th>' . esc_html__( 'Who', 'dck-core' ) . '</th><th>' . esc_html__( 'People', 'dck-core' ) . '</th><th>IP</th></tr></thead><tbody>';
			foreach ( $log as $l ) {
				echo '<tr><td>' . esc_html( $l['time'] ) . '</td><td>' . esc_html( $l['user'] ) . '</td><td>' . esc_html( $l['count'] ) . '</td><td>' . esc_html( $l['ip'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		} else {
			echo '<p>' . esc_html__( 'No downloads yet.', 'dck-core' ) . '</p>';
		}
		echo '</div></div>';
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

add_action(
	'admin_post_dck_mem_import',
	function () {
		check_admin_referer( 'dck_mem_import' );
		if ( ! current_user_can( DCK_MEMBERS_CAP ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-core' ) );
		}
		$f = isset( $_FILES['dck_csv'] ) ? $_FILES['dck_csv'] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! $f || ! empty( $f['error'] ) || ! is_uploaded_file( $f['tmp_name'] ) || $f['size'] > 2 * MB_IN_BYTES || ! preg_match( '/\.csv$/i', (string) $f['name'] ) ) {
			wp_die( esc_html__( 'Please upload a .csv file smaller than 2 MB.', 'dck-core' ) );
		}
		$h = fopen( $f['tmp_name'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $h ) {
			wp_die( esc_html__( 'Could not read the file.', 'dck-core' ) );
		}
		$map = array( 'name' => 'name', 'full name' => 'name', 'phone' => 'phone', 'phone number' => 'phone', 'email' => 'email', 'gender' => 'gender', 'age group' => 'age', 'age' => 'age', 'marital status' => 'marital', 'area' => 'area', 'area / estate' => 'area', 'estate' => 'area', 'status' => 'status', 'membership status' => 'status', 'date joined' => 'joined', 'joined' => 'joined', 'baptised' => 'baptised', 'baptized' => 'baptised', 'departments' => 'departments', 'ministry departments' => 'departments', 'department' => 'departments', 'fellowship' => 'fellowship', 'home fellowship' => 'fellowship' );
		$cols = array();
		$head = fgetcsv( $h );
		foreach ( (array) $head as $i => $t ) {
			$t = strtolower( trim( preg_replace( '/^\xEF\xBB\xBF/', '', (string) $t ) ) );
			if ( isset( $map[ $t ] ) ) {
				$cols[ $i ] = $map[ $t ];
			}
		}
		if ( ! in_array( 'name', $cols, true ) ) {
			wp_die( esc_html__( 'The first row must include a "Name" heading.', 'dck-core' ) );
		}
		$new = 0;
		$upd = 0;
		$n   = 0;
		while ( ( $row = fgetcsv( $h ) ) !== false && $n < 5000 ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
			$n++;
			$d = array();
			foreach ( $cols as $i => $k ) {
				$d[ $k ] = isset( $row[ $i ] ) ? trim( (string) $row[ $i ] ) : '';
			}
			$name = sanitize_text_field( isset( $d['name'] ) ? $d['name'] : '' );
			if ( '' === $name ) {
				continue;
			}
			foreach ( array( 'gender', 'age', 'marital', 'status', 'baptised' ) as $sk ) {
				if ( isset( $d[ $sk ] ) ) {
					$opts = dck_mem_options( 'baptised' === $sk ? 'yesno' : $sk );
					foreach ( $opts as $o ) {
						if ( 0 === stripos( $o, $d[ $sk ] ) && '' !== $d[ $sk ] ) {
							$d[ $sk ] = $o;
							break;
						}
					}
				}
			}
			if ( ! empty( $d['joined'] ) && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d['joined'] ) ) {
				$ts          = strtotime( $d['joined'] );
				$d['joined'] = $ts ? gmdate( 'Y-m-d', $ts ) : '';
			}
			$deps = array();
			if ( ! empty( $d['departments'] ) ) {
				foreach ( preg_split( '/[;|]/', $d['departments'] ) as $dp ) {
					foreach ( dck_mem_departments() as $known ) {
						if ( '' !== trim( $dp ) && false !== stripos( $known, trim( $dp ) ) ) {
							$deps[] = $known;
						}
					}
				}
			}
			$exist = 0;
			$digits = preg_replace( '/\D/', '', isset( $d['phone'] ) ? $d['phone'] : '' );
			if ( strlen( $digits ) >= 9 ) {
				$f2 = get_posts( array( 'post_type' => 'dck_member', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_dck_m_phone', 'meta_value' => sanitize_text_field( $d['phone'] ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
				$exist = $f2 ? (int) $f2[0] : 0;
			}
			if ( ! $exist && ! empty( $d['email'] ) && is_email( $d['email'] ) ) {
				$f2 = get_posts( array( 'post_type' => 'dck_member', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_dck_m_email', 'meta_value' => sanitize_email( $d['email'] ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
				$exist = $f2 ? (int) $f2[0] : 0;
			}
			if ( $exist ) {
				$old  = dck_mem_get( $exist );
				$d    = array_merge( $old, array_filter( $d, 'strlen' ) );
				$deps = array_values( array_unique( array_merge( $old['departments'], $deps ) ) );
				wp_update_post( array( 'ID' => $exist, 'post_title' => $name ) );
				$id = $exist;
				$upd++;
			} else {
				$id = wp_insert_post( array( 'post_type' => 'dck_member', 'post_status' => 'private', 'post_title' => $name ) );
				if ( ! $id || is_wp_error( $id ) ) {
					continue;
				}
				$new++;
			}
			dck_mem_store( $id, $d, $deps, ! empty( $_POST['consent'] ) || ( $exist && '' !== get_post_meta( $exist, '_dck_m_consent', true ) ) );
		}
		fclose( $h ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		wp_safe_redirect( add_query_arg( 'dck_msg', rawurlencode( sprintf( __( 'Import finished: %1$d added, %2$d updated.', 'dck-core' ), $new, $upd ) ), admin_url( 'edit.php?post_type=dck_member&page=dck-members-io' ) ) );
		exit;
	}
);
