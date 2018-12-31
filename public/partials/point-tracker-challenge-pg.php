<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wppointtracker.com
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/public/partials
 */
global $wpdb;

$chal_link = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

if (! $chal_link) {
    $chal_link = filter_var(Point_Tracker_Public::$chal, FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
}

if(!is_admin()) {
$chal = Point_Tracker::init($chal_link);

$act_page = get_page_by_title("My Activity");
$part = null;

$query = "SELECT *
FROM {$wpdb->prefix}pt_activities
WHERE
    challenge_id = %d
ORDER BY `order`";
$chal->activities = $wpdb->get_results($wpdb->prepare($query, $chal->id));
$chal->name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
$chal->desc = nl2br(html_entity_decode(stripcslashes($chal->desc), ENT_QUOTES | ENT_HTML5));
$groups = [];
$grouped_activities = [];

if (! $chal->activities) {
    wp_die("There are no activities in this challenge");
}
else {
    $query = "SELECT DISTINCT(`group`) AS 'g'
FROM {$wpdb->prefix}pt_activities
WHERE
    challenge_id = %d AND
    `group` IS NOT NULL AND
    `group` != ''";
    $groups = $wpdb->get_results($wpdb->prepare($query, $chal->id));
}

$part = null;
if (is_user_logged_in()) {
    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_participants WHERE user_id = %d AND challenge_id = %d", get_current_user_id(), $chal->id);
    $part = $wpdb->get_row($query);
}
?>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>
<input type='hidden' id='chal-link'
    value='<?php print $chal->short_link; ?>' />

<h1><?php print $chal->name; ?></h1>
<small><?php print $chal->desc; ?></small>
<br />
<a href='<?php print "{$act_page->guid}?chal={$chal_link}"; ?>'
    target='_blank'>View My Activity</a>

<input type='text' id='member-id' placeholder='Member ID...'
    title='Please enter your member ID'
    value='<?php print ($part ? $part->member_id : null); ?>' />
<br />
<input type='text' id='user-name' placeholder='Name...'
    title='Please enter your first and last name'
    value='<?php print ($part ? html_entity_decode($part->name, ENT_QUOTES | ENT_HTML5) : null); ?>' />
<br />
<input type='email' id='user-email' placeholder='Email...'
    title='Please enter your email'
    value='<?php print ($part ? $part->email : null); ?>' />
<?php
if(count($groups)) {
    foreach($chal->activities as $a) {
        $grouped_activities["{$a->group}"][] = $a;
    }

    foreach ($grouped_activities as $k => $g) {
        print "<h2>{$k}</h2>";
        foreach($g as $act) {
            Point_Tracker_Public::print_Activity($act, $part);
        }
        print "<hr/>";
    }
} else {
    foreach ($chal->activities as $act) {
        Point_Tracker_Public::print_Activity($act, $part);
    }
}
}
