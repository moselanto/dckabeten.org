<?php
/**
 * Church Settings screen: one place to edit all site content.
 *
 * @package DCK_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DCK_CORE_CAP', 'edit_theme_options' );

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'Church Settings', 'dck-core' ),
			__( 'DCK Church', 'dck-core' ),
			DCK_CORE_CAP,
			'dck-church',
			'dck_core_render_page',
			'dashicons-building',
			3
		);
	}
);

/* Quick access from the admin bar on every screen. */
add_action(
	'admin_bar_menu',
	function ( $bar ) {
		if ( ! current_user_can( DCK_CORE_CAP ) ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'dck-church',
				'title' => '<span class="ab-icon dashicons dashicons-building" style="top:2px"></span>' . esc_html__( 'Church Settings', 'dck-core' ),
				'href'  => admin_url( 'admin.php?page=dck-church' ),
			)
		);
	},
	80
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'toplevel_page_dck-church' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'dck-core-admin', DCK_CORE_URL . 'assets/admin.css', array(), DCK_CORE_VERSION );
		wp_enqueue_script( 'dck-core-admin', DCK_CORE_URL . 'assets/admin.js', array( 'jquery' ), DCK_CORE_VERSION, true );
		wp_localize_script(
			'dck-core-admin',
			'DCK_CORE',
			array(
				'choose' => __( 'Choose image', 'dck-core' ),
				'use'    => __( 'Use this image', 'dck-core' ),
				'remove' => __( 'Remove this item?', 'dck-core' ),
			)
		);
	}
);

/* ---------------- Rendering ---------------- */

if ( ! function_exists( 'dck_core_current_value' ) ) {
	function dck_core_current_value( $field ) {
		$v = get_theme_mod( $field['id'], null );
		if ( null === $v || '' === $v || ( is_array( $v ) && empty( $v ) ) ) {
			return $field['default'];
		}
		return $v;
	}
}

if ( ! function_exists( 'dck_core_render_field' ) ) {
	function dck_core_render_field( $field ) {
		$id    = $field['id'];
		$name  = 'dck[' . $id . ']';
		$value = dck_core_current_value( $field );
		$type  = $field['type'];
		$label = $field['label'];
		$hay   = strtolower( $label . ' ' . $field['help'] );

		echo '<div class="dck-field dck-field--' . esc_attr( $type ) . '" data-search="' . esc_attr( $hay ) . '">';

		if ( in_array( $type, array( 'services', 'sections', 'gatherings' ), true ) ) {
			$rows = is_array( $value ) ? array_values( $value ) : array();
			if ( 'services' === $type ) {
				$cols = array( 'name' => array( __( 'Gathering', 'dck-core' ), 'text' ), 'time' => array( __( 'Day / time', 'dck-core' ), 'text' ) );
			} elseif ( 'sections' === $type ) {
				$cols = array( 'title' => array( __( 'Section title', 'dck-core' ), 'text' ), 'body' => array( __( 'Content', 'dck-core' ), 'textarea' ) );
			} else {
				$cols = array(
					'name' => array( __( 'Name', 'dck-core' ), 'text' ),
					'type' => array( __( 'Shows under', 'dck-core' ), 'select', array( 'sunday' => __( 'Sunday programme', 'dck-core' ), 'weekly' => __( 'During the week', 'dck-core' ), 'monthly' => __( 'Monthly / special rhythm', 'dck-core' ) ) ),
					'when' => array( __( 'Day(s) - e.g. Sunday, Tuesday, Friday & Saturday', 'dck-core' ), 'text' ),
					'time' => array( __( 'Time - e.g. 10:40 AM - 1:00 PM', 'dck-core' ), 'text' ),
					'desc' => array( __( 'Short description', 'dck-core' ), 'textarea' ),
					'img'  => array( __( 'Photo', 'dck-core' ), 'image' ),
					'live' => array( __( 'Streamed live online', 'dck-core' ), 'check' ),
				);
			}
			echo '<div class="dck-repeater dck-repeater--' . esc_attr( $type ) . '" data-name="' . esc_attr( $name ) . '" data-type="' . esc_attr( $type ) . '">';
			echo '<div class="dck-rows">';
			$rows[] = array(); // Template row.
			$last   = count( $rows ) - 1;
			foreach ( $rows as $i => $row ) {
				$is_tpl = ( $i === $last );
				$idx    = $is_tpl ? '__i__' : $i;
				$na     = $is_tpl ? 'data-name' : 'name';
				echo '<div class="dck-row' . ( $is_tpl ? ' dck-row--tpl' : '' ) . '"' . ( $is_tpl ? ' hidden' : '' ) . '>';
				echo '<span class="dck-row__handle" title="' . esc_attr__( 'Move', 'dck-core' ) . '">';
				echo '<button type="button" class="button-link dck-up" aria-label="' . esc_attr__( 'Move up', 'dck-core' ) . '">&#9650;</button>';
				echo '<button type="button" class="button-link dck-down" aria-label="' . esc_attr__( 'Move down', 'dck-core' ) . '">&#9660;</button></span>';
				echo '<div class="dck-row__cols">';
				foreach ( $cols as $k => $c ) {
					$v    = isset( $row[ $k ] ) ? $row[ $k ] : '';
					$nm   = $name . '[' . $idx . '][' . $k . ']';
					$kind = $c[1];
					echo '<label class="dck-col dck-col--' . esc_attr( $k ) . ' dck-col--' . esc_attr( $kind ) . '"><span>' . esc_html( $c[0] ) . '</span>';
					if ( 'textarea' === $kind ) {
						echo '<textarea rows="' . ( 'body' === $k ? 6 : 2 ) . '" ' . $na . '="' . esc_attr( $nm ) . '">' . esc_textarea( $v ) . '</textarea>'; // phpcs:ignore
					} elseif ( 'select' === $kind ) {
						echo '<select ' . $na . '="' . esc_attr( $nm ) . '">'; // phpcs:ignore
						foreach ( $c[2] as $ov => $ol ) {
							echo '<option value="' . esc_attr( $ov ) . '"' . selected( $v, $ov, false ) . '>' . esc_html( $ol ) . '</option>';
						}
						echo '</select>';
					} elseif ( 'check' === $kind ) {
						echo '<input type="checkbox" value="1" ' . $na . '="' . esc_attr( $nm ) . '"' . checked( ! empty( $v ), true, false ) . '>'; // phpcs:ignore
					} elseif ( 'image' === $kind ) {
						echo '</label><div class="dck-col dck-col--img"><span class="dck-col__lbl">' . esc_html( $c[0] ) . '</span><div class="dck-image">';
						echo '<img class="dck-image__preview" src="' . esc_url( $v ) . '" alt=""' . ( $v ? '' : ' hidden' ) . '>';
						echo '<input type="hidden" ' . $na . '="' . esc_attr( $nm ) . '" value="' . esc_attr( $v ) . '">'; // phpcs:ignore
						echo '<button type="button" class="button dck-image__pick">' . esc_html__( 'Choose photo', 'dck-core' ) . '</button> ';
						echo '<button type="button" class="button-link dck-image__clear"' . ( $v ? '' : ' hidden' ) . '>' . esc_html__( 'Use default', 'dck-core' ) . '</button>';
						echo '</div></div>';
						continue;
					} else {
						echo '<input type="text" ' . $na . '="' . esc_attr( $nm ) . '" value="' . esc_attr( $v ) . '">'; // phpcs:ignore
					}
					echo '</label>';
				}
				echo '</div><button type="button" class="button-link dck-row__del">' . esc_html__( 'Remove', 'dck-core' ) . '</button></div>';
			}
			echo '</div><button type="button" class="button dck-add">+ ' . esc_html__( 'Add item', 'dck-core' ) . '</button>';
			echo '<input type="hidden" name="' . esc_attr( $name ) . '[__present]" value="1">';
			echo '</div>';
		} else {
			echo '<label class="dck-field__label" for="dck-' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
			switch ( $type ) {
				case 'textarea':
				case 'html':
					echo '<textarea id="dck-' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="3" placeholder="' . esc_attr( is_string( $field['default'] ) ? $field['default'] : '' ) . '">' . esc_textarea( get_theme_mod( $id, '' ) ) . '</textarea>';
					break;
				case 'image':
					$url = (string) get_theme_mod( $id, '' );
					if ( is_numeric( $url ) ) {
						$url = (string) wp_get_attachment_image_url( (int) $url, 'medium' );
					}
					echo '<div class="dck-image">';
					echo '<img class="dck-image__preview" src="' . esc_url( $url ) . '" alt=""' . ( $url ? '' : ' hidden' ) . '>';
					echo '<input type="hidden" id="dck-' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $url ) . '">';
					echo '<button type="button" class="button dck-image__pick">' . esc_html__( 'Choose image', 'dck-core' ) . '</button> ';
					echo '<button type="button" class="button-link dck-image__clear"' . ( $url ? '' : ' hidden' ) . '>' . esc_html__( 'Use default', 'dck-core' ) . '</button>';
					echo '</div>';
					break;
				default:
					$t = in_array( $type, array( 'url', 'email', 'date' ), true ) ? $type : 'text';
					echo '<input type="' . esc_attr( $t ) . '" class="regular-text" id="dck-' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( get_theme_mod( $id, '' ) ) . '" placeholder="' . esc_attr( is_string( $field['default'] ) ? $field['default'] : '' ) . '">';
			}
		}
		if ( $field['help'] ) {
			echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
		}
		echo '</div>';
	}
}

if ( ! function_exists( 'dck_core_render_page' ) ) {
	function dck_core_render_page() {
		if ( ! current_user_can( DCK_CORE_CAP ) ) {
			return;
		}
		$schema = dck_core_settings_schema();
		$tab    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'church'; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! isset( $schema[ $tab ] ) ) {
			$tab = 'church';
		}
		$t = $schema[ $tab ];
		?>
		<div class="wrap dck-wrap">
			<div class="dck-head">
				<div>
					<h1><?php esc_html_e( 'Church Settings', 'dck-core' ); ?></h1>
					<p><?php esc_html_e( 'Edit everything visitors see, in one place. Blank fields use the built-in wording, so nothing ever breaks.', 'dck-core' ); ?></p>
				</div>
				<div class="dck-head__actions">
					<input type="search" class="dck-search" placeholder="<?php esc_attr_e( 'Find a setting on this tab...', 'dck-core' ); ?>">
					<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'dck-core' ); ?></a>
				</div>
			</div>

			<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved. Your changes are live. If you use a caching plugin or Cloudflare, purge the cache to see them immediately.', 'dck-core' ); ?></p></div>
			<?php endif; ?>

			<nav class="dck-tabs" aria-label="<?php esc_attr_e( 'Settings sections', 'dck-core' ); ?>">
				<?php foreach ( $schema as $key => $s ) : ?>
					<a class="dck-tab<?php echo $key === $tab ? ' is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=dck-church&tab=' . $key ) ); ?>">
						<span class="dashicons <?php echo esc_attr( $s['icon'] ); ?>"></span><?php echo esc_html( $s['title'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="dck-form">
				<input type="hidden" name="action" value="dck_core_save">
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
				<?php wp_nonce_field( 'dck_core_save_' . $tab ); ?>
				<?php if ( ! empty( $t['intro'] ) ) : ?>
					<p class="dck-intro"><?php echo esc_html( $t['intro'] ); ?></p>
				<?php endif; ?>
				<?php foreach ( $t['sections'] as $sec ) : ?>
					<section class="dck-card">
						<h2><?php echo esc_html( $sec['title'] ); ?></h2>
						<div class="dck-grid">
							<?php
							foreach ( $sec['fields'] as $field ) {
								dck_core_render_field( $field );
							}
							?>
						</div>
					</section>
				<?php endforeach; ?>
				<div class="dck-savebar">
					<?php submit_button( __( 'Save changes', 'dck-core' ), 'primary large', 'submit', false ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

/* ---------------- Saving ---------------- */

if ( ! function_exists( 'dck_core_sanitize' ) ) {
	function dck_core_sanitize( $field, $raw ) {
		switch ( $field['type'] ) {
			case 'url':
			case 'image':
				return esc_url_raw( trim( (string) $raw ) );
			case 'email':
				return sanitize_email( (string) $raw );
			case 'textarea':
				return sanitize_textarea_field( (string) $raw );
			case 'html':
				return wp_kses_post( (string) $raw );
			case 'date':
				$raw = sanitize_text_field( (string) $raw );
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
			case 'gatherings':
				$out = array();
				if ( is_array( $raw ) ) {
					unset( $raw['__present'], $raw['__i__'] );
					foreach ( $raw as $row ) {
						if ( ! is_array( $row ) || empty( $row['name'] ) ) {
							continue;
						}
						$t     = isset( $row['type'] ) && in_array( $row['type'], array( 'sunday', 'weekly', 'monthly' ), true ) ? $row['type'] : 'sunday';
						$out[] = array(
							'name' => sanitize_text_field( $row['name'] ),
							'type' => $t,
							'when' => isset( $row['when'] ) ? sanitize_text_field( $row['when'] ) : '',
							'time' => isset( $row['time'] ) ? sanitize_text_field( $row['time'] ) : '',
							'desc' => isset( $row['desc'] ) ? sanitize_textarea_field( $row['desc'] ) : '',
							'img'  => isset( $row['img'] ) ? esc_url_raw( $row['img'] ) : '',
							'live' => empty( $row['live'] ) ? '' : '1',
						);
					}
				}
				return $out;
			case 'services':
			case 'sections':
				$out = array();
				if ( is_array( $raw ) ) {
					unset( $raw['__present'], $raw['__i__'] );
					foreach ( $raw as $row ) {
						if ( ! is_array( $row ) ) {
							continue;
						}
						if ( 'services' === $field['type'] ) {
							$n = isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '';
							$m = isset( $row['time'] ) ? sanitize_text_field( $row['time'] ) : '';
							if ( '' !== $n ) {
								$out[] = array( 'name' => $n, 'time' => $m );
							}
						} else {
							$ti = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
							$bo = isset( $row['body'] ) ? wp_kses_post( $row['body'] ) : '';
							if ( '' !== $ti ) {
								$out[] = array( 'title' => $ti, 'body' => $bo );
							}
						}
					}
				}
				return $out;
			default:
				return sanitize_text_field( (string) $raw );
		}
	}
}

add_action(
	'admin_post_dck_core_save',
	function () {
		if ( ! current_user_can( DCK_CORE_CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'dck-core' ) );
		}
		$tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : '';
		check_admin_referer( 'dck_core_save_' . $tab );
		$schema = dck_core_settings_schema();
		if ( ! isset( $schema[ $tab ] ) ) {
			wp_die( esc_html__( 'Unknown settings section.', 'dck-core' ) );
		}
		$in = isset( $_POST['dck'] ) && is_array( $_POST['dck'] ) ? wp_unslash( $_POST['dck'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $schema[ $tab ]['sections'] as $sec ) {
			foreach ( $sec['fields'] as $field ) {
				if ( ! array_key_exists( $field['id'], $in ) ) {
					continue;
				}
				$val = dck_core_sanitize( $field, $in[ $field['id'] ] );
				if ( '' === $val || ( is_array( $val ) && empty( $val ) && ! in_array( $field['type'], array( 'sections', 'services', 'gatherings' ), true ) ) ) {
					remove_theme_mod( $field['id'] );
				} else {
					set_theme_mod( $field['id'], $val );
				}
			}
		}
		do_action( 'dck_core_settings_saved', $tab );
		wp_safe_redirect( admin_url( 'admin.php?page=dck-church&tab=' . $tab . '&saved=1' ) );
		exit;
	}
);
