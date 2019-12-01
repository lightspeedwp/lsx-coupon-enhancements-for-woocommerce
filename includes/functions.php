<?php
/**
 * LSX Starter Plugin functions.
 *
 * @package lsx-cew
 */

/**
 * Adds text domain.
 */
function lsx_cew_load_plugin_textdomain() {
	load_plugin_textdomain( 'lsx-cew', false, basename( LSX_CEW_PATH ) . '/languages' );
}
add_action( 'init', 'lsx_cew_load_plugin_textdomain' );