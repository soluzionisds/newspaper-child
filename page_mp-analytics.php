<?php
/*** Template Name: MemberPress Custom Analytics */
get_header();
?>

<div class="td-main-content-wrap td-container-wrap">
	<div class="td-container">
		<div class="td-crumb-container">
			<?php echo tagdiv_page_generator::get_breadcrumbs(array(
				'template' => 'page',
				'page_title' => get_the_title(),
			)); ?>
		</div>
		<div class="td-pb-row">
			<div class="td-pb-span12 td-main-content">
				<div class="td-ss-main-content">
					<?php
						if (have_posts()) {
							while ( have_posts() ) : the_post();
								?>
								<div class="td-page-header">
									<h1 class="entry-title td-page-title">
										<span><?php the_title() ?></span>
									</h1>
								</div>
								<div class="td-page-content tagdiv-type">
									<?php

									the_content(); // page content

									///////////////////////////////////////
									// Variables
									//
									global $wpdb;
									$user = wp_get_current_user();
									$allowed_roles = array('user_manager', 'administrator');
									$date_from = '2025-02-01 00:00:00';
									//$date_no_renewals_to = date('Y-m-d H:i:s', time());
									$date_to = '2025-03-31 23:59:59';
									//Now that we've created such a nice heading for our html table, lets create a heading for our csv table
									$csv_hdr = "Mail, User, Membership, Subscription, Auto Rebill, Subscription Created At, Transaction Expired At, Transaction Amount";
									//Quickly create a variable for our output that'll go into the CSV file (we'll make it blank to start).
								  	$csv_output="";

									if( array_intersect($allowed_roles, $user->roles ) ) {

										$page_to_load = "expired";

										switch ($page_to_load) {
										case "new_subscribers":
											require_once get_stylesheet_directory() . '/utility/mp-analytics-new-subscribers.php';
											break;
										case "no_renewals":
											require_once get_stylesheet_directory() . '/utility/mp-analytics-no-renewals.php';
											break;
										case "renewals":
											require_once get_stylesheet_directory() . '/utility/mp-analytics-renewals.php';
											break;
										case "expired":
											require_once get_stylesheet_directory() . '/utility/mp-analytics-expire.php';
											break;
										case "active":
											require_once get_stylesheet_directory() . '/utility/mp-analytics-active.php';
											break;
										default:
											echo "No selected page";
										}

									}?>
								</div>
					<?php endwhile;//end loop
						}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
