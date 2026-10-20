<?php
/**
 * Small framework for private "record" post types filled from website forms
 * (pastoral requests, volunteers & fellowship joins): register, status pills,
 * row actions, filters, WhatsApp column, CSV export and a details box.
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_rec_register' ) ) {
	/**
	 * @param string $type   Post type.
	 * @param array  $cfg    label, menu, icon, position, statuses [key=>[label,tone]], fields [key=>label], filter (meta key), filter_label, filter_options callable, wa callable($id)=>[phone,text], csv filename.
	 */
	function dck_rec_register( $type, $cfg ) {
		$GLOBALS['dck_rec'][ $type ] = $cfg;
		add_action(
			'init',
			function () use ( $type, $cfg ) {
				register_post_type(
					$type,
					array(
						'labels'          => array(
							'name'          => $cfg['label'],
							'singular_name' => $cfg['single'],
							'menu_name'     => $cfg['label'],
							'search_items'  => sprintf( __( 'Search %s', 'dck-elite' ), strtolower( $cfg['label'] ) ),
							'not_found'     => __( 'Nothing here yet', 'dck-elite' ),
							'edit_item'     => $cfg['single'],
						),
						'public'          => false,
						'show_ui'         => true,
						'show_in_menu'    => isset( $cfg['parent'] ) ? $cfg['parent'] : true,
						'menu_icon'       => $cfg['icon'],
						'menu_position'   => $cfg['position'],
						'supports'        => array( 'title' ),
						'capability_type' => 'page',
						'map_meta_cap'    => true,
						'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
					)
				);
			}
		);

		add_filter(
			'manage_' . $type . '_posts_columns',
			function () use ( $cfg ) {
				$c = array( 'cb' => '<input type="checkbox">', 'title' => __( 'Name', 'dck-elite' ) );
				foreach ( $cfg['columns'] as $k => $l ) {
					$c[ 'dck_' . $k ] = $l;
				}
				$c['dck__status'] = __( 'Status', 'dck-elite' );
				$c['dck__wa']     = __( 'WhatsApp', 'dck-elite' );
				$c['date']        = __( 'Received', 'dck-elite' );
				return $c;
			}
		);
		add_action(
			'manage_' . $type . '_posts_custom_column',
			function ( $col, $id ) use ( $type, $cfg ) {
				if ( 'dck__status' === $col ) {
					$cur = get_post_meta( $id, '_dck_status', true );
					$cur = isset( $cfg['statuses'][ $cur ] ) ? $cur : key( $cfg['statuses'] );
					echo dck_ct_pill( $cfg['statuses'][ $cur ][0], $cfg['statuses'][ $cur ][1] ); // phpcs:ignore WordPress.Security.EscapeOutput
					echo '<div style="margin-top:4px;font-size:12px">';
					foreach ( $cfg['statuses'] as $k => $s ) {
						if ( $k !== $cur ) {
							echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_rec_status&type=' . $type . '&id=' . $id . '&to=' . $k ), 'dck_rec_' . $id ) ) . '">' . esc_html( $s[0] ) . '</a> ';
						}
					}
					echo '</div>';
				} elseif ( 'dck__wa' === $col ) {
					$w = call_user_func( $cfg['wa'], $id );
					$l = dck_ct_wa_link( $w[0], $w[1] );
					if ( $l ) {
						echo '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url( $l ) . '">' . esc_html( isset( $w[2] ) ? $w[2] : __( 'Message', 'dck-elite' ) ) . '</a>';
					}
				} elseif ( 0 === strpos( $col, 'dck_' ) ) {
					$k = substr( $col, 4 );
					$v = get_post_meta( $id, '_dck_' . $k, true );
					if ( isset( $cfg['display'][ $k ] ) ) {
						$v = call_user_func( $cfg['display'][ $k ], $v, $id );
					}
					echo esc_html( is_array( $v ) ? implode( ', ', $v ) : (string) $v );
				}
			},
			10,
			2
		);

		add_action(
			'restrict_manage_posts',
			function ( $pt ) use ( $type, $cfg ) {
				if ( $pt !== $type ) {
					return;
				}
				$cur = isset( $_GET['dck_status'] ) ? sanitize_key( wp_unslash( $_GET['dck_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				echo '<select name="dck_status"><option value="">' . esc_html__( 'All statuses', 'dck-elite' ) . '</option>';
				foreach ( $cfg['statuses'] as $k => $s ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $s[0] ) . '</option>';
				}
				echo '</select>';
				if ( ! empty( $cfg['filter'] ) ) {
					$f = isset( $_GET['dck_f'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_f'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					echo '<select name="dck_f"><option value="">' . esc_html( $cfg['filter_label'] ) . '</option>';
					foreach ( call_user_func( $cfg['filter_options'] ) as $k => $l ) {
						echo '<option value="' . esc_attr( $k ) . '"' . selected( $f, (string) $k, false ) . '>' . esc_html( $l ) . '</option>';
					}
					echo '</select>';
				}
			}
		);
		add_action(
			'pre_get_posts',
			function ( $q ) use ( $type, $cfg ) {
				if ( ! is_admin() || ! $q->is_main_query() || $type !== $q->get( 'post_type' ) ) {
					return;
				}
				$mq = array();
				$st = isset( $_GET['dck_status'] ) ? sanitize_key( wp_unslash( $_GET['dck_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				if ( $st ) {
					$mq[] = array( 'key' => '_dck_status', 'value' => $st );
				}
				$f = isset( $_GET['dck_f'] ) ? sanitize_text_field( wp_unslash( $_GET['dck_f'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				if ( '' !== $f && ! empty( $cfg['filter'] ) ) {
					$mq[] = array( 'key' => '_dck_' . $cfg['filter'], 'value' => $f );
				}
				if ( $mq ) {
					$q->set( 'meta_query', $mq );
				}
			}
		);
		add_action(
			'manage_posts_extra_tablenav',
			function ( $which ) use ( $type ) {
				$s = get_current_screen();
				if ( 'top' === $which && $s && 'edit-' . $type === $s->id ) {
					echo '<div class="alignleft actions"><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dck_rec_csv&type=' . $type ), 'dck_rec_csv_' . $type ) ) . '">' . esc_html__( 'Download CSV', 'dck-elite' ) . '</a></div>';
				}
			}
		);
		add_action(
			'add_meta_boxes_' . $type,
			function () use ( $type, $cfg ) {
				add_meta_box(
					'dck_rec_box',
					__( 'Details', 'dck-elite' ),
					function ( $post ) use ( $cfg ) {
						wp_nonce_field( 'dck_rec_save', 'dck_rec_nonce' );
						echo '<table class="widefat striped"><tbody>';
						foreach ( $cfg['fields'] as $k => $l ) {
							$v = get_post_meta( $post->ID, '_dck_' . $k, true );
							if ( isset( $cfg['display'][ $k ] ) ) {
								$v = call_user_func( $cfg['display'][ $k ], $v, $post->ID );
							}
							echo '<tr><th style="width:180px">' . esc_html( $l ) . '</th><td>' . nl2br( esc_html( is_array( $v ) ? implode( ', ', $v ) : (string) $v ) ) . '</td></tr>';
						}
						echo '</tbody></table>';
						echo '<p><label><strong>' . esc_html__( 'Status', 'dck-elite' ) . '</strong><br><select name="dck_rec_status">';
						$cur = get_post_meta( $post->ID, '_dck_status', true );
						foreach ( $cfg['statuses'] as $k => $s ) {
							echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $s[0] ) . '</option>';
						}
						echo '</select></label></p>';
						echo '<p><label><strong>' . esc_html__( 'Team notes', 'dck-elite' ) . '</strong><br><textarea name="dck_rec_notes" rows="4" style="width:100%">' . esc_textarea( get_post_meta( $post->ID, '_dck_notes', true ) ) . '</textarea></label></p>';
						$w = call_user_func( $cfg['wa'], $post->ID );
						$l = dck_ct_wa_link( $w[0], $w[1] );
						if ( $l ) {
							echo '<p><a class="button button-primary" target="_blank" rel="noopener" href="' . esc_url( $l ) . '">' . esc_html( isset( $w[2] ) ? $w[2] : __( 'Message on WhatsApp', 'dck-elite' ) ) . '</a></p>';
						}
					},
					$type,
					'normal',
					'high'
				);
			}
		);
		add_action(
			'save_post_' . $type,
			function ( $post_id ) use ( $cfg ) {
				if ( ! isset( $_POST['dck_rec_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dck_rec_nonce'] ) ), 'dck_rec_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				$st = isset( $_POST['dck_rec_status'] ) ? sanitize_key( wp_unslash( $_POST['dck_rec_status'] ) ) : '';
				if ( isset( $cfg['statuses'][ $st ] ) ) {
					update_post_meta( $post_id, '_dck_status', $st );
				}
				update_post_meta( $post_id, '_dck_notes', isset( $_POST['dck_rec_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dck_rec_notes'] ) ) : '' );
			}
		);
	}
}

add_action(
	'admin_post_dck_rec_status',
	function () {
		$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
		$id   = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$to   = isset( $_GET['to'] ) ? sanitize_key( wp_unslash( $_GET['to'] ) ) : '';
		check_admin_referer( 'dck_rec_' . $id );
		$cfg = isset( $GLOBALS['dck_rec'][ $type ] ) ? $GLOBALS['dck_rec'][ $type ] : null;
		if ( ! $cfg || get_post_type( $id ) !== $type || ! current_user_can( 'edit_post', $id ) || ! isset( $cfg['statuses'][ $to ] ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		update_post_meta( $id, '_dck_status', $to );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=' . $type ) );
		exit;
	}
);

add_action(
	'admin_post_dck_rec_csv',
	function () {
		$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
		check_admin_referer( 'dck_rec_csv_' . $type );
		$cfg = isset( $GLOBALS['dck_rec'][ $type ] ) ? $GLOBALS['dck_rec'][ $type ] : null;
		if ( ! $cfg || ! current_user_can( 'edit_others_pages' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'dck-elite' ) );
		}
		$rows = array();
		foreach ( get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
			$r = array( get_the_date( 'Y-m-d H:i', $id ) );
			foreach ( $cfg['fields'] as $k => $l ) {
				if ( ! empty( $cfg['csv_skip'] ) && in_array( $k, $cfg['csv_skip'], true ) ) {
					continue;
				}
				$v = get_post_meta( $id, '_dck_' . $k, true );
				if ( isset( $cfg['display'][ $k ] ) ) {
					$v = call_user_func( $cfg['display'][ $k ], $v, $id );
				}
				$r[] = is_array( $v ) ? implode( ', ', $v ) : $v;
			}
			$st    = get_post_meta( $id, '_dck_status', true );
			$r[]   = isset( $cfg['statuses'][ $st ] ) ? $cfg['statuses'][ $st ][0] : '';
			$r[]   = get_post_meta( $id, '_dck_notes', true );
			$rows[] = $r;
		}
		$head = array( 'Received' );
		foreach ( $cfg['fields'] as $k => $l ) {
			if ( empty( $cfg['csv_skip'] ) || ! in_array( $k, $cfg['csv_skip'], true ) ) {
				$head[] = $l;
			}
		}
		$head[] = 'Status';
		$head[] = 'Team notes';
		dck_ct_csv( $cfg['csv'] . '-' . gmdate( 'Y-m-d' ) . '.csv', $head, $rows );
	}
);

if ( ! function_exists( 'dck_rec_insert' ) ) {
	/* Save a record from a form; returns post ID or 0. */
	function dck_rec_insert( $type, $title, $meta, $status ) {
		$id = wp_insert_post( array( 'post_type' => $type, 'post_status' => 'private', 'post_title' => $title ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}
		foreach ( $meta as $k => $v ) {
			update_post_meta( $id, '_dck_' . $k, $v );
		}
		update_post_meta( $id, '_dck_status', $status );
		return $id;
	}
}

/* Create the tool pages once (I'm New is created by visitors.php). */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'dck_tool_pages_v1' ) || ! current_user_can( 'publish_pages' ) ) {
			return;
		}
		foreach ( array( array( 'pastoral-care', 'Pastoral Care', 'template-care.php' ), array( 'fellowships', 'Home Fellowships', 'template-fellowships.php' ), array( 'serve', 'Serve With Us', 'template-serve.php' ) ) as $p ) {
			if ( ! get_page_by_path( $p[0] ) ) {
				$pid = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $p[1], 'post_name' => $p[0], 'post_content' => '' ) );
				if ( $pid && ! is_wp_error( $pid ) ) {
					update_post_meta( $pid, '_wp_page_template', $p[2] );
				}
			}
		}
		update_option( 'dck_tool_pages_v1', 1, false );
	}
);
