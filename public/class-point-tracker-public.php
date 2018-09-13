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
     * Variable to temporarily store the challenge unique ID
     *
     * @var string
     */
    public static $chal = '';

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

        add_shortcode('challenge', [
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
        $chal_page = get_page_by_title('Challenge');

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . "js/point-tracker-public.min.js", [
            'jquery'
        ], $this->version, false);
        wp_localize_script($this->plugin_name, 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'chal_page' => $chal_page->guid,
            'date_format' => $this->php_to_js_date(get_option('date_format'))
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
            "F j, Y" => "MM d, yy",
            "d/m/Y" => "dd/mm/yy"
        ];
        return isset($arr["{$php_format}"]) ? $arr["{$php_format}"] : "yy-mm-dd";
    }

    /**
     * Function to display the challenge data
     *
     * @param array $attrs
     * @param string $content
     * @param string $tag
     */
    public function display_challenge_page($attrs = [], $content = null, $tag = '')
    {
        $attrs = array_change_key_case((array) $attrs, CASE_LOWER);
        if(is_array($attrs) && count($attrs) && isset($attrs['chal'])) {
            self::$chal = $attrs['chal'];
        }

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
     *
     * @param array $attrs
     * @param string $content
     * @param string $tag
     */
    public function display_activity_page($attrs = [], $content = null, $tag = '')
    {
        $attrs = array_change_key_case((array) $attrs, CASE_LOWER);
        if(is_array($attrs) && count($attrs) && isset($attrs['chal'])) {
            self::$chal = $attrs['chal'];
        }

        include_once ('partials/point-tracker-my-activity-pg.php');
    }

    /**
     * Method to print out the activity
     *
     * @global wpdb $wpdb
     *
     * @param object $act
     * @param object $part
     */
    public static function print_Activity(&$act, &$part)
    {
        global $wpdb;

        $desc = esc_attr($act->desc);
        $id = str_replace(" ", "-", strtolower($act->name));
        $ques = html_entity_decode($act->question, ENT_QUOTES | ENT_HTML5);
        $la = null;

        print <<<EOR
<div class='activity tooltip-field' title='{$desc}'>
    <input type='hidden' class='id' value='{$act->id}' />
    <input type='hidden' class='type' value='{$act->type}' />
    <div class='question-container'>
EOR;

        if ($part) {
            $query = $wpdb->prepare("SELECT CONCAT(log_date,' ', log_time) as 'last-activity'
FROM {$wpdb->prefix}pt_log
WHERE
    `user_id` = %d AND
    `activity_id` = %d
ORDER BY log_date DESC
LIMIT 1", $part->user_id, $act->id);

            $ques = html_entity_decode($act->question, ENT_QUOTES | ENT_HTML5);
            if ($last_activity = $wpdb->get_var($query)) {
                $last_activity = new DateTime($last_activity);
                $la = "&nbsp;&nbsp;({$last_activity->format(get_option("date_format"))})";
            }
        }

        $pts = null;
        if ($act->chal_max) {
            $pts = "<small title='Activity Point Value / Max Allowed'>($act->points / $act->chal_max)</small>";
        } else {
            $pts = "<small title='Activity Point Value'>($act->points pts)</small>";
        }
        print "<h3>{$ques} {$pts}{$la}</h3>";

        if ($act->type == 'radio' || $act->type == 'checkbox') {
            $labels = explode(",", $act->label);

            foreach ($labels as $label) {
                $id = str_replace(" ", "-", strtolower($label));
                $label = esc_attr($label);

                print <<<EOR
<input type='{$act->type}' class='value' id='$id' value='$label' />&nbsp;&nbsp;
<label for='$id'>$label</label><br />
EOR;
            }
        } else {
            $min = ($act->type == 'number' && $act->min ? " min='{$act->min}'" : '');
            $max = ($act->type == 'number' && $act->max ? " max='{$act->max}'" : '');
            $val = ($act->type == 'number' && $act->min ? " value='{$act->min}'" : '');

            $max = ($act->type == 'text' && $act->max ? " maxlength='{$act->max}'" : $max);

            $inputmode = ($act->type == 'number' ? " inputmode='numeric' pattern='[0-9]*'" : null);
            $text_max = ($act->type == 'text' && $act->max ? " text-max" : null);

            if ($act->type == 'long-text') {
                print "<textarea class='value' cols='1' rows='5' id='$id'></textarea>";
            } else {
                print "<input type='{$act->type}' class='value{$text_max}' id='{$id}'{$inputmode}{$min}{$max}{$val} />&nbsp;&nbsp;";
                print($act->type == 'text' && $act->max ? "<br />(<span id='text-len-{$id}'>0</span> / {$act->max})" : null);
            }
        }

        print <<<EOS
    </div>
    <div class='save-container'>
        <input type='submit' class='save' value='Save' />
    </div>
</div>
EOS;
    }
}
