<?php

/**
 * FACEBOOK API
 */
 
require_once get_stylesheet_directory() . '/api/config-facebook.php';

function get_from_webhook_fb_api($webhook_url){
	$ch = curl_init($webhook_url);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
	$header = array();
	$header[] = 'MEMBERPRESS-API-KEY: '.FB_MEMBERPRESS_API_KEY;
	$header[] = 'Content-Type: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	$response = json_decode(curl_exec($ch));
	curl_close($ch);
	return $response;
}

function init_fb_api(){
	$curl = curl_init('https://graph.facebook.com/'.FB_API_VERSION.'/'.FB_PIXEL_ID.'/events?access_token='.FB_TOKEN);
	curl_setopt_array($curl, array(
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json'
	  ),
	));
	return $curl;
}

function init_fb_data(){
	$data = array();
	if(defined('TEST_EVENT_CODE')) $data['test_event_code'] = TEST_EVENT_CODE;
	$data['data'] = array();
    $data['data'][0] = array();
	return $data;
}

function execute_call($curl, $data){
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data) );
	error_log(print_r(curl_exec($curl),true),FB_LOG_TYPE,__DIR__.'/debug.log');
	curl_close($curl);
}

function fb_purchase($event)
{
	$id_transaction = $event->get_data()->rec->id;
	$webhook_url = FB_BASE_URL."transactions/$id_transaction";
	$response = get_from_webhook_fb_api($webhook_url);
	if(isset($_COOKIE['_fbp'])) $fbp = $_COOKIE['_fbp'];
	if(isset($_COOKIE['_fbc'])) $fbc = $_COOKIE['_fbc'];
	
	$api = init_fb_api();
	$data = init_fb_data();
	$data['data'][0]['event_id'] = "$id_transaction";
	$data['data'][0]['event_name'] = 'Purchase';
	$data['data'][0]['event_time'] = time();
	$data['data'][0]['action_source'] = 'website';
	$data['data'][0]['opt_out'] = true;
	
	$data['data'][0]['user_data'] = array();
	$data['data'][0]['user_data']['em'] = hash('sha256',$response->member->email);
	$data['data'][0]['user_data']['client_ip_address'] = $_SERVER['REMOTE_ADDR'];
	$data['data'][0]['user_data']['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	if(isset($fbp)) $data['data'][0]['user_data']['fbp'] = $fbp;
	if(isset($fbc)) $data['data'][0]['user_data']['fbc'] = $fbc;
	
	$data['data'][0]['custom_data'] = array();
	$data['data'][0]['custom_data']['content_name'] = $response->membership->title;
	$data['data'][0]['custom_data']['content_type'] = 'product';
	$data['data'][0]['custom_data']['order_id'] = "$id_transaction";
	$data['data'][0]['custom_data']['num_items'] = '1';
	$data['data'][0]['custom_data']['currency'] = 'EUR';
	$data['data'][0]['custom_data']['value'] = $response->total;
	
	if($response->gateway==FB_PAYMENT_OFFLINE) $method = 'Offline payment';
	else if($response->gateway==FB_PAYMENT_STRIPE) $method = 'Stripe';
	else if($response->gateway==FB_PAYMENT_PAYPAL) $method = 'Paypal Standard';
	else $method = 'Other';
	$data['data'][0]['custom_data']['payment_method'] = $method;
	
	if(isset($response->membership->period_type) && isset($response->membership->period)) $data['data'][0]['custom_data']['period'] = $response->membership->period . ' ' . $response->membership->period_type;
	else $data['data'][0]['custom_data']['period'] = 'other';
	
	if($response->prorated=='1') $data['data'][0]['custom_data']['upgrade'] = 'Upgrade or downgrade';
	
	if($response->subscription=='0') $data['data'][0]['custom_data']['content_category'] = 'gift';
	else $data['data'][0]['custom_data']['content_category'] = 'subscription';
	
	execute_call($api, $data);
}

function fb_signup($event)
{
	$id = $event->get_data()->rec->ID;
	$webhook_url = FB_BASE_URL."members/$id";
	$response = get_from_webhook_fb_api($webhook_url);
	if(isset($_COOKIE['_fbp'])) $fbp = $_COOKIE['_fbp'];
	if(isset($_COOKIE['_fbc'])) $fbc = $_COOKIE['_fbc'];
	
	$api = init_fb_api();
	$data = init_fb_data();
	$data['data'][0]['event_id'] = "$id";
	$data['data'][0]['event_name'] = 'CompleteRegistration';
	$data['data'][0]['event_time'] = time();
	$data['data'][0]['action_source'] = 'website';
	$data['data'][0]['opt_out'] = true;
	
	$data['data'][0]['user_data'] = array();
	$data['data'][0]['user_data']['em'] = hash('sha256',$response->email);
	$data['data'][0]['user_data']['client_ip_address'] = $_SERVER['REMOTE_ADDR'];
	$data['data'][0]['user_data']['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	if(isset($fbp)) $data['data'][0]['user_data']['fbp'] = $fbp;
	if(isset($fbc)) $data['data'][0]['user_data']['fbc'] = $fbc;
	
	execute_call($api, $data);
}

add_action('mepr-event-transaction-completed', 'fb_purchase', 9993);
add_action('mepr-event-member-signup-completed', 'fb_signup', 9993);

/**
 * END API FACEBOOK
 */
