<?php

/**
 * ERPNEXT API
 */
 
require_once get_stylesheet_directory() . '/api/config-erpnext.php';

function get_from_webhook($webhook_url){
	$ch = curl_init($webhook_url);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
	$header = array();
	$header[] = 'MEMBERPRESS-API-KEY: '.MEMBERPRESS_API_KEY;
	$header[] = 'Content-Type: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	$response = json_decode(curl_exec($ch));
	curl_close($ch);
	return $response;
}

function init_erpnext_api(){
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_COOKIEFILE => 'cookies',
	  CURLOPT_COOKIEJAR => 'cookies',
	  CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json'
	  )
	));
	execute_call_erpnext($curl, ROOT_URL.'/api/method/login?usr='.EMAIL.'&pwd='.PASSWORD, 'POST', null);
	return $curl;
}

function execute_call_erpnext($curl, $url, $type, $data){
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
	if(!is_null($data)){
	    $json_data = json_encode($data);
		error_log('['.date("F j, Y, g:i a e O").']'.$json_data."\n",LOG_TYPE,__DIR__.'/debug-erpnext.log');
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data );
	}
	$result = curl_exec($curl);
	error_log('['.date("F j, Y, g:i a e O").']'.curl_getinfo($curl, CURLINFO_RESPONSE_CODE).' '.print_r($result,true)."\n",LOG_TYPE,__DIR__.'/debug-erpnext.log');
	error_log($url."\n",LOG_TYPE,__DIR__.'/debug-erpnext.log');
	error_log(print_r(curl_errno($curl)."\n",true),LOG_TYPE,__DIR__.'/debug-erpnext.log');
	return $result;
}

function erpnext_purchase($event)
{
	date_default_timezone_set('Europe/Rome');
	$id_transaction = $event->get_data()->rec->id;
	$webhook_url = BASE_URL."transactions/$id_transaction";
	$response = get_from_webhook($webhook_url);
	
	//init API
	$api = init_erpnext_api();
	
	//check customer
	$customer = execute_call_erpnext($api, ROOT_URL.'/api/resource/Customer/'.$response->member->username, 'GET',null);
	if(curl_getinfo($api, CURLINFO_RESPONSE_CODE)==404){
		$data = array();
		$data['data']['customer_name'] = $response->member->username;
		$data['data']['first_name'] = $response->member->first_name;
		$data['data']['last_name'] = $response->member->last_name;
		$data['data']['customer_type'] = 'Individual';
		$data['data']['customer_group'] = 'Individual';
		$data['data']['territory'] = 'Italia';
		$data['data']['recipient_code'] = '0000000';
		$data['data']['so_required'] = 0;
		$data['data']['dn_required'] = 0;
		$data['data']['disabled'] = 0;
		$data['data']['is_internal_customer'] = 0;
		$data['data']['is_public_administration'] = 0;
		$data['data']['exempt_from_sales_tax'] = 0;
		$data['data']['language'] = 'it';
		$data['data']['is_frozen'] = 0;
		$data['data']['default_commission_rate'] = 0.0;
		$data['data']['doctype'] = 'Customer';
		$data['data']['companies'] = [];
		$data['data']['accounts'] = [];
		$data['data']['credit_limits'] = [];
		$data['data']['sales_team'] = [];
		execute_call_erpnext($api, ROOT_URL.'/api/resource/Customer', 'POST',$data);
		
		
		$data = array();
		$data['data']['address_title'] = $response->member->username;
		$data['data']['address_line1'] = $response->member->address->mepr-address-one;
		$data['data']['city'] = $response->member->address->mepr-address-city;
		$data['data']['state'] = $response->member->address->mepr-address-state;
		$data['data']['country'] = 'Italy';
		$data['data']['country_code'] = 'it';
		$data['data']['is_primary_address'] = 0;
		$data['data']['is_shipping_address'] = 0;
		$data['data']['disabled'] = 0;
		$data['data']['is_your_company_address'] = 0;
		$data['data']['doctype'] = 'Address';
		$data['data']['links'] = array();
		$data['data']['links'][0]['link_doctype'] = 'Customer';
		$data['data']['links'][0]['link_name'] = $response->member->username;
		$data['data']['links'][0]['link_title'] = $response->member->username;
		$data['data']['links'][0]['doctype'] = 'Dynamic Link';
		$data['data']['address_type'] = 'Billing';
		execute_call_erpnext($api, ROOT_URL.'/api/resource/Address', 'POST',$data);
	    $data['data']['address_type'] = 'Shipping';
		execute_call_erpnext($api, ROOT_URL.'/api/resource/Address', 'POST',$data);
		
		
		$data = array();
		$data['data']['name'] = $response->member->username;
		$data['data']['first_name'] = $response->member->username;
		$data['data']['email_id'] = $response->member->email;
		$data['data']['sync_with_google_contacts'] = 0;
		$data['data']['status'] = 'Passive';
		$data['data']['is_primary_contact'] = 1;
		$data['data']['is_billing_contact'] = 1;
		$data['data']['email_ids'] = array();
		$data['data']['email_ids'][0]['parent'] = $response->member->username;
		$data['data']['email_ids'][0]['parentfield'] = 'email_ids';
		$data['data']['email_ids'][0]['parenttype'] = 'Contact';
		$data['data']['email_ids'][0]['email_id'] = $response->member->email;
		$data['data']['email_ids'][0]['is_primary'] = 1;
		$data['data']['email_ids'][0]['doctype'] = 'Contact Email';
		$data['data']['links'] = array();
		$data['data']['links'][0]['parent'] = $response->member->username;
		$data['data']['links'][0]['parentfield'] = 'links';
		$data['data']['links'][0]['parenttype'] = 'Contact';
		$data['data']['links'][0]['link_doctype'] = 'Customer';
		$data['data']['links'][0]['link_name'] = $response->member->username;
		$data['data']['links'][0]['link_title'] = $response->member->username;
		$data['data']['links'][0]['doctype'] = 'Dynamic Link';
		execute_call_erpnext($api, ROOT_URL.'/api/resource/Contact', 'POST',$data);
	}
	
	//check product
	$product = execute_call_erpnext($api, ROOT_URL.'/api/resource/Item/'.$response->membership->id, 'GET',null);
	if(curl_getinfo($api, CURLINFO_RESPONSE_CODE)==404){
		$data = array();
		$data['data']['item_code'] = ''.$response->membership->id;
		$data['data']['stock_uom'] = 'Nos';
		$data['data']['item_group'] = 'Abbonamenti';
		$data['data']['item_name'] = $response->membership->title;		
		execute_call_erpnext($api, ROOT_URL.'/api/resource/Item', 'POST',$data);
		
		$data = array();
		$data['data']['item_code'] = ''.$response->membership->id;
		$data['data']['stock_uom'] = 'Nos';
		$data['data']['price_list'] = 'Standard Selling';
		$data['data']['item_name'] = $response->membership->title;
		$data['data']['selling'] = 1;
		$data['data']['price_list_rate'] = (float) $response->total;
		
		execute_call_erpnext($api, ROOT_URL.'/api/resource/Item%20Price', 'POST',$data);
	}
	
	$data = array();
	
	//create sale order
	$data['data']['docstatus'] = 1;
	$data['data']['status'] = 'Completed';
	$data['data']['title'] = "{customer_name}";
	$data['data']['naming_series'] = "SAL-ORD-.YYYY.-";
	$data['data']['customer'] = $response->member->username;
	$data['data']['customer_name'] = $response->member->first_name.' '.$response->member->last_name;
	$data['data']['order_type'] = "Sales";
	$data['data']['skip_delivery_note'] = 1;
	$data['data']['company'] = "L'Indipendente";
	$data['data']['selling_price_list'] = 'Standard Selling';	
	$data['data']['contact_display'] = $data['data']['customer'];
	$data['data']['territory'] = "Italia";
	$data['data']['currency'] = "EUR";
	$data['data']['delivery_date'] = date("Y-m-d");
	$data['data']['price_list_currency'] = "EUR";
	$data['data']['ignore_pricing_rule'] = 1;
	$data['data']['total_qty'] = 1;
	$data['data']['base_total'] = (float) $response->total;
	$data['data']['base_net_total'] = (float) $response->total;
	$data['data']['delivery_status'] = 'Fully Delivered';
	$data['data']['billing_status'] = 'Fully Billed';
	$data['data']['items'] = array();
	$data['data']['items'][0]['item_code'] = $response->membership->id;
	$data['data']['items'][0]['docstatus'] = 1;
	$data['data']['items'][0]['qty'] = 1;
	$data['data']['items'][0]['stock_uom'] = 'Nos';
	$data['data']['items'][0]['base_rate'] = (float) $response->total;
	$data['data']['items'][0]['base_amount'] = (float) $response->total;
	$data['data']['items'][0]['delivery_date'] = date("Y-m-d");
	$data['data']['items'][0]['billed_amt'] = (float) $response->total;
	
	$sale_order = execute_call_erpnext($api, ROOT_URL.'/api/resource/Sales%20Order', 'POST', $data);
	
	$data = array();
	
	//create payment
	$data['data']['docstatus'] = 1;
	$data['data']['naming_series'] = 'ACC-PAY-.YYYY.-';
	$data['data']['payment_type'] = "Receive";
	$data['data']['payment_order_status'] = "Initiated";
	$data['data']['posting_date'] = date("Y-m-d");
	$data['data']['company'] = "L'Indipendente";
	
	if($response->gateway==PAYMENT_OFFLINE) $method = 'Wire Transfer';
	else if($response->gateway==PAYMENT_STRIPE) $method = 'Stripe';
	else if($response->gateway==PAYMENT_PAYPAL) $method = 'PayPal';
	else $method = 'Other';
	
	$data['data']['mode_of_payment'] = $method;
	$data['data']['party_type'] = 'Customer';
	$data['data']['party'] = $response->member->username;
	$data['data']['party_name'] = $response->member->username;	
	$data['data']['status'] = 'Submitted';
	$data['data']['paid_amount'] = (float) $response->total;
	$data['data']['paid_amount_after_tax'] = (float) $response->total;
	$data['data']['base_paid_amount'] = (float) $response->total;
	$data['data']['base_paid_amount_after_tax'] = (float) $response->total;
	$data['data']['received_amount'] = (float) $response->total;
	$data['data']['received_amount_after_tax'] = (float) $response->total;
	$data['data']['paid_from_account_currency'] = 'EUR';
	$data['data']['paid_to'] = 'XYZ - B';
	$data['data']['paid_to_account_type'] = 'Bank';
	$data['data']['paid_to_account_currency'] = 'EUR';
	$data['data']['base_received_amount'] = (float) $response->total;
	$data['data']['base_received_amount_after_tax'] = (float) $response->total;
	$data['data']['total_allocated_amount'] = (float) $response->total;
	$data['data']['base_total_allocated_amount'] = (float) $response->total;
	$data['data']['reference_no'] = (int) $response->id;
	$data['data']['reference_date'] = date("Y-m-d");
    $data['data']['references'] = array();
	$data['data']['references'][0]['doctype'] = 'Payment Entry Reference';
	$data['data']['references'][0]['docstatus'] = 1;
	$data['data']['references'][0]['total_amount'] = (float) $response->total;
	$data['data']['references'][0]['outstanding_amount'] = (float) $response->total;
	$data['data']['references'][0]['outstanding_amount'] = (float) $response->total;
	$data['data']['references'][0]['allocated_amount'] = (float) $response->total;
	$data['data']['references'][0]['parentfield'] = 'references';
	$data['data']['references'][0]['parenttype'] = 'Payment Entry';
	$data['data']['references'][0]['reference_doctype'] = 'Sales Order';
	$data['data']['references'][0]['reference_name'] = json_decode($sale_order)->data->name;
	
	execute_call_erpnext($api, ROOT_URL.'/api/resource/Payment%20Entry', 'POST', $data);

	/*if($response->gateway==PAYMENT_OFFLINE) $method = 'Offline payment';
	else if($response->gateway==PAYMENT_STRIPE) $method = 'Stripe';
	else if($response->gateway==PAYMENT_PAYPAL) $method = 'Paypal Standard';
	else $method = 'Other';
	$data['data'][0]['custom_data']['payment_method'] = $method;
	
	if(isset($response->membership->period_type) && isset($response->membership->period)) $data['data'][0]['custom_data']['period'] = $response->membership->period . ' ' . $response->membership->period_type;
	else $data['data'][0]['custom_data']['period'] = 'other';
	
	if($response->prorated=='1') $data['data'][0]['custom_data']['upgrade'] = 'Upgrade or downgrade';
	
	if($response->subscription=='0') $data['data'][0]['custom_data']['content_category'] = 'gift';
	else $data['data'][0]['custom_data']['content_category'] = 'subscription';*/
	
	curl_close($api);
}

function erpnext_signup($event)
{
	$id = $event->get_data()->rec->ID;
	$webhook_url = BASE_URL."members/$id";
	$response = get_from_webhook($webhook_url);
	
	/*$api = init_erpnext_api();
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
	
	execute_call($api, $data);*/
}

add_action('mepr-event-transaction-completed', 'erpnext_purchase');
//add_action('mepr-event-member-signup-completed', 'erpnext_signup');

/**
 * END API ERPNEXT
 */
