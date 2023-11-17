<?php
function create_customer(
    $api,
    $username,
    $first_name,
    $last_name
) {
    $data = array();
	//$username = PREFIX . $username;
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