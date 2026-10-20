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
