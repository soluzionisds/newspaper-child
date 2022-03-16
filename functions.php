<?php

/*if (!function_exists('suffice_child_enqueue_child_styles')) {
	function Newspaper_child_enqueue_child_styles()
	{
		// loading parent style
		wp_register_style(
			'parente2-style',
			get_template_directory_uri() . '/style.css'
		);
		wp_enqueue_style('parente2-style');
		// loading child style
		wp_register_style(
			'childe2-style',
			get_stylesheet_directory_uri() . '/style.css',
			array(),
			'1.0.15',
			'all'
		);
		wp_enqueue_style('childe2-style');

		wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/scripts.js', array('jquery'));
	}
}
add_action('wp_enqueue_scripts', 'Newspaper_child_enqueue_child_styles');*/

/**
 * Enqueues the parent stylesheet. Do not remove this function.
 *
 */
add_action( 'wp_enqueue_scripts', 'newspaper_child_enqueue' );

function newspaper_child_enqueue() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/scripts.js', array( 'jquery' ), '1.0.1');
}

/*Scrivi qui le tue funzioni */

previous_post_link('<span class="previous-post-link">%link</span>', apply_filters('wpbf_previous_post_link', __('&larr; Previous Post', 'page-builder-framework')));
next_post_link('<span class="next-post-link">%link</span>', apply_filters('wpbf_next_post_link', __('Next Post &rarr;', 'page-builder-framework')));

/***************************
 * MemberPress

Send transaction "failed" mail also if change status by backoffice
 */
function mepr_custom_failed_status_email($txn)
{
	\MeprUtils::send_failed_txn_notices($txn);
}
add_action('mepr-txn-status-failed', 'mepr_custom_failed_status_email');
//Used to check if a signup limit has been reached for a particular membership
function has_reached_limit($membership_id, $limit) {
  global $wpdb;

  $query = "SELECT count(DISTINCT user_id)
            FROM {$wpdb->prefix}mepr_transactions
            WHERE status IN('complete', 'confirmed')
              AND (
                expires_at IS NULL
                OR expires_at = '0000-00-00 00:00:00'
                OR expires_at >= NOW()
              )
              AND product_id = {$membership_id}";

  $count = $wpdb->get_var($query);

  return ($count >= $limit);
}
//Limit membership premium
function limit_signups_for_membership_premium($errors) {
  //CHANGE THE FOLLOWING TWO VARS
  $membership_id = 13565; //The Product you want to limits' ID
  $limit = 1; //Number of signups allowed

  if($_POST['mepr_product_id'] != $membership_id) { return $errors; }

  if(has_reached_limit($membership_id, $limit)) {
    $errors[] = __('Sorry, our signup limit of ' . $limit . ' members has been reached. No further signups are allowed.', 'memberpress');
  }

  return $errors;
}
add_filter('mepr-validate-signup', 'limit_signups_for_membership_premium');

/* Send subscription resumed email
function mepr_capture_resumed_sub($event) {
  \MeprUtils::send_resumed_sub_notices($event);
}
add_action('mepr-event-subscription-resumed', 'mepr_capture_resumed_sub');*/

/***************************
 * Login logo
 ****************************/

function lindipendente_login_logo()
{ ?>
	<style type="text/css">
		#login h1 a,
		.login h1 a {
			background-image: url(/wp-content/uploads/2021/03/L..png);
			width: 150px;
			height: 150px;
			background-size: 150px 150px;
			background-repeat: no-repeat;
			padding-bottom: 10px;
		}
	</style>
<?php }
add_action('login_enqueue_scripts', 'lindipendente_login_logo');

function lindipendente_login_logo_url()
{
	return home_url();
}
add_filter('login_headerurl', 'lindipendente_login_logo_url');

function lindipendente_login_logo_url_title()
{
	return 'L\'INDIPENDENTE';
}
add_filter('login_headertext', 'lindipendente_login_logo_url_title');

/***************************
 * bbPress
 * remove bbPress breadcrumb
function bm_bbp_no_breadcrumb ($param) {
	return true;
}
add_filter ('bbp_no_breadcrumb', 'bm_bbp_no_breadcrumb');

 * temporary userpage redirection bbPress
function user_profile_link(){
    $author_id = bbp_get_reply_author_id();
    $user_info = get_userdata($author_id);
    return site_url()."/";
}
add_filter('bbp_get_user_profile_url', 'user_profile_link');*/

/**
 * FACEBOOK API
 */

require ABSPATH . '/vendor/autoload.php';

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

define('FB_TOKEN','EAATuyV8ZAilUBACp6F7B68HtEypnIBKcqIpZB8Nxq37iWZAAphTAc19EZAwEJTZBEs3UmVzlHJZACXA5GvKSZBqmoCvY3iIwpGZC4LM8roxu6DlFGBY9IiBadWN9NRtFXJV2Bl1X29opZC4GOQ4xbJ3ZBcFMh7GIqfZBmOPljf1ZA64jL4hzNPkp5IrX');
define('FB_PIXEL_ID','586115422638324');
define('MEMBERPRESS_API_KEY','qeCOHg9wSK');
define('BASE_URL',"https://".$_SERVER['SERVER_NAME']."/wp-json/mp/v1/");
define('TEST_EVENT_CODE','TEST95265');

function fb_purchase($event)
{
	$id_transaction = $event->get_data()->rec->id;
	$webhook_url = BASE_URL."transactions/$id_transaction";
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
	$fb_api = Api::init(null, null, FB_TOKEN);
	$fb_api->setLogger(new CurlLogger());
	$user_data = (new UserData())
	->setEmail($response->member->email);
	/*->setPhones(array('12345678901', '14251234567'))
	// It is recommended to send Client IP and User Agent for Conversions API Events.
	->setClientIpAddress($_SERVER['REMOTE_ADDR'])
	->setClientUserAgent($_SERVER['HTTP_USER_AGENT'])
	->setFbc('fb.1.1554763741205.AbCdEfGhIjKlMnOpQrStUvWxYz1234567890')
	->setFbp('fb.1.1558571054389.1098115397');*/
	
	$content = (new Content())
	->setProductId($response->membership->title)
	->setQuantity(1);
	
	$custom_data = (new CustomData())
	->setContents(array($content))
	->setCurrency('eur')
	->setValue($response->total);
	
	$event = (new Event())
	->setEventName('Purchase')
	->setEventTime(time())
	->setUserData($user_data)
	->setCustomData($custom_data);
	
	$events = array();
	array_push($events, $event);

	(new EventRequest(FB_PIXEL_ID))
	->setEvents($events)
	->setTestEventCode(TEST_EVENT_CODE);
}

add_action('mepr-event-transaction-completed', 'fb_purchase');
//add_action('mepr-event-subscription-created', 'fb_purchase');

/**
 * END API FACEBOOK
 */
