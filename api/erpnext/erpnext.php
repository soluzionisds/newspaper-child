<?php
/**
 * ERPNext API
**/

if (strpos($_SERVER['SERVER_NAME'], "www.lindipendente.online") !== false){
    require_once get_stylesheet_directory() . '/api/erpnext/config/config-erpnext--prod.php';
} else {
    require_once get_stylesheet_directory() . '/api/erpnext/config/config-erpnext--staging.php';
}

function get_from_webhook_en_api($webhook_url)
{
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $header = array();
    $header[] = 'MEMBERPRESS-API-KEY: ' . MEMBERPRESS_API_KEY;
    $header[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $event = json_decode(curl_exec($ch));
    curl_close($ch);
    return $event;
}

function init_erpnext_api()
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
    ));
    return $curl;
}

function execute_call_erpnext($curl, $url, $type_request, $type, $data)
{
	$default_tz = date_default_timezone_get();
	date_default_timezone_set('Europe/Rome');
    $header = array();
    $header[] = 'Authorization: Basic ' . base64_encode(ERPNEXT_API_KEY . ':' . ERPNEXT_API_SECRET);
    if (!is_null($type)) $header[] = 'Content-Type: application/' . $type;
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type_request);
    if (!is_null($data)) {
        if ($type == 'json') {
            $json_data = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
        } else curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    $result = curl_exec($curl);
    error_log($type_request . ' ' . $url . "\n", LOG_TYPE, __DIR__ . '/debug-erpnext.log');
	error_log('[' . date("F j, Y, g:i a e O") . '] Input: ' . print_r($json_data, true) . "\n", LOG_TYPE, __DIR__ . '/debug-erpnext.log');
    error_log('[' . date("F j, Y, g:i a e O") . '] ' . curl_getinfo($curl, CURLINFO_RESPONSE_CODE) . ' ' . print_r($result, true) . "\n", LOG_TYPE, __DIR__ . '/debug-erpnext.log');
    error_log(print_r(curl_errno($curl) . "\n\n", true), LOG_TYPE, __DIR__ . '/debug-erpnext.log');
	date_default_timezone_set($default_tz);
    return $result;
}

/**
 * ERPNext DocTypes
**/
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/payment-entry.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/sales-invoice.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/customer.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/address.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/contact.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/item.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/subscription-plan.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/doctypes/subscription.php';

/**
 * MemberPress Actions to ERPNext
**/
require_once get_stylesheet_directory() . '/api/erpnext/core/actions/transaction-completed.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/actions/signup-completed.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/actions/subscription-created.php';
require_once get_stylesheet_directory() . '/api/erpnext/core/actions/subscription-stopped.php';
//require_once get_stylesheet_directory() . '/api/erpnext/core/actions/transaction-refunded.php';
//require_once get_stylesheet_directory() . '/api/erpnext/core/actions/subscription-expired.php';

/**
 * MemberPress Hooks
**/
add_action('mepr-event-transaction-completed', 'erpnext_transaction_completed', 9992);
add_action('mepr-event-member-signup-completed', 'erpnext_signup_completed', 9992);
add_action('mepr-event-subscription-created', 'erpnext_subscription_created', 9992);
add_action('mepr-event-subscription-stopped', 'erpnext_subscription_stopped', 9992);
//add_action('mepr-event-transaction-refunded', 'erpnext_transaction_refunded', 9992);
//add_action('mepr-event-subscription-expired', 'erpnext_subscription_expired', 9992, 2);

/**
 * END API ERPNext
**/