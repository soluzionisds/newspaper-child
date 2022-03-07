<?php
/*Questo file è parte di Newspaper-child, Newspaper child theme.

Tutte le funzioni di questo file saranno caricate prima delle funzioni del tema genitore.
Per saperne di più https://codex.wordpress.org/Child_Themes.

Nota: questa funzione carica prima il foglio di stile genitore, poi il foglio di stile figlio
(non toccare se non sai cosa stai facendo)
*/

if ( ! function_exists( 'suffice_child_enqueue_child_styles' ) ) {
	function Newspaper_child_enqueue_child_styles() {
	    // loading parent style
	    wp_register_style(
	      'parente2-style', get_template_directory_uri() . '/style.css'
	    );
	    wp_enqueue_style( 'parente2-style' );
	    // loading child style
	    wp_register_style(
	      'childe2-style', get_stylesheet_directory_uri() . '/style.css', array(), '1.0.14', 'all'
	    );
	    wp_enqueue_style( 'childe2-style');

		wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri() . '/scripts.js', array( 'jquery' ));
	 }
}
add_action( 'wp_enqueue_scripts', 'Newspaper_child_enqueue_child_styles' );

/*Scrivi qui le tue funzioni */

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
