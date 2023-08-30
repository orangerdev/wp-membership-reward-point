<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Pmp_Crp
 * @subpackage Pmp_Crp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Pmp_Crp
 * @subpackage Pmp_Crp/includes
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Pmp_Crp
{

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Pmp_Crp_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    if (defined('PMP_CRP_VERSION')) {
      $this->version = PMP_CRP_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->plugin_name = 'pmp-crp';

    $this->load_dependencies();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Pmp_Crp_Loader. Orchestrates the hooks of the plugin.
   * - Pmp_Crp_i18n. Defines internationalization functionality.
   * - Pmp_Crp_Admin. Defines all hooks for the admin area.
   * - Pmp_Crp_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies()
  {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pmp-crp-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pmp-crp-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-pmp-crp-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-pmp-crp-public.php';

    $this->loader = new Pmp_Crp_Loader();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Pmp_Crp_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale()
  {

    $plugin_i18n = new Pmp_Crp_i18n();

    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {

    $admin = new PMP_CRP\Admin($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('pmpro_membership_level_before_content_settings', $admin, 'add_crp_setting');
    $this->loader->add_action('pmpro_save_membership_level', $admin, 'save_crp_setting');
    $this->loader->add_action('woocommerce_order_status_completed', $admin, 'add_cashback_data', 999, 1);
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks()
  {

    $public = new PMP_CRP\Front($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_styles');
    $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_scripts');
    $this->loader->add_action('woocommerce_new_order', $public, 'set_cashback_data', 999, 2);
    $this->loader->add_action('woocommerce_review_order_after_order_total', $public, 'display_apply_point', 900);
    $this->loader->add_action('woocommerce_review_order_after_order_total', $public, 'display_potential_cashback', 999);
    $this->loader->add_action('wp_ajax_pmp_crp_apply_point', $public, 'apply_point');
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name()
  {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    Pmp_Crp_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader()
  {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version()
  {
    return $this->version;
  }
}
