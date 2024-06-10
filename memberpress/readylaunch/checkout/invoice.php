<?php if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );}

  $mepr_coupon_code = $coupon && isset($coupon->ID) ? $coupon->post_title : '';

  if($mepr_coupon_code || ( is_object($tmpsub) && $tmpsub->prorated_trial ) ){
    unset( $sub_price_str );
  }
?>

<div class="mp_wrapper mp_invoice">
  <?php if ( isset( $sub_price_str ) ) : ?>
  <div class="mp_price_str">
    <strong><?php _ex( 'Terms:', 'ui', 'memberpress' ); ?></strong> <?php echo $sub_price_str; ?>
    <?php include get_stylesheet_directory() . '/memberpress/custom-inclusion/readylaunch-checkout-invoice.php'; ?>
  </div>
  <div class="mp-spacer">&nbsp;</div>
  <?php endif; ?>
  <table class="mp-table">

    <tbody>
      <?php foreach ( $invoice['items'] as $item ) : ?>
      <tr>
        <td>
          <img src="<?php echo esc_url( MEPR_IMAGES_URL . '/checkout/product.png' ); ?>" />
        </td>
        <td>
          <p><?php echo str_replace(MeprProductsHelper::renewal_str($prd), '', $item['description']); ?></p>
          <?php if(isset($txn, $sub) && !$txn->is_one_time_payment() && $sub instanceof MeprSubscription && $sub->id > 0) : ?>
            <p class="desc"><?php echo MeprAppHelper::format_price_string($sub, $sub->price, true, $mepr_coupon_code); ?></p>
          <?php elseif(!(isset($txn) && $txn->txn_type == 'sub_account')) : ?>
            <p class="desc"><?php MeprProductsHelper::display_invoice( $prd, $mepr_coupon_code ); ?></p>
          <?php endif; ?>
        </td>
        <?php if ( $show_quantity ) : ?>
        <td><?php echo $item['quantity']; ?></td>
        <?php endif; ?>
        <td class="mp-currency-cell"><?php echo MeprAppHelper::format_currency( $item['amount'], true, false ); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if ( isset( $invoice['coupon'] ) && ! empty( $invoice['coupon'] ) && $invoice['coupon']['id'] != 0 ) : ?>
      <tr>
        <td></td>
        <td>
          <?php echo $invoice['coupon']['desc']; ?>
        </td>
        <?php if ( $show_quantity ) : ?>
        <td>&nbsp;</td>
        <?php endif; ?>
        <td class="mp-currency-cell">
          <?php if( '0' !== $invoice['coupon']['amount'] ) : ?>
            <?php echo MeprAppHelper::format_currency(MeprCouponsHelper::format_coupon_amount($invoice['coupon']['amount']), true, false); ?>
          <?php else: ?>
            &nbsp;
          <?php endif; ?>
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <?php if ( $invoice['tax']['amount'] > 0.00 || $invoice['tax']['percent'] > 0 ) : ?>
      <tr>
        <th></th>
        <?php if ( $show_quantity ) : ?>
        <th>&nbsp;</th>
        <?php endif; ?>
        <th class="bb"><?php _ex( 'Sub-Total', 'ui', 'memberpress' ); ?></th>
        <th class="mp-currency-cell bb"><?php echo MeprAppHelper::format_currency( $subtotal, true, false ); ?></th>
      </tr>
      <tr>
        <th></th>
        <?php if ( $show_quantity ) : ?>
        <th>&nbsp;</th>
        <?php endif; ?>
        <th class="mepr-tax-invoice">
          <?php echo MeprUtils::format_tax_percent_for_display( $invoice['tax']['percent'] ) . '% ' . $invoice['tax']['type']; ?>
        </th>
        <th class="mp-currency-cell">
          <?php echo MeprAppHelper::format_currency( $invoice['tax']['amount'], true, false ); ?></th>
      </tr>
      <?php endif; ?>
      <tr>
        <th></th>
        <?php if ( $show_quantity ) : ?>
        <th>&nbsp;</th>
        <?php endif; ?>
        <th class="bt"><?php _ex( 'Total', 'ui', 'memberpress' ); ?></th>
        <th class="mp-currency-cell bt total_cell"><?php echo MeprAppHelper::format_currency( $total, true, false ); ?>
        </th>
        <input type="hidden" name="mepr_stripe_txn_amount"
          value="<?php echo MeprUtils::format_stripe_currency( $total ); ?>" />
      </tr>
    </tfoot>
  </table>
</div>
