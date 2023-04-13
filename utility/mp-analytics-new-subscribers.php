<?php

$txn_query = $wpdb->prepare("SELECT first_txn_id
FROM {$wpdb->prefix}mepr_members
WHERE created_at > %s
AND created_at < %s", $date_from, $date_to);

$txn_ids = $wpdb->get_col($txn_query);

if ( ! empty( $txn_ids ) ) {
  $count = 0;
  ?>
  <h3>Nuovi abbonati dal <?php echo $date_from; ?> al <?php echo $date_to; ?></h3>
  <table class="li-mp-analytics-table">
  <tr><th>Mail</th><th>User</th><th>Membership</th><th>Subscription</th><th>Subscription Created At</th><th>Transaction Expired At</th></tr>
  <?php
  foreach ( $txn_ids as $txn_id ) {
    $txn = new MeprTransaction($txn_id);
    $user = new MeprUser($txn->user_id);
    $subscription = new MeprSubscription($txn->subscription_id);
    $product = new MeprProduct($txn->product_id);

    if($txn->status = 'complete' && $txn->subscription_id > 0 && $txn->user_id != 0) {
      echo '<tr>';
      echo '<td>'.$user->user_email.'</td>';
      $csv_output .= $user->user_email . ", ";
      echo '<td>'. $user->user_login.'</td>';
      $csv_output .= $user->user_login . ", ";
      echo '<td>'.$product->post_title.'</td>';
      $csv_output .= $product->post_title . ", ";
      echo '<td>'.$subscription->subscr_id.'</td>';
      $csv_output .= $subscription->subscr_id . ", ";
      echo '<td>'.$subscription->created_at.'</td>';
      $csv_output .= $subscription->created_at . ", ";
      echo '<td>'.$subscription->expires_at.'</td>';
      $csv_output .= $subscription->expires_at . "\n";
      echo '</tr>';
      $count++;
    }
  }
  ?>
  <tr>
      <td colspan="5"><strong>Number of users: <?php echo $count; ?></strong></td>
      <td>
        <form name="export" action="/wp-content/themes/Newspaper-child/utility/mp-analytics-export-csv.php" method="post">
        <input type="submit" value="Export table to CSV">
        <input type="hidden" value="<?php echo $csv_hdr; ?>" name="csv_hdr">
        <input type="hidden" value="<?php echo $csv_output; ?>" name="csv_output">
      </form>
      </td>
  </tr>
  </table>
                    
<?php } ?>