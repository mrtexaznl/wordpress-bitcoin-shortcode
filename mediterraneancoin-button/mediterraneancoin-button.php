<?php
/**
 * @package jonls.dk-Mediterraneancoin-Button
 * @author Jon Lund Steffensen - modified by MEDteam developers
 * @version 0.4
 */
/*
Plugin Name: jonls.dk-Mediterraneancoin-Button
Plugin URI: http://www.mediterraneancoin.org/
Description: Shortcode for inserting a Mediterraneancoin button
Author: Jon Lund Steffensen - modified by MEDteam developers
Version: 0.4
Author URI: http://jonls.dk/
*/

function mediterraneancoin_button_load_scripts_init_cb() {
	if (!is_admin()) {
        wp_enqueue_style('mediterraneancoin-button', plugins_url('/style.css', __FILE__));
		wp_enqueue_script('mediterraneancoin-button', plugins_url('/script.js', __FILE__), array('jquery'));
        wp_localize_script('mediterraneancoin-button', 'mediterraneancoin_button_ajax', array('url' => admin_url('admin-ajax.php')));
	}
}
add_action('init', 'mediterraneancoin_button_load_scripts_init_cb');


/* Shortcode handler for "mediterraneancoin" */
function mediterraneancoin_button_shortcode_handler($atts) {
    $address = $atts['address'];
    $info = isset($atts['info']) ? $atts['info'] : 'received';
    $amount = isset($atts['amount']) ? $atts['amount'] : null;
    $label = isset($atts['label']) ? $atts['label'] : null;
    $message = isset($atts['message']) ? $atts['message'] : null;

    /* Build mediterraneancoin url */
    $url = 'mediterraneancoin:'.$address;
    $params = array();
    if (!is_null($amount)) $params[] = 'amount='.urlencode($amount);
    if (!is_null($label)) $params[] = 'label='.urlencode($label);
    if (!is_null($message)) $params[] = 'message='.urlencode($message);
    if (count($params) > 0) $url .= '?'.implode('&', $params);

    /* Allow this address to be queried externally from address-info.php. */
    $address_list = get_option('mediterraneancoin_address_list', array());
    $address_list[] = $address;
    update_option('mediterraneancoin_address_list', $address_list);

	$t = null;
	if (!is_feed()) {
		$t = '<a class="mediterraneancoin-button" data-address="'.$address.'" data-info="'.$info.'" href="'.$url.'">Mediterraneancoin</a>';
	} else {
		$t = 'Mediterraneancoin: <a href="'.$url.'">'.(!is_null($label) ? $label : $address).'</a>';
	}

	return $t;
}
add_shortcode('mediterraneancoin', 'mediterraneancoin_button_shortcode_handler');


/* AJAX handler for Mediterraneancoin address data */
function mediterraneancoin_button_get_address_info() {
    $blockchain_cache_time = 30*60;

    if (!isset($_GET['address'])) {
        header('Content-Type: text/plain');
        status_header(404);
        echo 'Invalid address';
        exit;
    }

    $address = trim($_GET['address']);
    if ($address === '') {
        header('Content-Type: text/plain');
        status_header(404);
        echo 'Invalid address';
        exit;
    }

    /* Only allow external queries that have been explicitly allowed. */
    $address_list = get_option('mediterraneancoin_address_list', array());
    if (!in_array($address, $address_list)) {
        header('Content-Type: text/plain');
        status_header(404);
        echo 'Address not allowed';
        exit;
    }

    header('Content-Type: application/json');

    /* Set JSON content type and caching policy. */
    header('Expires: '.gmdate('D, d M Y H:i:s', time() + $blockchain_cache_time).' GMT');

    $output = array('address' => $address);

    $data = get_transient('medaddr-'.$address);
    if ($data === false) {
        $response = wp_remote_get('http://explorer.mediterraneancoin.org/chain/Mediterraneancoin/q/addressbalance/'.urlencode($address).'?format=json&limit=0');
        $code = wp_remote_retrieve_response_code($response);

        if ($code != 200) {
            error_log('Response '.$code.' from xplorer.mediterraneancoin.org');
            echo json_encode($output);
            exit;
        }

        $data = json_decode(wp_remote_retrieve_body($response));

        set_transient('medaddr-'.$address, $data, $blockchain_cache_time);
    }

    if (!isset($data->address) || $data->address != $address) {
        echo json_encode($output);
        exit;
    }

    if (isset($data->n_tx)) $output['transactions'] = intval($data->n_tx);
    if (isset($data->final_balance)) $output['balance'] = intval($data->final_balance);
    if (isset($data->total_received)) $output['received'] = intval($data->total_received);

    echo json_encode($output);
    exit;
}
add_action('wp_ajax_nopriv_mediterraneancoin-address-info', 'mediterraneancoin_button_get_address_info');
add_action('wp_ajax_mediterraneancoin-address-info', 'mediterraneancoin_button_get_address_info');
