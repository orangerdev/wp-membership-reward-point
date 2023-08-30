<?php

/**
 * Display potential cashback
 * 
 * @var   float $cashback_gained
 */


if (!defined('ABSPATH')) {
  exit; // Direct access not allowed.
}

?>

<tr class="pmp-crp-potential-cashback-notification" style="margin-top: 2rem;">
  <th><?php esc_html_e("Potential Cashback", "pmp-crp"); ?></th>
  <td>
    <?php echo wc_price($cashback_gained); ?>
  </td>
</tr>
<tr class="pmp-crp-potential-cashback-notification">
  <td colspan="2">
    <p class="pmp-crp-potential-cashback-notification-message">
      <?php esc_html_e("You will get this cashback after completing the order.", "pmp-crp"); ?>
    </p>
  </td>
</tr>