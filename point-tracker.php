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
 * @since             1.0.0
 * @package           Point_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       Point Tracker
 * Plugin URI:        https://github.com/godsgood33/point-tracker
 * Description:       Allow network marketing leaders to create challenges and let people track their points.
 * Version:           1.0.0
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
define('PT_PLUGIN_NAME_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-point-tracker-activator.php
 */
function activate_point_tracker()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-point-tracker-activator.php';

    $site_url = get_site_url();

    $the_page = get_page_by_title("Challenge");
    if (! $the_page->ID) {
        // create page with template
        $post_id = wp_insert_post(array(
            'post_title' => 'Challenge',
            'post_content' => "[challenge_page]",
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
            'guid' => "{$site_url}/index.php/challenge/"
        ));
    } else {
        // make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page->post_content = "[challenge_page]";
        $the_page->guid = "{$site_url}/index.php/challenge/";
        $post_id = wp_update_post($the_page);
    }

    if (! $post_id) {
        die("Failed to save Challenge page");
    }

    $the_page = get_page_by_title("Challenge List");
    if (! $the_page->ID) {
        $post_id = wp_insert_post(array(
            'post_title' => 'Challenge List',
            'post_content' => '[challenge_list]',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
            'guid' => "{$site_url}/index.php/challenge-list/"
        ));
    } else {
        $the_page->post_status = 'publish';
        $the_page->post_content = '[challenge_list]';
        $the_page->guid = "{$site_url}/index.php/challenge-list/";
        $post_id = wp_update_post($the_page);
    }

    if (! $post_id) {
        die("Failed to save Challenge List page");
    }

    $the_page = get_page_by_title("My Activity");
    if (! $the_page->ID) {
        $post_id = wp_insert_post(array(
            'post_title' => 'My Activity',
            'post_content' => '[my_activity]',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
            'guid' => "{$site_url}/index.php/my-activity/"
        ));
    } else {
        $the_page->post_status = 'publish';
        $the_page->post_content = '[my_activity]';
        $the_page->guid = "{$site_url}/index.php/my-activity/";
        $post_id = wp_update_post($the_page);
    }

    if (! $post_id) {
        die("Failed to save My Activity page");
    }

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
