<?php
/**
 * @package   Musilda
 * @author    musilda.cz
 * @license   GPL-2.0+
 * @link      https://musilda.cz/
 * @copyright 2022 Musilda.cz
 *
 * Plugin Name:       Discount for first purchase
 * Plugin URI:        https://musilda.cz/automaticke-vygenerovani-kuponu-na-slevu-za-prvni-nakup-ve-woocommerce/
 * Description:       Automatické vygenerování kupónu na slevu za první nákup ve WooCommerce
 * Version:           1.0.0
 * Author:            musilda.cz
 * Author URI:        https://musilda.cz/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is activated 
 */
if (! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	die;
}


add_action( 'woocommerce_checkout_order_created', 'generate_custom_coupon' );
function generate_custom_coupon( $order ) {

	global $wpdb;
	$table = $wpdb->prefix . 'postmeta';
	$query   = $wpdb->prepare( "SELECT meta_id FROM $table WHERE meta_key = '_billing_email' AND meta_value = '%s'", array( $order->get_billing_email() ) );
	$results = $wpdb->get_results( $query );
	if ( !empty( $results ) && count( $results ) == 1 ) {

	   	$code = make_coupon_code();

		$coupon_args = array(
			'post_title' 	=> $code,
			'post_content' 	=> '',
			'post_status' 	=> 'publish',
			'post_author' 	=> 1,
			'post_type' 	=> 'shop_coupon'
		);
		   
		$coupon_id = wp_insert_post( $coupon_args );

		update_post_meta( $coupon_id, 'discount_type', 'percent' );
		update_post_meta( $coupon_id, 'coupon_amount', 10 );
		update_post_meta( $coupon_id, 'free_shipping', 'no' );
		update_post_meta( $coupon_id, 'usage_limit', 1 );
    
		update_post_meta( $order->get_id(), 'generated_coupon', $code );
		update_post_meta( $order->get_id(), 'generated_coupon_id', $coupon_id );
    
	}

}

function make_coupon_code() {

	$code = '';
	$keys = array_merge( range( 0, 9 ), range( 'a', 'z' ) );

	for ($i = 0; $i < 10; $i++) {
		$code .= $keys[array_rand( $keys )];
	}

	return strtoupper( $code );

}

add_action( 'user_register', 'add_discount_for_registration', 1 );
function add_discount_for_registration($user_id) {

	$user = get_user_by('id',$user_id);//Get the current user object

	$coupon_title =  strtoupper( get_bloginfo('name')) . '10';
	$coupon = new WC_Coupon( $coupon_title );//Get the coupon object
	$emails = $coupon->get_email_restrictions();//Returns an empty array or array of emails
	$emails[] = strtolower($user->user_email);//Add user's billing email address to the array
	$emails = array_filter($emails);//Remove empty values
	array_unique($emails);//Remove any duplicate values
	$coupon->set_email_restrictions($emails);//Set the coupon's array with the updated array
	$coupon->set_discount_type('percent');
	$coupon->set_amount(10);
	$coupon->set_usage_limit_per_user(1);
	$coupon->set_individual_use('no');
	$coupon->set_description('Slevový kód -10% za registraci na webu');
	$coupon->save();//Save the coupon


}

/**
 * @snippet       Add Text to Customer Processing Order Email
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @testedwith    Woo 4.6
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
  
add_action( 'woocommerce_email_before_order_table', 'bbloomer_add_content_specific_email', 20, 4 );
  
function bbloomer_add_content_specific_email( $order, $sent_to_admin, $plain_text, $email ) {
   if ( $email->id == 'customer_new_account' ) {
	echo '<h3>' . __( 'Děkujeme za Vaši registraci!', 'musilda' ) . '</h3>';
	echo '<p>' . __( 'Jako poděkování jsme Vám vytvořili slevový kupón ve výši 10% na nákup.', 'musilda' ) . '<br/>';
	echo __( 'Pro získání slevy, při nákupu zadete v pokladně následující kód:', 'musilda' ) . ' ' ,  strtoupper( get_bloginfo('name')) . '10' .'</p>';
   }
}
/**
 * Display coupon code on the thank you page
 */

add_action( 'woocommerce_thankyou', 'display_generated_coupon' );
function display_generated_coupon( $order_id ) {

	$coupon_code = get_post_meta( $order_id, 'generated_coupon', true );
	if ( !empty( $coupon_code ) ) {
		echo '<h3>' . __( 'Děkujeme za váš první nákup!', 'musilda' ) . '</h3>';
		echo '<p>' . __( 'Jako poděkování jsme Vám vytvořili slevový kupón ve výši 10% na další nákup.', 'musilda' ) . '<br/>';
		echo __( 'Pro získání slevy, při dalším nákupu zadete v pokladně následující kód:', 'musilda' ) . ' ' , $coupon_code .'</p>';
	}

}



/**
 * Class Custom_WC_Email
 */
class Discount_Custom_WC_Email {

	/**
	 * Custom_WC_Email constructor.
	 */
	public function __construct() {
    // Filtering the emails and adding our own email.
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
    // Absolute path to the plugin folder.
		define( 'CUSTOM_WC_EMAIL_PATH', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * @param array $emails
	 *
	 * @return array
	 */
	public function register_email( $emails ) {
		require_once 'email/WC_First_Order_Coupon_Email.php';

		$emails['WC_First_Order_Coupon_Email'] = new WC_First_Order_Coupon_Email();

		return $emails; 
	}
}

new Discount_Custom_WC_Email();

add_filter( 'woocommerce_defer_transactional_emails', '__return_false' );
add_action( 'woocommerce_checkout_order_processed', 'send_first_order_coupon_email' );
function send_first_order_coupon_email( $order_id ) {
	
	$coupon_code = get_post_meta( $order_id, 'generated_coupon', true );

	if ( !empty( $coupon_code ) ) {
		
		WC()->mailer();		
		$send = new WC_First_Order_Coupon_Email();
		$mail = $send->trigger( $order_id );

	}

}
