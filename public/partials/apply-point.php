<?php

/**
 * Display apply point
 * 
 * @var   float $cashback_used
 * @var   float $maximum_cashback_gained
 */


if (!defined('ABSPATH')) {
  exit; // Direct access not allowed.
}

?>

<tr class="pmp-crp-apply-point-notification" style="margin-top: 2rem;">
  <th>
    <button type="button" id="pmp-crp-apply-point">
      <?php esc_html_e("Apply Point", "pmp-crp"); ?>
    </button>
  <td>
    <?php echo wc_price($cashback_used); ?>
  </td>
</tr>
<tr class="pmp-crp-apply-point-notification">
  <td colspan="2">
    <p>
      <?php
      printf(
        __("You have %s point(s). <br />Based on your membership level, maximum point that you can use is %.15g %%", "pmp-crp"),
        wc_price($user_points),
        $maximum_cashback_used
      ); ?>
    </p>
  </td>
</tr>