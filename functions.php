<?php
/*----------------------------------------------------------------------------
    Newspaper V9.0+ Child theme - Please do not use this child theme with older versions of Newspaper Theme
    What can be overwritten via the child theme:
     - everything from /parts folder
     - all the loops (loop.php loop-single-1.php) etc
	   - please read the child theme documentation: http://forum.tagdiv.com/the-child-theme-support-tutorial/
*/
/* ----------------------------------------------------------------------------
    add the parent style + style.css from this folder
 */
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles() {
  wp_enqueue_style('td-theme', get_template_directory_uri() . '/style.css', '', TD_THEME_VERSION, 'all' );
  wp_enqueue_style('td-theme-child', get_stylesheet_directory_uri() . '/style.css', array('td-theme'), '1.6.8', 'all' );
  wp_enqueue_script('td-custom-script', get_stylesheet_directory_uri() . '/scripts.js', array( 'jquery' ), '1.2.6');
}

previous_post_link( '<span class="previous-post-link">%link</span>', apply_filters( 'wpbf_previous_post_link', __( '&larr; Previous Post', 'page-builder-framework' ) ) );
next_post_link( '<span class="next-post-link">%link</span>', apply_filters( 'wpbf_next_post_link', __( 'Next Post &rarr;', 'page-builder-framework' ) ) );

/***************************
* includes
****************************/
require_once get_stylesheet_directory() . '/functions/memberpress.php';
require_once get_stylesheet_directory() . '/functions/login-logo.php';
require_once get_stylesheet_directory() . '/api/erpnext/erpnext.php';
require_once get_stylesheet_directory() . '/api/facebook.php';
//require_once get_stylesheet_directory() . '/api/wordpress.php';

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
