<?php
function erpnext_subscription_stopped($event)
{
    $id_subscription = $event->get_data()->rec->id;
    $api = init_erpnext_api();
    $subscription_name = json_decode(execute_call_erpnext($api, ROOT_URL . '/api/resource/Subscription', 'GET', 'x-www-form-urlencoded', 'filters=[["Subscription","mepr_id","=","' . PREFIX . $id_subscription . '"]]'))->data[0]->name;
    cancel_subscription(
        $api,
        $subscription_name
    );
    curl_close($api);
}