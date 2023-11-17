<?php
function create_contact(
    $api,
    $username,
    $email,
    $first_name,
    $last_name
) {
    $data = array();
	//$username = PREFIX . $username;
    $data['data']['name'] = $username;
    $data['data']['first_name'] = $first_name;
    $data['data']['last_name'] = $last_name;
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