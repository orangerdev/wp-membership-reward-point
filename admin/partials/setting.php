<div id="cashback-settings" class="pmpro_section" data-visibility="hidden" data-activated="true">
  <div class="pmpro_section_toggle">
    <button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
      <span class="dashicons dashicons-arrow-up-alt2"></span>
      <?php esc_html_e('cashback Settings', 'pmp-crp'); ?>
    </button>
  </div>
  <div class="pmpro_section_inside">
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row" valign="top">
            <label for="maximum_cashback_gained">
              <?php esc_html_e('Maximum Cashback Gained (in %)', 'pmp-crp'); ?>
            </label>
          </th>
          <td>
            <input name="maximum_cashback_gained" type="text" value="<?php echo $setup['maximum_cashback_gained']; ?>" class="regular-text" />
            <p class="description"><?php esc_html_e('Maximum cashback that member can gained from an order.', 'pmp-crp'); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row" valign="top">
            <label for="maximum_cashback_used">
              <?php esc_html_e('Maximum Cashback Used (in %)', 'pmp-crp'); ?>
            </label>
          </th>
          <td>
            <input name="maximum_cashback_used" type="text" value="<?php echo $setup['maximum_cashback_used']; ?>" class="regular-text" />
            <p class="description"><?php esc_html_e('Maximum cashback that member can use for checkout.', 'pmp-crp'); ?></p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>