<?php
/**
 * Plugin Name: Patched Up Community Events Captcha
 * Plugin URI: 
 * Description: Connects Modern Tribe's Community Events to Really Simple Captcha 
 * Version: 1.0
 * Author: Casey Patrick Driscoll 
 * Author URI: http://caseypatrickdriscoll.com
 * License: GPL2
 */

add_action( 'tribe_events_community_before_form_submit', 'patched_up_add_captcha' );
add_action( 'tribe_captcha_community_events', 'patched_up_check_captcha' );

function patched_up_add_captcha() {
	$captcha_instance = new ReallySimpleCaptcha();

	$word = $captcha_instance->generate_random_word();
	$prefix = mt_rand();

	$img = $captcha_instance->generate_image( $prefix, $word );
	$dir = $captcha_instance->tmp_dir;
?>
	<div class="tribe-events-community-details eventForm bubble" id="events-captcha">
		<table class="tribe-community-event-info" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td colspan="2" class="tribe_sectionheader">
						<h4>Captcha</h4>
					</td><!-- .tribe_sectionheader -->
				</tr>

				<tr class="">
					<td><label for="EventCaptcha" class="">Captcha: </label></td>
					<td>
						<img src="<?php echo plugins_url( $img, $dir . '/tmp/' );?>">
						<input type="text" id="Captcha" name="EventCaptcha" size="4" value="">
						<input type="hidden" name="EventCaptchaPrefix" value="<?php echo $prefix;?>"/>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<small>Please fill in to prove you are human!</small>
					</td>
				</tr><!-- .captcha -->
			</tbody>
		</table><!-- #event_cost -->
	</div>
<?php
}

function patched_up_check_captcha() {
	$captcha_instance = new ReallySimpleCaptcha();

	if ( isset( $_POST['EventCaptchaPrefix'] ) ) {
		$prefix = $_POST['EventCaptchaPrefix'];
		$the_answer = $_POST['EventCaptcha'];
	} else
		return FALSE;

	if ( $captcha_instance->check( $prefix, $the_answer ) ) {
		$captcha_instance->remove( $prefix );
		return TRUE;
	} else {
		$captcha_instance->remove( $prefix );
		return FALSE;
	
		//wp_redirect( home_url() . '/events/community/add' ); exit;
	}

}
