<?php
function erpnext_subscription_created($event)
{
    $id_subscription = $event->get_data()->rec->id;
    $webhook_url = BASE_URL . "subscriptions/$id_subscription";
    $subscription = get_from_webhook_en_api($webhook_url);
    $username = $subscription->member->username;
    $first_name = $subscription->member->first_name;
    $last_name = $subscription->member->last_name;
    $email = $subscription->member->email;
    $membership_id = '' . $subscription->membership->id;
    $membership_title = '' . $subscription->membership->title;
    $membership_period = intval($subscription->membership->expire_after);
    $membership_period_type = ucfirst(rtrim($subscription->membership->period_type, " s"));
    $total = round((float) $subscription->total, 2, PHP_ROUND_HALF_EVEN);
    $created_at = date("Y-m-d",strtotime($subscription->created_at)+7200);
	$expires_at = '';
    $payment_gateway = $subscription->gateway;
    if ($payment_gateway == PAYMENT_BANKDRAFT) $method = 'Bank Draft';
    else if ($payment_gateway == PAYMENT_CASH) $method = 'Cash';
    else if ($payment_gateway == PAYMENT_STRIPE) $method = 'Stripe';
    else if ($payment_gateway == PAYMENT_PAYPAL) $method = 'PayPal';
    else if ($payment_gateway == PAYMENT_FREE) $method = 'Free';
    else if ($payment_gateway == PAYMENT_MANUAL) $method = 'Manual';
    $trial_amount = round((float) $subscription->trial_amount, 2, PHP_ROUND_HALF_EVEN);
    if ($trial_amount > 0) $discount = round((float) $total - $trial_amount, 2, PHP_ROUND_HALF_EVEN);
    else $discount = 0;
	if($subscription->coupon !== "0") $coupon = $subscription->coupon->coupon_code;
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
    $subscription = create_subscription(
        $api,
        $id_subscription,
        $username,
        $created_at,
        $membership_title
    );
    $subscription_name = $subscription['data']['name'];
    $subscription['data']['status'] = "Unpaid";
    $subscription['data']['current_invoice_start'] = $created_at;
    $subscription['data']['current_invoice_end'] = $expires_at;
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
    curl_close($api);
}