<?php

namespace PMP_CRP;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Pmp_Crp
 * @subpackage Pmp_Crp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pmp_Crp
 * @subpackage Pmp_Crp/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Admin
{

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version)
  {

    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  public function add_crp_setting($level)
  {
    if (!empty($level->id)) :
      $key = "pmp_crp_" . $level->id;
      $setup = wp_parse_args(
        get_option($key),
        [
          'maximum_cashback_gained' => 0,
          'maximum_cashback_used' => 0
        ]
      );
    endif;

    require_once PMP_CRP_PLUGIN_DIR . 'admin/partials/setting.php';
  }

  /**
   * Save cashback setting
   * Hooked via action pmpro_save_membership_level, 10, 2
   * @since   1.0.0
   * @param   int $level_id
   * @return  void
   */
  public function save_crp_setting($member_id)
  {
    if (
      isset($_POST['maximum_cashback_gained']) &&
      isset($_POST['maximum_cashback_used'])
    ) :
      $key = "pmp_crp_" . $member_id;

      update_option($key, [
        'maximum_cashback_gained' => floatval($_POST['maximum_cashback_gained']),
        'maximum_cashback_used' => floatval($_POST['maximum_cashback_used'])
      ]);

    endif;
  }

  /**
   * Add cashback data to current user reward point
   * Hooked via action woocommerce_order_status_completed, 999, 1
   * @since   1.0.0
   * @param   int $order_id
   */
  public function add_cashback_data($order_id)
  {
    $order = wc_get_order($order_id);

    $user_id = $order->get_user_id();
    $cashback_value = $order->get_meta(PMP_CRP_CASHBACK_ORDER_META);

    if (!$cashback_value)
      return;

    $get_points = get_user_meta($user_id, 'wps_wpr_points', true);

    /*Total Point of the order*/
    $total_points = intval($cashback_value + $get_points);

    $data = array(
      'wps_par_order_id' => $order_id,
    );

    /*Update points details in woocommerce*/
    $this->wps_wpr_update_points_details($user_id, 'pro_conversion_points', $cashback_value, $data);

    /*update users totoal points*/
    update_user_meta($user_id, 'wps_wpr_points', $total_points);

    /*update that user has get the rewards points*/
    update_post_meta($order_id, "$order_id#item_conversion_id", 'set');
  }


  /**
   * Update points details in the public section.
   *
   * @name wps_wpr_update_points_details
   * @since 1.0.0
   * @author WP Swings <webmaster@wpswings.com>
   * @link https://www.wpswings.com/
   * @param int    $user_id  User id of the user.
   * @param string $type type of description.
   * @param int    $points  No. of points.
   * @param array  $data  Data of the points details.
   */
  public function wps_wpr_update_points_details($user_id, $type, $points, $data)
  {
    $today_date = date_i18n('Y-m-d h:i:sa');

    // Refund points per currency setting conversions.
    if ($points > 0 && 'pro_conversion_points' == $type) :

      $get_referral_detail = get_user_meta($user_id, 'points_details', true);

      if (isset($get_referral_detail[$type]) && !empty($get_referral_detail[$type])) :
        $custom_array = array(
          $type => $points,
          'date' => $today_date,
          'refered_order_id' => $data['wps_par_order_id'],
        );

        $get_referral_detail[$type][] = $custom_array;

      else :

        if (!is_array($get_referral_detail)) :
          $get_referral_detail = array();
        endif;

        $get_referral_detail[$type][] = array(
          $type => $points,
          'date' => $today_date,
          'refered_order_id' => $data['wps_par_order_id'],
        );

      endif;

      update_user_meta($user_id, 'points_details', $get_referral_detail);
    endif;
  }
}
