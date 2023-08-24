<?php

$txn_query = $wpdb->prepare("SELECT DISTINCT subscription_id
FROM {$wpdb->prefix}mepr_transactions
WHERE txn_type = %s
AND subscription_id > 0
AND user_id != 0
AND (expires_at >= %s AND expires_at <= %s)" , 'payment', $date_from, $date_to);

$txn_ids = $wpdb->get_col($txn_query);

if ( ! empty( $txn_ids ) ) {
  $count = 0;
  ?>
  <h3>Utenti che NON hanno rinnovato dal <?php echo $date_from; ?> al <?php echo $date_to; ?></h3>
  <table class="li-mp-analytics-table">
  <tr><th>Mail</th><th>User</th><th>Membership</th><th>Subscription</th><th>Auto Rebill</th><th>Subscription Created At</th><th>Transaction Expired At</th></tr>
  <?php
  foreach ( $txn_ids as $txn_id ) {
    $subscription = new MeprSubscription($txn_id);
    $user = new MeprUser($subscription->user_id);
    $product = new MeprProduct($subscription->product_id);
    $autorebill = $subscription->status;

    if($subscription->txn_count > 1 && !$user->is_active()) {
        echo '<tr>';
        echo '<td>'.$user->user_email.'</td>';
        $csv_output .= $user->user_email . ", ";
        echo '<td>'. $user->user_login.'</td>';
        $csv_output .= $user->user_login . ", ";
        echo '<td>'.$product->post_title.'</td>';
        $csv_output .= $product->post_title . ", ";
        echo '<td>'.$subscription->subscr_id.'</td>';
        $csv_output .= $subscription->subscr_id . ", ";
        echo '<td>'.$autorebill.'</td>';
        $csv_output .= $autorebill . ", ";
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
        <td colspan="6"><strong>Number of users: <?php echo $count; ?></strong></td>
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