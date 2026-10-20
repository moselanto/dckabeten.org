<?php
/**
 * DCK Elite - FAQ page: editable question bank, accordion render, and FAQPage schema.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* The FAQ content. Grouped for display; flattened for schema. Filterable. */
if ( ! function_exists( 'dck_elite_faq_groups' ) ) {
	function dck_elite_faq_groups() {
		$groups = array(
			array(
				'heading' => 'Visiting Us',
				'items'   => array(
					array(
						'q' => 'Where is Deliverance Church Kabete located?',
						'a' => <<<'DCKA'
We are located at the Cistern of Hope Center in Uthiru, Kabete, behind N Market, Nairobi, Kenya. Find us on <a href='https://share.google/LJmcXS6D5Nq1aF2R5'>Google Maps</a> or see our <a href='%HOME%contact/'>Contact page</a> for directions.
DCKA
					),
					array(
						'q' => 'What time are your Sunday services?',
						'a' => <<<'DCKA'
We gather for worship every Sunday at our sanctuary in Uthiru. For current service times, please call or WhatsApp us on +254 110 304 521 and we will gladly help you plan your visit.
DCKA
					),
					array(
						'q' => 'I am visiting for the first time. What can I expect?',
						'a' => <<<'DCKA'
Expect a warm welcome, heartfelt worship, and practical Bible teaching. Come as you are; there is no dress code, and our team will help you feel right at home.
DCKA
					),
					array(
						'q' => 'Do you have programmes for children and youth?',
						'a' => <<<'DCKA'
Yes. We have vibrant children and youth ministries so every member of your family can grow in faith. Visit our <a href='%HOME%ministries/'>Ministries page</a> to learn more.
DCKA
					),
					array(
						'q' => 'How can I contact the church?',
						'a' => <<<'DCKA'
You can call or WhatsApp us on +254 110 304 521, email info@dckabeten.org, or use our <a href='%HOME%contact/'>Contact page</a>. We would love to hear from you.
DCKA
					),
				),
			),
			array(
				'heading' => 'Faith &amp; Salvation',
				'items'   => array(
					array(
						'q' => 'How can I give my life to Christ?',
						'a' => <<<'DCKA'
Following Jesus begins with a simple, sincere prayer: admit your need, believe that Jesus died and rose for you, and invite Him to be Lord of your life. If you have taken this step, or want to, <a href='%HOME%contact/'>let us know</a> so we can celebrate and support you.
DCKA
					),
					array(
						'q' => 'What is water baptism and how can I be baptised?',
						'a' => <<<'DCKA'
Water baptism is a public declaration of your faith in Christ and a step of obedience for every believer. To be baptised, simply <a href='%HOME%contact/'>contact our team</a> and we will guide you to our next baptism.
DCKA
					),
					array(
						'q' => 'Do I need to be a member to attend?',
						'a' => <<<'DCKA'
Not at all. Everyone is welcome at Deliverance Church Kabete, whether you are exploring faith for the first time or have followed Jesus for years.
DCKA
					),
					array(
						'q' => 'How do I become a member?',
						'a' => <<<'DCKA'
Start by attending regularly and getting connected in a group. When you are ready, <a href='%HOME%contact/'>reach out</a> and we will walk you through membership.
DCKA
					),
					array(
						'q' => 'Can I request prayer?',
						'a' => <<<'DCKA'
Yes. Our prayer team would be honoured to pray with you. Submit a request through our <a href='%HOME%contact/'>Contact page</a> at any time, day or night.
DCKA
					),
				),
			),
			array(
				'heading' => 'Giving',
				'items'   => array(
					array(
						'q' => 'How can I give my tithes and offerings?',
						'a' => <<<'DCKA'
You can give through M-Pesa Paybill 247247, Account 403263; M-Pesa Till 9900619; or Equity Bank Account 1370274104398. See our <a href='%HOME%give/'>Giving page</a> for full details.
DCKA
					),
					array(
						'q' => 'Where does my giving go?',
						'a' => <<<'DCKA'
Your generosity supports worship, discipleship, community outreach, and the day-to-day ministry of the church as we serve Uthiru and beyond.
DCKA
					),
					array(
						'q' => 'Can I give if I am outside Kenya?',
						'a' => <<<'DCKA'
Yes. Our M-Pesa and bank channels can be used from abroad, and you are welcome to <a href='%HOME%contact/'>contact us</a> if you need any help giving from outside the country.
DCKA
					),
				),
			),
			array(
				'heading' => 'Get Involved',
				'items'   => array(
					array(
						'q' => 'How can I serve or volunteer?',
						'a' => <<<'DCKA'
There is a place for you to serve. Explore opportunities on our <a href='%HOME%ministries/'>Ministries page</a> or <a href='%HOME%contact/'>tell us</a> how you would like to help.
DCKA
					),
					array(
						'q' => 'Do you have small groups or fellowships?',
						'a' => <<<'DCKA'
Yes. Small groups and fellowships are where faith and friendship grow together. <a href='%HOME%contact/'>Contact us</a> to find a group near you.
DCKA
					),
					array(
						'q' => 'Do you have marriage and family programmes?',
						'a' => <<<'DCKA'
Yes. We support couples and families through teaching, mentorship, and fellowship. Read our <a href='%HOME%blog/'>blog</a> or <a href='%HOME%contact/'>reach out</a> to get involved.
DCKA
					),
				),
			),
			array(
				'heading' => 'About Our Church',
				'items'   => array(
					array(
						'q' => 'Who are the lead pastors?',
						'a' => <<<'DCKA'
Our lead pastors are Justus and Clare Kubai, who shepherd the church with a heart for God and for people. Meet them on our <a href='%HOME%our-pastors/'>Our Pastors page</a>.
DCKA
					),
					array(
						'q' => 'When was the church established?',
						'a' => <<<'DCKA'
Deliverance Church Kabete was established in 2017 and continues to grow as a family of faith in the Uthiru community.
DCKA
					),
					array(
						'q' => 'What do you believe?',
						'a' => <<<'DCKA'
We are a Bible-believing, Christ-centred church. We believe in salvation through Jesus Christ, the power of the Holy Spirit, and the truth of God's Word. Learn more on our <a href='%HOME%about/'>About page</a>.
DCKA
					),
				),
			),
			array(
				'heading' => 'Online &amp; Media',
				'items'   => array(
					array(
						'q' => 'Can I watch services online?',
						'a' => <<<'DCKA'
Yes. When available, you can join us through our <a href='%HOME%live/'>Live Stream page</a>. Follow us on social media for notifications.
DCKA
					),
					array(
						'q' => 'Where can I listen to past sermons?',
						'a' => <<<'DCKA'
Our full message library is on the <a href='%HOME%sermons/'>Sermons page</a>, where you can search, read, and listen to teachings.
DCKA
					),
					array(
						'q' => 'Do you have a Bible study tool?',
						'a' => <<<'DCKA'
Yes. Our <a href='%HOME%bible/'>Bible page</a> features an AI study tool that answers your questions and helps you explore Scripture in plain language.
DCKA
					),
				),
			),
		);
		return apply_filters( 'dck_elite_faq_groups', $groups );
	}
}

/* Render the FAQ accordion (works without JavaScript via <details>). */
if ( ! function_exists( 'dck_faq_render' ) ) {
	function dck_faq_render() {
		$home = home_url( '/' );
		foreach ( dck_elite_faq_groups() as $g ) {
			echo '<div class="dck-faq-group">';
			echo '<h2>' . esc_html( $g['heading'] ) . '</h2>';
			echo '<div class="dck-faq-list">';
			foreach ( $g['items'] as $it ) {
				$ans = str_replace( '%HOME%', $home, $it['a'] );
				echo '<details class="dck-faq-item">';
				echo '<summary>' . esc_html( $it['q'] ) . '</summary>';
				echo '<div class="dck-faq-answer">' . wp_kses_post( wpautop( $ans ) ) . '</div>';
				echo '</details>';
			}
			echo '</div></div>';
		}
	}
}

/* Output FAQPage JSON-LD on the FAQ page for rich results. */
add_action( 'wp_head', 'dck_faq_schema', 3 );
if ( ! function_exists( 'dck_faq_schema' ) ) {
	function dck_faq_schema() {
		if ( is_admin() || ! is_page_template( 'template-faq.php' ) ) {
			return;
		}
		$home = home_url( '/' );
		$q    = array();
		foreach ( dck_elite_faq_groups() as $g ) {
			foreach ( $g['items'] as $it ) {
				$ans = wp_strip_all_tags( str_replace( '%HOME%', $home, $it['a'] ) );
				$q[] = array(
					'@type'          => 'Question',
					'name'           => $it['q'],
					'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $ans ),
				);
			}
		}
		if ( empty( $q ) ) {
			return;
		}
		$data = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $q );
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . chr( 10 );
	}
}
