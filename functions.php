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
  wp_enqueue_style('td-theme-child', get_stylesheet_directory_uri() . '/style.css', array('td-theme'), '1.7.18', 'all' );
  wp_enqueue_script('td-custom-script', get_stylesheet_directory_uri() . '/scripts.js', array( 'jquery' ), '1.3.8');
}

previous_post_link( '<span class="previous-post-link">%link</span>', apply_filters( 'wpbf_previous_post_link', __( '&larr; Previous Post', 'page-builder-framework' ) ) );
next_post_link( '<span class="next-post-link">%link</span>', apply_filters( 'wpbf_next_post_link', __( 'Next Post &rarr;', 'page-builder-framework' ) ) );

/***************************
* includes
****************************/
require_once get_stylesheet_directory() . '/functions/memberpress.php';
require_once get_stylesheet_directory() . '/functions/login-logo.php';
require_once get_stylesheet_directory() . '/functions/various.php';
require_once get_stylesheet_directory() . '/api/erpnext/erpnext.php';
require_once get_stylesheet_directory() . '/api/facebook.php';
//require_once get_stylesheet_directory() . '/api/wordpress.php';
