<?php
/**
 * DCK Elite - Blog engine: posts page wiring, illustrated seed content,
 * category images, related posts, and Media-menu integration.
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Featured image for a post: real thumbnail, else the seeded category image. */
if ( ! function_exists( 'dck_post_image_url' ) ) {
	function dck_post_image_url( $id = 0 ) {
		$id = $id ? $id : get_the_ID();
		if ( has_post_thumbnail( $id ) ) {
			return get_the_post_thumbnail_url( $id, 'large' );
		}
		$m = get_post_meta( $id, '_dck_post_image', true );
		if ( $m ) {
			/*
			 * The stored URL may point to an old theme folder (e.g. build-v4.1.0).
			 * If it is one of our seeded blog images, rebuild the URL from the
			 * current theme directory so folder renames never break images.
			 */
			if ( preg_match( '#/assets/images/blog/([^/]+)$#', $m, $match ) ) {
				return get_template_directory_uri() . '/assets/images/blog/' . $match[1];
			}
			return $m;
		}
		if ( function_exists( 'dck_seo_image_default' ) ) {
			return dck_seo_image_default();
		}
		return '';
	}
}

/* Related posts (same category) for the single-post template. */
if ( ! function_exists( 'dck_related_posts' ) ) {
	function dck_related_posts( $id, $limit = 3 ) {
		$cats = wp_get_post_categories( $id );
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $id ),
			'no_found_rows'  => true,
			'orderby'        => 'rand',
		);
		if ( ! empty( $cats ) ) {
			$args['category__in'] = $cats;
		}
		return get_posts( $args );
	}
}

/* Ensure a posts page ("Blog") exists and is wired as the WordPress blog index. */
add_action( 'admin_init', 'dck_blog_bootstrap', 20 );
if ( ! function_exists( 'dck_blog_bootstrap' ) ) {
	function dck_blog_bootstrap() {
		$blog = get_page_by_path( 'blog' );
		if ( $blog ) {
			$home = get_page_by_path( 'home' );
			if ( $home && ! (int) get_option( 'page_on_front' ) ) {
				update_option( 'page_on_front', $home->ID );
			}
			if ( (int) get_option( 'page_on_front' ) ) {
				update_option( 'show_on_front', 'page' );
			}
			if ( (int) get_option( 'page_for_posts' ) !== (int) $blog->ID ) {
				update_option( 'page_for_posts', $blog->ID );
			}
		}
		dck_blog_seed();
		dck_blog_seed_v2();
		dck_blog_seed_v3();
	}
}

/* Seed a first batch of illustrated, SEO-friendly church blog posts (once). */
if ( ! function_exists( 'dck_blog_seed' ) ) {
	function dck_blog_seed() {
		if ( get_option( 'dck_blog_seeded_v1' ) ) {
			return;
		}
		$base  = get_template_directory_uri() . '/assets/images/blog/';
		$home  = home_url( '/' );
		$posts = array(
		array(
			'title'   => 'Finding Peace in God: A Daily Devotion for Busy Kenyans',
			'slug'    => 'finding-peace-in-god-daily-devotion',
			'cat'     => 'Devotions',
			'img'     => 'bible',
			'excerpt' => 'Life in Nairobi and across Kenya moves fast. Here is how to find lasting peace in God through a simple daily devotion.',
			'body'    => <<<'DCKB'
<p>Between traffic on Waiyaki Way, work deadlines, and family responsibilities, it is easy to feel that peace is always out of reach. Yet Jesus offers a peace the world cannot give.</p>
<blockquote>&ldquo;Peace I leave with you; my peace I give you. I do not give to you as the world gives. Do not let your hearts be troubled and do not be afraid.&rdquo; &mdash; John 14:27</blockquote>
<h2>Start your day with God</h2>
<p>Before you reach for your phone, take five quiet minutes with the Lord. A short devotion in the morning sets the tone for everything that follows. Read one passage, pray one honest prayer, and carry that stillness into your day.</p>
<h2>A simple daily rhythm</h2>
<ul><li><strong>Read</strong> &ndash; one chapter or a few verses of Scripture.</li><li><strong>Reflect</strong> &ndash; ask what God is showing you.</li><li><strong>Respond</strong> &ndash; pray and obey one small thing.</li></ul>
<p>Peace grows where we make room for God. If you have a question about a Bible passage, our <a href='%HOME%bible/'>AI Bible study tool</a> can help you dig deeper, and you are always welcome to <a href='%HOME%contact/'>reach out to our team</a> for prayer.</p>
<p>At Deliverance Church Kabete, we believe every believer in Nairobi can walk in the peace of Christ, one quiet morning at a time.</p>
DCKB
		),
		array(
			'title'   => 'Building a Christ-Centred Marriage: Biblical Foundations for Couples',
			'slug'    => 'christ-centred-marriage-biblical-foundations',
			'cat'     => 'Marriage &amp; Family',
			'img'     => 'family',
			'excerpt' => 'A strong marriage is built on Christ, not on feelings alone. Discover biblical foundations that help Kenyan couples thrive.',
			'body'    => <<<'DCKB'
<p>Every lasting marriage is built on a foundation. When that foundation is Christ, a home can weather any storm.</p>
<blockquote>&ldquo;Unless the Lord builds the house, the builders labour in vain.&rdquo; &mdash; Psalm 127:1</blockquote>
<h2>Put God at the centre</h2>
<p>A Christ-centred marriage is not about two perfect people. It is about two forgiven people keeping God at the centre of every decision, from finances to parenting.</p>
<h2>Three habits that strengthen couples</h2>
<ul><li><strong>Pray together</strong> &ndash; even a short daily prayer builds unity.</li><li><strong>Serve together</strong> &ndash; a shared purpose deepens love.</li><li><strong>Forgive quickly</strong> &ndash; grace keeps small wounds from becoming walls.</li></ul>
<h2>Grow in community</h2>
<p>You were never meant to do marriage alone. Our church family in Kabete walks with couples through every season. Join us at a <a href='%HOME%events/'>weekly service or couples gathering</a>, or <a href='%HOME%contact/'>talk to our pastors</a> about mentorship.</p>
<p>When Christ builds the home, love has a foundation that lasts.</p>
DCKB
		),
		array(
			'title'   => 'The Power of Prayer: How to Build a Consistent Prayer Life',
			'slug'    => 'power-of-prayer-consistent-prayer-life',
			'cat'     => 'Prayer',
			'img'     => 'worship',
			'excerpt' => 'Prayer changes things. Learn practical, biblical steps to build a consistent and powerful prayer life.',
			'body'    => <<<'DCKB'
<p>Prayer is not a religious duty; it is a conversation with a loving Father. Yet many believers struggle to pray consistently.</p>
<blockquote>&ldquo;Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God.&rdquo; &mdash; Philippians 4:6</blockquote>
<h2>Start small and stay honest</h2>
<p>You do not need long, eloquent prayers. Begin with five honest minutes. God is far more interested in your heart than your words.</p>
<h2>A simple pattern to follow</h2>
<ul><li><strong>Praise</strong> &ndash; thank God for who He is.</li><li><strong>Repent</strong> &ndash; ask forgiveness and receive grace.</li><li><strong>Ask</strong> &ndash; bring your needs and the needs of others.</li><li><strong>Listen</strong> &ndash; sit quietly and let God speak.</li></ul>
<h2>Pray with others</h2>
<p>There is power when believers agree in prayer. Submit a request through our <a href='%HOME%contact/'>prayer team</a>, and our intercessors will stand with you. You can also <a href='%HOME%sermons/'>listen to teaching on prayer</a> in our sermon library.</p>
<p>A consistent prayer life is built one faithful day at a time.</p>
DCKB
		),
		array(
			'title'   => 'Raising Godly Children in a Modern World',
			'slug'    => 'raising-godly-children-modern-world',
			'cat'     => 'Marriage &amp; Family',
			'img'     => 'family',
			'excerpt' => 'Parenting in a fast-changing world is hard. Here are biblical principles for raising godly children in Kenya today.',
			'body'    => <<<'DCKB'
<p>Screens, peer pressure, and busy schedules make parenting harder than ever. Yet God gives parents everything they need to raise children who love Him.</p>
<blockquote>&ldquo;Start children off on the way they should go, and even when they are old they will not turn from it.&rdquo; &mdash; Proverbs 22:6</blockquote>
<h2>Model the faith you teach</h2>
<p>Children learn more from what we do than what we say. When they see parents pray, forgive, and worship, faith becomes real to them.</p>
<h2>Practical steps for parents</h2>
<ul><li><strong>Family devotions</strong> &ndash; even ten minutes shapes young hearts.</li><li><strong>Guard the atmosphere</strong> &ndash; fill your home with worship and encouragement.</li><li><strong>Stay connected</strong> &ndash; listen more than you lecture.</li></ul>
<h2>Let the church help</h2>
<p>Our children and youth ministries partner with parents to disciple the next generation. Bring your family to a <a href='%HOME%services/'>Sunday service</a> and discover ministries built for every age. Questions? <a href='%HOME%contact/'>Reach out to us</a> any time.</p>
<p>Raising godly children is a long obedience, but the reward is a legacy of faith.</p>
DCKB
		),
		array(
			'title'   => 'Healing and Hope: Trusting God Through Difficult Seasons',
			'slug'    => 'healing-and-hope-trusting-god-difficult-seasons',
			'cat'     => 'Faith &amp; Healing',
			'img'     => 'worship',
			'excerpt' => 'When life is hard, God is still good. Find biblical hope and encouragement for seasons of pain, loss, and waiting.',
			'body'    => <<<'DCKB'
<p>Every one of us walks through seasons of pain: sickness, loss, disappointment, or waiting that seems to have no end. In those valleys, God has not forgotten you.</p>
<blockquote>&ldquo;The Lord is close to the broken-hearted and saves those who are crushed in spirit.&rdquo; &mdash; Psalm 34:18</blockquote>
<h2>God is present in the pain</h2>
<p>Faith does not mean pretending everything is fine. It means trusting that God is with you even when you cannot feel Him. He is a healer of bodies, hearts, and homes.</p>
<h2>How to hold on to hope</h2>
<ul><li><strong>Speak the Word</strong> &ndash; let Scripture anchor your mind.</li><li><strong>Stay in community</strong> &ndash; do not carry the weight alone.</li><li><strong>Keep worshipping</strong> &ndash; praise shifts the atmosphere.</li></ul>
<h2>We are here for you</h2>
<p>If you are walking through a hard season, let us pray with you. Contact our <a href='%HOME%contact/'>prayer team</a> or join us for worship this week. God still heals, and hope still rises.</p>
DCKB
		),
		array(
			'title'   => 'Youth and Faith: Staying Rooted in Christ as a Young Person',
			'slug'    => 'youth-and-faith-staying-rooted-in-christ',
			'cat'     => 'Youth',
			'img'     => 'family',
			'excerpt' => 'Being young and following Jesus is not easy. Here is encouragement for Kenyan youth who want to stay rooted in Christ.',
			'body'    => <<<'DCKB'
<p>As a young person, you face pressure from every direction: social media, friends, and the pull to fit in. But God has a bigger plan for your life than the crowd ever could.</p>
<blockquote>&ldquo;Don&rsquo;t let anyone look down on you because you are young, but set an example for the believers.&rdquo; &mdash; 1 Timothy 4:12</blockquote>
<h2>Your youth is not a waiting room</h2>
<p>God can use you now. Your gifts, your voice, and your faith matter today, not just when you are older.</p>
<h2>How to stay rooted</h2>
<ul><li><strong>Find your people</strong> &ndash; surround yourself with friends who love God.</li><li><strong>Feed your faith</strong> &ndash; read the Word and worship often.</li><li><strong>Serve</strong> &ndash; get involved and discover your purpose.</li></ul>
<h2>Join the movement</h2>
<p>Our youth ministry is a place to belong, grow, and lead. Come to our next <a href='%HOME%events/'>youth gathering</a> or <a href='%HOME%contact/'>connect with us</a> to find out more. Your generation can change Kenya for Christ.</p>
DCKB
		),
		array(
			'title'   => 'Why Fellowship Matters: The Value of Belonging to a Local Church',
			'slug'    => 'why-fellowship-matters-belonging-local-church',
			'cat'     => 'Church Life',
			'img'     => 'worship',
			'excerpt' => 'You were not created to follow Jesus alone. Discover why belonging to a local church family in Kabete matters.',
			'body'    => <<<'DCKB'
<p>In a busy, individualistic world, many people try to follow Jesus alone. But the Christian life was designed to be lived in community.</p>
<blockquote>&ldquo;Let us not give up meeting together&hellip; but let us encourage one another.&rdquo; &mdash; Hebrews 10:25</blockquote>
<h2>Faith grows in fellowship</h2>
<p>A local church is more than a Sunday event. It is a family that prays for you, celebrates with you, and carries you when life is heavy.</p>
<h2>What belonging gives you</h2>
<ul><li><strong>Encouragement</strong> &ndash; you are never alone in the battle.</li><li><strong>Accountability</strong> &ndash; people who help you stay on track.</li><li><strong>Purpose</strong> &ndash; a place to use your gifts to serve others.</li></ul>
<h2>Find your home with us</h2>
<p>Deliverance Church Kabete, at Cistern of Hope Center in Uthiru, is a family where you can belong. Plan a visit to our <a href='%HOME%services/'>weekly services</a> or <a href='%HOME%contact/'>say hello</a>. There is a seat waiting for you.</p>
DCKB
		),
		array(
			'title'   => 'Understanding Salvation: What It Means to Follow Jesus',
			'slug'    => 'understanding-salvation-following-jesus',
			'cat'     => 'Faith &amp; Healing',
			'img'     => 'bible',
			'excerpt' => 'What does it really mean to be saved? A clear, biblical look at salvation and how to begin a life with Christ.',
			'body'    => <<<'DCKB'
<p>Salvation is the heart of the Christian faith, yet it is often misunderstood. It is not about being good enough; it is about receiving what Jesus has already done.</p>
<blockquote>&ldquo;For it is by grace you have been saved, through faith&mdash;and this is not from yourselves, it is the gift of God.&rdquo; &mdash; Ephesians 2:8</blockquote>
<h2>The good news, simply</h2>
<p>We have all fallen short. But God loved us so much that He sent Jesus to die and rise again, so that everyone who trusts in Him can be forgiven and given new life.</p>
<h2>How to respond</h2>
<ul><li><strong>Believe</strong> &ndash; that Jesus is Lord and rose from the dead.</li><li><strong>Repent</strong> &ndash; turn from sin and toward God.</li><li><strong>Receive</strong> &ndash; welcome Christ as Saviour and Lord.</li></ul>
<h2>Take your next step</h2>
<p>If you are ready to follow Jesus, we would love to celebrate and walk with you. <a href='%HOME%contact/'>Contact our team</a>, explore the Scriptures with our <a href='%HOME%bible/'>Bible tool</a>, and join us this Sunday. A new life begins today.</p>
DCKB
		),
		);
		$day = 0;
		foreach ( $posts as $p ) {
			if ( get_page_by_path( $p['slug'], OBJECT, 'post' ) ) {
				continue;
			}
			$body = str_replace( '%HOME%', $home, $p['body'] );
			$pid  = wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $p['slug'],
				'post_content' => $body,
				'post_excerpt' => $p['excerpt'],
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( $day * 4 ) . ' days' ) ),
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				$term = term_exists( $p['cat'], 'category' );
				if ( ! $term ) {
					$term = wp_insert_term( $p['cat'], 'category' );
				}
				if ( ! is_wp_error( $term ) ) {
					$tid = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
					wp_set_object_terms( $pid, $tid, 'category' );
				}
				update_post_meta( $pid, '_dck_post_image', $base . $p['img'] . '.webp' );
				update_post_meta( $pid, '_dck_seo_desc', $p['excerpt'] );
			}
			$day++;
		}
		update_option( 'dck_blog_seeded_v1', 1 );
	}
}

/* Second batch of illustrated posts (Phase 2), to reach 20 total (once). */
if ( ! function_exists( 'dck_blog_seed_v2' ) ) {
	function dck_blog_seed_v2() {
		if ( get_option( 'dck_blog_seeded_v2' ) ) {
			return;
		}
		$base  = get_template_directory_uri() . '/assets/images/blog/';
		$home  = home_url( '/' );
		$posts = array(
			array(
				'title'   => 'How to Study the Bible: A Beginner\'s Guide for Everyday Believers',
				'slug'    => 'how-to-study-the-bible-beginners-guide',
				'cat'     => 'Devotions',
				'img'     => 'study',
				'excerpt' => 'New to reading the Bible? Here is a simple, practical guide to studying Scripture and hearing God speak.',
				'body'    => <<<'DCKB'
<p>Many believers want to read the Bible but do not know where to start. The good news is that God's Word is for everyone, not just pastors and scholars.</p>
<blockquote>&ldquo;Your word is a lamp for my feet, a light on my path.&rdquo; &mdash; Psalm 119:105</blockquote>
<h2>Start with a plan</h2>
<p>Begin with the Gospel of John or the Psalms rather than starting at Genesis and giving up in Leviticus. A little each day beats a lot once in a while.</p>
<h2>A simple study method</h2>
<ul><li><strong>Observe</strong> &ndash; what does the passage actually say?</li><li><strong>Interpret</strong> &ndash; what did it mean to the first readers?</li><li><strong>Apply</strong> &ndash; how do I live this out today?</li></ul>
<h2>Ask questions and grow</h2>
<p>When a verse puzzles you, our <a href='%HOME%bible/'>AI Bible study tool</a> can explain it in plain language, and our <a href='%HOME%sermons/'>sermon library</a> unpacks key passages. Study is a journey; take the first step today.</p>
DCKB
			),
			array(
				'title'   => 'Overcoming Anxiety and Fear Through Faith',
				'slug'    => 'overcoming-anxiety-and-fear-through-faith',
				'cat'     => 'Faith &amp; Healing',
				'img'     => 'hope',
				'excerpt' => 'Worry and fear are real, but they do not have the final word. Discover how faith brings peace to an anxious heart.',
				'body'    => <<<'DCKB'
<p>Anxiety touches all of us, from students facing exams to parents worried about tomorrow. God does not scold us for our fears; He invites us to bring them to Him.</p>
<blockquote>&ldquo;Cast all your anxiety on him because he cares for you.&rdquo; &mdash; 1 Peter 5:7</blockquote>
<h2>Name it and give it to God</h2>
<p>Faith is not pretending you are fine. It is honestly handing your fears to a Father who is bigger than every one of them.</p>
<h2>Practical steps toward peace</h2>
<ul><li><strong>Pray specifically</strong> &ndash; tell God exactly what is worrying you.</li><li><strong>Fill your mind</strong> &ndash; replace fearful thoughts with Scripture.</li><li><strong>Reach out</strong> &ndash; do not isolate; let others carry it with you.</li></ul>
<h2>You are not alone</h2>
<p>If anxiety is heavy today, let us stand with you. Send a request to our <a href='%HOME%contact/'>prayer team</a> and let God's peace guard your heart.</p>
DCKB
			),
			array(
				'title'   => 'The Blessing of Generous Giving: Tithes, Offerings and a Cheerful Heart',
				'slug'    => 'blessing-of-generous-giving',
				'cat'     => 'Church Life',
				'img'     => 'outreach',
				'excerpt' => 'Giving is not about guilt; it is about worship and trust. Learn the joy and blessing of generous, cheerful giving.',
				'body'    => <<<'DCKB'
<p>Money can be a hard topic in church, but the Bible speaks about it honestly because our giving reveals our hearts.</p>
<blockquote>&ldquo;Each of you should give what you have decided in your heart to give&hellip; for God loves a cheerful giver.&rdquo; &mdash; 2 Corinthians 9:7</blockquote>
<h2>Giving is worship, not pressure</h2>
<p>When we give tithes and offerings, we declare that God is our provider, not our paycheck. Generosity breaks the grip of greed and fear.</p>
<h2>Ways to give at DCK</h2>
<ul><li><strong>M-Pesa Paybill</strong> 247247, Account 403263</li><li><strong>M-Pesa Till</strong> 9900619</li><li><strong>Equity Bank</strong> Account 1370274104398</li></ul>
<h2>Give with joy</h2>
<p>Every gift supports ministry, outreach, and care for our community. Learn more on our <a href='%HOME%give/'>giving page</a> or <a href='%HOME%contact/'>talk to us</a> about partnering with the work of God in Kabete.</p>
DCKB
			),
			array(
				'title'   => 'Finding Your Purpose: Discovering God\'s Plan for Your Life',
				'slug'    => 'finding-your-purpose-gods-plan',
				'cat'     => 'Faith &amp; Healing',
				'img'     => 'hope',
				'excerpt' => 'You were created on purpose, for a purpose. Here is how to discover and walk in God\'s plan for your life.',
				'body'    => <<<'DCKB'
<p>So many people drift through life wondering if their existence matters. Scripture answers with a resounding yes: you were made by God, for a reason.</p>
<blockquote>&ldquo;For I know the plans I have for you, declares the Lord, plans to prosper you and not to harm you.&rdquo; &mdash; Jeremiah 29:11</blockquote>
<h2>Purpose begins with the Maker</h2>
<p>You discover why you were made by drawing close to the One who made you. Purpose is found in relationship with God, not just in a career.</p>
<h2>How to discern your calling</h2>
<ul><li><strong>Pray</strong> &ndash; ask God to direct your steps.</li><li><strong>Serve</strong> &ndash; your gifts become clear as you use them.</li><li><strong>Seek counsel</strong> &ndash; wise mentors help you see what you cannot.</li></ul>
<h2>Take the next step</h2>
<p>We would love to help you grow into your calling. Join a ministry through our <a href='%HOME%ministries/'>ministries page</a> or <a href='%HOME%contact/'>connect with a leader</a> today.</p>
DCKB
			),
			array(
				'title'   => 'Forgiveness: How to Set Your Heart Free',
				'slug'    => 'forgiveness-set-your-heart-free',
				'cat'     => 'Devotions',
				'img'     => 'worship',
				'excerpt' => 'Unforgiveness is a prison. Discover the freedom and healing that come from forgiving as Christ forgave you.',
				'body'    => <<<'DCKB'
<p>We have all been hurt, and some wounds run deep. Yet holding on to bitterness harms us more than the one who wronged us.</p>
<blockquote>&ldquo;Be kind and compassionate to one another, forgiving each other, just as in Christ God forgave you.&rdquo; &mdash; Ephesians 4:32</blockquote>
<h2>Forgiveness is a choice, not a feeling</h2>
<p>Forgiving does not mean the offence did not matter. It means you release the debt to God and refuse to let it control you.</p>
<h2>Steps toward freedom</h2>
<ul><li><strong>Be honest</strong> &ndash; acknowledge the hurt before God.</li><li><strong>Choose to release</strong> &ndash; forgive as an act of obedience.</li><li><strong>Let God heal</strong> &ndash; healing often follows the decision.</li></ul>
<h2>Walk it out with support</h2>
<p>If you are carrying a heavy hurt, you do not have to do it alone. Reach out to our <a href='%HOME%contact/'>care and prayer team</a> and begin the journey to freedom.</p>
DCKB
			),
			array(
				'title'   => 'Worship as a Lifestyle, Not Just a Sunday Event',
				'slug'    => 'worship-as-a-lifestyle',
				'cat'     => 'Church Life',
				'img'     => 'worship',
				'excerpt' => 'True worship is more than songs on Sunday. Discover what it means to make your whole life an offering to God.',
				'body'    => <<<'DCKB'
<p>When we hear the word worship, we often think of music. But biblical worship is far bigger; it is how we live every day.</p>
<blockquote>&ldquo;Offer your bodies as a living sacrifice, holy and pleasing to God&mdash;this is your true and proper worship.&rdquo; &mdash; Romans 12:1</blockquote>
<h2>Every moment can be worship</h2>
<p>Your work, your parenting, your honesty, and your kindness can all be offered to God as acts of worship.</p>
<h2>Growing a worshipful heart</h2>
<ul><li><strong>Gratitude</strong> &ndash; thank God throughout the day.</li><li><strong>Obedience</strong> &ndash; let your choices honour Him.</li><li><strong>Presence</strong> &ndash; invite God into ordinary moments.</li></ul>
<h2>Worship with the family</h2>
<p>Gathered worship still matters. Join us for a <a href='%HOME%services/'>Sunday service</a> and let corporate praise fuel a lifestyle of worship all week long.</p>
DCKB
			),
			array(
				'title'   => 'Strengthening Family Bonds Through Faith',
				'slug'    => 'strengthening-family-bonds-through-faith',
				'cat'     => 'Marriage &amp; Family',
				'img'     => 'family',
				'excerpt' => 'A family rooted in faith grows stronger together. Practical ways to build a home where God is honoured.',
				'body'    => <<<'DCKB'
<p>Family life is full of joy, but also busyness and pressure. Faith is the glue that helps a home hold together through every season.</p>
<blockquote>&ldquo;As for me and my household, we will serve the Lord.&rdquo; &mdash; Joshua 24:15</blockquote>
<h2>Make God the centre of your home</h2>
<p>When faith is shared, not just individual, families grow closer to God and to one another.</p>
<h2>Habits that strengthen families</h2>
<ul><li><strong>Eat together</strong> &ndash; protect time to connect.</li><li><strong>Pray together</strong> &ndash; invite God into daily life.</li><li><strong>Worship together</strong> &ndash; make church a family rhythm.</li></ul>
<h2>Grow with our church family</h2>
<p>Bring your household to a <a href='%HOME%services/'>weekly service</a>, and explore family and children's ministries. Questions? <a href='%HOME%contact/'>We are here to help</a>.</p>
DCKB
			),
			array(
				'title'   => 'Trusting God Through Financial Hardship',
				'slug'    => 'trusting-god-through-financial-hardship',
				'cat'     => 'Faith &amp; Healing',
				'img'     => 'hope',
				'excerpt' => 'Money troubles can steal your peace. Find biblical hope and practical faith for seasons of financial pressure.',
				'body'    => <<<'DCKB'
<p>The rising cost of living weighs on many families across Kenya. In tight seasons, it is easy to feel forgotten, but God has not abandoned you.</p>
<blockquote>&ldquo;And my God will meet all your needs according to the riches of his glory in Christ Jesus.&rdquo; &mdash; Philippians 4:19</blockquote>
<h2>God sees your struggle</h2>
<p>Faith does not deny the difficulty; it trusts that God is faithful even when the numbers do not add up.</p>
<h2>Faith and wisdom together</h2>
<ul><li><strong>Pray boldly</strong> &ndash; bring every need to God.</li><li><strong>Steward wisely</strong> &ndash; plan, save, and avoid unnecessary debt.</li><li><strong>Stay generous</strong> &ndash; giving keeps fear from ruling your heart.</li></ul>
<h2>Let the church stand with you</h2>
<p>You do not have to carry this alone. Reach out to our <a href='%HOME%contact/'>care team</a> for prayer and encouragement in this season.</p>
DCKB
			),
			array(
				'title'   => 'Why Water Baptism Matters',
				'slug'    => 'why-water-baptism-matters',
				'cat'     => 'Faith &amp; Healing',
				'img'     => 'worship',
				'excerpt' => 'What is water baptism and why is it important? A clear, biblical look at this powerful step of obedience.',
				'body'    => <<<'DCKB'
<p>After choosing to follow Jesus, one of the first steps of obedience is water baptism. But what does it really mean?</p>
<blockquote>&ldquo;Therefore go and make disciples of all nations, baptising them in the name of the Father and of the Son and of the Holy Spirit.&rdquo; &mdash; Matthew 28:19</blockquote>
<h2>A public declaration of faith</h2>
<p>Baptism is an outward sign of an inward change. Going under the water pictures dying to the old life; rising up pictures new life in Christ.</p>
<h2>Common questions</h2>
<ul><li><strong>Do I need to be perfect first?</strong> No, baptism follows faith, not perfection.</li><li><strong>What if I was baptised as a baby?</strong> Many choose believer's baptism as their own step of faith.</li></ul>
<h2>Ready to be baptised?</h2>
<p>If you would like to take this step, we would be honoured to walk with you. <a href='%HOME%contact/'>Contact our team</a> to learn about our next baptism.</p>
DCKB
			),
			array(
				'title'   => 'Discovering and Using Your Spiritual Gifts in Church',
				'slug'    => 'discovering-using-your-spiritual-gifts',
				'cat'     => 'Church Life',
				'img'     => 'outreach',
				'excerpt' => 'God has given every believer gifts to serve. Discover yours and find your place in the body of Christ.',
				'body'    => <<<'DCKB'
<p>You are not an accident in the church; you are a vital part of the body. God has given you gifts meant to bless others.</p>
<blockquote>&ldquo;Each of you should use whatever gift you have received to serve others.&rdquo; &mdash; 1 Peter 4:10</blockquote>
<h2>Everyone has something to offer</h2>
<p>Whether you teach, encourage, lead, serve, give, or show hospitality, your gift matters. The church is healthiest when everyone plays their part.</p>
<h2>How to discover your gifts</h2>
<ul><li><strong>Try serving</strong> &ndash; gifts become clear in action.</li><li><strong>Ask others</strong> &ndash; people often see your strengths first.</li><li><strong>Pray</strong> &ndash; ask the Spirit to guide you.</li></ul>
<h2>Find your place</h2>
<p>There is a team waiting for you. Explore our <a href='%HOME%ministries/'>ministries</a> or <a href='%HOME%contact/'>let us know</a> how you would like to serve.</p>
DCKB
			),
			array(
				'title'   => 'Hope for the Broken-Hearted: God\'s Comfort in Grief',
				'slug'    => 'hope-for-the-broken-hearted-comfort-in-grief',
				'cat'     => 'Faith &amp; Healing',
				'img'     => 'hope',
				'excerpt' => 'Grief is heavy, but you are not alone. Find God\'s comfort and hope in seasons of loss and mourning.',
				'body'    => <<<'DCKB'
<p>Losing someone we love is one of life's deepest pains. In grief, well-meaning words often fall short, but God draws near to the hurting.</p>
<blockquote>&ldquo;Blessed are those who mourn, for they will be comforted.&rdquo; &mdash; Matthew 5:4</blockquote>
<h2>God is near in your sorrow</h2>
<p>You do not have to be strong all the time. God welcomes your tears and promises His presence in the valley.</p>
<h2>Gentle steps through grief</h2>
<ul><li><strong>Let yourself mourn</strong> &ndash; grief is not a lack of faith.</li><li><strong>Lean on community</strong> &ndash; let others carry you.</li><li><strong>Hold to hope</strong> &ndash; in Christ, death is not the end.</li></ul>
<h2>We grieve with you</h2>
<p>If you are walking through loss, please let us pray with you and walk alongside you. <a href='%HOME%contact/'>Reach out to our care team</a> any time.</p>
DCKB
			),
			array(
				'title'   => 'Building Friendships That Honour God',
				'slug'    => 'building-friendships-that-honour-god',
				'cat'     => 'Youth',
				'img'     => 'family',
				'excerpt' => 'The friends you keep shape the life you live. Discover how to build godly friendships that lift you higher.',
				'body'    => <<<'DCKB'
<p>Friendship is one of God's greatest gifts, but the wrong company can pull us away from Him. Choosing friends wisely shapes our future.</p>
<blockquote>&ldquo;Walk with the wise and become wise, for a companion of fools suffers harm.&rdquo; &mdash; Proverbs 13:20</blockquote>
<h2>Friends who point you to God</h2>
<p>Godly friends celebrate your growth, tell you the truth in love, and encourage your faith rather than dragging it down.</p>
<h2>How to build strong friendships</h2>
<ul><li><strong>Be the friend you seek</strong> &ndash; loyalty and honesty go both ways.</li><li><strong>Choose community</strong> &ndash; grow around people of faith.</li><li><strong>Guard your circle</strong> &ndash; not everyone deserves full access.</li></ul>
<h2>Find your people</h2>
<p>Our youth and small-group communities are a great place to build lasting, God-honouring friendships. Join us at an <a href='%HOME%events/'>upcoming gathering</a> or <a href='%HOME%contact/'>say hello</a>.</p>
DCKB
			),
		);
		$day = 0;
		foreach ( $posts as $p ) {
			if ( get_page_by_path( $p['slug'], OBJECT, 'post' ) ) {
				continue;
			}
			$body = str_replace( '%HOME%', $home, $p['body'] );
			$pid  = wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $p['slug'],
				'post_content' => $body,
				'post_excerpt' => $p['excerpt'],
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( 34 + $day * 4 ) . ' days' ) ),
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				$term = term_exists( $p['cat'], 'category' );
				if ( ! $term ) {
					$term = wp_insert_term( $p['cat'], 'category' );
				}
				if ( ! is_wp_error( $term ) ) {
					$tid = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
					wp_set_object_terms( $pid, $tid, 'category' );
				}
				update_post_meta( $pid, '_dck_post_image', $base . $p['img'] . '.webp' );
				update_post_meta( $pid, '_dck_seo_desc', $p['excerpt'] );
			}
			$day++;
		}
		update_option( 'dck_blog_seeded_v2', 1 );
	}
}

/* Third batch: local-SEO content folded into blog posts (Nairobi-only). */
if ( ! function_exists( 'dck_blog_seed_v3' ) ) {
	function dck_blog_seed_v3() {
		if ( get_option( 'dck_blog_seeded_v3' ) ) {
			return;
		}
		$base  = get_template_directory_uri() . '/assets/images/blog/';
		$home  = home_url( '/' );
		$posts = array(
			array(
				'title'   => 'Looking for a Church in Nairobi? Welcome to Deliverance Church Kabete',
				'slug'    => 'looking-for-a-church-in-nairobi',
				'cat'     => 'Church Life',
				'img'     => 'worship',
				'excerpt' => 'Searching for a Bible-believing church in Nairobi? Deliverance Church Kabete in Uthiru welcomes you to worship, grow, and belong.',
				'body'    => <<<'DCKB'
<p>If you are searching for a <strong>church in Nairobi</strong> where you can encounter God, grow in your faith, and belong to a loving family, we would be glad to welcome you. Deliverance Church Kabete meets at the Cistern of Hope Center in Uthiru, Kabete, just behind N Market.</p>
<blockquote>&ldquo;How good and pleasant it is when God's people live together in unity.&rdquo; &mdash; Psalm 133:1</blockquote>
<h2>A church family near you</h2>
<p>Whether you live in Kabete, Uthiru, Kikuyu, Westlands, or anywhere across Nairobi, there is a place for you here. Every Sunday we gather for heartfelt worship and clear, practical Bible teaching, with ministries for children, youth, and families.</p>
<h2>What to expect on your first visit</h2>
<ul><li><strong>A warm welcome</strong> &ndash; come as you are.</li><li><strong>Worship and the Word</strong> &ndash; uplifting praise and grounded teaching.</li><li><strong>Room to belong</strong> &ndash; friendly people ready to know you.</li></ul>
<h2>Plan your visit</h2>
<p>We would love to see you this Sunday. <a href='%HOME%contact/'>Contact us</a> for directions and service times, learn more <a href='%HOME%about/'>about our church</a>, or explore our <a href='%HOME%services/'>weekly services</a>. There is a seat waiting for you at Deliverance Church Kabete.</p>
DCKB
			),
			array(
				'title'   => 'Worship With Us From Anywhere in Kenya',
				'slug'    => 'worship-with-us-from-anywhere-in-kenya',
				'cat'     => 'Church Life',
				'img'     => 'hope',
				'excerpt' => 'Based in Nairobi, our heart reaches across Kenya. Join our online services and Bible study from Nakuru, Mombasa, Eldoret, and beyond.',
				'body'    => <<<'DCKB'
<p>Deliverance Church Kabete is based in Uthiru, Nairobi, but the family of God is not limited by distance. Whether you are in Nakuru, Mombasa, Eldoret, or a village far from the city, you can worship and grow with us.</p>
<blockquote>&ldquo;For where two or three gather in my name, there am I with them.&rdquo; &mdash; Matthew 18:20</blockquote>
<h2>Connect from wherever you are</h2>
<p>You do not have to be in Nairobi to be encouraged in your faith. Our online tools help you stay rooted in the Word every day.</p>
<ul><li><strong>Watch online</strong> &ndash; join our services through our <a href='%HOME%live/'>Watch Live page</a> when we stream.</li><li><strong>Study the Bible</strong> &ndash; ask any question with our <a href='%HOME%bible/'>AI Bible study tool</a>.</li><li><strong>Listen anytime</strong> &ndash; explore past messages in our <a href='%HOME%sermons/'>sermon library</a>.</li></ul>
<h2>Visit when you are in the city</h2>
<p>Travelling to Nairobi? Our doors in Uthiru are always open to you. And wherever you are in Kenya, our team would be honoured to <a href='%HOME%contact/'>pray with you</a>. Distance is no barrier to the love of God.</p>
DCKB
			),
		);
		$day = 0;
		foreach ( $posts as $p ) {
			if ( get_page_by_path( $p['slug'], OBJECT, 'post' ) ) {
				continue;
			}
			$body = str_replace( '%HOME%', $home, $p['body'] );
			$pid  = wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $p['slug'],
				'post_content' => $body,
				'post_excerpt' => $p['excerpt'],
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( 2 + $day ) . ' days' ) ),
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				$term = term_exists( $p['cat'], 'category' );
				if ( ! $term ) {
					$term = wp_insert_term( $p['cat'], 'category' );
				}
				if ( ! is_wp_error( $term ) ) {
					$tid = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
					wp_set_object_terms( $pid, $tid, 'category' );
				}
				update_post_meta( $pid, '_dck_post_image', $base . $p['img'] . '.webp' );
				update_post_meta( $pid, '_dck_seo_desc', $p['excerpt'] );
			}
			$day++;
		}
		update_option( 'dck_blog_seeded_v3', 1 );
	}
}
