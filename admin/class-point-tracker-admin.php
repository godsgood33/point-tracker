<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/godsgood33
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Point_Tracker
 * @subpackage Point_Tracker/admin
 * @author Ryan Prather <godsgood33@gmail.com>
 */
class Point_Tracker_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $plugin_name
     *            The name of this plugin.
     * @param string $version
     *            The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Point_Tracker_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Point_Tracker_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . "css/point-tracker-admin.min.css", [], $this->version, 'all');

        wp_enqueue_style('ui-datepicker-css', plugin_dir_url(__DIR__) . "includes/jquery-ui-1.12.1/jquery-ui.min.css", [], $this->version, 'all');

        wp_enqueue_style('datatables', plugin_dir_url(__DIR__) . "includes/datatables/DataTables-1.10.9/css/jquery.dataTables.min.css");
        wp_enqueue_style('dt-buttons', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/css/buttons.dataTables.min.css");

        wp_enqueue_style('font-awesome', plugin_dir_url(__DIR__) . "includes/font-awesome/font-awesome-v5.2.0.min.css", [], $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Point_Tracker_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Point_Tracker_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script("{$this->plugin_name}-admin-core", plugin_dir_url(__FILE__) . "js/point-tracker-admin.min.js", [
            'jquery'
        ], $this->version, false);
        wp_localize_script("{$this->plugin_name}-admin-core", 'my_object', [
            'date_format' => $this->php_to_js_date(get_option('date_format'))
        ]);

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-autocomplete');

        wp_enqueue_script('spinner', plugin_dir_url(__DIR__) . 'includes/spin/spin.min.js', [], $this->version, false);

        wp_enqueue_script('datatables', plugin_dir_url(__DIR__) . "includes/datatables/DataTables-1.10.9/js/jquery.dataTables.min.js");
        wp_enqueue_script('dt-jszip', plugin_dir_url(__DIR__) . "includes/jszip/jszip.min.js");
        wp_enqueue_script('dt-pdfmake1', plugin_dir_url(__DIR__) . "includes/datatables/pdfmake-0.1.18/build/pdfmake.min.js");
        wp_enqueue_script('dt-pdfmake2', plugin_dir_url(__DIR__) . "includes/datatables/pdfmake-0.1.18/build/vfs_fonts.js");
        wp_enqueue_script('dt-buttons', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/js/dataTables.buttons.min.js");
        wp_enqueue_script('dt-buttons-html5', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/js/buttons.html5.min.js");
        wp_enqueue_script('dt-buttons-print', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/js/buttons.print.min.js");
        wp_enqueue_script('dt-responsive', plugin_dir_url(__DIR__) . "includes/datatables/Responsive-1.0.7/js/dataTables.responsive.min.js");
        wp_enqueue_script('dt-scroller', plugin_dir_url(__DIR__) . "includes/datatables/Scroller-1.3.0/js/dataTables.scroller.min.js");
        wp_enqueue_script('dt-select', plugin_dir_url(__DIR__) . "includes/datatables/Select-1.0.1/js/dataTables.select.min.js");
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since 1.0.0
     */
    public function add_plugin_admin_menu()
    {
        add_menu_page('Point Tracker', 'Point Tracker', 'manage_options', 'point-tracker-menu', [
            $this,
            'display_core_menu_page'
        ], 'dashicons-admin-generic', 2);

        add_submenu_page('point-tracker-menu', 'Challenges', 'Challenges', 'manage_options', 'point-tracker-menu', [
            $this,
            'display_core_menu_page'
        ]);

        add_submenu_page('point-tracker-menu', 'Activities', 'Activities', 'manage_options', 'point-tracker-activities', [
            $this,
            'display_activity_submenu_page'
        ]);

        add_submenu_page('point-tracker-menu', 'Participants', 'Participants', 'manage_options', 'point-tracker-participants', [
            $this,
            'display_participant_submenu_page'
        ]);

        add_submenu_page('point-tracker-menu', 'Participant Log', 'Log', 'manage_options', 'point-tracker-participant-log', [
            $this,
            'display_participant_log_submenu_page'
        ]);

        add_submenu_page('point-tracker-menu', 'Upgrade!', 'Upgrade!', 'manage_options', 'point-tracker-upgrade', [
            $this,
            'display_upgrade_page'
        ]);

        add_options_page("Point Tracker Settings", "PT Settings", "manage_options", "pt-settings", [
            $this,
            "display_admin_options_page"
        ]);
    }

    /**
     * Function to add contextual help dropdown
     *
     * @since 1.5
     */
    public function add_help()
    {
        $s = get_current_screen();
        $match = [];
        if (preg_match("/(point\-tracker\-.*)/", $s->base, $match)) {
            $page = str_replace("-", "_", $match[1]) . '_help';
            $s->remove_help_tab('point-tracker-help');
            $s->add_help_tab([
                'id' => 'point-tracker-help',
                'title' => 'Point Tracker',
                'callback' => [
                    $this,
                    $page
                ]
            ]);

            // $s->set_help_sidebar('Test');
        } else {
            $s->remove_help_tab('point-tracker-help');
        }
    }

    /**
     * Method to print the contextual help for the challenge page
     *
     * @since 1.5
     */
    public function point_tracker_menu_help()
    {}

    /**
     * Method to print the contextual help for the activities page
     *
     * @since 1.5
     */
    public function point_tracker_activities_help()
    {}

    /**
     * Method to print the contextual help for the participants page
     *
     * @since 1.5
     */
    public function point_tracker_participants_help()
    {}

    /**
     * Method to print the contextual help for the log page
     *
     * @since 1.5
     */
    public function point_tracker_log_help()
    {}
    
    /**
     * Method to print the contextual help for the upgrade page
     * 
     * @since 1.5
     */
    public function point_tracker_upgrade_help()
    {}

    /**
     * Function to add the dashboard widget code
     *
     * @since 1.5
     */
    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget('pt-dashboard-widget', 'Point Tracker', [
            $this,
            'dashboard_widget_handler'
        ]);
    }

    /**
     * Method to display the dashboard widget
     *
     * @since 1.5
     */
    public function dashboard_widget_handler()
    {
        include_once ('partials/point-tracker-dashboard-widget.php');
    }

    /**
     * Function to return the equivelant date formatting string
     *
     * @since 1.3
     *       
     * @return string
     */
    public function php_to_js_date($php_format)
    {
        // most common date formats listed in WordPress admin
        $arr = [
            "Y-m-d" => "yy-mm-dd",
            "m/d/Y" => "mm/dd/yy",
            "M j, Y" => "M d, yy",
            "F j, Y" => "MM d, yy",
            "d/m/Y" => "dd/mm/yy"
        ];
        return isset($arr["{$php_format}"]) ? $arr["{$php_format}"] : "yy-mm-dd";
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since 1.0.0
     */
    public function display_core_menu_page()
    {
        include_once ('partials/point-tracker-main-pg.php');
    }

    /**
     * Render the activity tracker page
     *
     * @since 1.0
     */
    public function display_activity_submenu_page()
    {
        include_once ('partials/point-tracker-activity-pg.php');
    }

    /**
     * Render the participant tracker page
     *
     * @since 1.0
     */
    public function display_participant_submenu_page()
    {
        include_once ('partials/point-tracker-participant-pg.php');
    }

    /**
     * Render the participant log
     *
     * @since 1.0
     */
    public function display_participant_log_submenu_page()
    {
        include_once ('partials/point-tracker-log-pg.php');
    }

    /**
     * Method to display to upgrade page
     *
     * @since 1.4
     */
    public function display_upgrade_page()
    {
        print <<<EOL
<h2>Upgrade to Point Tracker Pro</h2>
<p><a href='https://wppointtracker.com/point-tracker-pro' target='_blank'>Point Tracker Pro</a> is the next level of challenge tracking.  It includes functionality like:</p>

<ul>
    <li>Leader Lists &mdash; for grouping people which allows you to create multiple challenge winners</li>
    <li>Public Leader Boards &mdash; create a public leader board that is available to all your participants so they can see where they rank in the challenge</li>
    <li>Activity Backdating &mdash; allow participants to log an activity on a different date than the one they are actually logging it on</li>
    <li>Activity Start/End Dates &mdash; allow participants to log an activity only during a specific date range</li>
    <li>Participant Upload &mdash; upload a file to automatically create all of your participants and add them to the challenge</li>
    <li>Random Name Drawing &mdash; Randomly select a name from the filterable list of potential winners</li>
</ul>
EOL;
    }

    /**
     * Render the admin options page
     *
     * @since 1.0
     */
    public function display_admin_options_page()
    {
        include_once ('partials/options.php');
    }
}
