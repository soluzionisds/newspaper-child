<?php

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
