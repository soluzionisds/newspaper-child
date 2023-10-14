<?php

/**
 * Commissions
**/
function paypal_deduction($total){
	return $total * 0.034 + 0.35;
}
function stripe_deduction($total){
	return $total * 0.015 + 0.25;
}
function environmental_deduction_stripe($total){
	return $total * 0.005;
}

/**
 * Create
**/
function create_payment(
    $api,
    $id_transaction,
    $created_at,
    $method,
    $username,
    $total,
    $invoice_name
) {
	$paid = $total;
	$paypal_deduction = 0;
	$stripe_deduction = 0;
	$environmental_deduction_stripe = 0;
	switch($method){
		case "Stripe":
			$stripe_deduction = stripe_deduction($total);
			$environmental_deduction_stripe = environmental_deduction_stripe($total);
			$paid -= ($stripe_deduction + $environmental_deduction_stripe);
			break;
		case "PayPal":
			$paypal_deduction = paypal_deduction($total);
			$paid -= $paypal_deduction;
			break;
	}
    $data = array();
    $data['data']['docstatus'] = 1;
    $data['data']['naming_series'] = 'ACC-PAY-.YYYY.-';
    $data['data']['mepr_name'] = 'pe-'.$id_transaction;
    $data['data']['payment_type'] = "Receive";
    $data['data']['payment_order_status'] = "Initiated";
    $data['data']['posting_date'] = $created_at;
    $data['data']['company'] = "L'Indipendente S.r.l.";
    $data['data']['mode_of_payment'] = $method;
    $data['data']['party_type'] = 'Customer';
    $data['data']['party'] = $username;
    $data['data']['party_name'] = $username;
    $data['data']['status'] = 'Submitted';
    $data['data']['paid_amount'] = $paid;
    $data['data']['paid_amount_after_tax'] = $paid;
    $data['data']['base_paid_amount'] = $paid;
    $data['data']['base_paid_amount_after_tax'] = $paid;
    $data['data']['received_amount'] = $paid;
    $data['data']['received_amount_after_tax'] = $paid;
    $data['data']['paid_from'] = "Debtors - LI";
    $data['data']['paid_from_account_type'] = "Receivable";
    $data['data']['paid_from_account_currency'] = 'EUR';
    $data['data']['paid_to'] = 'Cash - LI';
    $data['data']['paid_to_account_type'] = 'Cash';
    $data['data']['paid_to_account_currency'] = 'EUR';
    $data['data']['base_received_amount'] = $paid;
    $data['data']['base_received_amount_after_tax'] = $paid;
    $data['data']['total_allocated_amount'] = $total;
    $data['data']['base_total_allocated_amount'] = $total;
	if($method=="Stripe"){
		$data['data']['deductions'] = array();
		$data['data']['deductions'][0]['doctype'] = 'Payment Entry Deduction';
		$data['data']['deductions'][0]['docstatus'] = 1;
		$data['data']['deductions'][0]['account'] = 'Stripe Fee - LI';
		$data['data']['deductions'][0]['cost_center'] = 'Stripe Fee - LI';
		$data['data']['deductions'][0]['amount'] = $stripe_deduction;
		$data['data']['deductions'][1]['doctype'] = 'Payment Entry Deduction';
		$data['data']['deductions'][1]['docstatus'] = 1;
		$data['data']['deductions'][1]['account'] = 'Stripe Climate Fee - LI';
		$data['data']['deductions'][1]['cost_center'] = 'Stripe Climate Fee - LI';
		$data['data']['deductions'][1]['amount'] = $environmental_deduction_stripe;
	}
	elseif($method=="PayPal"){
		$data['data']['deductions'] = array();
		$data['data']['deductions'][0]['doctype'] = 'Payment Entry Deduction';
		$data['data']['deductions'][0]['docstatus'] = 1;
		$data['data']['deductions'][0]['account'] = 'PayPal Fee - LI';
		$data['data']['deductions'][0]['cost_center'] = 'PayPal Fee - LI';
		$data['data']['deductions'][0]['amount'] = $paypal_deduction;
	}
    $data['data']['references'] = array();
    $data['data']['references'][0]['doctype'] = 'Payment Entry Reference';
    $data['data']['references'][0]['docstatus'] = 1;
    $data['data']['references'][0]['total_amount'] = $total;
    $data['data']['references'][0]['outstanding_amount'] = 0;
    $data['data']['references'][0]['allocated_amount'] = $total;
    $data['data']['references'][0]['parentfield'] = 'references';
    $data['data']['references'][0]['parenttype'] = 'Payment Entry';
    $data['data']['references'][0]['reference_doctype'] = 'Sales Invoice';
    $data['data']['references'][0]['reference_name'] = $invoice_name;
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Payment%20Entry', 'POST', 'json', $data);
}
