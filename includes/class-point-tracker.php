<?php

namespace PointTracker;

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

use DateTime;
use DateTimeZone;
use PointTracker\PointTrackerAdmin;
use PointTracker\PointTrackerPublic;
use PointTracker\ChallengeAjax;
use PointTracker\EntryAjax;
use PointTracker\ParticipantAjax;

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
class PointTracker
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var PointTrackerLoader $loader Maintains and registers all hooks for the plugin.
     */
    protected PointTrackerLoader $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since 1.0.0
     * @access protected
     * @var string $pluginName The string used to uniquely identify this plugin.
     */
    protected string $pluginName;

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
            $this->version = '1.7';
        }
        $this->pluginName = 'point-tracker';

        $this->loadDependencies();
        $this->setLocale();
        $this->defineAdminHooks();
        $this->definePublicHooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Point_Tracker_Loader. Orchestrates the hooks of the plugin.
     * - Point_Tracker_i18n. Defines internationalization functionality.
     * - PointTrackerAdmin. Defines all hooks for the admin area.
     * - Point_Tracker_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since 1.0.0
     * @access private
     */
    private function loadDependencies()
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

        $this->loader = new PointTrackerLoader();
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
    private function setLocale()
    {
        $plugin_i18n = new PointTrackeri18n();

        $this->loader->addAction('plugins_loaded', $plugin_i18n, 'loadPluginTextdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function defineAdminHooks()
    {
        $plugin_admin = new PointTrackerAdmin($this->getPluginName(), $this->getVersion());

        $this->loader->addAction('admin_enqueue_scripts', $plugin_admin, 'enqueueStyles');
        $this->loader->addAction('admin_enqueue_scripts', $plugin_admin, 'enqueueScripts');

        $this->loader->addAction('admin_menu', $plugin_admin, 'addPluginAdminMenu');
        //$this->loader->addAction('admin_head', $plugin_admin, 'addHelp');
        $this->loader->addAction('wp_dashboard_setup', $plugin_admin, 'addDashboardWidget');

        // Challenge Ajax
        $this->loader->addAction('wp_ajax_get-challenge', ChallengeAjax::class, 'getChallenge');
        $this->loader->addAction('wp_ajax_save-challenge', ChallengeAjax::class, 'saveChallenge');
        $this->loader->addAction('wp_ajax_delete-challenge', ChallengeAjax::class, 'deleteChallenge');
        $this->loader->addAction('wp_ajax_pt-get-widget-data', ChallengeAjax::class, 'getWidgetData');
        $this->loader->addAction('wp_ajax_remove-winner', ChallengeAjax::class, 'removeWinner');

        // Activity Ajax
        $this->loader->addAction('wp_ajax_get-activities', ActivityAjax::class, 'getActivityTable');
        $this->loader->addAction('wp_ajax_get-activity-details', ActivityAjax::class, 'getActivityDetails');
        $this->loader->addAction('wp_ajax_save-activity', ActivityAjax::class, 'saveActivity');
        $this->loader->addAction('wp_ajax_delete-activity', ActivityAjax::class, 'deleteActivity');
        $this->loader->addAction('wp_ajax_ac-group', ActivityAjax::class, 'groupActivity');

        // Participant Ajax
        $this->loader->addAction('wp_ajax_get-participants', ParticipantAjax::class, 'getParticipantTable');
        $this->loader->addAction('wp_ajax_approve-participant', ParticipantAjax::class, 'approveParticipant');
        $this->loader->addAction('wp_ajax_remove-participant', ParticipantAjax::class, 'removeParticipant');
        $this->loader->addAction('wp_ajax_add-participant', ParticipantAjax::class, 'addParticipant');
        $this->loader->addAction('wp_ajax_join-challenge', ParticipantAjax::class, 'joinChallenge');
        $this->loader->addAction('wp_ajax_clear-participants', ParticipantAjax::class, 'clearParticipants');
        $this->loader->addAction('wp_ajax_mark-winner', ParticipantAjax::class, 'markWinner');

        // Entry ajax
        $this->loader->addAction('wp_ajax_get-log', EntryAjax::class, 'getLogTable');
        $this->loader->addAction('wp_ajax_save-entry', EntryAjax::class, 'participantSaveEntry');
        $this->loader->addAction(
            'wp_ajax_delete-participant-activity',
            EntryAjax::class,
            'deleteParticipantActivity'
        );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function definePublicHooks()
    {
        $plugin_public = new PointTrackerPublic($this->getPluginName(), $this->getVersion());

        $this->loader->addAction('wp_enqueue_scripts', $plugin_public, 'enqueueStyles');
        $this->loader->addAction('wp_enqueue_scripts', $plugin_public, 'enqueueScripts');

        // public ajax requests
        $this->loader->addAction('wp_ajax_nopriv_save-entry', EntryAjax::class, 'participantSaveEntry');
        $this->loader->addAction('wp_ajax_nopriv_get-my-activity', EntryAjax::class, 'getMyActivityTable');
        $this->loader->addAction(
            'wp_ajax_nopriv_delete-participant-activity',
            EntryAjax::class,
            'deleteParticipantActivity'
        );
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

        if ($list && !$chal_link) {
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
            wp_die("Web site settings required that you <a href='".wp_login_url().
                "'>login</a> to participate in a challenge", "ACCOUNT_REQUIRED", [
                'response' => 301
            ]);
        } elseif ($chal->approval && ! is_user_logged_in()) {
            wp_die("Challenge requires approval so you must <a href='".wp_login_url().
                "'>login</a> on this website", "ACCOUNT_REQUIRED", [
                'response' => 301
            ]);
        }

        if (is_user_logged_in() && !$list) {
            if (! PointTracker::isUserInChallenge($chal->id, get_current_user_id()) && $chal->approval) {
                print "<script type='text/javascript'>".
                    "document.location.href = '{$list_page->guid}?chal={$chal_link}';".
                "</script>";
            } elseif (! PointTracker::isParticipantApproved($chal->id, get_current_user_id()) && $chal->approval) {
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
    public static function joinChallenge(&$chal)
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
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since 1.0.0
     * @return Point_Tracker_Loader Orchestrates the hooks of the plugin.
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since 1.0.0
     * @return string The version number of the plugin.
     */
    public function getVersion()
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
    public static function isUserInChallenge($challenge_id, $user_id)
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
    public static function isParticipantApproved($chal_id, $user_id)
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
