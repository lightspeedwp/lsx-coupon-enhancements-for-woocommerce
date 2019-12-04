<?php
namespace lsx_cew\classes;

/**
 * LSX Starter Plugin Admin Class.
 *
 * @package lsx-cew
 */
class Admin {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx_cew\classes\Admin()
	 */
	protected static $instance = null;

	/**
	 * Contructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		// Add Plugin Settings Page into WooCommerse Tab
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'lsx_cew_add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_lsx_cew', array( $this, 'lsx_cew_setup_settings_tab' ) );
		add_action( 'woocommerce_update_options_lsx_cew', array( $this, 'lsx_cew_save_settings' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\member_directory\classes\Admin()    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function assets() {
		// wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_script( 'lsx-cew-admin', LSX_CEW_URL . 'assets/js/lsx-cew-admin.min.js', array( 'jquery' ), LSX_CEW_VER, true );
		wp_enqueue_style( 'lsx-cew-admin', LSX_CEW_URL . 'assets/css/lsx-cew-admin.css', array(), LSX_CEW_VER );
	}

	/**
	 * Adds a new settings tab to a WooCommerse Settings.
	 *
	 * @param   array $settings_tabs  WooCommerse Settings array.
	 *
	 * @return  array                  New Settings array.
	 */
	public static function lsx_cew_add_settings_tab( $settings_tabs ) {
		$settings_tabs['lsx_cew'] = __( 'Coupons', 'lsx-cew' );
		return $settings_tabs;
	}

	/**
	 * Setup our custom settings tab.
	 */
	public function lsx_cew_setup_settings_tab() {
		woocommerce_admin_fields( \lsx_cew\classes\Admin::lsx_cew_get_settings() );
	}
	/**
	 * Save our settings.
	 */
	public function lsx_cew_save_settings() {
		woocommerce_update_options( \lsx_cew\classes\Admin::lsx_cew_get_settings() );
	}

	/**
	 * Details of our custom settings tab.
	 */
	public function lsx_cew_get_settings() {
		$settings = array(
			'section_1_title'     => array(
				'name' => __( 'Coupon 1 Settings', 'lsx-cew' ),
				'type' => 'title',
				'desc' => __( 'Settings for Monthly Subscription Coupon', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_1_setting_section',
			),
			'coupon_1_product_id' => array(
				'name' => __( 'Product ID', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'Product ID for Monthly Subscription', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_1_product_id',
			),
			'coupon_1_gen_url'    => array(
				'name' => __( 'Coupon Generating URL', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'URL of the REST API host that will generate the coupon (NOTE: use "localhost" for this server)', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_1_coupon_gen_url',
			),
			'coupon_1_gen_key'    => array(
				'name' => __( 'REST API Consumer Key', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'REST API Consumer Key (NOTE: leave empty if using "localhost" above)', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_1_rest_consumer_key',
			),
			'coupon_1_gen_secret' => array(
				'name' => __( 'REST API Consumer Secret', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'REST API Consumer Secret (NOTE: leave empty if using "localhost" above)', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_1_rest_consumer_secret',
			),
			'section_1_end'       => array(
				'type' => 'sectionend',
				'id'   => 'lsx_cew_coupon_1_setting_section_end',
			),
			'section_2_title'     => array(
				'name' => __( 'Coupon 2 Settings', 'lsx-cew' ),
				'type' => 'title',
				'desc' => __( 'Settings for Annual Subscription Coupon', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_2_setting_section',
			),
			'coupon_2_product_id' => array(
				'name' => __( 'Product ID', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'Product ID for Annual Subscription', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_2_product_id',
			),
			'coupon_2_gen_url'    => array(
				'name' => __( 'Coupon Generating URL', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'URL of the REST API host that will generate the coupon (NOTE: use "localhost" for this server)', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_2_coupon_gen_url',
			),
			'coupon_2_gen_key'    => array(
				'name' => __( 'REST API Consumer Key', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'REST API Consumer Key (NOTE: leave empty if using "localhost" above)', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_2_rest_consumer_key',
			),
			'coupon_2_gen_secret' => array(
				'name' => __( 'REST API Consumer Secret', 'lsx-cew' ),
				'type' => 'text',
				'desc' => __( 'REST API Consumer Secret (NOTE: leave empty if using "localhost" above)', 'lsx-cew' ),
				'id'   => 'lsx_cew_coupon_2_rest_consumer_secret',
			),
			'section_2_end'       => array(
				'type' => 'sectionend',
				'id'   => 'lsx_cew_coupon_2_setting_section_end',
			),
			'section_3_title'     => array(
				'name' => __( 'Coupon Email Notification Settings', 'lsx-cew' ),
				'type' => 'title',
				'desc' => "<strong><a href='http://rwplus.local/wp-admin/admin.php?page=wc-settings&tab=email&section=lsx_cnw_coupon_notification_email'>Email Notification Settings</a></strong>",
				'id'   => 'lsx_cew_coupon_3_setting_section',
			),
			'section_3_end'       => array(
				'type' => 'sectionend',
				'id'   => 'lsx_cew_coupon_3_setting_section_end',
			),
		);

		return apply_filters( 'wc_lsx_cew_settings', $settings );
	}
}
