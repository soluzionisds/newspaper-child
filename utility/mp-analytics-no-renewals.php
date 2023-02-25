<?php

///////////////////////////////////////
// No renewals
//
$txn_query_no_renewals = $wpdb->prepare("SELECT DISTINCT subscription_id
FROM {$wpdb->prefix}mepr_transactions
WHERE txn_type = %s
AND subscription_id > 0
AND user_id != 0
AND (expires_at >= %s AND expires_at <= %s)" , 'payment', $date_no_renewals_from, $date_no_renewals_to);

$txn_ids_no_renewals = $wpdb->get_col($txn_query_no_renewals);

if ( ! empty( $txn_ids_no_renewals ) ) {
  $count_no_renewals = 0;
  ?>
  <h3>Utenti che NON hanno rinnovato dal <?php echo $date_no_renewals_from; ?> al <?php echo $date_no_renewals_to; ?></h3>
  <table class="li-mp-analytics-table">
  <tr><th>Mail</th><th>User</th><th>Membership</th><th>Subscription</th><th>Subscription Created At</th><th>Transaction Expired At</th></tr>
  <?php
  foreach ( $txn_ids_no_renewals as $txn_id_no_renewals ) {
    $sub_no_renewals = new MeprSubscription($txn_id_no_renewals);
    $user_no_renewals = new MeprUser($sub_no_renewals->user_id);
    $product_no_renewals = new MeprProduct($sub_no_renewals->product_id);

    if($sub_no_renewals->txn_count > 1 && !$user_no_renewals->is_active()) {
        echo '<tr>';
        echo '<td>'.$user_no_renewals->user_email.'</td>';
        $csv_output_no_renewals .= $user_no_renewals->user_email . ", ";
        echo '<td>'. $user_no_renewals->user_login.'</td>';
        $csv_output_no_renewals .= $user_no_renewals->user_login . ", ";
        echo '<td>'.$product_no_renewals->post_title.'</td>';
        $csv_output_no_renewals .= $product_no_renewals->post_title . ", ";
        echo '<td>'.$sub_no_renewals->subscr_id.'</td>';
        $csv_output_no_renewals .= $sub_no_renewals->subscr_id . ", ";
        echo '<td>'.$sub_no_renewals->created_at.'</td>';
        $csv_output_no_renewals .= $sub_no_renewals->created_at . ", ";
        echo '<td>'.$sub_no_renewals->expires_at.'</td>';
        $csv_output_no_renewals .= $sub_no_renewals->expires_at . "\n";
        echo '</tr>';
        $count_no_renewals++;
    }
  }
  ?>
  <tr>
        <td colspan="5"><strong>Number of users: <?php echo $count_no_renewals; ?></strong></td>
        <td>
            <form name="export" action="/wp-content/themes/Newspaper-child/utility/mp-analytics-export-csv.php" method="post">
            <input type="submit" value="Export table to CSV">
            <input type="hidden" value="<?php echo $csv_hdr; ?>" name="csv_hdr">
            <input type="hidden" value="<?php echo $csv_output_no_renewals; ?>" name="csv_output_no_renewals">
            </form>
        </td>
  </tr>
  </table>
<?php } ?>