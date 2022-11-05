<?php
/***************************
 * MemberPress

Send transaction "failed" mail also if change status by backoffice
 */
function mepr_custom_failed_status_email($txn)
{
	\MeprUtils::send_failed_txn_notices($txn);
}
add_action('mepr-txn-status-failed', 'mepr_custom_failed_status_email');

/* Add default country for phone field */
function cp_filter_default_phone_input_country( $args ) {
	$args['defaultCountry'] = strtolower( 'IT' ); 	return $args;
}
add_filter( 'mepr-phone-input-config', 'cp_filter_default_phone_input_country' );

/* Substitute a translation string to use HTML */
add_action('wp_head', function() { ?>
<script>
(function($) {
  $(document).ready(function() {
    var rowProratio = $('.account #mepr-account-subscriptions-table .mepr-account-terms');
		var rowCancelSubscription  = $('.account .mp_wrapper .mepr_updated');
    if(rowProratio.length) {
      rowProratio.html(function(i, html) {
        return html.replace('(compensazione)', '(<a href="/termini-e-condizioni" target="_blank">compensazione</a>)');
      });
    }
		if(rowCancelSubscription.length) {
      rowCancelSubscription.html(function(i, html) {
        return html.replace('Il rinnovo automatico del tuo abbonamento è stato cancellato con successo.', 'Il rinnovo automatico del tuo abbonamento è stato cancellato con successo. Facci sapere come possiamo migliorare, <a href="https://us2.list-manage.com/survey?u=fc6a2373726095bfbf68aad96&id=53bdbf3acb&attribution=false" target="_blank">clicca qui</a>.');
      });
    }
  });
})(jQuery);
</script>
<?php });

/*
Custom fields: Add an Option to a Select indicated. ATTENTION: script add a field every load of the page
add_action('init', function() {
$options = get_option('mepr_options');
foreach ($options['custom_fields'] as $key => $value) {
  if ( $value['field_key'] == 'mepr_provincia' ) {
    array_unshift($options['custom_fields'][$key]['options'], array(
        'option_name' => '---',
        'option_value' => ''
    ));
  }
}
update_option('mepr_options', $options);
});*/

/* Send subscription resumed email
function mepr_capture_resumed_sub($event) {
  \MeprUtils::send_resumed_sub_notices($event);
}
add_action('mepr-event-subscription-resumed', 'mepr_capture_resumed_sub');*/

//Used to check if a signup limit has been reached for a particular membership
/*function has_reached_limit($membership_id, $limit) {
  global $wpdb;

  $query = "SELECT count(DISTINCT user_id)
            FROM {$wpdb->prefix}mepr_transactions
            WHERE status IN('complete', 'confirmed')
              AND (
                expires_at IS NULL
                OR expires_at = '0000-00-00 00:00:00'
                OR expires_at >= NOW()
              )
              AND product_id = {$membership_id}";

  $count = $wpdb->get_var($query);

  return ($count >= $limit);
}
//Limit membership premium
function limit_signups_for_membership_premium($errors) {
  //CHANGE THE FOLLOWING TWO VARS
  $membership_id = 13565; //The Product you want to limits' ID
  $limit = 1; //Number of signups allowed

  if($_POST['mepr_product_id'] != $membership_id) { return $errors; }

  if(has_reached_limit($membership_id, $limit)) {
    $errors[] = __('Sorry, our signup limit of ' . $limit . ' members has been reached. No further signups are allowed.', 'memberpress');
  }

  return $errors;
}
add_filter('mepr-validate-signup', 'limit_signups_for_membership_premium');*/
