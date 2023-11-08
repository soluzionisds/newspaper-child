<?php
/***************************
 * MemberPress
****************************/

/**
 * PROBLEM: Mailchimp users remains active if transaction expire (offline payment)
 **/
//Turn off Auto rebill for offline gateway if a transactions expires manually.
add_action( 'mepr-txn-store', function ( $txn ) {
  // Bail if no id's
  if(!isset($txn->id) || $txn->id <= 0 || !isset($txn->user_id) || $txn->user_id <= 0) { return; }

  // Ignore "pending" txns
  if(!isset($txn->status) || empty($txn->status) || $txn->status == MeprTransaction::$pending_str) { return; }

  $active_status  = array(MeprTransaction::$complete_str, MeprTransaction::$confirmed_str);
  $now            = time();
  $expires        = 0; // Lifetime

  if ( ! empty( $txn->expires_at ) && $txn->expires_at != MeprUtils::db_lifetime() ) {
    $expires = strtotime($txn->expires_at);
  }

  if(in_array($txn->status, $active_status)) {
    if($expires !== 0 && $expires < $now && $txn->payment_method() instanceof MeprArtificialGateway && $sub = $txn->subscription()) {
      $sub->status = MeprSubscription::$cancelled_str;
      $sub->store();
    }
  }
}, 9991 );

// Turn off Auto rebill for offline gateway when a transaction expires naturally.
add_action('mepr-txn-expired', function($txn) {
	// Bail if no id's
  if(!isset($txn->id) || $txn->id <= 0 || !isset($txn->user_id) || $txn->user_id <= 0) { return; }

  // Ignore "pending" txns
  if(!isset($txn->status) || empty($txn->status) || $txn->status == MeprTransaction::$pending_str) { return; }

  if($txn->payment_method() instanceof MeprArtificialGateway && $sub = $txn->subscription()) {
	  $sub->status = MeprSubscription::$cancelled_str;
	  $sub->store();

	  remove_action('mepr-txn-expired', array('MeprActiveInactiveHooksCtrl', 'handle_txn_expired'), 11);

	  add_action('mepr-txn-expired', function($txn) {
		  global $wpdb;

      	// Allow third party plugins to stop the running of the method
      	if(MeprHooks::apply_filters('mepr-active-inactive-hooks-skip', false, $txn)){
        	return;
      	}

      	// Go directly to the database and maybe flush caches beforehand
      	if(MeprHooks::apply_filters('mepr-autoresponder-flush-caches', true)) {
        	wp_cache_flush();
        	$wpdb->flush();
      	}

      	$query = $wpdb->prepare(
        	"SELECT count(*) FROM {$wpdb->prefix}mepr_transactions WHERE user_id = %d AND product_id = %d AND status IN (%s, %s) AND (expires_at >= %s OR expires_at = %s)",
        	$txn->user_id,
        	$txn->product_id,
        	MeprTransaction::$complete_str,
        	MeprTransaction::$confirmed_str,
        	MeprUtils::db_now(),
        	MeprUtils::db_lifetime()
      	);

      	$active_on_membership = $wpdb->get_var($query);

      	if($active_on_membership) {
        	MeprHooks::do_action('mepr-account-is-active', $txn);
      	}
      	else {
        	MeprHooks::do_action('mepr-account-is-inactive', $txn);
      	}
      }, 11, 1);
  	}
}, 1, 1);

//If a transaction is completed on an offline gateway subscription - turn auto rebill back on
add_action ( 'mepr-txn-status-complete', function ( $txn ) {
	$expires = 0; // Lifetime
	$now = time();

	if ( ! empty( $txn->expires_at ) && $txn->expires_at != MeprUtils::db_lifetime() ) {
	  $expires = strtotime($txn->expires_at);
	}

	if ( ( $expires === 0 || $expires >= $now ) && $txn->payment_method() instanceof MeprArtificialGateway && $sub = $txn->subscription() ) {
		$sub->status = MeprSubscription::$active_str;
		$sub->store();
	}
});

/* Send transaction "failed" mail also if change status by backoffice */
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

/**
 * Print mail variable {$product-price} in order to show membership price without discount
 **/
add_filter('mepr_transaction_email_params', function($params, $txn) {
  $sub = $txn->subscription();
  $params['product-price'] = str_replace( '.', ',', $sub->total );
  return $params;
}, 10, 2);

/**
 * Endpoint for logged users, used for Mobile APP
**/
function enrich_post($post){
  $post->id = $post->ID;
	unset($post->ID);
	$post->title = new stdClass();
	$post->title->rendered = $post->post_title;
	unset($post->post_title);
	$post->date = str_replace(" ","T",$post->post_date);
	unset($post->post_date);
	$post->link = explode("?",$post->guid)[0].str_replace("-","/",explode(" ",$post->post_date)[0])."/".$post->post_name;
	$post->excerpt = new stdClass();
	$post->excerpt->rendered = get_the_excerpt($post->id);
	$post->featured_media = get_post_thumbnail_id($post->id);
	$post->categories = wp_get_post_categories($post->id);
	$post->content = new stdClass();
	$post->content->rendered = $post->post_content;
	unset($post->post_content);
	$tags = get_the_tags($post->id);
	if($tags)$post->tags = array_map(function($tag){return $tag->term_id;}, $tags);
	else $post->tags = array();
	$post->status = $post->post_status;
	unset($post->post_status);
	$post->author = intval($post->post_author);
	unset($post->post_author);
	$post->modified_by = get_userdata(get_post_meta( $post->id, '_edit_last', true ))->display_name;
	return $post;
}

function get_logged_user_mp_posts($request) {
  $user_id = get_current_user_id();

  if(!$user_id) {
    return new WP_Error('authentication_failure', 'Non hai effettuato l\'autenticazione.', array('status' => 403));
  }
	
	$tags = null;
	if(null !== $request->get_param('tags')) $tags = explode(",",$request->get_param('tags'));

	/*if(null !== $request->get_param('tags') && $request->get_param('tax_relation') == 'AND') $tags_key = 'tag__and';
	else $tags_key = 'tag__in';*/
	$tags_key = 'tag__in';
	
  $posts_query = new WP_Query(
		array(
			's' => $request->get_param('search'),
			'post__not_in' => explode(',',$request->get_param('exclude')),
			'posts_per_page' => $request->get_param('per_page'),
			'paged' => $request->get_param('page'),
			'date_query' => array(
				array('after' => $request->get_param('after')),
				array('before' => $request->get_param('before'))
			),
			'orderby' => 'date',
			'order' => strtoupper($request->get_param('order')),
			'cat' => $request->get_param('categories'),
			'category__not_in' => explode(',',$request->get_param('categories_exclude')),
			'post_status' => $request->get_param('status'),
			'embed' => $request->get_param('context') == 'embed' ? true : false,
			'post_type' => 'post',
			$tags_key => $tags
		)
	);

  if (empty($posts_query->posts)) {
    return new WP_Error('no_posts', 'Nessun articolo trovato.', array('status' => 404));
  }

  $mp_user = new MeprUser($user_id);

  $posts = $posts_query->posts;
  $posts_to_display = array();

  foreach ($posts as $post) {
    if (!MeprRule::is_locked_for_user($mp_user, $post)) {
      $post = enrich_post($post);
      $posts_to_display[] = $post;
    }
  }

  return new WP_REST_Response($posts_to_display, 200);
}

function get_logged_user_mp_post($request) {
  $user_id = get_current_user_id();

  if(!$user_id) {
    return new WP_Error('authentication_failure', 'Non hai effettuato l\'autenticazione.', array('status' => 403));
  }

  $post_id = $request->get_param('id');
  $post = get_post($post_id);

  if(!$post) {
    return new WP_Error('no_post', 'Nessun articolo trovato con l\'ID fornito.', array('status' => 404));
  }

  $mp_user = new MeprUser($user_id);

  if(!MeprRule::is_locked_for_user($mp_user, $post)) {
  $post = enrich_post($post);		
    return new WP_REST_Response($post, 200);
  }
}

function register_logged_user_posts_routes() {
  register_rest_route(
    'mp/v1',
    '/logged-user-posts',
    array(
      'methods' => 'GET',
      'callback' => 'get_logged_user_mp_posts',
      'permission_callback' => function() {
        return is_user_logged_in();
      },
			'args' => array(
        'search' => array(
          'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'exclude' => array(
          'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'per_page' => array(
          'type'              => 'integer',
					'default'           => 10,
					'minimum'           => 1,
					'maximum'           => 10000,
					'sanitize_callback' => 'absint',
					'validate_callback' => 'rest_validate_request_arg',
        ),
				'page' => array(
          'type'              => 'integer',
					'default'           => 1,
					'minimum'           => 1,
					'maximum'           => 10000,
					'sanitize_callback' => 'absint',
					'validate_callback' => 'rest_validate_request_arg',
        ),
				'after' => array(
          'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'before' => array(
          'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'order' => array(
          'type'              => 'string',
					'default'			      => 'DESC',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'tags' => array(
          'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'tax_relation' => array(
          'type'              => 'string',
					'default'			      => 'AND',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'categories' => array(
          'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'categories_exclude' => array(
          'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'status' => array(
          'type'              => 'string',
					'default'			      => 'publish',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        ),
				'context' => array(
          'type'              => 'string',
					'default'			      => 'view',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg'
        )
      )
    )
  );

  register_rest_route(
      'mp/v1',
      '/logged-user-posts/(?P<id>\d+)',
      array(
        'methods' => 'GET',
        'callback' => 'get_logged_user_mp_post',
        'permission_callback' => function() {
          return is_user_logged_in();
        },
        'args' => array(
          'id' => array(
            'default' => ''
          )
        )
      )
  );
}
add_action('rest_api_init', 'register_logged_user_posts_routes');

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
