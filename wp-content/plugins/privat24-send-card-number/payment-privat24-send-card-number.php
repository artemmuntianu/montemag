<?php
/*
Plugin Name:       Payment Privat24 Card Number
Plugin URI:        
Description:       A WooCommerce Extension that adds the payment gateway "Privat24 Card Number"
Version:           1.0.0
Author:            Montend
*/

/**
 * Start the plugin
 */
function wc_privat24_send_card_number_init() {
    global $woocommerce;

    if( !isset( $woocommerce ) ) { return; }

	$domain = 'payment-privat24-send-card-number';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$path   = plugins_url('payment-privat24-send-card-number/language/'.$domain.'-'.$locale.'.mo');
	$loaded = load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/language/' );
	if ( !$loaded )
	{
		$path   = plugins_url('payment-privat24-send-card-number/language/'.$domain.'-en_US.mo');
		$loaded = load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/language/' );
	}

    require_once( 'classes/class.wc-cp.php' );
}
add_action( 'plugins_loaded', 'wc_privat24_send_card_number_init' );

/**
 * Add in WooCommerce payment gateways
 * @param $methods
 * @return array
 */
function add_privat24_send_card_number( $methods ) {
    $methods[] = 'WC_Gateway_Privat24_send_card_number';
    return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_privat24_send_card_number' );
