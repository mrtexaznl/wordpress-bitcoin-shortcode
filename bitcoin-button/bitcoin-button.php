<?php
/**
 * @package jonls.dk-Bitcoin-Button
 * @author Jon Lund Steffensen
 * @version 0.4
 */
/*
Plugin Name: jonls.dk-Bitcoin-Button
Plugin URI: http://jonls.dk/
Description: Shortcode for inserting a bitcoin button
Author: Jon Lund Steffensen
Version: 0.4
Author URI: http://jonls.dk/
*/

function bitcoin_button_load_scripts_init_cb() {
	echo '<script type="text/javascript">window.bitcoinwidget_init = { autoload: true, host: "//bitcoinwidget.appspot.com" };</script>';
	echo '<script type="text/javascript" src="//bitcoinwidget.appspot.com/js/bitcoinwidget.js" async></script>';
}
add_action('wp_head', 'bitcoin_button_load_scripts_init_cb');


/* Shortcode handler for "bitcoin" */
function bitcoin_button_shortcode_handler($atts) {
	$address = $atts['address'];
	$info = isset($atts['info']) ? $atts['info'] : 'received';
	$amount = isset($atts['amount']) ? $atts['amount'] : null;
	$label = isset($atts['label']) ? $atts['label'] : null;
	$message = isset($atts['message']) ? $atts['message'] : null;

	$t = null;
	if (!is_feed()) {
		$t = '<div class="bitcoin-button" data-address="'.$address.'" data-info="'.$info.'" data-message="'.$message.'"></div>';
	} else {
		$t = 'Bitcoin: <a href="bitcoin:'.$address.'">'.(!is_null($label) ? $label : $address).'</a>';
	}

	return $t;
}
add_shortcode('bitcoin', 'bitcoin_button_shortcode_handler');
