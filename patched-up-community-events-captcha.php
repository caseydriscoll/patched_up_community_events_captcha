<?php
/**
 * Plugin Name: Patched Up Community Events Captcha
 * Plugin URI: 
 * Description: Connects Modern Tribe's Community Events to Really Simple Captcha 
 * Version: 1.0
 * Author: Casey Patrick Driscoll 
 * Author URI: http://caseypatrickdriscoll.com
 * License: GPL2
 * Needs: Community Events - http://tri.be/shop/wordpress-community-events/
 *		  Really Simply Captcha - http://wordpress.org/plugins/really-simple-captcha/
 *
 * Description: It's very simple, the plugin generates and renders a captcha before the community events submit button and then checks its accuracy during validation.
 *
 * Right now, it doesn't render the form if the captcha is invalid for some reason. That's not great, but at least it prevents spam.
 *
 * Installation: IMPORTANT! You need to add one line of code to the Community Events Core. In tribe-community-events/submission-handler.php at line 37 in function validate() add:
 *
 * if ( !apply_filters( 'tribe_community_events_captcha', '' ) ) $this->valid = FALSE;
 *
 * This is not ideal and will be overwritten with every update. You'll need to add it back.
 *
 * TODO:
 *		- Check dependency on both plugins
 *		- Render form (with message?!) if invalid
 *		- Find a better hook so don't need to add line to CE core
 */

add_action( 'tribe_events_community_before_form_submit', 'patched_up_add_captcha' );
add_filter( 'tribe_community_events_captcha', 'patched_up_check_captcha' );

/*
 * Generate and render the captcha before the submit button
 *
*/
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
						<small>Please fill in the Captcha to prove you are human!</small>
					</td>
				</tr><!-- .captcha -->
			</tbody>
		</table><!-- #event-captcha -->
	</div>
<?php
}

/*
 * After submission, check validity and return to 
 * TribeCommunityEvents_SubmissionHandler::validate()
 *
 * @return boolean
*/
function patched_up_check_captcha() {
	$captcha_instance = new ReallySimpleCaptcha();
	$isValid = FALSE;

	if ( isset( $_POST['EventCaptchaPrefix'] ) && isset( $_POST['EventCaptcha'] ) ) {
		$prefix = $_POST['EventCaptchaPrefix'];
		$answer = $_POST['EventCaptcha'];
	} 

	if ( $captcha_instance->check( $prefix, $answer ) ) {
		$captcha_instance->remove( $prefix );
		$isValid = TRUE;
	} else {
		$captcha_instance->remove( $prefix );
	}
	
	//error_log( 'PREFIX: ' . $prefix . ' ANSWER: ' . $answer . ' VALID: ' . $isValid );

	return $isValid;
}
