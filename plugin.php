<?php
/*
Plugin Name: WSUWP Content Syndicate People
Plugin URI: https://web.wsu.edu/wordpress/plugins/wsuwp-content-syndicate/
Description: Retrieve people for display from people.wsu.edu.
Author: washingtonstateuniversity, jeremyfelt
Author URI: https://web.wsu.edu/
Version: 1.5.0
*/

namespace WSU\ContentSyndicate\People;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'wsuwp_content_syndicate_shortcodes', 'WSU\ContentSyndicate\People\activate_shortcodes' );
/**
 * Activates the wsuwp_people shortcode.
 *
 * @since 1.0.0
 */
function activate_shortcodes() {
	include_once dirname( __FILE__ ) . '/includes/class-wsu-syndicate-shortcode-people.php';

	// Add the [wsuwp_people] shortcode to pull calendar events.
	new \WSU_Syndicate_Shortcode_People();
}
