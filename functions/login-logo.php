<?php
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
