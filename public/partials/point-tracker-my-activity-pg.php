<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://essentialscentsabilities.com
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/public/partials
 */
global $wpdb;

$chal_link = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$chal = Point_Tracker::init($chal_link);
?>

<h2><?php print $chal->name; ?></h2>
<small><?php print $chal->desc; ?></small>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>
<?php

if (is_user_logged_in()) {
    $user = wp_get_current_user();

    $query = $wpdb->prepare("SELECT
    ca.*,al.*
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_activities ca ON ca.`challenge_id` = c.id
JOIN {$wpdb->prefix}pt_log al ON al.`activity_id` = ca.id
WHERE
    al.`user_id` = %d AND
    c.short_link = '%s'
ORDER BY
    al.log_date,al.log_time", $user->ID, $chal_link);

    $my_act = $wpdb->get_results($query);
    ?>

<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-entry'); ?>' />
<input type='hidden' id='chal' value='<?php print $chal->short_link; ?>' />
<div id='left-half'>
	<table id='my-activity-table' class="stripe">
		<thead>
			<tr>
				<th>Name</th>
				<th>Points</th>
				<th>Date</th>
				<th>Time</th>
				<th>Answer</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody id='my-activity-body'>
<?php
    foreach ($my_act as $act) {
        print <<<EOR
<tr>
    <td>{$act->question}</td>
    <td>{$act->points}</td>
    <td>{$act->log_date}</td>
    <td>{$act->log_time}</td>
    <td>{$act->value}</td>
    <td><i class='far fa-trash-alt' title='Delete this activity so you can reinput with the correct info' data-act-id='{$act->id}' data-log-date='{$act->log_date}' data-user-id='{$user->ID}'></i></td>
</tr>
EOR;
    }
    ?>
		</tbody>
	</table>
</div>

<?php
} else {
    ?>
<div id='left-half'>
	<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-entry'); ?>' />
	<input type='hidden' id='chal' value='<?php print $chal->short_link; ?>' />
	<input type='text' id='member-id' placeholder='Member ID...'
		title='Enter your member ID EXACTLY as you first entered it' /><br />
	<input type='text' id='email' placeholder='Email...'
		title='Enter your email' /><br />
	<input type='button' id='get-activity' value='Get Activity' />&nbsp;&nbsp;
	<div id='tp'>Total Points: <span id='total-points'></span></div>
	<table id='my-activity-table'></table>
</div>
<?php
}