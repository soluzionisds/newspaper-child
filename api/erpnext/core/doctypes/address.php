<?php
function create_address(
    $api,
    $username,
    $address_type
) {
    $data = array();
	//$username = PREFIX . $username;
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