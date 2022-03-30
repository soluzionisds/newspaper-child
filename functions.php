<?php
/*----------------------------------------------------------------------------
  Newspaper V9.0+ Child theme - Please do not use this child theme with older versions of Newspaper Theme

  What can be overwritten via the child theme:
  - everything from /parts folder
  - all the loops (loop.php loop-single-1.php) etc
  - please read the child theme documentation: http://forum.tagdiv.com/the-child-theme-support-tutorial/
*/
/* add the parent style + style.css from this folder */
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles() {
  wp_enqueue_style('td-theme', get_template_directory_uri() . '/style.css', '', TD_THEME_VERSION, 'all' );
  wp_enqueue_style('td-theme-child', get_stylesheet_directory_uri() . '/style.css', array('td-theme'), '1.0.19', 'all' );
  wp_enqueue_script('td-custom-script', get_stylesheet_directory_uri() . '/scripts.js', array( 'jquery' ), '1.0.2');
}

previous_post_link( '<span class="previous-post-link">%link</span>', apply_filters( 'wpbf_previous_post_link', __( '&larr; Previous Post', 'page-builder-framework' ) ) );
next_post_link( '<span class="next-post-link">%link</span>', apply_filters( 'wpbf_next_post_link', __( 'Next Post &rarr;', 'page-builder-framework' ) ) );

/***************************
* MemberPress

Send transaction "failed" mail also if change status by backoffice
*/
function mepr_custom_failed_status_email($txn) {
  \MeprUtils::send_failed_txn_notices($txn);
}
add_action('mepr-txn-status-failed', 'mepr_custom_failed_status_email');
/* Send subscription resumed email
function mepr_capture_resumed_sub($event) {
  \MeprUtils::send_resumed_sub_notices($event);
}
add_action('mepr-event-subscription-resumed', 'mepr_capture_resumed_sub');*/

/***************************
* Login logo
****************************/

function lindipendente_login_logo() { ?>
    <style type="text/css">
			#login h1 a, .login h1 a {
			background-image: url(/wp-content/uploads/2021/03/L..png);
			width: 150px;
			height: 150px;
			background-size: 150px 150px;
			background-repeat: no-repeat;
			padding-bottom: 10px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'lindipendente_login_logo' );

function lindipendente_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'lindipendente_login_logo_url' );

function lindipendente_login_logo_url_title() {
    return 'L\'INDIPENDENTE';
}
add_filter( 'login_headertext', 'lindipendente_login_logo_url_title' );

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
