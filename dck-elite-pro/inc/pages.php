<?php
/**
 * Inner pages content: editable lists + defaults for About, Pastors,
 * Services, Ministries and FAQ. Edit in DCK Church (DCK Core plugin).
 *
 * List fields use one item per line, with parts separated by |
 *
 * @package DCK_Elite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_lines' ) ) {
	/**
	 * Parse a "one per line, parts separated by |" setting into arrays.
	 */
	function dck_lines( $key, $default, $parts = 2 ) {
		$raw = (string) dck_home_val( $key, $default );
		$out = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$p = array_map( 'trim', explode( '|', $line, $parts ) );
			$out[] = array_pad( $p, $parts, '' );
		}
		return $out;
	}
}

/* ---------------- About ---------------- */

if ( ! function_exists( 'dck_about_defaults' ) ) {
	function dck_about_defaults() {
		return array(
			'values'   => "Bible Teaching|Sound, Scripture-based teaching that equips every believer to grow and serve.\nPrayer|A praying church that seeks God together through intercession and overnight Keshas.\nEvangelism|Reaching our community and the nations with the hope of the Gospel.\nWorship|Joyful, heartfelt worship that exalts the name of Jesus Christ.\nExcellence|Giving God our very best in all that we do, with diligence and care.\nIntegrity|Living honestly and faithfully before God and before people.",
			'timeline' => "9 Jun 2017|First Prayer Meeting|A small gathering seeking God for a new work in Uthiru.\n11 Jun 2017|First Sunday Service|The congregation held its very first Sunday worship service.\n18 Jun 2017|Official Launch|The church was officially launched by Rev. Ben Luvutse.\n2020|A New Home|During the COVID-19 pandemic we moved to our current site behind N Market and kept building by faith.",
		);
	}
}

/* ---------------- Pastors ---------------- */

if ( ! function_exists( 'dck_pastor_defs' ) ) {
	function dck_pastor_defs() {
		return array(
			array(
				'k'     => 'justus',
				'focus' => 'Teaching the Word, Church planting, Marriage & family counselling, Community development',
				'img'   => 'assets/images/pastors/justus.webp',
				'ini'   => 'JK',
				'name'  => 'Pastor Justus Kubai',
				'role'  => 'Lead Pastor',
				'title' => 'Founder & Lead Pastor · Marriage Therapist & Counselor',
				'lead'  => 'Founder and Lead Pastor of Deliverance Church Kabete N Market, Justus is a passionate teacher of the Word, a certified church planter, and Executive Director of the Cistern of Hope Foundation.',
				'bio'   => "<p>Justus M. Kubai is a Kenyan citizen by birth: an esteemed pastor, marriage therapist, counselor, community leader and founder, known for his profound impact in both spiritual and community development. He is a committed family man, married to Clare Auma and blessed with four children: Asaph, Brilliant, Splendiferous and Scintillant.</p><p>His spiritual journey began in the early 2000s, when he gave his life to Christ and was delivered from alcohol dependency and unhealthy company. He was discipled at Deliverance Church Kangemi under the late Rev. Dr. James Kuria, growing quickly and serving in ushering, intercessory, youth leadership and evangelism. In 2007 he accompanied Rev. Ambrose Amugah in planting Deliverance Church Muthangari, receiving in-depth mentorship in ministerial work.</p><p>In 2010 Justus answered God's call to pastoral ministry. Together with his wife Clare, he served faithfully as an associate pastor for seven years until 2017, when God led them to plant Deliverance Church Kabete N in Uthiru, Nairobi.</p><p>He holds a Diploma in Theology from KAG East University and several international certifications, including Mission Work from Life Ministry, Church Planting from Dynamic Church Planting International (DCPI, where he is now a Certified Trainer), and Leadership Training from Sentinels for Christ in Colorado. With over 14 years of pastoral experience and seven as Lead Pastor, he is known for dynamic leadership, transformative teaching and a deep anointing in ministry.</p><p>Justus is the founder and Executive Director of the Cistern of Hope Foundation, a nonprofit dedicated to uplifting marginalized communities in Kenya through technical skills training, vocational counseling for youth, and small-business programs that empower women and persons with disabilities. Through his counseling and mentorship, hundreds have overcome personal challenges, grown spiritually and built successful livelihoods.</p><p>An accomplished marriage therapist and counselor, he blends biblical principles with practical strategies to help couples and families resolve conflict and build lasting, healthy relationships. He also teaches the Word and prays for global needs through virtual meetings four days a week with the Warrior Notes fellowship and Sentinels for Christ.</p>",
			),
			array(
				'k'     => 'clare',
				'focus' => 'Women\'s ministry, Pastoral care, Mentoring young women, Community outreach',
				'img'   => 'assets/images/pastors/clare.webp',
				'ini'   => 'CK',
				'name'  => 'Pastor Clare Auma Justus',
				'role'  => 'Co-Pastor',
				'title' => "Co-Pastor & Women's Leader",
				'lead'  => "A passionate preacher with a heart for women and families, Clare serves alongside her husband, leading in pastoral care, women's ministry and community outreach.",
				'bio'   => "<p>Pastor Clare Auma Justus is a passionate servant of God, dedicated to empowering women, strengthening families, and advancing the mission of the church. She serves faithfully alongside her husband, Pastor Justus Kubai, at Deliverance Church Kabete N, where she plays a vital role in pastoral care, women's ministry and community outreach.</p><p>A passionate preacher with a heart for counseling and mentoring women and young girls, she offers spiritual guidance and practical support. Her ministry is marked by compassion, wisdom, and a deep commitment to seeing lives transformed through the power of God's Word.</p><p>As a mother of four and a devoted wife, Clare is a strong example of godly womanhood and leadership. Her life and ministry continue to impact many in the church and beyond.</p>",
			),
			array(
				'k'     => 'henry',
				'focus' => 'Discipleship, Youth ministry, Home churches, Prayer',
				'img'   => 'assets/images/pastors/onzere.webp',
				'ini'   => 'HO',
				'name'  => 'Pastor Henry Onzere',
				'role'  => 'Assistant Pastor',
				'title' => 'Assistant Pastor · Discipleship & Home Churches',
				'lead'  => 'A devoted minister with a passion for discipleship and prayer, Henry oversees youth, discipleship and home-church ministries with humility and solid teaching.',
				'bio'   => '<p>Pastor Henry Onzere serves as the Assistant Pastor at Deliverance Church Kabete N, where he plays a key role in preaching, teaching and pastoral care. He is a devoted minister of the Gospel with a strong passion for discipleship, prayer, and equipping believers for effective Christian living.</p><p>With a heart for servant leadership, he supports the vision of the church by overseeing various ministries, including youth, discipleship and home churches. He is known for his humility, solid teaching of the Word, and his ability to connect with people across all generations.</p><p>His ministry is characterized by faithfulness, integrity and a deep burden for souls. As part of the leadership team, he works closely with the Lead Pastor to shepherd the congregation and nurture spiritual growth within the church community.</p>',
			),
			array(
				'k'     => 'nancy',
				'focus' => 'Intercession, Worship & singing, Encouragement',
				'img'   => 'assets/images/pastors/nancy.webp',
				'ini'   => 'NO',
				'name'  => 'Pastor Nancy Onzere',
				'role'  => 'Assistant Pastor',
				'title' => 'Assistant Pastor · Intercession & Worship',
				'lead'  => 'Serving alongside her husband, Nancy is a pillar of intercession and worship, offering prayerful strength and encouragement to the whole church.',
				'bio'   => '<p>Pastor Nancy Onzere serves faithfully alongside her husband, Pastor Henry Onzere, at Deliverance Church Kabete N. She is actively involved in the intercessory ministry and in singing, providing strong moral and spiritual support to both her family and the church.</p><p>Her quiet strength, prayerful spirit, and deep commitment to the work of God have made her a dependable pillar in ministry. Through her intercession and encouragement, she continues to uplift many and strengthen the body of Christ.</p>',
			),
		);
	}
}

if ( ! function_exists( 'dck_pastors' ) ) {
	/** Pastors with any edits from Church Settings applied. Photos keep the existing dck_pastor_{k}_img keys. */
	function dck_pastors() {
		$out = array();
		foreach ( dck_pastor_defs() as $d ) {
			$k = $d['k'];
			if ( get_theme_mod( 'dck_pastor_' . $k . '_hide', '' ) ) {
				continue;
			}
			$out[] = array(
				'k'     => $k,
				'name'  => dck_home_val( 'dck_pastor_' . $k . '_name', $d['name'] ),
				'role'  => dck_home_val( 'dck_pastor_' . $k . '_role', $d['role'] ),
				'title' => dck_home_val( 'dck_pastor_' . $k . '_title', $d['title'] ),
				'lead'  => dck_home_val( 'dck_pastor_' . $k . '_lead', $d['lead'] ),
				'bio'   => dck_home_val( 'dck_pastor_' . $k . '_bio', $d['bio'] ),
				'focus' => array_filter( array_map( 'trim', explode( ',', (string) dck_home_val( 'dck_pastor_' . $k . '_focus', $d['focus'] ) ) ) ),
				'img'   => ( '' !== trim( (string) get_theme_mod( 'dck_pastor_' . $k . '_img', '' ) ) ) ? trim( (string) get_theme_mod( 'dck_pastor_' . $k . '_img', '' ) ) : get_template_directory_uri() . '/' . $d['img'],
				'photo' => dck_pastor_photo_html( 'dck_pastor_' . $k . '_img', dck_home_val( 'dck_pastor_' . $k . '_name', $d['name'] ), $d['ini'], $d['img'] ),
			);
		}
		return $out;
	}
}

/* ---------------- Services ---------------- */

if ( ! function_exists( 'dck_services_defaults' ) ) {
	function dck_services_defaults() {
		return array(
			'sunday_desc' => "Starting the day seeking God together in fervent prayer.\nDigging deep into the Word for understanding and growth.\nJoyful worship, Spirit-filled preaching, and celebration.\nOvernight prayer on the first and third week of each month.",
			'week'        => "Tuesday|6:00 - 7:00 PM|Prayers in the Church\nThursday|6:00 - 7:00 PM|Midweek Service in Church\nFriday & Saturday|6:00 - 7:00 PM|Home-Based Church Services\nSaturday|From 4:00 PM|Worship Team Practice in Church",
			'expect'      => "A warm welcome|Our ushers and Care Ministry will greet you and help you find a seat.\nJoyful worship|Spirit-filled praise and worship led by our worship team and choir.\nBible-based teaching|Clear, practical preaching from the Word of God.\nSomething for every age|Children, teens and youth have their own ministries, so the whole family grows together.\nCome as you are|There is no dress code. Just come, you are welcome here.\nStay connected|Meet a pastor after the service, or join a ministry or home church.",
		);
	}
}

/* ---------------- Front-end scripts for these pages ---------------- */

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_page_template( 'template-pastors.php' ) ) {
			wp_enqueue_script( 'dck-pastors', get_template_directory_uri() . '/assets/js/pastors.js', array(), DCK_ELITE_VERSION, true );
		}
		if ( is_page_template( array( 'template-ministries.php', 'template-faq.php' ) ) ) {
			wp_enqueue_script( 'dck-pages', get_template_directory_uri() . '/assets/js/pages.js', array(), DCK_ELITE_VERSION, true );
			wp_localize_script(
				'dck-pages',
				'DCK_PAGES',
				array(
					'expand'   => __( 'Expand all', 'dck-elite' ),
					'collapse' => __( 'Collapse all', 'dck-elite' ),
					'none'     => __( 'No matches. Try another word, or contact us.', 'dck-elite' ),
				)
			);
		}
	}
);


/* ---------------- Shared: photo hero + weekly calendar ---------------- */

if ( ! function_exists( 'dck_photo_hero' ) ) {
	/**
	 * Full-bleed photo hero used by Services and Events.
	 *
	 * @param string $img     Background image URL.
	 * @param string $eyebrow Small label.
	 * @param string $title   H1.
	 * @param string $lead    Intro.
	 * @param string $aside   Optional HTML for the right-hand card (already escaped).
	 */
	function dck_photo_hero( $img, $eyebrow, $title, $lead, $aside = '' ) {
		echo '<section class="dck-phero' . ( $aside ? ' dck-phero--split' : '' ) . '">';
		if ( $img ) {
			echo '<img class="dck-phero__img skip-lazy" src="' . esc_url( $img ) . '" alt="" width="1600" height="900" fetchpriority="high" loading="eager" decoding="async" data-no-lazy="1">';
		}
		echo '<div class="dck-phero__inner"><div class="dck-phero__copy">';
		echo '<p class="dck-eyebrow dck-eyebrow--light">' . esc_html( $eyebrow ) . '</p>';
		echo '<h1>' . esc_html( $title ) . '</h1>';
		echo '<p class="dck-phero__lead">' . esc_html( $lead ) . '</p>';
		echo '</div>';
		if ( $aside ) {
			echo '<div class="dck-phero__aside">' . $aside . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts by caller.
		}
		echo '</div></section>';
	}
}

if ( ! function_exists( 'dck_label_days' ) ) {
	/**
	 * Weekday indexes (0 = Sunday) mentioned in a label such as "Friday & Saturday" or "Fridays, 1st & 3rd week".
	 */
	function dck_label_days( $label ) {
		$map = array( 'sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'jumapili' => 0, 'jumatatu' => 1, 'jumanne' => 2, 'jumatano' => 3, 'alhamisi' => 4, 'ijumaa' => 5, 'jumamosi' => 6 );
		$l   = strtolower( (string) $label );
		$out = array();
		if ( false !== strpos( $l, 'daily' ) || false !== strpos( $l, 'every day' ) ) {
			return array( 0, 1, 2, 3, 4, 5, 6 );
		}
		foreach ( $map as $k => $d ) {
			if ( preg_match( '/\b' . $k . '/', $l ) ) {
				$out[] = $d;
			}
		}
		return array_values( array_unique( $out ) );
	}
}

if ( ! function_exists( 'dck_week_calendar' ) ) {
	/**
	 * Render a Monday-first 7-day calendar.
	 *
	 * @param array $items Each: array( 'days' => int[], 'title' => '', 'time' => '', 'note' => '' ).
	 */
	function dck_week_calendar( $items ) {
		$names = array( 1 => __( 'Monday', 'dck-elite' ), 2 => __( 'Tuesday', 'dck-elite' ), 3 => __( 'Wednesday', 'dck-elite' ), 4 => __( 'Thursday', 'dck-elite' ), 5 => __( 'Friday', 'dck-elite' ), 6 => __( 'Saturday', 'dck-elite' ), 0 => __( 'Sunday', 'dck-elite' ) );
		echo '<div class="dck-week3" role="list">';
		foreach ( $names as $d => $name ) {
			$list = array();
			foreach ( $items as $it ) {
				if ( in_array( $d, $it['days'], true ) ) {
					$list[] = $it;
				}
			}
			echo '<div class="dck-week3__day' . ( $list ? '' : ' is-empty' ) . '" role="listitem" data-dow="' . esc_attr( $d ) . '">';
			echo '<p class="dck-week3__name"><span class="dck-week3__full">' . esc_html( $name ) . '</span><span class="dck-week3__short">' . esc_html( function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 3 ) : substr( $name, 0, 3 ) ) . '</span><em class="dck-week3__today">' . esc_html__( 'Today', 'dck-elite' ) . '</em></p>';
			if ( $list ) {
				foreach ( $list as $it ) {
					echo '<div class="dck-week3__ev"><strong>' . esc_html( $it['title'] ) . '</strong>';
					if ( $it['time'] ) {
						echo '<span>' . esc_html( $it['time'] ) . '</span>';
					}
					if ( ! empty( $it['note'] ) ) {
						echo '<em>' . esc_html( $it['note'] ) . '</em>';
					}
					echo '</div>';
				}
			} else {
				echo '<p class="dck-week3__rest">' . esc_html__( 'Rest & personal devotion', 'dck-elite' ) . '</p>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_page_template( array( 'template-services.php', 'template-events.php' ) ) ) {
			wp_enqueue_script( 'dck-week', get_template_directory_uri() . '/assets/js/week.js', array(), DCK_ELITE_VERSION, true );
		}
	}
);


/* ---------------- Gatherings: one editable list for the Services page ---------------- */

if ( ! function_exists( 'dck_gatherings_defaults' ) ) {
	function dck_gatherings_defaults() {
		$img = get_template_directory_uri() . '/assets/images/';
		return array(
			array( 'name' => 'Intensive Prayers', 'type' => 'sunday', 'when' => 'Sunday', 'time' => '8:00 - 9:00 AM', 'desc' => 'Starting the day seeking God together in fervent prayer.', 'img' => $img . 'home/church-slide-4.webp', 'live' => '' ),
			array( 'name' => 'Intensive Bible Study', 'type' => 'sunday', 'when' => 'Sunday', 'time' => '9:30 - 10:30 AM', 'desc' => 'Digging deep into the Word for understanding and growth.', 'img' => $img . 'blog/study.webp', 'live' => '' ),
			array( 'name' => 'Glorious Service', 'type' => 'sunday', 'when' => 'Sunday', 'time' => '10:40 AM - 1:00 PM', 'desc' => 'Joyful worship, Spirit-filled preaching, and celebration.', 'img' => $img . 'home/joy.webp', 'live' => '1' ),
			array( 'name' => 'Keshas (Overnight)', 'type' => 'monthly', 'when' => '1st & 3rd Week', 'time' => '1st & 3rd Week', 'desc' => 'Overnight prayer on the first and third week of each month.', 'img' => $img . 'home/church-slide-3.webp', 'live' => '' ),
			array( 'name' => 'Prayers in the Church', 'type' => 'weekly', 'when' => 'Tuesday', 'time' => '6:00 - 7:00 PM', 'desc' => '', 'img' => '', 'live' => '' ),
			array( 'name' => 'Midweek Service in Church', 'type' => 'weekly', 'when' => 'Thursday', 'time' => '6:00 - 7:00 PM', 'desc' => '', 'img' => '', 'live' => '' ),
			array( 'name' => 'Home-Based Church Services', 'type' => 'weekly', 'when' => 'Friday & Saturday', 'time' => '6:00 - 7:00 PM', 'desc' => '', 'img' => '', 'live' => '' ),
			array( 'name' => 'Worship Team Practice in Church', 'type' => 'weekly', 'when' => 'Saturday', 'time' => 'From 4:00 PM', 'desc' => '', 'img' => '', 'live' => '' ),
		);
	}
}

if ( ! function_exists( 'dck_gatherings_saved' ) ) {
	/** True once the church has saved its own list in DCK Church > Gatherings. */
	function dck_gatherings_saved() {
		$g = get_theme_mod( 'dck_gatherings', array() );
		return is_array( $g ) && ! empty( $g );
	}
}

if ( ! function_exists( 'dck_gatherings' ) ) {
	/**
	 * All gatherings. Before the list is saved, builds it from the older
	 * Service Times / weekly schedule settings so nothing changes visually.
	 */
	function dck_gatherings( $type = '' ) {
		if ( dck_gatherings_saved() ) {
			$all = get_theme_mod( 'dck_gatherings' );
		} else {
			$all  = dck_gatherings_defaults();
			$sd   = dck_services_defaults();
			$desc = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_services_sunday_desc', $sd['sunday_desc'] ) ) ) ) );
			$imgs = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) dck_home_val( 'dck_services_images', '' ) ) ) ) );
			$old  = get_theme_mod( 'dck_service_times', array() );
			if ( is_array( $old ) && $old ) {
				$sun = array();
				foreach ( array_values( $old ) as $i => $r ) {
					if ( empty( $r['name'] ) ) {
						continue;
					}
					$monthly = (bool) preg_match( '/week|month/i', (string) $r['time'] );
					$def     = isset( $all[ $i ] ) ? $all[ $i ] : array( 'img' => '' );
					$sun[]   = array( 'name' => $r['name'], 'type' => $monthly ? 'monthly' : 'sunday', 'when' => $monthly ? $r['time'] : 'Sunday', 'time' => $r['time'], 'desc' => isset( $desc[ $i ] ) ? $desc[ $i ] : '', 'img' => isset( $imgs[ $i ] ) ? $imgs[ $i ] : $def['img'], 'live' => false !== stripos( $r['name'], 'glorious' ) ? '1' : '' );
				}
				$all = array_merge( $sun, array_values( array_filter( $all, function ( $g ) { return 'weekly' === $g['type']; } ) ) );
			}
			if ( '' !== (string) get_theme_mod( 'dck_services_week', '' ) ) {
				$all = array_values( array_filter( $all, function ( $g ) { return 'weekly' !== $g['type']; } ) );
				foreach ( dck_lines( 'dck_services_week', $sd['week'], 3 ) as $w ) {
					$all[] = array( 'name' => $w[2], 'type' => 'weekly', 'when' => $w[0], 'time' => $w[1], 'desc' => '', 'img' => '', 'live' => '' );
				}
			}
		}
		$fallback = get_template_directory_uri() . '/assets/images/home/joy.webp';
		$out      = array();
		foreach ( $all as $g ) {
			if ( empty( $g['name'] ) ) {
				continue;
			}
			$g = wp_parse_args( $g, array( 'type' => 'sunday', 'when' => '', 'time' => '', 'desc' => '', 'img' => '', 'live' => '' ) );
			if ( '' === $g['img'] ) {
				$g['img'] = $fallback;
			}
			if ( '' === $type || $type === $g['type'] || ( is_array( $type ) && in_array( $g['type'], $type, true ) ) ) {
				$out[] = $g;
			}
		}
		return $out;
	}
}

/* Front-end "Edit this page" shortcut for editors. */
if ( ! function_exists( 'dck_edit_bar' ) ) {
	function dck_edit_bar( $tab, $label ) {
		if ( ! current_user_can( 'edit_theme_options' ) || ! defined( 'DCK_CORE_VERSION' ) ) {
			return;
		}
		echo '<a class="dck-editbar" href="' . esc_url( admin_url( 'admin.php?page=dck-church&tab=' . $tab ) ) . '"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>' . esc_html( $label ) . '</a>';
	}
}
