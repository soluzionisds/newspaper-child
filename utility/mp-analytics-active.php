<?php

$date_exp_from = '2024-01-25 00:00:00';
$txn_query = $wpdb->prepare("SELECT DISTINCT subscription_id
FROM {$wpdb->prefix}mepr_transactions
WHERE txn_type = %s
AND status = %s
AND user_id != 0
AND subscription_id != 0
AND (created_at <= %s AND expires_at >= %s AND expires_at <= %s)" , 'payment', 'complete', $date_from, $date_exp_from, $date_to);

$txn_ids = $wpdb->get_col($txn_query);

if ( ! empty( $txn_ids ) ) {
  $count = 0;
  $sum_total = 0;
  ?>
  <h3>Utenti attivi creati prima del <?php echo $date_from; ?> e che scadono prima del <?php echo $date_to; ?> (per abbonamenti che scadono dopo il <?php echo $date_exp_from; ?>)</h3>
  <table class="li-mp-analytics-table">
  <tr><th>Mail</th><th>User</th><th>Membership</th><th>Subscription</th><th>Auto Rebill</th><th>Subscription Created At</th><th>Transaction Expired At</th><th>Transaction Amount</th></tr>
  <?php
  foreach ( $txn_ids as $txn_id ) {
    $subscription = new MeprSubscription($txn_id);
    $user = new MeprUser($subscription->user_id);
    $product = new MeprProduct($subscription->product_id);
    $autorebill = $subscription->status;

    if($user->is_active()) {
      echo '<tr>';
      echo '<td>'.$user->user_email.'</td>';
      $csv_output .= $user->user_email . ", ";
      echo '<td>'.$user->user_login.'</td>';
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
      $csv_output .= $subscription->expires_at . ", ";
      echo '<td>&euro;'.$subscription->total.'</td>';
      $csv_output .= $subscription->total . "\n";
      echo '</tr>';
      $count++;
      $sum_total+= $subscription->total;
    }
  }
  ?>
  <tr>
    <td colspan="7">Number of Users: <strong><?php echo $count; ?></strong> | Total of Transactions: <strong>&euro;<?php echo $sum_total; ?></strong></td>
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