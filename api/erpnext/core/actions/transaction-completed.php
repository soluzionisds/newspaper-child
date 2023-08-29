<?php

function erpnext_transaction_completed($event)
{
    $id_transaction = $event->get_data()->rec->id;
    $webhook_url = BASE_URL . "transactions/$id_transaction";
    $transaction = get_from_webhook_en_api($webhook_url);
	$trans_num = $transaction->trans_num;
    $username = $transaction->member->username;
	$first_name = $transaction->member->first_name;
	$last_name = $transaction->member->last_name;
	$email = $transaction->member->email;
    $membership_id = '' . $transaction->membership->id;
    $membership_title = '' . $transaction->membership->title;
    $total = round((float) $transaction->total, 2, PHP_ROUND_HALF_EVEN);
    $created_at = date("Y-m-d",strtotime($transaction->created_at)+7200);
    $expires_at = date("Y-m-d",strtotime($transaction->expires_at)+7200);
    $payment_gateway = $transaction->gateway;
    if ($payment_gateway == PAYMENT_BANKDRAFT) $method = 'Bank Draft';
    else if ($payment_gateway == PAYMENT_CASH) $method = 'Cash';
    else if ($payment_gateway == PAYMENT_STRIPE) $method = 'Stripe';
    else if ($payment_gateway == PAYMENT_PAYPAL) $method = 'PayPal';
    else if ($payment_gateway == PAYMENT_FREE) $method = 'Free';
	else if ($payment_gateway == PAYMENT_MANUAL) $method = 'Manual';
	$amount = round((float) $transaction->amount, 2, PHP_ROUND_HALF_EVEN);
    if ($amount != $total) $discount = round((float) $total - $amount, 2, PHP_ROUND_HALF_EVEN);
    else $discount = 0;
	if($total==0) {
		$total = round((float) $transaction->membership->price, 2, PHP_ROUND_HALF_EVEN);
		$discount = $total;
	}
	if($transaction->coupon !== "0") $coupon = $transaction->coupon->coupon_code;
	else $coupon = "0";
    //init API
    $api = init_erpnext_api();
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Customer/' . $username, 'GET', null, null);
    if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
        create_customer(
            $api,
            $username,
            $first_name,
            $last_name
        );
        execute_call_erpnext($api, ROOT_URL . '/api/resource/Address/' . $username . '-Billing', 'GET', null, null);
        if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
            create_address(
                $api,
                $username,
                'Billing'
            );
        }
        execute_call_erpnext($api, ROOT_URL . '/api/resource/Address/' . $username . '-Shipping', 'GET', null, null);
        if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
            create_address(
                $api,
                $username,
                'Shipping'
            );
        }
        execute_call_erpnext($api, ROOT_URL . '/api/resource/Contact/' . $first_name .'%20'. $last_name . '-' . $username, 'GET', null, null);
        if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
            create_contact(
                $api,
                $username,
                $email,
                $first_name,
                $last_name
            );
        }
    }
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Item/' . $membership_id, 'GET', null, null);
    if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
        create_item(
            $api,
            $membership_id,
            $membership_title,
            $total
        );
    }
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription%20Plan/' . str_replace(' ', '%20', $membership_title), 'GET', null, null);
    if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
        create_subscription_plan(
            $api,
            $membership_id,
            $membership_title,
            $total,
            $membership_period,
            $membership_period_type
        );
    }	
	if("0"!==$transaction->subscription){
    	$data = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'GET', 'x-www-form-urlencoded', 'filters=[["Subscription","mepr_id","=","' . PREFIX_SUB . $transaction->subscription->id . '"]]'))->data;
		if(empty($data)){
			$mepr_subscription_id = PREFIX_SUB . $transaction->subscription->id;
            $subscription_name = 'mp-sub-id-'.$mepr_subscription_id;
			$subscription = create_subscription(
				$api,
				$mepr_subscription_id,
				$username,
				$created_at,
				$membership_title
			);
			$subscription['data']['status'] = "Active";
			$subscription['data']['current_invoice_start'] = $created_at;
			$subscription['data']['current_invoice_end'] = $expires_at;
			execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
		}
		else $subscription_name = $data[0]->name;
    	$subscription = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'GET', null, null), true);
		$invoice = create_invoice(
			$api,
			$trans_num,
			$created_at,
			$expires_at,
			$username,
			$total,
			$membership_id,
			$membership_title,
			$method,
			$discount,
			$coupon
		);
		$subscription['data']['invoices'][] = array(
			'docstatus' => 0,
			'invoice' => $invoice->data->name,
			'document_type' => 'Sales Invoice',
			'doctype' => 'Subscription Invoice',
			'parentfield' => 'invoices',
			'parent' => $subscription_name,
			'idx' => count($subscription['data']['invoices']) + 1
		);
		$invoice_name = $subscription['data']['invoices'][count($subscription['data']['invoices']) - 1]['invoice'];
		$subscription['data']['current_invoice_start'] = $created_at;
		$subscription['data']['current_invoice_end'] = $expires_at;
		$subscription['data']['status'] = "Active";
		execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
	} else {
		$membership_id = '' . $transaction->membership->id;
		$membership_title = '' . $transaction->membership->title;
		$membership_period = $transaction->membership->expire_after;
		$membership_period_type = ucfirst(rtrim($transaction->membership->expire_unit, " s"));
		execute_call_erpnext($api, ROOT_URL . '/api/resource/Item/' . $membership_id, 'GET', null, null);
		if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
			create_item(
				$api,
				$membership_id,
				$membership_title,
				$total
			);
		}
		$invoice = create_invoice(
			$api,
			$trans_num,
			$created_at,
			$expires_at,
			$username,
			$total,
			$membership_id,
			$membership_title,
			$method,
			$discount,
			$coupon
		);
		$invoice_name = $invoice->data->name;
	}
	if($total != $discount){
		create_payment(
			$api,
			$trans_num,
			$created_at,
			$method,
			$username,
			$total - $discount,
			$invoice_name
		);
	}
	$to_disable_name = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'GET', 'x-www-form-urlencoded', 'filters=%5B%5B%22Subscription%22%2C%22party%22%2C%22%3D%22%2C%22'.$username.'%22%5D%2C%5B%22Subscription%22%2C%22status%22%2C%22%3D%22%2C%22Active%22%5D%5D'));
	if(count($to_disable_name->data) > 1 && isset($to_disable_name->data[0]->name)) $to_disable_name = $to_disable_name->data[0]->name;
	else $to_disable_name = '';
	if($to_disable_name!=''){
		upgraded_subscription(
			$api,
			$to_disable_name
		);
	}
    curl_close($api);
}
