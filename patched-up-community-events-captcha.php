<?php
/**
 * Plugin Name: Patched Up Community Events Captcha
 * Plugin URI:
 * Description: Connects Modern Tribe's Community Events to Really Simple Captcha - you must install Really Simple Captcha for this plugin to operate successfully!
 * Version: 1.0
 * Author: Casey Patrick Driscoll
 * Author URI: http://caseypatrickdriscoll.com
 * License: GPL2
 * Needs: Community Events - http://tri.be/shop/wordpress-community-events/
 *		  Really Simply Captcha - http://wordpress.org/plugins/really-simple-captcha/
 */

add_filter( 'plugins_loaded', 'patched_up_maybe_add_community_captcha' );

function patched_up_maybe_add_community_captcha() {
	if ( ! class_exists( 'TribeCommunityEvents' ) ) $bail = true;
	if ( ! class_exists( 'ReallySimpleCaptcha' ) ) $bail = true;
	if ( ! isset( $bail ) ) patched_up_do_community_captcha();
}

/**
 * Wire up Community Events and Really Simple Captcha.
 */
function patched_up_do_community_captcha() {
	add_action('tribe_events_community_before_form_submit', 'patched_up_add_captcha');
	add_filter('tribe_events_community_allowed_event_fields', 'patched_up_allow_captcha_fields');
	add_filter('tribe_community_is_field_valid', 'patched_up_check_captcha', 20, 3);
}

/**
 * Generate and render the captcha before the submit button
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

/**
 * Ensure TribeCommunityEvents_SubmissionHandler doesn't strip out the captcha fields
 * (and so ensure a validation check is performed upon them).
 *
 * @param array $allowed_fields
 * @return array
 */
function patched_up_allow_captcha_fields( array $allowed_fields ) {
	$allowed_fields[] = 'EventCaptcha';
	$allowed_fields[] = 'EventCaptchaPrefix';
	return $allowed_fields;
}

/*
 * After submission, check validity and return to 
 * TribeCommunityEvents_SubmissionHandler::validate()
 *
 * @return boolean
*/
function patched_up_check_captcha( $valid, $key, $value ) {
	// We're only interested in the captcha field
	if ( 'EventCaptcha' !== $key && 'EventCaptchaPrefix' !== $key ) return $valid;

	// We only need this callback to run one
	remove_filter( 'tribe_community_is_field_valid', 'patched_up_check_captcha', 20, 3 );

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

	return $isValid;
}
