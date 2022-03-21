<?php

/**
 * FACEBOOK API
 */

require get_stylesheet_directory() . '/vendor/autoload.php';

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

define('FB_TOKEN','EAATuyV8ZAilUBAEwZAUJ3pK2qSJVmyaTq85MLwB1krWhHVnZCleevTxJrKng9yQl6m7XMY9ivpVXHnrvuvgmYXZAAKzDbH1luIZBPPX8BKOft6moIGpTPu8pY5YuDxx9zzlXX8GXbDkn7u1PZCzYWK0PUcOfZAIAPtqaiK5KVqkzb7FUo8r6usQ');
define('FB_PIXEL_ID','586115422638324');
define('MEMBERPRESS_API_KEY','qeCOHg9wSK');
define('BASE_URL',"https://".$_SERVER['SERVER_NAME']."/wp-json/mp/v1/");
define('TEST_EVENT_CODE','TEST84309');

function get_from_webhook($webhook_url){
	$ch = curl_init($webhook_url);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
	$header = array();
	$header[] = 'MEMBERPRESS-API-KEY: '.MEMBERPRESS_API_KEY;
	$header[] = 'Content-Type: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	$response = json_decode(curl_exec($ch));
	if(curl_errno($ch)){
	  throw new Exception(curl_error($ch));
	}
	curl_close($ch);
	return $response;
}

function init_fb_api(){
	$fb_api = Api::init(null, null, FB_TOKEN);
	$fb_api->setLogger(new CurlLogger());
	return $fb_api;
}

function fb_purchase($event)
{
	$id_transaction = $event->get_data()->rec->id;
	$webhook_url = BASE_URL."transactions/$id_transaction";
	$response = get_from_webhook($webhook_url);
	if(isset($_COOKIE['_fbp'])) $fbp = $_COOKIE['_fbp'];
	if(isset($_COOKIE['_fbc'])) $fbc = $_COOKIE['_fbc'];
	
	init_fb_api();
	$user_data = (new UserData())
	->setEmail($response->member->email)	
	->setClientIpAddress($_SERVER['REMOTE_ADDR'])
	->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);
	if(isset($_COOKIE['_fbp'])) $user_data->setFbp($fbp);
	if(isset($_COOKIE['_fbc'])) $user_data->setFbc($fbc);
	
	$content = (new Content())
	->setProductId($id_transaction)
	->setTitle($response->membership->title)
	->setQuantity(1);
	
	$custom_data = (new CustomData())
	->setContents(array($content))
	->setCurrency('EUR')
	->setValue($response->total);
	
	if($response->gateway=='qt1g6k-284') $method = 'Offline payment';
	else if($response->gateway=='r3uix2-e7') $method = 'Stripe';
	else if($response->gateway=='qs2fvu-462') $method = 'Paypal Standard';
	else $method = 'Other';
	$custom_data->addCustomProperty('payment_method',$method);
	
	if($response->membership->period_type=='years') $period = '1 year';
	else if($response->membership->period=='3') $period = '3 months';
	else if($response->membership->period=='6') $period = '6 months';
	else $period = 'other';
	$custom_data->addCustomProperty('period',$period);
	
	if($response->prorated=='1') $up_down = 'Upgrade or downgrade';
	else $up_down = '';
	$custom_data->addCustomProperty('up_down',$up_down);
	
	if($response->subscription=='0') $is_gift = "Gift";
	else $is_gift = "Subscription";
	$custom_data->addCustomProperty('gift',$is_gift);
	
	$event = (new Event())
	->setEventName('Purchase')
	->setEventTime(time())
	->setUserData($user_data)
	->setCustomData($custom_data);
	
	$events = array();
	array_push($events, $event);

	(new EventRequest(FB_PIXEL_ID))
	->setEvents($events)
	->setTestEventCode(TEST_EVENT_CODE)
	->execute();
}

function fb_signup($event)
{
	$id = $event->get_data()->rec->ID;
	$webhook_url = BASE_URL."members/$id";
	$response = get_from_webhook($webhook_url);
	if(isset($_COOKIE['_fbp'])) $fbp = $_COOKIE['_fbp'];
	if(isset($_COOKIE['_fbc'])) $fbc = $_COOKIE['_fbc'];
	
	init_fb_api();
	$user_data = (new UserData())
	->setEmail($response->email)	
	->setClientIpAddress($_SERVER['REMOTE_ADDR'])
	->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);
	if(isset($_COOKIE['_fbp'])) $user_data->setFbp($fbp);
	if(isset($_COOKIE['_fbc'])) $user_data->setFbc($fbc);
	
	$event = (new Event())
	->setEventName('CompleteRegistration')
	->setEventTime(time())
	->setUserData($user_data);
	
	$events = array();
	array_push($events, $event);

	(new EventRequest(FB_PIXEL_ID))
	->setEvents($events)	
	->setTestEventCode(TEST_EVENT_CODE)
	->execute();
}

add_action('mepr-event-transaction-completed', 'fb_purchase');
add_action('mepr-event-member-signup-completed', 'fb_signup');

/**
 * END API FACEBOOK
 */
