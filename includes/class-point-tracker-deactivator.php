<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/godsgood33
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 * @package Point_Tracker
 * @subpackage Point_Tracker/includes
 * @author Ryan Prather <godsgood33@gmail.com>
 */
class Point_Tracker_Deactivator
{

    /**
     * Function called when deactivating the plugin
     *
     * @since 1.0.0
     */
    public static function deactivate()
    {
        global $wpdb;

        delete_option('pt-require-login');
        delete_option('pt-email-new-participants');
        delete_option('pt-admin-summary-email');

        $the_page = get_page_by_title("Challenge");
        wp_update_post([
            'ID' => $the_page->ID,
            'post_status' => 'draft'
        ]);

        $the_page = get_page_by_title("Challenge List");
        wp_update_post([
            'ID' => $the_page->ID,
            'post_status' => 'draft'
        ]);

        $the_page = get_page_by_title("My Activity");
        wp_update_post([
            'ID' => $the_page->ID,
            'post_status' => 'draft'
        ]);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pt_challenges");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pt_activities");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pt_participants");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pt_log");
            $wpdb->query("DROP VIEW IF EXISTS {$wpdb->prefix}point_totals");
            $wpdb->query("DROP VIEW IF EXISTS {$wpdb->prefix}leader_board");
            $wpdb->query("DROP VIEW IF EXISTS {$wpdb->prefix}user_activity");
            $wpdb->query("DROP FUNCTION IF EXISTS get_pt_activity_id");
            $wpdb->query("DROP FUNCTION IF EXISTS get_pt_challenge_id");
            $wpdb->query("DROP FUNCTION IF EXISTS get_pt_user_id");
        }
    }
}
