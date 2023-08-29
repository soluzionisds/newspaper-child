<?php

/**
 * Create
**/
function create_subscription(
    $api,
    $subscription_id,
    $username,
    $created_at,
    $membership_title
) {
	$subscription_id = PREFIX_SUB . $subscription_id;
    $data = array();
    $data['data']['doctype'] = "Subscription";
    $data['data']['mepr_name'] = "mp-sub-id-$subscription_id";
    $data['data']['mepr_id'] = "$subscription_id";
    $data['data']['party_type'] = "Customer";
    $data['data']['party'] = $username;
    $data['data']['company'] = "L'Indipendente S.r.l.";
    $data['data']['start_date'] = $created_at;
    $data['data']['follow_calendar_months'] = 0;
    $data['data']['generate_new_invoices_past_due_date'] = 0;
    $data['data']['current_invoice_start'] = $created_at;
    $data['data']['days_until_due'] = 360;
    $data['data']['cancel_at_period_end'] = 0;
    $data['data']['generate_invoice_at_period_start'] = 0;
    $data['data']['sales_tax_template'] = "Italy VAT 4% - LI";
    $data['data']['apply_additional_discount'] = "";
    $data['data']['additional_discount_percentage'] = 0.0;
    $data['data']['additional_discount_amount'] = 0.0;
    $data['data']['submit_invoice'] = 0;
    $data['data']['cost_center'] = 'MemberPress - LI';
    $data['data']['plans'] = array();
    $data['data']['plans'][0] = array();
    $data['data']['plans'][0]['name'] = $membership_title;
    $data['data']['plans'][0]['plan'] = $membership_title;
    $data['data']['plans'][0]['qty'] = 1;
    $data['data']['plans'][0]['doctype'] = "Subscription Plan Detail";
    return json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'POST', 'json', $data), true);
}

/**
 * Cancel
**/
function cancel_subscription(
    $api,
    $subscription_name
) {
    $subscription = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'GET', null, null), true);
    $subscription['data']['status'] = 'Cancelled';
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
}

function upgraded_subscription(
	$api,
    $subscription_name
){
	$subscription = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'GET', null, null), true);
    $subscription['data']['status'] = 'Cancelled';
	$subscription['data']['current_invoice_end'] = date("Y-m-d",time()+7200);
	execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
}