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
        if(!remove_all_actions('save_post')) {
            wp_die("Could not remove save_post actions");
        }

        delete_option('pt-require-login');

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
    }
}
