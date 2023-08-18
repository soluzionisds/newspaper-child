<?php

function erpnext_subscription_expired($subscription, $transaction)
{
    error_log(print_r($subscription . "\n\n", true), LOG_TYPE, __DIR__ . '/debug-erpnext-expired.log');
    error_log(print_r($transaction . "\n\n", true), LOG_TYPE, __DIR__ . '/debug-erpnext-expired.log');
}
