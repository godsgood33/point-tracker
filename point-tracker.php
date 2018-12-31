<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/godsgood33
 * @since             1.0
 * @package           Point_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       Point Tracker
 * Plugin URI:        https://github.com/godsgood33/point-tracker
 * Description:       Allow leaders to create challenges and let people track their points.
 * Version:           1.6
 * Author:            Ryan Prather
 * Author URI:        https://github.com/godsgood33
 * License:           Apache-2.0
 * License URI:       https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain:       point-tracker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die();
}

/**
 * Currently plugin version.
 * Start at version 2.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PT_VERSION', '1.6');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-point-tracker-activator.php
 */
function activate_point_tracker()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-point-tracker-activator.php';

    Point_Tracker_Activator::activate();
    Point_Tracker_Activator::install_tables();
    Point_Tracker_Activator::install_functions();
    Point_Tracker_Activator::create_views();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-point-tracker-deactivator.php
 */
function deactivate_point_tracker()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-point-tracker-deactivator.php';

    Point_Tracker_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_point_tracker');
register_deactivation_hook(__FILE__, 'deactivate_point_tracker');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-point-tracker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_point_tracker()
{
    $plugin = new Point_Tracker();
    $plugin->run();
}
run_point_tracker();
