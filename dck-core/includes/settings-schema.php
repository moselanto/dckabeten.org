<?php
/**
 * Church Settings schema: every editable item, grouped into tabs.
 * Keys match the theme's existing theme mods, so the Customizer, the theme and
 * this screen all share one source of data.
 *
 * Field types: text, textarea, html, url, email, image, services, sections.
 *
 * @package DCK_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dck_core_f' ) ) {
	function dck_core_f( $id, $label, $type = 'text', $default = '', $help = '' ) {
		return array(
			'id'      => $id,
			'label'   => $label,
			'type'    => $type,
			'default' => $default,
			'help'    => $help,
		);
	}
}

if ( ! function_exists( 'dck_core_settings_schema' ) ) {
	function dck_core_settings_schema() {
		$f = 'dck_core_f';

		/* ---------- Church & contact ---------- */
		$tabs['church'] = array(
			'title'    => __( 'Church & Contact', 'dck-core' ),
			'icon'     => 'dashicons-building',
			'intro'    => __( 'Your church details. These appear in the top bar, footer, contact page, Google listing data and the AI assistant.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Contact details', 'dck-core' ),
					'fields' => array(
						$f( 'dck_top_phone', __( 'Phone number', 'dck-core' ), 'text', '+254 110 304521', __( 'Shown on the site and used for click-to-call. Include the country code.', 'dck-core' ) ),
						$f( 'dck_prayer_whatsapp', __( 'WhatsApp number', 'dck-core' ), 'text', '254110304521', __( 'Digits only, with country code (for example 254110304521).', 'dck-core' ) ),
						$f( 'dck_top_email', __( 'Email address', 'dck-core' ), 'email', 'info@dckabeten.org' ),
						$f( 'dck_ai_prayer_email', __( 'Prayer team email', 'dck-core' ), 'email', '', __( 'Where prayer requests and photo preferences are sent. Leave blank to use the site admin email.', 'dck-core' ) ),
					),
				),
				array(
					'title'  => __( 'Location', 'dck-core' ),
					'fields' => array(
						$f( 'dck_top_address', __( 'Street address', 'dck-core' ), 'text', 'Behind N Market, Uthiru, Kabete' ),
						$f( 'dck_seo_locality', __( 'Area / locality', 'dck-core' ), 'text', 'Uthiru' ),
						$f( 'dck_seo_region', __( 'City / region', 'dck-core' ), 'text', 'Nairobi' ),
						$f( 'dck_directions_url', __( 'Google Maps / directions link', 'dck-core' ), 'url', 'https://share.google/LJmcXS6D5Nq1aF2R5' ),
					),
				),
				array(
					'title'  => __( 'Footer', 'dck-core' ),
					'fields' => array(
						$f( 'dck_copyright', __( 'Copyright name', 'dck-core' ), 'text', 'Deliverance Church Kabete N' ),
					),
				),
			),
		);

		/* ---------- Social ---------- */
		$tabs['social'] = array(
			'title'    => __( 'Social Media', 'dck-core' ),
			'icon'     => 'dashicons-share',
			'intro'    => __( 'Leave a link blank to hide that icon.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Profiles', 'dck-core' ),
					'fields' => array(
						$f( 'dck_fb_url', 'Facebook', 'url', 'https://www.facebook.com/' ),
						$f( 'dck_yt_url', 'YouTube', 'url', 'https://www.youtube.com/' ),
						$f( 'dck_ig_url', 'Instagram', 'url' ),
						$f( 'dck_tiktok_url', 'TikTok', 'url' ),
						$f( 'dck_x_url', 'X (Twitter)', 'url' ),
					),
				),
			),
		);

		/* ---------- Service times ---------- */
		$tabs['services'] = array(
			'title'    => __( 'Service Times', 'dck-core' ),
			'icon'     => 'dashicons-clock',
			'intro'    => __( 'Add, remove or reorder your gatherings. Used on the homepage, Services page and by the AI assistant.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Weekly gatherings', 'dck-core' ),
					'fields' => array(
						$f( 'dck_service_times', __( 'Gatherings', 'dck-core' ), 'services', function_exists( 'dck_elite_default_service_times' ) ? dck_elite_default_service_times() : array() ),
					),
				),
			),
		);

		/* ---------- Homepage ---------- */
		$hero = array();
		for ( $n = 1; $n <= 3; $n++ ) {
			$hero[] = $f( 'dck_slide' . $n . '_img', sprintf( __( 'Slide %d image', 'dck-core' ), $n ), 'image' );
			$hero[] = $f( 'dck_slide' . $n . '_eyebrow', sprintf( __( 'Slide %d small label', 'dck-core' ), $n ) );
			$hero[] = $f( 'dck_slide' . $n . '_title', sprintf( __( 'Slide %d title', 'dck-core' ), $n ) );
			$hero[] = $f( 'dck_slide' . $n . '_text', sprintf( __( 'Slide %d text', 'dck-core' ), $n ), 'textarea' );
		}
		$tabs['home'] = array(
			'title'    => __( 'Homepage', 'dck-core' ),
			'icon'     => 'dashicons-admin-home',
			'intro'    => __( 'Every homepage section. Leave a field blank to use the built-in wording.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Hero slides', 'dck-core' ),
					'fields' => $hero,
				),
				array(
					'title'  => __( 'Welcome', 'dck-core' ),
					'fields' => array(
						$f( 'dck_welcome_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'A Word of Welcome' ),
						$f( 'dck_welcome_heading', __( 'Heading', 'dck-core' ), 'text', 'Karibu Home, You Belong Here' ),
						$f( 'dck_lead_text', __( 'Welcome paragraph', 'dck-core' ), 'textarea' ),
						$f( 'dck_welcome_pastors', __( 'Pastors line', 'dck-core' ), 'textarea', 'Led by Pastor Justus Kubai and Mrs. Clare Kubai, with Assistant Pastors Mr. and Mrs. Henry Onzere.' ),
						$f( 'dck_about_since', __( 'Story card tag', 'dck-core' ), 'text', 'Since 2017' ),
						$f( 'dck_about_faith_heading', __( 'Story card heading', 'dck-core' ), 'text', 'Built by Faith' ),
						$f( 'dck_about_faith_text', __( 'Story card text', 'dck-core' ), 'textarea' ),
					),
				),
				array(
					'title'  => __( 'Stats counters', 'dck-core' ),
					'fields' => array(
						$f( 'dck_stat1_num', __( 'Stat 1 number', 'dck-core' ), 'text', '850', __( 'Add a + to show it, for example 850+.', 'dck-core' ) ),
						$f( 'dck_stat1_label', __( 'Stat 1 label', 'dck-core' ), 'text', 'Members and Growing' ),
						$f( 'dck_stat2_num', __( 'Stat 2 number', 'dck-core' ), 'text', '', __( 'Leave blank to count departments automatically.', 'dck-core' ) ),
						$f( 'dck_stat2_label', __( 'Stat 2 label', 'dck-core' ), 'text', 'Ministry Departments' ),
						$f( 'dck_stat3_num', __( 'Stat 3 number', 'dck-core' ), 'text', '', __( 'Leave blank to calculate years since June 2017.', 'dck-core' ) ),
						$f( 'dck_stat3_label', __( 'Stat 3 label', 'dck-core' ), 'text', 'Years of Ministry' ),
						$f( 'dck_stat4_num', __( 'Stat 4 number', 'dck-core' ), 'text', '12' ),
						$f( 'dck_stat4_label', __( 'Stat 4 label', 'dck-core' ), 'text', 'Outreach Projects' ),
					),
				),
				array(
					'title'  => __( 'Ministries section', 'dck-core' ),
					'fields' => array(
						$f( 'dck_min_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'Get Involved' ),
						$f( 'dck_min_heading', __( 'Heading', 'dck-core' ), 'text', 'Our Ministry Departments' ),
						$f( 'dck_min_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
					),
				),
				array(
					'title'  => __( 'Join us this Sunday', 'dck-core' ),
					'fields' => array(
						$f( 'dck_sunday_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'Join Us This Sunday' ),
						$f( 'dck_sunday_heading', __( 'Heading', 'dck-core' ), 'text', 'A Joyful Sunday Gathering' ),
						$f( 'dck_sunday_tag2', __( 'Supporting text', 'dck-core' ), 'textarea' ),
					),
				),
				array(
					'title'  => __( 'Closing call to action', 'dck-core' ),
					'fields' => array(
						$f( 'dck_cta_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'Come As You Are' ),
						$f( 'dck_cta_title', __( 'Heading', 'dck-core' ), 'text', 'Become Part of Our Family' ),
						$f( 'dck_cta_text', __( 'Text', 'dck-core' ), 'textarea' ),
					),
				),
				array(
					'title'  => __( 'Button labels', 'dck-core' ),
					'fields' => array(
						$f( 'dck_btn_plan_visit', __( 'Plan Your Visit', 'dck-core' ), 'text', 'Plan Your Visit' ),
						$f( 'dck_btn_watch_live', __( 'Watch Live', 'dck-core' ), 'text', 'Watch Live' ),
						$f( 'dck_btn_our_story', __( 'Our Story', 'dck-core' ), 'text', 'Our Story' ),
						$f( 'dck_btn_give_online', __( 'Give Online', 'dck-core' ), 'text', 'Give Online' ),
						$f( 'dck_btn_all_ministries', __( 'View All Ministries', 'dck-core' ), 'text', 'View All Ministries' ),
						$f( 'dck_btn_prayer_request', __( 'Prayer Request', 'dck-core' ), 'text', 'Prayer Request' ),
					),
				),
			),
		);

		/* ---------- Ministries ---------- */
		$min_sections = array();
		if ( function_exists( 'dck_home_min_defs' ) ) {
			foreach ( dck_home_min_defs() as $d ) {
				$min_sections[] = array(
					'title'  => $d['name'],
					'fields' => array(
						$f( 'dck_min_' . $d['k'] . '_name', __( 'Name', 'dck-core' ), 'text', $d['name'] ),
						$f( 'dck_min_' . $d['k'] . '_desc', __( 'Description', 'dck-core' ), 'textarea', $d['desc'] ),
						$f( 'dck_min_' . $d['k'] . '_img', __( 'Photo', 'dck-core' ), 'image' ),
					),
				);
			}
		}
		$min_sections[] = array(
			'title'  => __( 'Care Ministry', 'dck-core' ),
			'fields' => array(
				$f( 'dck_care_title', __( 'Name', 'dck-core' ), 'text', 'Care Ministry' ),
				$f( 'dck_care_text', __( 'Description', 'dck-core' ), 'textarea', 'Handles ushering and hospitality, ensuring a welcoming experience for all.' ),
				$f( 'dck_care_img', __( 'Photo', 'dck-core' ), 'image' ),
			),
		);
		$min_sections[] = array(
			'title'  => __( 'Protocol Department', 'dck-core' ),
			'fields' => array(
				$f( 'dck_protocol_title', __( 'Name', 'dck-core' ), 'text', 'Protocol Department' ),
				$f( 'dck_protocol_text', __( 'Description', 'dck-core' ), 'textarea', 'Oversees the welfare and honor of our pastors and visiting gospel ministers.' ),
				$f( 'dck_protocol_img', __( 'Photo', 'dck-core' ), 'image' ),
			),
		);
		$tabs['ministries'] = array(
			'title'    => __( 'Ministries', 'dck-core' ),
			'icon'     => 'dashicons-groups',
			'intro'    => __( 'Names, descriptions and photos for every department. Shown on the homepage and the Ministries page.', 'dck-core' ),
			'sections' => $min_sections,
		);

		/* ---------- Page text ---------- */
		$tabs['pages'] = array(
			'title'    => __( 'Page Text', 'dck-core' ),
			'icon'     => 'dashicons-media-text',
			'intro'    => __( 'Headings and wording on the Contact, Sermons and Bible pages. Leave blank to use the built-in wording.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Contact page', 'dck-core' ),
					'fields' => array(
						$f( 'dck_contact_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'We Would Love To Hear From You' ),
						$f( 'dck_contact_title', __( 'Page title', 'dck-core' ), 'text', 'Contact Us' ),
						$f( 'dck_contact_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
						$f( 'dck_contact_form_title', __( 'Form heading', 'dck-core' ), 'text', 'Write To Us' ),
						$f( 'dck_contact_reply_note', __( 'Reply-time note', 'dck-core' ), 'textarea', 'We usually reply within one working day. For urgent prayer or pastoral care, please call or WhatsApp us.' ),
						$f( 'dck_contact_topics', __( 'Message topics (one per line)', 'dck-core' ), 'textarea', "General enquiry\nPlan a visit\nPrayer and pastoral care\nGiving\nMinistries and serving\nMedia and partnerships" ),
						$f( 'dck_contact_map_query', __( 'Map location (name or address)', 'dck-core' ), 'text', 'Deliverance Church Kabete N Market, Uthiru, Nairobi, Kenya' ),
					),
				),
				array(
					'title'  => __( 'Sermons page', 'dck-core' ),
					'fields' => array(
						$f( 'dck_sermons_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'Grow in the Word' ),
						$f( 'dck_sermons_title', __( 'Page title', 'dck-core' ), 'text', 'Sermon Library' ),
						$f( 'dck_sermons_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
					),
				),
				array(
					'title'  => __( 'Bible page', 'dck-core' ),
					'fields' => array(
						$f( 'dck_bible_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'Grow in the Word' ),
						$f( 'dck_bible_title', __( 'Page title', 'dck-core' ), 'text', 'Bible Study Assistant' ),
						$f( 'dck_bible_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
						$f( 'dck_bible_default', __( 'Default Bible version ID', 'dck-core' ), 'text', 'BSB', __( 'For example BSB, eng_kjv, swh_bib, kik_bib, luo_bib.', 'dck-core' ) ),
						$f( 'dck_bible_versions', __( 'Versions offered (one ID per line, in order)', 'dck-core' ), 'textarea', function_exists( 'dck_bible_catalog' ) ? implode( "\n", array_keys( dck_bible_catalog() ) ) : '', __( 'IDs from bible.helloao.org. Remove a line to hide a version.', 'dck-core' ) ),
						$f( 'dck_bible_votd', __( 'Verse of the Day list (one per line: BOOK CHAPTER VERSE, e.g. JHN 3 16)', 'dck-core' ), 'textarea' ),
						$f( 'dck_bible_suggestions', __( 'Suggested questions (one per line)', 'dck-core' ), 'textarea', "Romans 8:28\nPsalm 23\nPhilippians 4:6-7\nOvercoming anxiety\nForgiveness\nFinding hope in hard times\nGrowing in faith\nThe armor of God" ),
					),
				),
			),
		);

		/* ---------- About & Pastors ---------- */
		$ad    = function_exists( 'dck_about_defaults' ) ? dck_about_defaults() : array( 'values' => '', 'timeline' => '' );
		$about = array(
			array(
				'title'  => __( 'About page', 'dck-core' ),
				'fields' => array(
					$f( 'dck_about_title', __( 'Page title', 'dck-core' ), 'text', 'About Our Church' ),
					$f( 'dck_about_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
					$f( 'dck_about_image', __( 'Story photo', 'dck-core' ), 'image' ),
					$f( 'dck_about_story_heading', __( 'Story heading', 'dck-core' ), 'text', 'Founded in Faith, Growing in Grace' ),
					$f( 'dck_about_story', __( 'Our story (blank line between paragraphs)', 'dck-core' ), 'textarea' ),
					$f( 'dck_about_mission', __( 'Mission statement', 'dck-core' ), 'textarea', 'Reaching out and leading generations into a growing relationship with Christ through the Word of God.' ),
					$f( 'dck_about_motto', __( 'Motto', 'dck-core' ), 'text', 'Small enough to know you, big enough to accommodate you.' ),
					$f( 'dck_about_values', __( 'Core values (one per line: Title|Description)', 'dck-core' ), 'textarea', $ad['values'] ),
					$f( 'dck_about_timeline', __( 'Milestones (one per line: Date|Title|Description)', 'dck-core' ), 'textarea', $ad['timeline'] ),
				),
			),
			array(
				'title'  => __( 'Pastors page', 'dck-core' ),
				'fields' => array(
					$f( 'dck_pastors_title', __( 'Page title', 'dck-core' ), 'text', 'Our Pastors' ),
					$f( 'dck_pastors_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
					$f( 'dck_pastors_couple_img', __( 'Lead couple photo (header)', 'dck-core' ), 'image' ),
					$f( 'dck_pastors_story_title', __( 'Story heading', 'dck-core' ), 'text', 'From Kangemi to Uthiru, by faith' ),
					$f( 'dck_pastors_story', __( 'Story text', 'dck-core' ), 'textarea' ),
					$f( 'dck_pastors_credentials', __( 'Training & impact (one per line: Title|Detail)', 'dck-core' ), 'textarea' ),
					$f( 'dck_pastors_found_title', __( 'Foundation heading', 'dck-core' ), 'text', 'Cistern of Hope Foundation' ),
					$f( 'dck_pastors_found_text', __( 'Foundation text', 'dck-core' ), 'textarea' ),
					$f( 'dck_pastors_care', __( 'Pastoral care cards (one per line: Title|Description)', 'dck-core' ), 'textarea' ),
				),
			),
		);
		if ( function_exists( 'dck_pastor_defs' ) ) {
			foreach ( dck_pastor_defs() as $p ) {
				$k       = $p['k'];
				$about[] = array(
					'title'  => $p['name'],
					'fields' => array(
						$f( 'dck_pastor_' . $k . '_name', __( 'Name', 'dck-core' ), 'text', $p['name'] ),
						$f( 'dck_pastor_' . $k . '_role', __( 'Role badge', 'dck-core' ), 'text', $p['role'] ),
						$f( 'dck_pastor_' . $k . '_title', __( 'Title line', 'dck-core' ), 'text', $p['title'] ),
						$f( 'dck_pastor_' . $k . '_lead', __( 'Short introduction', 'dck-core' ), 'textarea', $p['lead'] ),
						$f( 'dck_pastor_' . $k . '_focus', __( 'Focus areas (comma separated)', 'dck-core' ), 'text', isset( $p['focus'] ) ? $p['focus'] : '' ),
						$f( 'dck_pastor_' . $k . '_bio', __( 'Full biography (leave blank to keep the current one)', 'dck-core' ), 'html' ),
						$f( 'dck_pastor_' . $k . '_img', __( 'Photo', 'dck-core' ), 'image' ),
						$f( 'dck_pastor_' . $k . '_hide', __( 'Hide this pastor (type yes)', 'dck-core' ), 'text' ),
					),
				);
			}
		}
		$tabs['about'] = array(
			'title'    => __( 'About & Pastors', 'dck-core' ),
			'icon'     => 'dashicons-id',
			'intro'    => __( 'Your story, values, milestones and pastor profiles.', 'dck-core' ),
			'sections' => $about,
		);

		/* ---------- Services, Ministries, FAQ page text ---------- */
		$sd = function_exists( 'dck_services_defaults' ) ? dck_services_defaults() : array( 'sunday_desc' => '', 'week' => '', 'expect' => '' );
		$tabs['services2'] = array(
			'title'    => __( 'Services & More', 'dck-core' ),
			'icon'     => 'dashicons-calendar-alt',
			'intro'    => __( 'Services page details, plus Ministries and FAQ page headings. Sunday times themselves are under Service Times.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Services page', 'dck-core' ),
					'fields' => array(
						$f( 'dck_services_title', __( 'Page title', 'dck-core' ), 'text', 'Our Services' ),
						$f( 'dck_services_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
						$f( 'dck_services_sunday_desc', __( 'Sunday descriptions (one per line, same order as Service Times)', 'dck-core' ), 'textarea', $sd['sunday_desc'] ),
						$f( 'dck_services_week', __( 'Weekly schedule (one per line: Day|Time|Activity)', 'dck-core' ), 'textarea', $sd['week'] ),
						$f( 'dck_services_week_lead', __( 'Weekly schedule intro', 'dck-core' ), 'textarea' ),
						$f( 'dck_services_image', __( 'Weekly schedule photo', 'dck-core' ), 'image' ),
						$f( 'dck_services_expect', __( 'What to expect (one per line: Title|Description)', 'dck-core' ), 'textarea', $sd['expect'] ),
					),
				),
				array(
					'title'  => __( 'Ministries page', 'dck-core' ),
					'fields' => array(
						$f( 'dck_ministries_title', __( 'Page title', 'dck-core' ), 'text', 'Our Ministries' ),
						$f( 'dck_ministries_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
					),
				),
				array(
					'title'  => __( 'Prayer page', 'dck-core' ),
					'fields' => array(
						$f( 'dck_prayer_title', __( 'Page title', 'dck-core' ), 'text', 'Prayer Request' ),
						$f( 'dck_prayer_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
						$f( 'dck_prayer_topics', __( 'Prayer topics (one per line)', 'dck-core' ), 'textarea', "Healing\nFamily\nMarriage\nFinances and work\nSalvation\nGuidance\nThanksgiving\nOther" ),
						$f( 'dck_prayer_verse', __( 'Scripture quote', 'dck-core' ), 'textarea' ),
						$f( 'dck_prayer_verse_ref', __( 'Scripture reference', 'dck-core' ), 'text', 'Philippians 4:6' ),
					),
				),
				array(
					'title'  => __( 'Watch Live, Events and Blog pages', 'dck-core' ),
					'fields' => array(
						$f( 'dck_live_title', __( 'Watch Live title', 'dck-core' ), 'text', 'Watch Live' ),
						$f( 'dck_live_lead', __( 'Watch Live intro', 'dck-core' ), 'textarea' ),
						$f( 'dck_live_day', __( 'Live service day (0 = Sunday ... 6 = Saturday)', 'dck-core' ), 'text', '0' ),
						$f( 'dck_live_start', __( 'Live service start (24h, EAT, e.g. 10:40)', 'dck-core' ), 'text', '10:40' ),
						$f( 'dck_live_end', __( 'Live service end (24h, EAT, e.g. 13:00)', 'dck-core' ), 'text', '13:00' ),
						$f( 'dck_live_event_title', __( 'Calendar event title', 'dck-core' ), 'text', 'Sunday Glorious Service - Deliverance Church Kabete N' ),
						$f( 'dck_live_default_video', __( 'Fallback YouTube video ID', 'dck-core' ), 'text', 'RKItJ4L4sSY', __( 'Shown when no channel ID or live link is set (Live Stream tab).', 'dck-core' ) ),
						$f( 'dck_events_title', __( 'Events title', 'dck-core' ), 'text', 'Events & Gatherings' ),
						$f( 'dck_events_lead', __( 'Events intro', 'dck-core' ), 'textarea' ),
						$f( 'dck_blog_title', __( 'Blog title', 'dck-core' ), 'text' ),
						$f( 'dck_blog_lead', __( 'Blog intro', 'dck-core' ), 'textarea' ),
					),
				),
				array(
					'title'  => __( 'FAQ page', 'dck-core' ),
					'fields' => array(
						$f( 'dck_faq_title', __( 'Page title', 'dck-core' ), 'text', 'How can we help you?' ),
						$f( 'dck_faq_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
					),
				),
			),
		);

		/* ---------- Giving ---------- */
		$g = function_exists( 'dck_give_defaults' ) ? dck_give_defaults() : array();
		$gd = function ( $k ) use ( $g ) {
			return isset( $g[ $k ] ) ? $g[ $k ] : '';
		};
		$tabs['giving'] = array(
			'title'    => __( 'Giving', 'dck-core' ),
			'icon'     => 'dashicons-money-alt',
			'intro'    => __( 'Payment details on the Give page, FAQ and AI assistant. Leave a channel blank to hide it. Please double-check numbers before saving.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Give page text', 'dck-core' ),
					'fields' => array(
						$f( 'dck_give_eyebrow', __( 'Small label', 'dck-core' ), 'text', 'Partner With Us' ),
						$f( 'dck_give_title', __( 'Page title', 'dck-core' ), 'text', 'Give Online' ),
						$f( 'dck_give_lead', __( 'Intro text', 'dck-core' ), 'textarea' ),
						$f( 'dck_give_purposes', __( 'Where your gift goes (one per line: Title|Description)', 'dck-core' ), 'textarea', "Tithes|Honour God with the first tenth of your increase, as taught in Malachi 3:10.\nOfferings|Freewill gifts of thanksgiving and worship every Sunday.\nChurch Building|Help us keep building our sanctuary in Uthiru, a house of prayer for many.\nMissions and Evangelism|Carry the Gospel to new places through outreach and soul winning.\nWelfare and Care|Support members and neighbours in need with compassion and practical help." ),
						$f( 'dck_give_why_heading', __( 'Why we give: heading', 'dck-core' ), 'text', 'Tithes, Offerings and Donations' ),
						$f( 'dck_give_why_text', __( 'Why we give: text', 'dck-core' ), 'textarea' ),
						$f( 'dck_give_verse', __( 'Scripture quote', 'dck-core' ), 'textarea' ),
						$f( 'dck_give_verse_ref', __( 'Scripture reference', 'dck-core' ), 'text', 'Malachi 3:10' ),
					),
				),
				array(
					'title'  => __( 'M-Pesa', 'dck-core' ),
					'fields' => array(
						$f( 'dck_give_paybill', __( 'Paybill number', 'dck-core' ), 'text', $gd( 'dck_give_paybill' ) ),
						$f( 'dck_give_paybill_account', __( 'Paybill account number', 'dck-core' ), 'text', $gd( 'dck_give_paybill_account' ) ),
						$f( 'dck_give_till', __( 'Till number (Buy Goods)', 'dck-core' ), 'text', $gd( 'dck_give_till' ) ),
					),
				),
				array(
					'title'  => __( 'Bank', 'dck-core' ),
					'fields' => array(
						$f( 'dck_give_bank_name', __( 'Bank', 'dck-core' ), 'text', $gd( 'dck_give_bank_name' ) ),
						$f( 'dck_give_bank_account_name', __( 'Account name', 'dck-core' ), 'text', $gd( 'dck_give_bank_account_name' ) ),
						$f( 'dck_give_bank_account', __( 'Account number', 'dck-core' ), 'text', $gd( 'dck_give_bank_account' ) ),
						$f( 'dck_give_bank_swift', __( 'SWIFT code', 'dck-core' ), 'text', $gd( 'dck_give_bank_swift' ) ),
						$f( 'dck_give_bank_branch', __( 'Branch', 'dck-core' ), 'text', $gd( 'dck_give_bank_branch' ) ),
					),
				),
			),
		);

		/* ---------- Live stream ---------- */
		$tabs['live'] = array(
			'title'    => __( 'Live Stream', 'dck-core' ),
			'icon'     => 'dashicons-video-alt3',
			'intro'    => __( 'Connect YouTube or Facebook so live services appear on the Watch Live page.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Streaming', 'dck-core' ),
					'fields' => array(
						$f( 'dck_youtube_channel_id', __( 'YouTube channel ID', 'dck-core' ), 'text', '', __( 'Starts with UC. Found in YouTube Studio > Settings > Channel > Advanced.', 'dck-core' ) ),
						$f( 'dck_youtube_live_url', __( 'YouTube live/video link (fallback)', 'dck-core' ), 'url' ),
						$f( 'dck_facebook_url', __( 'Facebook live/video link (fallback)', 'dck-core' ), 'url' ),
						$f( 'dck_youtube_playlist_url', __( 'Past services playlist link', 'dck-core' ), 'url' ),
					),
				),
			),
		);

		/* ---------- SEO ---------- */
		$tabs['seo'] = array(
			'title'    => __( 'SEO', 'dck-core' ),
			'icon'     => 'dashicons-search',
			'intro'    => __( 'How the church appears on Google and when links are shared. Leave blank to use the built-in, optimised wording.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Search & sharing', 'dck-core' ),
					'fields' => array(
						$f( 'dck_seo_home_desc', __( 'Homepage description', 'dck-core' ), 'textarea' ),
						$f( 'dck_seo_keywords', __( 'Keywords', 'dck-core' ), 'textarea' ),
						$f( 'dck_seo_image', __( 'Default share image', 'dck-core' ), 'image', '', __( '1200 x 630 pixels works best.', 'dck-core' ) ),
					),
				),
			),
		);

		/* ---------- Legal ---------- */
		$tabs['legal'] = array(
			'title'    => __( 'Terms & Privacy', 'dck-core' ),
			'icon'     => 'dashicons-shield',
			'intro'    => __( 'Everything on the Terms & Privacy page. Add, remove or reorder sections; basic formatting (bold, links, lists) is allowed.', 'dck-core' ),
			'sections' => array(
				array(
					'title'  => __( 'Page header', 'dck-core' ),
					'fields' => array(
						$f( 'dck_legal_title', __( 'Page title', 'dck-core' ), 'text', 'Terms, Privacy & Photography' ),
						$f( 'dck_legal_intro', __( 'Introduction', 'dck-core' ), 'textarea', 'How we care for your information, how you may use this website, and how we handle photos and video at church.' ),
						$f( 'dck_legal_updated', __( 'Last updated (date)', 'dck-core' ), 'date', '2026-10-20' ),
					),
				),
				array(
					'title'  => __( 'Sections', 'dck-core' ),
					'fields' => array(
						$f( 'dck_legal_sections', __( 'Sections', 'dck-core' ), 'sections', function_exists( 'dck_legal_default_sections' ) ? dck_legal_default_sections() : array() ),
					),
				),
				array(
					'title'  => __( 'Photography preference form', 'dck-core' ),
					'fields' => array(
						$f( 'dck_legal_optout_heading', __( 'Form heading', 'dck-core' ), 'text', 'I Prefer Not To Be Photographed' ),
						$f( 'dck_legal_optout_text', __( 'Form intro', 'dck-core' ), 'textarea', 'Complete the form and sign below. Our media team will be notified and your preference kept on record.' ),
					),
				),
			),
		);

		return apply_filters( 'dck_core_settings_schema', $tabs );
	}
}
