<?php
/**
 * ERPNEXT API
 */
require_once get_stylesheet_directory() . '/api/config-erpnext.php';
function get_from_webhook($webhook_url)
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
    return $result;
}
function paypal_deduction($total){
	return $total * 0.034 + 0.35;
}
function stripe_deduction($total){
	return $total * 0.015 + 0.25;
}
function environmental_deduction_stripe($total){
	return $total * 0.005;
}
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
    $data['data']['mepr_name'] = 'en-pe-'.$id_transaction;
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
		$data['data']['deductions'][0]['cost_center'] = 'MemberPress - LI';
		$data['data']['deductions'][0]['amount'] = $stripe_deduction;
		$data['data']['deductions'][1]['doctype'] = 'Payment Entry Deduction';
		$data['data']['deductions'][1]['docstatus'] = 1;
		$data['data']['deductions'][1]['account'] = 'Stripe Climate Fee - LI';
		$data['data']['deductions'][1]['cost_center'] = 'MemberPress - LI';
		$data['data']['deductions'][1]['amount'] = $environmental_deduction_stripe;
	}
	elseif($method=="PayPal"){
		$data['data']['deductions'] = array();
		$data['data']['deductions'][0]['doctype'] = 'Payment Entry Deduction';
		$data['data']['deductions'][0]['docstatus'] = 1;
		$data['data']['deductions'][0]['account'] = 'PayPal Fee - LI';
		$data['data']['deductions'][0]['cost_center'] = 'MemberPress - LI';
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
function create_invoice(
    $api,
	$id_transaction,
    $created_at,
    $expires_at,
    $username,
    $total,
    $membership_id,
    $membership_title,
    $method,
    $discount,
	$coupon
) {
    $data = array();
    $data['data']['from_date'] = $created_at;
    $data['data']['to_date'] = $expires_at;
    $data['data']['docstatus'] = 1;
    $data['data']['title'] = $username;
    $data['data']['naming_series'] = 'ACC-SINV-.YYYY.-';
    $data['data']['mepr_name'] = 'en-si-'.$id_transaction;
    $data['data']['customer'] = $username;
    $data['data']['company'] = "L'Indipendente S.r.l.";
    $data['data']['company_tax_id'] = 'tax-0';
    $data['data']['is_pos'] = 0;
    $data['data']['is_consolidated'] = 0;
    $data['data']['is_return'] = 0;
    $data['data']['update_billed_amount_in_sales_order'] = 0;
    $data['data']['is_debit_note'] = 0;
    $data['data']['cost_center'] = 'MemberPress - LI';
    $data['data']['currency'] = 'EUR';
    $data['data']['conversion_rate'] = 1.0;
    $data['data']['selling_price_list'] = 'Standard Selling';
    $data['data']['price_list_currency'] = 'EUR';
    $data['data']['plc_conversion_rate'] = 1.0;
    $data['data']['ignore_pricing_rule'] = 0;
    $data['data']['update_stock'] = 0;
    $data['data']['total_qty'] = 1.0;
    $data['data']['total_net_weight'] = 0.0;
    $data['data']['base_total'] = $total;
    $data['data']['total'] = $total;
    $data['data']['taxes_and_charges'] = 'Italy VAT 4% - LI';
    $data['data']['base_grand_total'] = $total;
    $data['data']['base_rounding_adjustment'] = 0.0;
    $data['data']['base_rounded_total'] = $total;
    $data['data']['grand_total'] = $total;
    $data['data']['rounding_adjustment'] = 0.0;
    $data['data']['rounded_total'] = $total;
    $data['data']['base_total_taxes_and_charges'] = 0.0;
    $data['data']['total_advance'] = 0.0;
    $data['data']['outstanding_amount'] = 0.0;
    $data['data']['disable_rounded_total'] = 0;
    $data['data']['apply_discount_on'] = 'Grand Total';
    $data['data']['base_discount_amount'] = 0.0;
    $data['data']['is_cash_or_non_trade_discount'] = 0;
    $data['data']['additional_discount_percentage'] = 0.0;
    $data['data']['discount_amount'] = $discount;
    $data['data']['total_billing_hours'] = 0.0;
    $data['data']['total_billing_amount'] = 0.0;
    $data['data']['base_paid_amount'] = 0.0;
    $data['data']['paid_amount'] = 0.0;
    $data['data']['base_change_amount'] = 0.0;
    $data['data']['change_amount'] = 0.0;
    $data['data']['allocate_advances_automatically'] = 0;
    $data['data']['write_off_amount'] = 0.0;
    $data['data']['base_write_off_amount'] = 0.0;
    $data['data']['write_off_outstanding_amount_automatically'] = 0;
    $data['data']['redeem_loyalty_points'] = 0;
    $data['data']['loyalty_points'] = 0;
    $data['data']['loyalty_amount'] = 0.0;
    $data['data']['company_address'] = "L'Indipendente S.r.l.-Billing";
    $data['data']['ignore_default_payment_terms_template'] = 0;
    $data['data']['payment_terms_template'] = 'Abbonamenti';
    $data['data']['debit_to'] = "Debtors - LI";
    $data['data']['party_account_currency'] = "EUR";
    $data['data']['is_opening'] = 'No';
    $data['data']['against_income_account'] = 'Sales - LI';
    $data['data']['company_fiscal_regime'] = 'RF01-Ordinario';
    $data['data']['commission_rate'] = 0.0;
    $data['data']['total_commission'] = 0.0;
    $data['data']['group_same_items'] = 0;
    $data['data']['language'] = 'en';
    if($total!==$discount)$data['data']['status'] = 'Unpaid';
	else $data['data']['status'] = 'Paid';
    $data['data']['customer_group'] = 'MemberPress';
    $data['data']['is_internal_customer'] = 0;
    if($discount===0)$data['data']['is_discounted'] = 0;
	else $data['data']['is_discounted'] = 1;
    $data['data']['remarks'] = "No Remarks";
    $data['data']['customer_fiscal_code'] = "0";
    $data['data']['group_same_items'] = 0;
    $data['data']['mepr_coupon'] = $coupon;
    $data['data']['items'] = array();
    $data['data']['items'][0] = array();
    $data['data']['items'][0]['docstatus'] = 1;
    $data['data']['items'][0]['item_code'] = $membership_id;
    $data['data']['items'][0]['name'] = $membership_title;
    $data['data']['items'][0]['description'] = $membership_title;
    $data['data']['items'][0]['has_item_scanned'] = 0;
    $data['data']['items'][0]['item_code'] = $membership_id;
    $data['data']['items'][0]['item_name'] = $membership_title;
    $data['data']['items'][0]['description'] = $membership_title;
    $data['data']['items'][0]['tax_rate'] = 4.0;
    $data['data']['items'][0]['total_amount'] = $total;
    $data['data']['items'][0]['item_group'] = 'Abbonamenti';
    $data['data']['items'][0]['qty'] = 1;
    $data['data']['items'][0]['stock_uom'] = 'Nos';
    $data['data']['items'][0]['uom'] = 'Nos';
    $data['data']['items'][0]['conversion_factor'] = 1.0;
    $data['data']['items'][0]['stock_qty'] = 1.0;
    $data['data']['items'][0]['price_list_rate'] = $total;
    $data['data']['items'][0]['base_price_list_rate'] = $total;
    $data['data']['items'][0]['margin_rate_or_amount'] = 0.0;
    $data['data']['items'][0]['rate_with_margin'] = 0.0;
    $data['data']['items'][0]['discount_percentage'] = 0.0;
    $data['data']['items'][0]['discount_amount'] = 0.0;
    $data['data']['items'][0]['rate'] = $total;
    $data['data']['items'][0]['base_rate_with_margin'] = $total;
    $data['data']['items'][0]['amount'] = $total;
    $data['data']['items'][0]['base_rate_with_margin'] = $total;
    $data['data']['items'][0]['base_rate'] = $total;
    $data['data']['items'][0]['base_amount'] = $total;
    $data['data']['items'][0]['stock_uom_rate'] = $total;
    $data['data']['items'][0]['is_free_item'] = 0;
    $data['data']['items'][0]['grant_commission'] = 1;
    $data['data']['items'][0]['delivered_by_supplier'] = 0;
    $data['data']['items'][0]['income_account'] = 'Sales - LI';
    $data['data']['items'][0]['is_fixed_asset'] = 0;
    $data['data']['items'][0]['expense_account'] = 'Cost of Goods Sold - LI';
    $data['data']['items'][0]['enable_deferred_revenue'] = 0;
    $data['data']['items'][0]['weight_per_unit'] = 0.0;
    $data['data']['items'][0]['total_weight'] = 0.0;
    $data['data']['items'][0]['warehouse'] = 'Stores - LI';
    $data['data']['items'][0]['incoming_rate'] = 0;
    $data['data']['items'][0]['allow_zero_valuation_rate'] = 0;
    $data['data']['items'][0]['actual_batch_qty'] = 0.0;
    $data['data']['items'][0]['actual_qty'] = 0.0;
    $data['data']['items'][0]['delivered_qty'] = 0.0;
    $data['data']['items'][0]['cost_center'] = 'MemberPress - LI';
    $data['data']['payment_schedule'] = array();
    $data['data']['payment_schedule'][0] = array();
    $data['data']['payment_schedule'][0]['docstatus'] = 1;
    $data['data']['payment_schedule'][0]['payment_term'] = 'Immediate';
    $data['data']['payment_schedule'][0]['due_date'] = $created_at;
    $data['data']['payment_schedule'][0]['mode_of_payment'] = $method;
    $data['data']['payment_schedule'][0]['invoice_portion'] = 100.0;
    $data['data']['payment_schedule'][0]['discount_type'] = 'Percentage';
    $data['data']['payment_schedule'][0]['discount'] = 0.0;
    $data['data']['payment_schedule'][0]['payment_amount'] = $total;
    $data['data']['payment_schedule'][0]['outstanding'] = 0.0;
    $data['data']['payment_schedule'][0]['paid_amount'] = $total;
    $data['data']['payment_schedule'][0]['discounted_amount'] = 0.0;
    $data['data']['payment_schedule'][0]['base_payment_amount'] = $total;
    $data['data']['taxes'] = array();
    $data['data']['taxes'][0] = array();
    $data['data']['taxes'][0]['docstatus'] = 1;
    $data['data']['taxes'][0]['charge_type'] = 'On Net Total';
    $data['data']['taxes'][0]['account_head'] = 'IVA 4% - LI';
    $data['data']['taxes'][0]['description'] = 'IVA 4%';
    $data['data']['taxes'][0]['included_in_print_rate'] = 1;
    $data['data']['taxes'][0]['tax_exemption_reason'] = 'N4-Esenti';
    $data['data']['taxes'][0]['included_in_paid_amount'] = 1;
    $data['data']['taxes'][0]['cost_center'] = 'MemberPress - LI';
    $data['data']['taxes'][0]['rate'] = 4.0;
    $data['data']['taxes'][0]['account_currency'] = 'EUR';
    $data['data']['taxes'][0]['total'] = $total;
    $data['data']['taxes'][0]['base_total'] = $total;
    $data['data']['taxes'][0]['dont_recompute_tax'] = 0;
    return json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Sales%20Invoice', 'POST', 'json', $data));
}
function create_customer(
    $api,
    $username,
    $first_name,
    $last_name
) {
    $data = array();
    $data['data']['customer_name'] = $username;
    $data['data']['first_name'] = $first_name;
    $data['data']['last_name'] = $last_name;
    $data['data']['customer_type'] = 'Individual';
    $data['data']['customer_group'] = 'MemberPress';
    $data['data']['territory'] = 'Italy';
    $data['data']['fiscal_code'] = '0';
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
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Customer', 'POST', 'json', $data);
}
function create_address(
    $api,
    $username,
    $address_type
) {
    $data = array();
    $data['data']['address_title'] = $username;
    $data['data']['address_line1'] = '0';
    $data['data']['city'] = '0';
    $data['data']['state'] = '0';
    $data['data']['pincode'] = '0';
    $data['data']['country'] = 'Italy';
    $data['data']['country_code'] = 'it';
    $data['data']['is_primary_address'] = 0;
    $data['data']['is_shipping_address'] = 0;
    $data['data']['disabled'] = 0;
    $data['data']['is_your_company_address'] = 0;
    $data['data']['doctype'] = 'Address';
    $data['data']['links'] = array();
    $data['data']['links'][0]['link_doctype'] = 'Customer';
    $data['data']['links'][0]['link_name'] = $username;
    $data['data']['links'][0]['link_title'] = $username;
    $data['data']['links'][0]['doctype'] = 'Dynamic Link';
    $data['data']['address_type'] = $address_type;
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Address', 'POST', 'json', $data);
}
function create_contact(
    $api,
    $username,
    $email
) {
    $data = array();
    $data['data']['name'] = $username;
    $data['data']['first_name'] = $username;
    $data['data']['email_id'] = $email;
    $data['data']['sync_with_google_contacts'] = 0;
    $data['data']['status'] = 'Passive';
    $data['data']['is_primary_contact'] = 1;
    $data['data']['is_billing_contact'] = 1;
    $data['data']['email_ids'] = array();
    $data['data']['email_ids'][0]['parent'] = $username;
    $data['data']['email_ids'][0]['parentfield'] = 'email_ids';
    $data['data']['email_ids'][0]['parenttype'] = 'Contact';
    $data['data']['email_ids'][0]['email_id'] = $email;
    $data['data']['email_ids'][0]['is_primary'] = 1;
    $data['data']['email_ids'][0]['doctype'] = 'Contact Email';
    $data['data']['links'] = array();
    $data['data']['links'][0]['parent'] = $username;
    $data['data']['links'][0]['parentfield'] = 'links';
    $data['data']['links'][0]['parenttype'] = 'Contact';
    $data['data']['links'][0]['link_doctype'] = 'Customer';
    $data['data']['links'][0]['link_name'] = $username;
    $data['data']['links'][0]['link_title'] = $username;
    $data['data']['links'][0]['doctype'] = 'Dynamic Link';
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Contact', 'POST', 'json', $data);
}
function create_item(
    $api,
    $membership_id,
    $membership_title,
    $total
) {
    $data = array();
    $data['data']['item_code'] = $membership_id;
    $data['data']['stock_uom'] = 'Nos';
    $data['data']['item_group'] = 'Abbonamenti';
    $data['data']['item_name'] = $membership_title;
    $data['data']['description'] = $membership_title;
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Item', 'POST', 'json', $data);
    $data = array();
    $data['data']['item_code'] = $membership_id;
    $data['data']['stock_uom'] = 'Nos';
    $data['data']['price_list'] = 'Standard Selling';
    $data['data']['item_name'] = $membership_title;
    $data['data']['description'] = $membership_title;
    $data['data']['selling'] = 1;
    $data['data']['price_list_rate'] = $total;
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Item%20Price', 'POST', 'json', $data);
}
function create_subscription_plan(
    $api,
    $membership_id,
    $membership_title,
    $total,
	$membership_period,
    $membership_period_type
) {
    $data = array();
    $data['data']['name'] = '' . $membership_title;
    $data['data']['plan_name'] = '' . $membership_title;
    $data['data']['currency'] = 'EUR';
    $data['data']['item'] = $membership_id;
    $data['data']['price_determination'] = 'Fixed Rate';
    $data['data']['cost'] = $total;
    $data['data']['billing_interval'] = $membership_period_type;
    $data['data']['billing_interval_count'] = $membership_period;
    $data['data']['cost_center'] = 'MemberPress - LI';
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription%20Plan', 'POST', 'json', $data);
}
function create_subscription(
    $api,
    $subscription_id,
	$mepr_subscription,
    $username,
    $created_at,
    $membership_title
) {
    $data = array();
    $data['data']['doctype'] = 'Subscription';
    $data['data']['mepr_id'] = $subscription_id;
    $data['data']['mepr_name'] = 'mp-sub-id-'.$subscription_id;
    $data['data']['party_type'] = "Customer";
    $data['data']['party'] = $username;
    $data['data']['company'] = 'L\'Indipendente S.r.l.';
    $data['data']['start_date'] = $created_at;
    $data['data']['follow_calendar_months'] = 0;
    $data['data']['generate_new_invoices_past_due_date'] = 0;
    $data['data']['current_invoice_start'] = $created_at;
    $data['data']['days_until_due'] = 0;
    $data['data']['cancel_at_period_end'] = 0;
    $data['data']['generate_invoice_at_period_start'] = 0;
    $data['data']['sales_tax_template'] = 'Italy VAT 4% - LI';
    $data['data']['apply_additional_discount'] = '';
    $data['data']['additional_discount_percentage'] = 0.0;
    $data['data']['additional_discount_amount'] = 0.0;
    $data['data']['submit_invoice'] = 0;
    $data['data']['cost_center'] = 'MemberPress - LI';
    $data['data']['plans'] = array();
    $data['data']['plans'][0] = array();
    $data['data']['plans'][0]['name'] = $membership_title;
    $data['data']['plans'][0]['plan'] = $membership_title;
    $data['data']['plans'][0]['qty'] = 1;
    $data['data']['plans'][0]['doctype'] = 'Subscription Plan Detail';
    return json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'POST', 'json', $data), true);
}
function cancel_subscription(
    $api,
    $subscription_name
) {
    $subscription = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'GET', null, null), true);
    $subscription['data']['status'] = 'Cancelled';
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
}
function erpnext_transaction_completed($event)
{
    $id_transaction = $event->get_data()->rec->id;
    $webhook_url = BASE_URL . "transactions/$id_transaction";
    $transaction = get_from_webhook($webhook_url);
	$trans_num = $transaction->trans_num;
    $username = $transaction->member->username;
    $membership_id = '' . $transaction->membership->id;
    $membership_title = '' . $transaction->membership->title;
    $total = round((float) $transaction->total, 2, PHP_ROUND_HALF_EVEN);
    $created_at = substr($transaction->created_at, 0, 10);
    $expires_at = substr($transaction->expires_at, 0, 10);
    $payment_gateway = $transaction->gateway;
    if ($payment_gateway == PAYMENT_OFFLINE) $method = 'Bank Draft';
    else if ($payment_gateway == PAYMENT_STRIPE) $method = 'Stripe';
    else if ($payment_gateway == PAYMENT_PAYPAL) $method = 'PayPal';
    else if ($payment_gateway == PAYMENT_FREE) $method = 'Free';
    $trial_amount = round((float) $transaction->subscription->trial_amount, 2, PHP_ROUND_HALF_EVEN);
    if ($trial_amount > 0) $discount = round((float) $total - $trial_amount, 2, PHP_ROUND_HALF_EVEN);
    else $discount = 0;
	if($total==0) {
		$total = round((float) $transaction->membership->price, 2, PHP_ROUND_HALF_EVEN);
		$discount = $total;
	}
	if($transaction->coupon !== "0") $coupon = $transaction->coupon->coupon_code;
	else $coupon = "0";
    //init API
    $api = init_erpnext_api();
	if("0"!==$transaction->subscription){
    	$subscription_name = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'GET', 'x-www-form-urlencoded', 'filters=[["Subscription","mepr_id","=","' . $transaction->subscription->id . '"]]'))->data[0]->name;
    	$subscription = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'GET', null, null), true);
		//if (count($subscription['data']['invoices']) > 1) {
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
		//}
		$invoice_name = $subscription['data']['invoices'][count($subscription['data']['invoices']) - 1]['invoice'];
		/*if(count($subscription['data']['invoices']) == 1){
			$invoice = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Sales%20Invoice/' . $invoice_name, 'GET', null,null), true);
			$invoice['data']['to_date'] = $expires_at;
			execute_call_erpnext($api, ROOT_URL . '/api/resource/Sales%20Invoice/' . $invoice_name, 'PUT', 'json', $invoice);
		}*/
		$subscription['data']['current_invoice_start'] = $created_at;
		$subscription['data']['current_invoice_end'] = $expires_at;
		execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
		$to_disable_name = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'GET', 'x-www-form-urlencoded', 'filters=[["Subscription","status","=","Active"],["Subscription","party","=","' . $username . '"]]'),true)['data'][0]['name'];
		if($to_disable_name!=''){
			cancel_subscription(
				$api,
				$to_disable_name
			);
		}
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
			$total,
			$invoice_name
		);
	}
    curl_close($api);
}
function erpnext_signup_completed($event)
{
    $id_member = $event->get_data()->rec->ID;
    $webhook_url = BASE_URL . "members/$id_member";
    $signup = get_from_webhook($webhook_url);
    $username = $signup->username;
    $first_name = $signup->first_name;
    $last_name = $signup->last_name;
    $email = $signup->email;
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
        execute_call_erpnext($api, ROOT_URL . '/api/resource/Contact/' . $username . '-' . $username, 'GET', null, null);
        if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
            create_contact(
                $api,
                $username,
                $email
            );
        }
    }
    curl_close($api);
}
function erpnext_subscription_created($event)
{
    $id_subscription = $event->get_data()->rec->id;
    $webhook_url = BASE_URL . "subscriptions/$id_subscription";
    $subscription = get_from_webhook($webhook_url);
    $username = $subscription->member->username;
    $first_name = $subscription->member->first_name;
    $last_name = $subscription->member->last_name;
    $email = $subscription->member->email;
    $membership_id = '' . $subscription->membership->id;
    $membership_title = '' . $subscription->membership->title;
    $membership_period = intval($subscription->membership->expire_after);
    $membership_period_type = ucfirst(rtrim($subscription->membership->period_type, " s"));
    $total = round((float) $subscription->total, 2, PHP_ROUND_HALF_EVEN);
    $created_at = substr($subscription->created_at, 0, 10);
	$expires_at = '';
    $payment_gateway = $subscription->gateway;
    if ($payment_gateway == PAYMENT_OFFLINE) $method = 'Bank Draft';
    else if ($payment_gateway == PAYMENT_STRIPE) $method = 'Stripe';
    else if ($payment_gateway == PAYMENT_PAYPAL) $method = 'PayPal';
    else if ($payment_gateway == PAYMENT_FREE) $method = 'Free';
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
        execute_call_erpnext($api, ROOT_URL . '/api/resource/Contact/' . $username . '-' . $username, 'GET', null, null);
        if (curl_getinfo($api, CURLINFO_RESPONSE_CODE) == 404) {
            create_contact(
                $api,
                $username,
                $email
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
	$mepr_subscription = $subscription->subscr_id;
    $subscription = create_subscription(
        $api,
        $subscription->id,
		$mepr_subscription,
        $username,
        $created_at,
        $membership_title
    );
    /*$invoice = create_invoice(
        $api,
		null,
        $created_at,
        $expires_at,
        $username,
        $total,
        $membership_id,
        $membership_title,
        $method,
        $discount,
		$coupon
    );*/
    $subscription_name = $subscription['data']['name'];
    /*$subscription['data']['invoices'][] = array(
        'docstatus' => 0,
        'invoice' => $invoice->data->name,
        'document_type' => 'Sales Invoice',
        'doctype' => 'Subscription Invoice',
        'parentfield' => 'invoices',
        'parent' => $subscription_name,
        'idx' => count($subscription['data']['invoices']) + 1
    );*/
    $subscription['data']['status'] = "Unpaid";
    $subscription['data']['current_invoice_start'] = $created_at;
    $subscription['data']['current_invoice_end'] = $expires_at;
    execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription/' . $subscription_name, 'PUT', 'json', $subscription);
    curl_close($api);
}
function erpnext_subscription_stopped($event)
{
    $id_subscription = $event->get_data()->rec->id;
    $api = init_erpnext_api();
    $subscription_name = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'GET', 'x-www-form-urlencoded', 'filters=[["Subscription","mepr","=","' . $id_subscription . '"]]'))->data[0]->name;
    cancel_subscription(
        $api,
        $subscription_name
    );
    curl_close($api);
}
function erpnext_transaction_refunded($event)
{
}
function erpnext_subscription_expired($subscription, $transaction)
{
    error_log(print_r($subscription . "\n\n", true), LOG_TYPE, __DIR__ . '/debug-erpnext-expired.log');
    error_log(print_r($transaction . "\n\n", true), LOG_TYPE, __DIR__ . '/debug-erpnext-expired.log');
}
add_action('mepr-event-transaction-completed', 'erpnext_transaction_completed', 999);
add_action('mepr-event-member-signup-completed', 'erpnext_signup_completed', 999);
add_action('mepr-event-subscription-created', 'erpnext_subscription_created', 999);
add_action('mepr-event-subscription-stopped', 'erpnext_subscription_stopped', 999);
add_action('mepr-event-transaction-refunded', 'erpnext_transaction_refunded', 999);
add_action('mepr-event-subscription-expired', 'erpnext_subscription_expired', 999, 2);
/**
 * END API ERPNEXT
 */