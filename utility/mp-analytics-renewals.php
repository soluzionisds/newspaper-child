<?php

///////////////////////////////////////
// Renewals from
//
$txn_query_renewals = $wpdb->prepare("SELECT subscription_id
FROM {$wpdb->prefix}mepr_transactions
WHERE created_at > %s
AND created_at < %s
AND status = %s
AND txn_type = %s
AND subscription_id > 0
AND user_id != 0
AND (expires_at > %s OR expires_at = %s)", $date_no_renewals_from, $date_no_renewals_to, 'complete', 'payment', $date_no_renewals_to, '0000-00-00 00:00:00');

$txn_ids_renewals = $wpdb->get_col($txn_query_renewals);

if ( ! empty( $txn_ids_renewals ) ) {
  $count_renewals = 0;
  ?>
  <h3>Utenti che hanno rinnovato dal <?php echo $date_no_renewals_from; ?> al <?php echo $date_no_renewals_to; ?></h3>
  <table class="li-mp-analytics-table">
  <tr><th>Mail</th><th>User</th><th>Membership</th><th>Subscription</th><th>Subscription Created At</th><th>Transaction Expired At</th></tr>
  <?php
  foreach ( $txn_ids_renewals as $txn_id_renewals ) {
    $sub_renewals = new MeprSubscription($txn_id_renewals);
    $user_renewals = new MeprUser($sub_renewals->user_id);
    $product_renewals = new MeprProduct($sub_renewals->product_id);

    if($sub_renewals->txn_count > 1) {
      echo '<tr>';
      echo '<td>'.$user_renewals->user_email.'</td>';
      $csv_output_renewals .= $user_renewals->user_email . ", ";
      echo '<td>'. $user_renewals->user_login.'</td>';
      $csv_output_renewals .= $user_renewals->user_login . ", ";
      echo '<td>'.$product_renewals->post_title.'</td>';
      $csv_output_renewals .= $product_renewals->post_title . ", ";
      echo '<td>'.$sub_renewals->subscr_id.'</td>';
      $csv_output_renewals .= $sub_renewals->subscr_id . ", ";
      echo '<td>'.$sub_renewals->created_at.'</td>';
      $csv_output_renewals .= $sub_renewals->created_at . ", ";
      echo '<td>'.$sub_renewals->expires_at.'</td>';
      $csv_output_renewals .= $sub_renewals->expires_at . "\n";
      echo '</tr>';
      $count_renewals++;
    }
  }
  ?>
  <tr>
      <td colspan="5"><strong>Number of users: <?php echo $count_renewals; ?></strong></td>
      <td>
        <form name="export" action="/wp-content/themes/Newspaper-child/utility/mp-analytics-export-csv.php" method="post">
        <input type="submit" value="Export table to CSV">
        <input type="hidden" value="<?php echo $csv_hdr; ?>" name="csv_hdr">
        <input type="hidden" value="<?php echo $csv_output_renewals; ?>" name="csv_output_renewals">
      </form>
      </td>
  </tr>
  </table>
                    
<?php } ?>