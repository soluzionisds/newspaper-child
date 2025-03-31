<?php
/***************************
* Cookie Session
* keep users logged in
****************************/
add_filter( 'auth_cookie_expiration', 'keep_me_logged_in_for_90_days' );
function keep_me_logged_in_for_90_days( $expirein ) {
  return 7776000; // 90 days in seconds
}

/***************************
* MonsterInsights
* Adds renewals in the MemberPress statistics
****************************/
add_filter( 'monsterinsights_ecommerce_skip_renewals', '__return_false' );

/***************************
* Nascondi elemento ad altri ruoli per view counter degli post
****************************/
function add_user_role_body_class($classes) {
    // Verifica se l'utente è loggato
    if (is_user_logged_in()) {
        // Ottieni il ruolo dell'utente
        $current_user = wp_get_current_user();
        // Controlla se l'utente è un amministratore, editor o autore
        if (!in_array('administrator', $current_user->roles) && !in_array('editor', $current_user->roles) && !in_array('author', $current_user->roles)) {
            // Aggiungi la classe 'hide-view-counter' se l'utente non è un admin, editor o autore
            $classes[] = 'hide-view-counter';
        }
    } else {
        // Utente non loggato, aggiungi la classe 'hide-view-counter'
        $classes[] = 'hide-view-counter';
    }
    return $classes;
}
add_filter('body_class', 'add_user_role_body_class');


/* Custom Author Box */
function li_author_box($content) {
    if (is_single()) {
        $author_id = get_the_author_meta('ID');
        $excluded_author_ids = array(27643); 
        $post_date = strtotime(get_the_date('Y-m-d'));
        $limit_date = strtotime("2025-04-01");

        if ($post_date > $limit_date && !in_array($author_id, $excluded_author_ids)) {
            
            $author_name = get_the_author_meta('display_name');
            $author_bio = get_the_author_meta('description');
            $author_avatar = get_avatar($author_id, 96); // 96 è la dimensione dell'avatar

            $author_output = '<div class="li-author-box">';
            $author_output .= '<div class="li-author-box__sx">' . $author_avatar . '</div>';
            $author_output .= '<div class="li-author-box__dx"><h3 class="li-author-box__name">' . $author_name . '</h3>';
            $author_output .= '<p class="li-author-box__bio">' . $author_bio . '</p></div>';
            $author_output .= '</div>';

            $content .= $author_output;
        }
    }
    return $content;
}
add_filter('the_content', 'li_author_box');

/***************************
* bbPress
* remove bbPress breadcrumb
function bm_bbp_no_breadcrumb ($param) {
	return true;
}
add_filter ('bbp_no_breadcrumb', 'bm_bbp_no_breadcrumb');

* temporary userpage redirection bbPress
function user_profile_link(){
    $author_id = bbp_get_reply_author_id();
    $user_info = get_userdata($author_id);
    return site_url()."/";
}
add_filter('bbp_get_user_profile_url', 'user_profile_link');*/