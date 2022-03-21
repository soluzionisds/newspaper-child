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

define('FB_TOKEN','EAATuyV8ZAilUBACZCu26ExJheeoauZB31uUknqZArBB34DphaXZBaSYZAYIECadyKMkHd4AlDZBAxWWgSFU47hOcGIJeA1L2YO5Tf5mx8CRGA4qzEhWAu8VLXOH69cZADuWhIZApih7xD15IAEtLZBubkdl8mfuL4u1EnMnIW5g4y1F4uxE9tQXdvn');
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
	->setProductId($response->subscription->subscr_id)
	->setTitle($response->membership->title)
	->setQuantity(1);
	
	if($response->gateway=='qt1g6k-284') $method = 'offline';
	else $method = 'other';
	if($response->subscription->period_type=='years') $period = '1 year';
	else $period = 'other';
	
	$custom_data = (new CustomData())
	->setContents(array($content))
	->setCurrency('EUR')
	->setValue($response->total);
	$custom_data->addCustomProperty('payment_method',$method);
	$custom_data->addCustomProperty('period',$period);
	
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
