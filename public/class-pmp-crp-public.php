<?php

namespace PMP_CRP;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Pmp_Crp
 * @subpackage Pmp_Crp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Pmp_Crp
 * @subpackage Pmp_Crp/public
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Front
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
   * Cashback setup for current user
   * 
   * @since   1.0.0
   * @access  private
   * @var     array | false
   */
  private $cashback_setup = false;

  /**
   * Used point
   * 
   * @since   1.0.0
   * @access  private
   * @var     bool | float
   */
  private $used_point = false;

  private $cart_key = "cart discount";

  private $session_key = 'wps_cart_points';

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version)
  {

    $this->plugin_name = $plugin_name;
    $this->version = $version;
  }

  /**
   * Get current user membership ID
   * @since   1.0.0
   * @param   int   $user_id  User ID
   * @return  int
   */
  protected function get_user_membership_id($user_id = 0)
  {
    $user_id = $user_id ?? get_current_user_id();

    global $wpdb;

    $user_id = get_current_user_id();


    $membership_id = $wpdb->get_var(
      $wpdb->prepare(
        "SELECT membership_id FROM {$wpdb->prefix}pmpro_memberships_users WHERE user_id = %d AND status='active' ",
        $user_id
      )
    );

    return absint($membership_id);
  }

  /**
   * Get current user membership's cashback setup
   * @since   1.0.0
   * @param   int   $user_id  User ID
   * @return  false | array
   */
  protected function get_membership_cashback_setup($user_id = 0)
  {
    if ($user_id === 0)
      return false;


    if ($this->cashback_setup)
      return $this->cashback_setup;

    $membership_id = $this->get_user_membership_id($user_id);

    if (!$membership_id || $membership_id === 0)
      return false;

    $cashback_setup = get_option('pmp_crp_' . $membership_id, false);

    if (!$cashback_setup)
      return false;

    return wp_parse_args($cashback_setup, [
      'maximum_cashback_gained' => 0,
      'maximum_cashback_used' => 0
    ]);
  }

  /**
   * Get cashback total
   * @since   1.0.0
   * @param   float   $maximum_cashback_gained  Maximum cashback gained
   * @param   float   $total  Total
   * @return  float
   */
  protected function get_cashback_total($maximum_cashback_gained, $total)
  {
    return floor($total * $maximum_cashback_gained / 100);
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   * Hooked via wp_enqueue_scripts, priority 999
   * @since   1.0.0
   * @return  void
   */
  public function enqueue_styles()
  {
    if (is_checkout()) :
      wp_enqueue_style($this->plugin_name, PMP_CRP_PLUGIN_URL . 'public/css/checkout.css', [], $this->version, 'all');
    endif;
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   * Hooked via wp_enqueue_scripts, priority 999
   * @since   1.0.0
   * @return  void
   */
  public function enqueue_scripts()
  {

    if (is_checkout()) :
      wp_enqueue_script($this->plugin_name, PMP_CRP_PLUGIN_URL . 'public/js/checkout.js', ['jquery'], $this->version, true);

      wp_localize_script(
        $this->plugin_name,
        'pmp_crp',
        [
          'ajax_url' => admin_url('admin-ajax.php'),
          'nonce' => wp_create_nonce('pmp-crp-verify-nonce'),
        ]
      );
    endif;
  }

  /**
   * Set cashback data to order meta
   * Hooked via woocommerce_new_order, priority 999
   * @since   1.0.0
   * @param   int       $order_id  Order ID
   * @param   WC_Order  $order  Order object
   */
  public function set_cashback_data($order_id, $order)
  {
    $cashback_setup = $this->get_membership_cashback_setup($order->get_user_id());

    if (!$cashback_setup)
      return;

    if ($cashback_setup['maximum_cashback_gained'] <= 0)
      return;

    $cart_value = $order->get_total() - $order->get_total_shipping();

    $cashback_gained = $this->get_cashback_total(
      $cashback_setup['maximum_cashback_gained'],
      $cart_value
    );

    $order->update_meta_data(PMP_CRP_CASHBACK_ORDER_META, $cashback_gained);
    $order->save();
  }

  /**
   * Display potential cashback
   * Hooked via woocommerce_review_order_after_order_total, priority 999
   * @since   1.0.0
   * @return  void
   */
  public function display_potential_cashback()
  {
    $user_id = get_current_user_id();
    $cashback_setup = $this->get_membership_cashback_setup($user_id);

    if (!$cashback_setup)
      return;

    $maximum_cashback_gained = $cashback_setup['maximum_cashback_gained'];

    if ($maximum_cashback_gained <= 0)
      return;

    $cashback_gained = $this->get_cashback_total($maximum_cashback_gained, WC()->cart->get_cart_contents_total());

    require_once(PMP_CRP_PLUGIN_DIR . 'public/partials/potential-cashback.php');
  }

  /**
   * Get maximum apply point
   * @since   1.0.0
   * @return  float
   */
  protected function get_max_apply_point()
  {
    $user_id = get_current_user_id();
    $cashback_setup = $this->get_membership_cashback_setup($user_id);

    if (!$cashback_setup)
      return 0;

    $maximum_cashback_used = $cashback_setup['maximum_cashback_used'];

    if ($maximum_cashback_used <= 0)
      return 0;

    $user_points = floatval(get_user_meta($user_id, 'wps_wpr_points', true));

    if ($user_points < 0)
      return 0;

    $max_apply_point = $this->get_cashback_total($maximum_cashback_used, WC()->cart->get_cart_contents_total() + WC()->cart->get_shipping_total());
    $max_apply_point = $max_apply_point > $user_points ? $user_points : $max_apply_point;

    $max_apply_point = floor($max_apply_point / 1000) * 1000;

    return floatval($max_apply_point);
  }

  /**
   * Display apply point
   * Hooked via woocommerce_review_order_after_order_total, priority 900
   * @since   1.0.0
   * @return  void
   */
  public function display_apply_point()
  {
    if (WC()->session->__isset($this->session_key)) :
      return;
    endif;

    $user_id = get_current_user_id();

    $user_points = floatval(get_user_meta($user_id, 'wps_wpr_points', true));

    if ($user_points <= 0)
      return;

    $cashback_setup = $this->get_membership_cashback_setup($user_id);

    $maximum_cashback_used = $cashback_setup['maximum_cashback_used'];

    if ($maximum_cashback_used <= 0)
      return;

    $cashback_used = $this->get_max_apply_point();

    require_once(PMP_CRP_PLUGIN_DIR . 'public/partials/apply-point.php');
  }

  /**
   * Apply point
   * Hooked via wp_ajax_pmp_crp_apply_point
   * @since   1.0.0
   * @return  array
   */
  public function apply_point()
  {
    check_ajax_referer('pmp-crp-verify-nonce', 'nonce');

    $response = [
      'result' => false,
      'message' => __('Can not redeem!', 'pmp-crp'),
    ];


    if (isset($_POST)) :

      // Get data via ajax.
      $user_id         = get_current_user_id();

      $cashback_setup = $this->get_membership_cashback_setup($user_id);

      if (!$cashback_setup)
        return $response;

      $maximum_cashback_used = $cashback_setup['maximum_cashback_used'];

      if ($maximum_cashback_used <= 0)
        return $response;

      $wps_cart_points = $this->get_cashback_total($maximum_cashback_used, WC()->cart->get_cart_contents_total() + WC()->cart->get_shipping_total());
      $get_points      = floatval(get_user_meta($user_id, 'wps_wpr_points', true));
      $get_points      = !empty($get_points) && $get_points > 0 ? $get_points : 0;

      do_action(
        "inspect",
        [
          'apply_point',
          [
            'wps_cart_points' => $wps_cart_points,
            'get_points' => $get_points,
            'session' => WC()->session,
            'cart' => WC()->cart
          ]
        ]
      );

      // Applied points here.
      if ($get_points > 0 && $wps_cart_points > 0) :
        if ($get_points >= $wps_cart_points) :
          WC()->session->set($this->session_key, $wps_cart_points);
          $response['result']  = true;
          $response['message'] = esc_html__('Custom Point has been applied Successfully!', 'points-and-rewards-for-woocommerce');
        else :

          $response['result']  = false;
          $response['message'] = __('Please enter some valid points!', 'points-and-rewards-for-woocommerce');
        endif;
      endif;

    endif;
    wp_send_json($response);
  }
}
