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

/**
 * PROBLEM - Stripe payments fail when the tagDiv Composer plugin is activated. Other themes that come with tagDiv Composer bundled may run into the same issue.
 * SOLUTION - Please add this code snippet using.
 * Remove an object filter.
 *
 * @param  string $tag                Hook name.
 * @param  string $class              Class name. Use 'Closure' for anonymous functions.
 * @param  string|void $method        Method name. Leave empty for anonymous functions.
 * @param  string|int|void $priority  Priority
 * @return void
 */
function remove_object_filter( $tag, $class, $method = null, $priority = null ) {
    global $wp_version;
    $new_wp_filter_struct = false;
    $filters = $GLOBALS['wp_filter'][ $tag ];

    if ( empty ( $filters ) ) {
        return;
    }

    if ( version_compare( $wp_version, '4.7', '>=' ) && isset( $filters->callbacks ) ) {
        $filters = $filters->callbacks;
        $new_wp_filter_struct = true;
    }

    foreach ( $filters as $p => $filter ) {
        if ( ! is_null( $priority ) && ( (int) $priority !== (int) $p ) ) {
            continue;
        }

        foreach ( $filter as $identifier => $function ) {
            $remove = false;
            $function = $function['function'];

            if ( $function instanceof Closure && $class === 'Closure' ) {
                $remove = true;
            }

            if ( $remove ) {
                if ( $new_wp_filter_struct ) {
                    unset( $GLOBALS['wp_filter'][ $tag ]->callbacks[ $p ][ $identifier ] );
                    if ( count($GLOBALS['wp_filter'][ $tag ]->callbacks[ $p ] ) == 0 ) {
                        unset( $GLOBALS['wp_filter'][ $tag ]->callbacks[ $p ] );
                    }
                } else {
                    unset( $GLOBALS['wp_filter'][ $tag ][ $p ][ $identifier ] );
                }
            }
        }
    }
}

function mepr_remove_closures() {
    global $post;
    if ( isset( $post->post_type ) && ( MeprProduct::is_product_page( $post ) || MeprUser::is_account_page( $post ) ) ) {
        remove_object_filter( 'wp_head', 'Closure', null, 10 );
    }
}
add_action( 'wp_head', 'mepr_remove_closures', 9 );

/*
////////////////////////////////////////////////////////////////////////////////
////////// Shortcode Example: [mepr-sub-expiration membership='123']
function mepr_sub_expiration_shortcode($atts = [], $content = null, $tag = '') {
  $sub_expire_html = '';

  if($atts['membership'] && is_numeric($atts['membership'])) {
    $date_str = MeprUser::get_user_product_expires_at_date(get_current_user_id(), $atts['membership']);

		if ($date_str) {
	      $date = date_create($date_str);
	      $sub_expire_html = "<div>Expires: " . date_format($date,"Y/m/d") . "</div>";
		}
  }

  return $sub_expire_html;
}
add_shortcode('mepr-sub-expiration', 'mepr_sub_expiration_shortcode');
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////// Custom fields: Add an Option to a Select indicated. ATTENTION: script add a field every load of the page
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
});
//////////////////////////////////////////////////
////////// Send subscription resumed email
function mepr_capture_resumed_sub($event) {
  \MeprUtils::send_resumed_sub_notices($event);
}
add_action('mepr-event-subscription-resumed', 'mepr_capture_resumed_sub');*/
//////////////////////////////////////////////////////////////////////////////////////////
////////// Used to check if a signup limit has been reached for a particular membership
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
//////////////////////////////////////
////////// Limit membership premium
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
add_filter('mepr-validate-signup', 'limit_signups_for_membership_premium');
*/
