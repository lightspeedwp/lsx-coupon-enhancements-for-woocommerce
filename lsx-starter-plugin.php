<?php
/**
 * Plugin Name: LSX Coupon Enhancements For WooCommerce
 * Plugin URI:  https://github.com/lightspeeddevelopment/lsx-coupon-enhancements-for-woocommerce
 * Description: LSX Coupon Enhancements For WooCommerce for generating a coupon on remote site and sending it to a user.
 * Author:      LightSpeed
 * Version:     1.1.0
 * Author URI:  https://www.lsdev.biz/
 * License:     GPL3
 * Text Domain: lsx-cew
 * Domain Path: /languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'LSX_CEW_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSX_CEW_CORE', __FILE__ );
define( 'LSX_CEW_URL', plugin_dir_url( __FILE__ ) );
define( 'LSX_CEW_VER', '1.1.0' );

/* ======================= Below is the Plugin Class init ========================= */

require_once LSX_CEW_PATH . '/classes/class-core.php';

/**
 * Undocumented function
 *
 * @return void
 */
function lsx_cew() {
	return \lsx_cew\classes\Core::get_instance();
}
lsx_cew();
