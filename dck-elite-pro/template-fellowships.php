<?php
/**
 * Template Name: DCK Home Fellowships
 *
 * @package DCK_Elite
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'dck-forms' );
get_header();
$dck_groups = dck_fs_groups();
$dck_areas  = array_values( array_unique( array_filter( wp_list_pluck( $dck_groups, 'area' ) ) ) );
dck_photo_hero(
	dck_theme_image( 'dck_fs_hero_img', 'assets/images/home/joy.webp' ),
	__( 'Home Fellowships', 'dck-elite' ),
	__( 'Find your people near you', 'dck-elite' ),
	__( 'Small groups meet in homes across Uthiru, Kabete and beyond to pray, study the Word and care for one another.', 'dck-elite' )
);
?>
<section class="dck-section">
	<div class="dck-container">
		<?php if ( $dck_groups ) : ?>
			<?php if ( count( $dck_areas ) > 1 ) : ?>
				<div class="dck-bible-chips dck-fs-filter" role="group" aria-label="<?php esc_attr_e( 'Filter by area', 'dck-elite' ); ?>">
					<button type="button" class="dck-chip is-active" data-area="" aria-pressed="true"><?php esc_html_e( 'All areas', 'dck-elite' ); ?></button>
					<?php foreach ( $dck_areas as $a ) : ?><button type="button" class="dck-chip" data-area="<?php echo esc_attr( $a ); ?>" aria-pressed="false"><?php echo esc_html( $a ); ?></button><?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="dck-fs-grid">
				<?php foreach ( $dck_groups as $g ) : ?>
					<article class="dck-fs-card" data-area="<?php echo esc_attr( $g['area'] ); ?>">
						<?php if ( $g['area'] ) : ?><span class="dck-card__tag"><?php echo esc_html( $g['area'] ); ?></span><?php endif; ?>
						<h3><?php echo esc_html( $g['name'] ); ?></h3>
						<?php if ( $g['desc'] ) : ?><p><?php echo esc_html( $g['desc'] ); ?></p><?php endif; ?>
						<ul class="dck-emeta">
							<?php if ( $g['day'] || $g['time'] ) : ?><li><?php echo esc_html( trim( $g['day'] . ( $g['time'] ? ', ' . $g['time'] : '' ) ) ); ?></li><?php endif; ?>
							<?php if ( $g['venue'] ) : ?><li><?php echo esc_html( $g['venue'] ); ?></li><?php endif; ?>
							<?php if ( $g['leader'] ) : ?><li><?php echo esc_html( sprintf( __( 'Leader: %s', 'dck-elite' ), $g['leader'] ) ); ?></li><?php endif; ?>
						</ul>
						<a class="dck-btn dck-btn--outline dck-btn--sm" href="#join" data-join="<?php echo esc_attr( $g['id'] ); ?>"><?php esc_html_e( 'Join this group', 'dck-elite' ); ?></a>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="dck-formcard" style="text-align:center"><h2><?php esc_html_e( 'Groups are being updated', 'dck-elite' ); ?></h2><p><?php esc_html_e( 'Tell us where you live below and we will connect you to the fellowship nearest you.', 'dck-elite' ); ?></p></div>
		<?php endif; ?>
	</div>
</section>
<section class="dck-section dck-gift" id="join">
	<div class="dck-container dck-gift__grid">
		<div class="dck-gift__intro">
			<p class="dck-eyebrow"><?php esc_html_e( 'Join a fellowship', 'dck-elite' ); ?></p>
			<h2><?php esc_html_e( 'We will connect you this week', 'dck-elite' ); ?></h2>
			<p><?php esc_html_e( 'Choose a group, or leave it on "Help me choose" and tell us your area. The group leader will reach out to welcome you.', 'dck-elite' ); ?></p>
		</div>
		<form class="dck-ctform dck-formcard" data-endpoint="<?php echo esc_url( rest_url( 'dck/v1/serve' ) ); ?>" novalidate>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Fellowship group', 'dck-elite' ); ?></span>
				<select name="group" id="dck-fs-group">
					<?php if ( $dck_groups ) : ?>
						<?php foreach ( $dck_groups as $g ) : ?><option value="<?php echo esc_attr( $g['id'] ); ?>"><?php echo esc_html( $g['name'] . ( $g['area'] ? ' (' . $g['area'] . ')' : '' ) ); ?></option><?php endforeach; ?>
					<?php endif; ?>
				</select>
				<?php if ( ! $dck_groups ) : ?><input type="hidden" name="ministry" value="Care and Welfare"><?php endif; ?>
			</label>
			<div class="dck-cform__row">
				<label class="dck-cform__field"><span><?php esc_html_e( 'Your name', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="text" name="name" autocomplete="name" required></label>
				<label class="dck-cform__field"><span><?php esc_html_e( 'Phone / WhatsApp', 'dck-elite' ); ?> <em class="dck-req">*</em></span><input type="tel" name="phone" autocomplete="tel" required placeholder="+254 7XX XXX XXX"></label>
			</div>
			<label class="dck-cform__field"><span><?php esc_html_e( 'Where do you live?', 'dck-elite' ); ?></span><input type="text" name="area" placeholder="<?php esc_attr_e( 'e.g. Uthiru, Kabete, Kangemi', 'dck-elite' ); ?>"></label>
			<div class="dck-hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<button type="submit" class="dck-btn dck-btn--primary dck-btn--block"><?php esc_html_e( 'Connect me', 'dck-elite' ); ?></button>
			<p class="dck-ctform__status" role="status" aria-live="polite"></p>
		</form>
	</div>
</section>
<script>
(function(){var chips=document.querySelectorAll('.dck-fs-filter .dck-chip'),cards=document.querySelectorAll('.dck-fs-card');
for(var i=0;i<chips.length;i++){chips[i].addEventListener('click',function(){var a=this.getAttribute('data-area');for(var j=0;j<chips.length;j++){chips[j].classList.toggle('is-active',chips[j]===this);chips[j].setAttribute('aria-pressed',chips[j]===this?'true':'false');}for(var k=0;k<cards.length;k++){cards[k].hidden=!!a&&cards[k].getAttribute('data-area')!==a;}});}
document.addEventListener('click',function(e){var b=e.target.closest&&e.target.closest('[data-join]');if(!b){return;}var s=document.getElementById('dck-fs-group');if(s){s.value=b.getAttribute('data-join');}});})();
</script>
<?php
get_footer();
