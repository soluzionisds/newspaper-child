# Newspaper child theme for Lindipendente.online

## MemberPress Utilities

### Gift purchased data
Unfortunately, we don't currently have an easy way to display who gifted the membership but you could obtain this information if you have access to your site's database. The first thing you'll want to do is copy the ID of the giftee's transaction by navigating to the Transactions page (Wp-Admin -> MemberPress -> Transactions) and looking under the "Id" column.

With the giftee's transaction ID copied, you'll want to access your site's database and run this SQL query:

```
SELECT * FROM wpor_mepr_transaction_meta WHERE transaction_id = 123;
```

You'll want to replace 123 with the ID of the transaction you copied. In the results of this query, there should be a row with the "meta_key" of "_gifter_txn". The "meta_value" column will contain the ID of the gifter's transaction, so you can look up the gifter by searching for that transaction by its ID on the Transactions page


### Select Active Users for certain period

```
SELECT count(DISTINCT user_id) FROM wpor_mepr_transactions WHERE status = 'complete' AND ( created_at <= '2023-01-02 00:00:00' AND expires_at >= '2023-01-02 00:00:00' OR expires_at = '0000-00-00 00:00:00' OR expires_at = NULL ) ;
```

With confirmed status, no created_at parameter (MemberPress support suggestion)

```
SELECT count(DISTINCT user_id) FROM wpor_mepr_transactions WHERE status IN('complete', 'confirmed') AND ( expires_at >= NOW() OR expires_at = '0000-00-00 00:00:00' OR expires_at = NULL );
```


### Expiration date Subscription Shortcode

Print the date when subscription expire

```
//Shortcode Example: [mepr-sub-expiration membership='123']
function mepr_sub_expiration_shortcode($atts = [], $content = null, $tag = '') {
  $sub_expire_html = '';

  if($atts['membership'] && is_numeric($atts['membership'])) {
    $date_str = MeprUser::get_user_product_expires_at_date(get_current_user_id(), $atts['membership']);

		if ($date_str) {
	      $date = date_create($date_str);
	      $sub_expire_html = "<div>Expires: " . date_format($date,"Y/m/d") . "</div>";
		}
  }

  return $sub_expire_html;
}
add_shortcode('mepr-sub-expiration', 'mepr_sub_expiration_shortcode');
```


### Coupon Usage

Find coupon usage number shown in the coupon page: *You can find the `_mepr_coupons_usage_count` record in the `wpor_postmeta` table based on `post_id` column, which is coupon ID, and update meta_value with correct value.*
