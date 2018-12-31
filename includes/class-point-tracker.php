<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/godsgood33
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/includes
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
 * @since 1.0.0
 * @package Point_Tracker
 * @subpackage Point_Tracker/includes
 * @author Ryan Prather <godsgood33@gmail.com>
 */
class Point_Tracker
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var Point_Tracker_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        if (defined('PT_VERSION')) {
            $this->version = PT_VERSION;
        } else {
            $this->version = '1.6';
        }
        $this->plugin_name = 'point-tracker';

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
     * - Point_Tracker_Loader. Orchestrates the hooks of the plugin.
     * - Point_Tracker_i18n. Defines internationalization functionality.
     * - Point_Tracker_Admin. Defines all hooks for the admin area.
     * - Point_Tracker_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since 1.0.0
     * @access private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-point-tracker-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-point-tracker-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-point-tracker-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-point-tracker-public.php';

        /**
         * Constants
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/constants.php';

        /**
         * All AJAX functions
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/ajax/challenge-ajax.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/ajax/activity-ajax.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/ajax/participant-ajax.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/ajax/entry-ajax.php';

        /**
         *
         * @var Point_Tracker $loader
         */
        $this->loader = new Point_Tracker_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Point_Tracker_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since 1.0.0
     * @access private
     */
    private function set_locale()
    {
        $plugin_i18n = new Point_Tracker_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Point_Tracker_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        //$this->loader->add_action('admin_head', $plugin_admin, 'add_help');
        $this->loader->add_action('wp_dashboard_setup', $plugin_admin, 'add_dashboard_widget');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Point_Tracker_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * Function to initialize each page
     *
     * @global wpdb $wpdb
     *
     * @param string $chal_link
     * @param boolean $list
     *
     * @return stdClass
     */
    public static function init($chal_link, $list = false)
    {
        global $wpdb;
        $req_login = (boolean) get_option('pt-require-login', 0);
        $now = new DateTime("now", new DateTimeZone(get_option('timezone_string')));
        $list_page = get_page_by_title("Challenge List");

        if($list && !$chal_link) {
            return null;
        }

        if (! $chal_link) {
            wp_die("You must select a challenge to participate in", "NO_LINK", [
                'response' => 301
            ]);
        }

        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE `short_link`=%s", $chal_link);
        $chal = $wpdb->get_row($query);

        if (! $chal) {
            wp_die("Could not find the challenge requested", "CHALLENGE_NOT_FOUND", [
                'response' => 301
            ]);
        }

        $start = new DateTime($chal->start, new DateTimeZone(get_option('timezone_string')));
        $end = new DateTime($chal->end, new DateTimeZone(get_option('timezone_string')));
        $end->setTime(23, 59, 59);

        if ($now < $start) {
            wp_die("Challenge hasn't started yet", "CHALLENGE_NOT_RUNNING", [
                'response' => 301
            ]);
        } elseif ($now > $end) {
            wp_die("Challenge is already over", "CHALLENGE_NOT_RUNNING", [
                'response' => 301
            ]);
        }

        if ($req_login && ! is_user_logged_in()) {
            wp_die("Web site settings required that you <a href='" . wp_login_url() . "'>login</a> to participate in a challenge", "ACCOUNT_REQUIRED", [
                'response' => 301
            ]);
        } elseif ($chal->approval && ! is_user_logged_in()) {
            wp_die("Challenge requires approval so you must <a href='" . wp_login_url() . "'>login</a> on this website", "ACCOUNT_REQUIRED", [
                'response' => 301
            ]);
        }

        if(is_user_logged_in() && !$list) {
            if (! Point_Tracker::is_user_in_challenge($chal->id, get_current_user_id()) && $chal->approval) {
                print "<script type='text/javascript'>document.location.href = '{$list_page->guid}?chal={$chal_link}';</script>";
            } elseif (! Point_Tracker::is_participant_approved($chal->id, get_current_user_id()) && $chal->approval) {
                wp_die("You have not been approved to access this challenge yet", "You shall not pass!", [
                    'response' => 301
                ]);
            }
        }

        return $chal;
    }

    /**
     * Function to print necessary content on the page to allow the user to join the challenge
     *
     * @param stdClass $chal
     */
    public static function join_challenge(&$chal)
    {
        $desc = stripcslashes(nl2br($chal->desc));
        print <<<EOL
<h3>{$chal->name}</h3>
<p>{$desc}</p>
<input type='button' id='join-challenge' value='Join Challenge' />
EOL;
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since 1.0.0
     * @return string The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since 1.0.0
     * @return Point_Tracker_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since 1.0.0
     * @return string The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Function to see if the current user is a participant in the challenge
     *
     * @global wpdb $wpdb
     *
     * @param int|string $challenge_id
     * @param int $user_id
     *
     * @return boolean
     */
    public static function is_user_in_challenge($challenge_id, $user_id)
    {
        global $wpdb;

        $query = $wpdb->prepare("SELECT COUNT(1)
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id = c.id
WHERE " . (is_numeric($challenge_id) ? "c.id = %d" : "c.short_link = %s") . " AND
    cp.`user_id`=%d", $challenge_id, $user_id);

        return (boolean) $wpdb->get_var($query);
    }

    /**
     * Function to determine if a user is approved in a particular challenge
     *
     * @global wpdb $wpdb
     *
     * @param int $chal_id
     * @param int $user_id
     *
     * @return boolean
     */
    public static function is_participant_approved($chal_id, $user_id)
    {
        global $wpdb;

        $query = $wpdb->prepare("SELECT IF(!c.approval,'1',cp.approved)
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id=c.id
WHERE " . (is_numeric($chal_id) ? "cp.challenge_id = %d" : "c.short_link = %s") . " AND
    cp.user_id = %d", $chal_id, $user_id);

        return (boolean) $wpdb->get_var($query);
    }
}
