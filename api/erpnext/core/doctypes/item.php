<?php

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
