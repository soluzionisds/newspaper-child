<?php

function erpnext_signup_completed($event)
{
    $id_member = $event->get_data()->rec->ID;
    $webhook_url = BASE_URL . "members/$id_member";
    $signup = get_from_webhook_en_api($webhook_url);
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
    curl_close($api);
}
