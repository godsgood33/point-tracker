<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/godsgood33
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package Point_Tracker
 * @subpackage Point_Tracker/includes
 * @author Ryan Prather <godsgood33@gmail.com>
 */
class Point_Tracker_Activator
{

    /**
     * Activation method
     *
     * @since 1.0.0
     */
    public static function activate()
    {
        if(is_null(get_option('pt-require-login', null))) {
            update_option('pt-require-login', 0);
        }

        if(!remove_all_actions('save_post')) {
            wp_die("Could not remove save_post actions");
        }
        
        $wp_version = get_bloginfo('version');
        $gutenberg_installed = false;
        if(is_plugin_active('gutenberg')) {
            $gutenberg_installed = true;
        } elseif(version_compare($wp_version, '5.0', ">=")) {
            $gutenberg_installed = true;
        }
        
        if(is_plugin_active('classic-editor')) {
            $gutenberg_installed = false;
        }

        $site_url = get_site_url();

        $the_page = get_page_by_title("Challenge");
        if (! $the_page->ID) {
            // create page with template
            $post_id = wp_insert_post([
                'post_title' => 'Challenge',
                'post_content' => ($gutenberg_installed ? "<!-- wp:shortcode --> " : "") . "[challenge]" . ($gutenberg_installed ? " <!-- /wp:shortcode -->" : ""),
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'page',
                'guid' => "{$site_url}/index.php/challenge/",
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => 'challenge'
            ]);
        } else {
            // make sure the page is not trashed...
            $the_page->post_status = 'publish';
            $the_page->post_content = ($gutenberg_installed ? "<!-- wp:shortcode --> " : "") . "[challenge]" . ($gutenberg_installed ? " <!-- /wp:shortcode -->" : "");
            $the_page->guid = "{$site_url}/index.php/challenge/";
            $post_id = wp_update_post($the_page);
        }

        if (! $post_id) {
            wp_die("Failed to save Challenge page");
        }

        $the_page = get_page_by_title("Challenge List");
        if (! $the_page->ID) {
            $post_id = wp_insert_post([
                'post_title' => 'Challenge List',
                'post_content' => ($gutenberg_installed ? "<!-- wp:shortcode --> " : "") . "[challenge_list]" . ($gutenberg_installed ? " <!-- /wp:shortcode -->" : ""),
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'page',
                'guid' => "{$site_url}/index.php/challenge-list/",
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => 'challenge-list'
            ]);
        } else {
            $the_page->post_status = 'publish';
            $the_page->post_content = ($gutenberg_installed ? "<!-- wp:shortcode --> " : "") . "[challenge_list]" . ($gutenberg_installed ? " <!-- /wp:shortcode -->" : "");
            $the_page->guid = "{$site_url}/index.php/challenge-list/";
            $post_id = wp_update_post($the_page);
        }

        if (! $post_id) {
            wp_die("Failed to save Challenge List page");
        }

        $the_page = get_page_by_title("My Activity");
        if (! $the_page->ID) {
            $post_id = wp_insert_post([
                'post_title' => 'My Activity',
                'post_content' => ($gutenberg_installed ? "<!-- wp:shortcode --> " : "") . "[my_activity]" . ($gutenberg_installed ? " <!-- /wp:shortcode -->" : ""),
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'page',
                'guid' => "{$site_url}/index.php/my-activity/",
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => 'my-activity'
            ]);
        } else {
            $the_page->post_status = 'publish';
            $the_page->post_content = ($gutenberg_installed ? "<!-- wp:shortcode --> " : "") . "[my_activity]" . ($gutenberg_installed ? " <!-- /wp:shortcode -->" : "");
            $the_page->guid = "{$site_url}/index.php/my-activity/";
            $post_id = wp_update_post($the_page);
        }

        if (! $post_id) {
            wp_die("Failed to save My Activity page");
        }
    }

    /**
     * Function to create all necessary tables
     *
     * @global wpdb $wpdb
     *
     * @since 1.0.0
     */
    public static function install_tables()
    {
        global $wpdb;
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

        $query = "CREATE TABLE `{$wpdb->prefix}pt_challenges` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `start` date DEFAULT NULL,
    `end` date DEFAULT NULL,
    `short_link` varchar(45) DEFAULT NULL,
    `approval` tinyint(1) DEFAULT '0',
    `desc` mediumtext,
    PRIMARY KEY (`id`)
)";
        dbDelta($query);
        $er = $wpdb->last_error;
        $res = $wpdb->get_row("SHOW TABLES LIKE '{$wpdb->prefix}pt_challenges'", ARRAY_N);
        if (! is_array($res) || ! count($res)) {
            wp_die("Failed to create table {$wpdb->prefix}pt_challenges<br />{$er}", "Database Failure");
        }

        $query = "CREATE TABLE `{$wpdb->prefix}pt_activities` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `challenge_id` int(11) NOT NULL,
    `name` varchar(60) NOT NULL,
    `points` decimal(4,1) DEFAULT NULL,
    `type` enum('checkbox','radio','text','number','long-text') NOT NULL,
    `label` mediumtext DEFAULT NULL,
    `question` varchar(100) DEFAULT NULL,
    `min` int(11) DEFAULT '0',
    `max` int(11) DEFAULT '0',
    `chal_max` int(11) DEFAULT '0',
    `desc` mediumtext,
    `order` tinyint(2) DEFAULT '0',
    `hidden` tinyint(1) DEFAULT '0',
    `group` varchar(20) DEFAULT NULL,
    PRIMARY KEY (`id`)
)";
        dbDelta($query);
        $er = $wpdb->last_error;
        $res = $wpdb->get_row("SHOW TABLES LIKE '{$wpdb->prefix}pt_activities'", ARRAY_N);
        if (! is_array($res) || ! count($res)) {
            wp_die("Failed to create table {$wpdb->prefix}pt_activities<br />{$er}", "Database Failure");
        }

        $query = "CREATE TABLE `{$wpdb->prefix}pt_participants` (
    `challenge_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `email` varchar(64) NOT NULL,
    `member_id` int(11) NOT NULL,
    `name` varchar(45) NOT NULL,
    `approved` tinyint(1) NOT NULL DEFAULT '0',
    `date_joined` date DEFAULT NULL,
    `date_approved` date DEFAULT NULL,
    PRIMARY KEY (`challenge_id`,`user_id`)
)";
        dbDelta($query);
        $er = $wpdb->last_error;
        $res = $wpdb->get_row("SHOW TABLES LIKE '{$wpdb->prefix}pt_participants'", ARRAY_N);
        if (! is_array($res) || ! count($res)) {
            wp_die("Failed to create table {$wpdb->prefix}pt_participants<br />{$er}", "Database Failure");
        }

        $query = "CREATE TABLE `{$wpdb->prefix}pt_log` (
    `user_id` int(11) NOT NULL,
    `activity_id` int(11) NOT NULL,
    `log_date` date NOT NULL,
    `log_time` time NOT NULL,
    `value` text NOT NULL DEFAULT '',
    PRIMARY KEY (`user_id`,`activity_id`,`log_date`)
)";
        dbDelta($query);
        $er = $wpdb->last_error;
        $res = $wpdb->get_row("SHOW TABLES LIKE '{$wpdb->prefix}pt_log'", ARRAY_N);
        if (! is_array($res) || ! count($res)) {
            wp_die("Failed to create table {$wpdb->prefix}pt_log<br />{$er}", 'Database Failure');
        }
    }

    /**
     * Function to create MySQL views
     *
     * @global wpdb $wpdb
     *
     * @since 1.0.0
     */
    public static function create_views()
    {
        global $wpdb;

        $wpdb->query("DROP VIEW IF EXISTS {$wpdb->prefix}leader_board");
        $query = "CREATE VIEW `{$wpdb->prefix}leader_board` AS
(SELECT
    `al`.`user_id` AS `user_id`,
    `al`.`activity_id` AS `activity_id`,
    `al`.`log_date` AS `log_date`,
    `al`.`log_time` AS `log_time`,
    `al`.`value` AS `value`,
    `ca`.`id` AS `id`,
    `ca`.`challenge_id` AS `challenge_id`,
    `ca`.`name` AS `activity_name`,
    `ca`.`points` AS `points`,
    `ca`.`type` AS `type`,
    `ca`.`label` AS `label`,
    `ca`.`question` AS `question`,
    `ca`.`min` AS `min`,
    `ca`.`max` AS `max`,
    `ca`.`chal_max` AS `chal_max`,
    `ca`.`desc` AS `desc`,
    `c`.`name` AS `challenge_name`,
    `cp`.`name` AS `participant_name`,
    `cp`.`member_id` AS `member_id`,
    `cp`.`email` AS `user_email`,
    IF((`ca`.`type` = 'number'),
        (`al`.`value` * `ca`.`points`),
        `ca`.`points`) AS `total_points`
FROM
    `{$wpdb->prefix}pt_log` `al`
    JOIN `{$wpdb->prefix}pt_activities` `ca` ON `ca`.`id` = `al`.`activity_id`
    JOIN `{$wpdb->prefix}pt_challenges` `c` ON `c`.`id` = `ca`.`challenge_id`
    JOIN `{$wpdb->prefix}pt_participants` `cp` ON `cp`.`challenge_id` = `c`.`id` AND `cp`.`user_id` = `al`.`user_id`
WHERE
    `c`.`id` = GET_PT_CHALLENGE_ID()
    AND
    `al`.`log_date` BETWEEN `c`.`start` AND `c`.`end`
    AND
    `ca`.`hidden` = 0
GROUP BY `ca`.`id` , `al`.`user_id` , `al`.`log_date`)";
        $wpdb->query($query);

        $wpdb->query("DROP VIEW IF EXISTS {$wpdb->prefix}point_totals");
        $query = "CREATE VIEW `{$wpdb->prefix}point_totals` AS
(SELECT
    `al`.`activity_id` AS `activity_id`,
    `al`.`user_id` AS `user_id`,
    `ca`.`id` AS `id`,
    `ca`.`challenge_id` AS `challenge_id`,
    `ca`.`name` AS `activity_name`,
    `ca`.`points` AS `points`,
    `ca`.`type` AS `type`,
    `ca`.`label` AS `label`,
    `ca`.`question` AS `question`,
    `ca`.`min` AS `min`,
    `ca`.`max` AS `max`,
    `ca`.`chal_max` AS `chal_max`,
    `ca`.`desc` AS `desc`,
    `c`.`name` AS `challenge_name`,
    `cp`.`email` AS `participant_email`,
    `cp`.`name` AS `participant_name`,
    `cp`.`member_id` AS `member_id`,
    IF((`ca`.`type` = 'number'),
        (`al`.`value` * `ca`.`points`),
        `ca`.`points`) AS `total_points`
FROM
    `{$wpdb->prefix}pt_log` `al`
    JOIN `{$wpdb->prefix}pt_activities` `ca` ON `ca`.`id` = `al`.`activity_id`
    JOIN `{$wpdb->prefix}pt_challenges` `c` ON `c`.`id` = `ca`.`challenge_id`
    JOIN `{$wpdb->prefix}pt_participants` `cp` ON `cp`.`challenge_id` = `c`.`id` AND `cp`.`user_id` = `al`.`user_id`
WHERE
    `al`.`activity_id` = GET_PT_ACTIVITY_ID()
    AND
    `ca`.`hidden` = 0
GROUP BY `ca`.`id` , `al`.`user_id` , `al`.`log_date`)";
        $wpdb->query($query);

        $wpdb->query("DROP VIEW IF EXISTS {$wpdb->prefix}user_activity");
        $query = "CREATE VIEW `{$wpdb->prefix}user_activity` AS
(SELECT
    `al`.`user_id` AS `user_id`,
    `al`.`activity_id` AS `activity_id`,
    `al`.`log_date` AS `log_date`,
    `al`.`log_time` AS `log_time`,
    `al`.`value` AS `value`,
    `ca`.`challenge_id` AS `challenge_id`,
    `ca`.`name` AS `activity_name`,
    `ca`.`points` AS `points`,
    `ca`.`type` AS `type`,
    `ca`.`label` AS `label`,
    `ca`.`question` AS `question`,
    `ca`.`min` AS `min`,
    `ca`.`max` AS `max`,
    `ca`.`chal_max` AS `chal_max`,
    `ca`.`desc` AS `desc`,
    `c`.`name` AS `challenge_name`,
    `cp`.`email` AS `participant_email`,
    `cp`.`name` AS `participant_name`,
    `cp`.`member_id` AS `member_id`,
    IF((`ca`.`type` = 'number'),
        (`al`.`value` * `ca`.`points`),
        `ca`.`points`) AS `total_points`
FROM
    `{$wpdb->prefix}pt_log` `al`
    JOIN `{$wpdb->prefix}pt_activities` `ca` ON `ca`.`id` = `al`.`activity_id`
    JOIN `{$wpdb->prefix}pt_challenges` `c` ON `c`.`id` = `ca`.`challenge_id`
    JOIN `{$wpdb->prefix}pt_participants` `cp` ON `cp`.`challenge_id` = `c`.`id` AND `cp`.`user_id` = `al`.`user_id`
WHERE
    `c`.`id` = GET_PT_CHALLENGE_ID()
    AND
    `al`.`user_id` = GET_PT_USER_ID()
    AND
    `al`.`log_date` BETWEEN `c`.`start` AND `c`.`end`
    AND
    `ca`.`hidden` = 0
GROUP BY `ca`.`id` , `al`.`user_id` , `al`.`log_date`)";
        $wpdb->query($query);
    }

    /**
     * Function to create MySQL functions
     *
     * @global wpdb $wpdb
     *
     * @since 1.0.0
     */
    public static function install_functions()
    {
        global $wpdb;

        $wpdb->query("DROP FUNCTION IF EXISTS get_pt_activity_id");
        $query = "CREATE FUNCTION `get_pt_activity_id`() RETURNS int(11)
      return @activity_id;";
        $wpdb->query($query);

        $wpdb->query("DROP FUNCTION IF EXISTS get_pt_challenge_id");
        $query = "CREATE FUNCTION `get_pt_challenge_id`() RETURNS int(11)
      return @challenge_id;";
        $wpdb->query($query);

        $wpdb->query("DROP FUNCTION IF EXISTS get_pt_user_id");
        $query = "CREATE FUNCTION `get_pt_user_id`() RETURNS int(11)
      return @user_id;";
        $wpdb->query($query);
    }
}
