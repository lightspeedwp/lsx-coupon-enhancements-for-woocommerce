<?php
namespace lsx_cew\classes;

require LSX_CEW_PATH . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

/**
 * Contains all the classes for 3rd party Integrations
 *
 * @package lsx-cew
 */
class Integrations {


	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx_cew\classes\Integrations()
	 */
	protected static $instance = null;

	/**
	 * Contructor
	 */
	public function __construct() {
		// We tap into woocommerce_before_thankyou hook.
		add_action( 'woocommerce_before_thankyou', array( $this, 'lsx_cew_post_order_actions' ) );
	}

	/**
	 * Function which generates coupon based on monthly or annual subscription.
	 *
	 * @param   int $order_id  Order ID.
	 */
	public function lsx_cew_post_order_actions( $order_id ) {
		// Get Order and User data to figure out if monthly or annual subscription was bought.
		$order                                = wc_get_order( $order_id );
		$products                             = $order->get_items();
		$user                                 = $order->get_user();
		$monthly_product_id                   = get_option( 'lsx_cew_coupon_1_product_id', true );
		$annual_product_id                    = get_option( 'lsx_cew_coupon_2_product_id', true );
		$subscriptions[ $monthly_product_id ] = 1; // monthly.
		$subscriptions[ $annual_product_id ]  = 2; // annual.

		foreach ( $products as $product ) {
			$product_id = (int) $product->get_product_id();
			$type       = $this->lsx_cew_get_subscription_type( $product_id, $subscriptions );

			if ( $user && $type ) {
				// Generate local or remote coupon.
				$coupon = $this->lsx_cew_generate_coupon( $user->user_email, $type );

				if ( $coupon ) {
					/* translators: %1$s: order id, %2$s: coupon code */
					$order->add_order_note( sprintf( __( 'Coupon was generated for order id %1$s (%2$s).' ), $order_id, $coupon ) );
					// We save coupon here, so we could get it when mailing this to user.
					update_post_meta( $order_id, 'lsx_cew_coupon_code', $coupon );
					// We modify the Thank You message to notify the user about the coupon. TODO: Do we need to do this? I just put it here as a "nice to have".
					// For PHP version 5.3 or newer we use closures.
					// If we have to, we could support older PHP?
					// https://wordpress.stackexchange.com/questions/45901/passing-a-parameter-to-filter-and-action-functions
					add_filter(
						'woocommerce_thankyou_order_received_text',
						function( $original_message ) use ( $status ) {
							return $this->lsx_cew_modify_thankyou_message( $original_message, $status );
						}
					);
				} else {
					/* translators: %1$s: order id */
					$order->add_order_note( sprintf( __( 'Coupon was not generated for order id %1$s.' ), $order_id ) );
				}

				break;
			}
		}
	}

	/**
	 * We use this function to modify the Thank You message after order is placed.
	 *
	 * @param   string  $original_message  Original Thank You message.
	 * @param   boolean $status            True if mail was sent to user with the coupon, False if it did not.
	 *
	 * @return  string                     Modified Thank You message
	 */
	public function lsx_cew_modify_thankyou_message( $original_message, $status ) {
		$message = $original_message;

		if ( $status ) {
			$message .= ' (coupon was mailed to you)';
		}

		return $message;
	}

	/**
	 * Helper function for determining the type of subscription that user selected.
	 *
	 * @param   int   $product_id     Product ID
	 * @param   array $subscriptions  Key - Value array containing possible subscriptions
	 *
	 * @return  int                   Returns integer representing type of the subscription. 1 for monthly, 2 for annual, null if not one of those.
	 */
	public function lsx_cew_get_subscription_type( $product_id, $subscriptions ) {
		$type = null;

		if ( array_key_exists( $product_id, $subscriptions ) ) {
			$type = (int) $subscriptions[ $product_id ];
		}

		return $type;
	}

	/**
	 * Generate coupon based on type of subscription.
	 *
	 * @param   string $email     Customers email.
	 * @param   int    $type      Type of subscription (1 - monthly, 2 - annual).
	 *
	 * @return  string            Generated Coupon Code.
	 */
	public function lsx_cew_generate_coupon( $email, $type ) {
		$coupon        = null;
		$generator_url = get_option( 'lsx_cew_coupon_' . $type . '_coupon_gen_url', true );

		if ( 'localhost' == $generator_url ) {
			$coupon = $this->lsx_cew_generate_local_coupon( $email );
		} else {
			$consumer_key    = get_option( 'lsx_cew_coupon_' . $type . '_rest_consumer_key', true );
			$consumer_secret = get_option( 'lsx_cew_coupon_' . $type . '_rest_consumer_secret', true );
			$coupon          = lsx_cew_generate_remote_coupon( $email, $generator_url, $consumer_key, $consumer_secret );
		}

		return $coupon;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx_cew\classes\Integrations()    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Generates a Coupon on a remote machine via WooCommerce REST API.
	 *
	 * @param   string $email            Customers email address.
	 * @param   string $generator_url    Url of the coupon generating machine.
	 * @param   string $consumer_key     REST API consumer key.
	 * @param   string $consumer_secret  REST API consumer secret.
	 *
	 * @return  string                    Generated coupon.
	 *
	 * TODO 1: need to know the value of coupon (10%? 50%? R20? R100?).
	 * TODO 2: when should coupon expire?
	 */
	public static function lsx_cew_generate_remote_coupon( $email, $generator_url, $consumer_key, $consumer_secret ) {
		// Connect to remote machines REST API.
		$woocommerce = new Client(
			$generator_url,
			$consumer_key,
			$consumer_secret,
			array(
				'wp_api'            => true,
				'version'           => 'wc/v3',
				'query_string_auth' => true,
			)
		);

		// Generate the coupon by hashing users email address.
		// NOTE: will only be unique if user does not try to buy subscription again, so think of a better way.
		$coupon = strtoupper( hash( 'adler32', $email, false ) );

		// Build coupon expiry date.
		$date_expires = date( 'Y-m-d H:i:s', strtotime( '2019-07-31' ) ); // TODO 1

		// Build data array needed for coupon generation.
		$coupon_data = array(
			'code'                 => $coupon,
			'discount_type'        => 'percent', // percent, fixed_cart, fixed_product. Default is fixed_cart.
			'amount'               => '10', // TODO 2
			'usage_limit'          => 1,
			'usage_limit_per_user' => 1,
			'email_restrictions'   => array( $email ),
			'date_expires'         => $date_expires, // 2019-07-31 00:00:00
		);

		// Generate coupon.
		$response = $woocommerce->post( 'coupons', $coupon_data );

		return $response->code;
	}

	/**
	 * Generate a Coupon on the local machine.
	 *
	 * @param   string $email  Customers email address.
	 *
	 * @return  string          Generated coupon.
	 *
	 * TODO 1: when should coupon expire?
	 */
	public static function lsx_cew_generate_local_coupon( $email ) {
		// Check if the coupon has already been created in the database.
		global $wpdb;
		$expiry_date  = '2020-12-31'; // TODO 1
		$coupon       = strtoupper( hash( 'adler32', $email, false ) );
		$date_expires = gmtdate( 'Y-m-d H:i:s', strtotime( $expiry_date ) );
		$sql          = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1;", $coupon );
		$coupon_id    = $wpdb->get_var( $sql ); // WPCS: unprepared SQL OK.

		if ( empty( $coupon_id ) ) {
			// Create a coupon with the properties you need.
			$data = array(
				'discount_type'        => 'percent', // percent, fixed_cart, fixed_product. Default is fixed_cart.
				'coupon_amount'        => 50,
				'usage_limit'          => 1,
				'usage_limit_per_user' => 1,
				'customer_email'       => array( $email ),
				'date_expires'         => $date_expires, // 2019-07-31 00:00:00

			);

			// Create database entry for a coupon.
			$coupon_data = array(
				'post_title'   => $coupon,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'shop_coupon',
			);

			// Save coupon into DB.
			$new_coupon_id = wp_insert_post( $coupon_data );

			foreach ( $data as $key => $value ) {
				update_post_meta( $new_coupon_id, $key, $value );
			}
		}

		return $coupon;
	}
}
