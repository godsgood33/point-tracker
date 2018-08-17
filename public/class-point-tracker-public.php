<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/godsgood33
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package Point_Tracker
 * @subpackage Point_Tracker/public
 * @author Ryan Prather <godsgood33@gmail.com>
 */
class Point_Tracker_Public
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
     *            The name of the plugin.
     * @param string $version
     *            The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_shortcode('challenge_page', [
            $this,
            'display_challenge_page'
        ]);
        add_shortcode('challenge_list', [
            $this,
            'display_challenge_list_page'
        ]);
        add_shortcode('my_activity', [
            $this,
            'display_activity_page'
        ]);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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
        $chal = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
        if ($chal) {
            // plugin CSS
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . "css/point-tracker-public.min.css", [], $this->version, 'all');
            // get jQuery UI css
            wp_enqueue_style('ui-datepicker-css', plugin_dir_url(__DIR__) . 'includes/jquery-ui-1.12.1/jquery-ui.min.css', [], $this->version, 'all');
            // Font Awesome CSS
            wp_enqueue_style('font-awesome', plugin_dir_url(__DIR__) . "includes/font-awesome/font-awesome-v5.2.0.min.css", [], $this->version, 'all');
            // DataTable CSS
            wp_enqueue_style('datatables-css', plugin_dir_url(__DIR__) . "includes/datatables/DataTables-1.10.9/css/jquery.dataTables.min.css");
            wp_enqueue_style('dt-jqueryui-css', plugin_dir_url(__DIR__) . "includes/datatables/DataTables-1.10.9/css/dataTables.jqueryui.min.css");
            wp_enqueue_style('dt-buttons-css', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/css/buttons.dataTables.min.css");
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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
        $chal = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
        if ($chal) {
            $chal_page = get_page_by_title('Challenge');

            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . "js/point-tracker-public.min.js", [
                'jquery'
            ], $this->version, false);
            wp_localize_script($this->plugin_name, 'ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'chal_page' => $chal_page->guid,
                'date_format' => $this->php_to_js_date(get_option('date_format', 'm/d/Y'))
            ]);
            wp_enqueue_script($this->plugin_name . "-spin", plugin_dir_url(__DIR__) . "includes/spin/spin.min.js", [
                'jquery'
            ], $this->version, false);

            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-tooltip');
            wp_enqueue_script('jquery-ui-dialog');

            wp_enqueue_script('datatables', plugin_dir_url(__DIR__) . "includes/datatables/DataTables-1.10.9/js/jquery.dataTables.min.js");
            wp_enqueue_script('dt-jszip', plugin_dir_url(__DIR__) . "includes/jszip/jszip.min.js");
            wp_enqueue_script('dt-pdfmake1', plugin_dir_url(__DIR__) . "includes/datatables/pdfmake-0.1.18/build/pdfmake.min.js");
            wp_enqueue_script('dt-pdfmake2', plugin_dir_url(__DIR__) . "includes/datatables/pdfmake-0.1.18/build/vfs_fonts.js");
            wp_enqueue_script('dt-buttons', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/js/dataTables.buttons.min.js");
            wp_enqueue_script('dt-buttons-html5', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/js/buttons.html5.min.js");
            wp_enqueue_script('dt-buttons-print', plugin_dir_url(__DIR__) . "includes/datatables/Buttons-1.0.3/js/buttons.print.min.js");
        }
    }

    /**
     * Function to return the equivelant date formatting string
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
            "F j, Y" => "MMMM d, yy",
            "d/m/Y" => "dd/mm/yy"
        ];
        return in_array($php_format, $arr) ? $arr["{$php_format}"] : "yy-mm-dd";
    }

    /**
     * Function to display the challenge data
     */
    public function display_challenge_page()
    {
        include_once ('partials/point-tracker-challenge-pg.php');
    }

    /**
     * Function to display the challenge list for this user
     */
    public function display_challenge_list_page()
    {
        include_once ('partials/point-tracker-challenge-list-pg.php');
    }

    /**
     * Function to display user activity
     */
    public function display_activity_page()
    {
        include_once ('partials/point-tracker-my-activity-pg.php');
    }
}
