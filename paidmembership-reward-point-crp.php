<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ridwan-arifandi.com
 * @since             1.0.0
 * @package           Pmp_Crp
 *
 * @wordpress-plugin
 * Plugin Name:       PaidMembershipPro - Custom Reward Point
 * Plugin URI:        https://ridwan-arifandi.com
 * Description:       Add maximum gained reward point and set maximum used reward point in checkout
 * Version:           1.0.1
 * Author:            Ridwan Arifandi
 * Author URI:        https://ridwan-arifandi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pmp-crp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PMP_CRP_VERSION', '1.0.0');
define('PMP_CRP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PMP_CRP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PMP_CRP_CASHBACK_ORDER_META', '_pmp_crp_cashback_gained');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pmp-crp-activator.php
 */
function activate_pmp_crp()
{
  require_once plugin_dir_path(__FILE__) . 'includes/class-pmp-crp-activator.php';
  Pmp_Crp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pmp-crp-deactivator.php
 */
function deactivate_pmp_crp()
{
  require_once plugin_dir_path(__FILE__) . 'includes/class-pmp-crp-deactivator.php';
  Pmp_Crp_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_pmp_crp');
register_deactivation_hook(__FILE__, 'deactivate_pmp_crp');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pmp-crp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pmp_crp()
{

  $plugin = new Pmp_Crp();
  $plugin->run();
}
run_pmp_crp();
