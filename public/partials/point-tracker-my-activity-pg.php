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
if (! $chal_link) {
    wp_die("You need to select a challenge to get", "Damnit Jim, I'm a doctor, not a mindreader", [
        'response' => 301
    ]);
}
$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE short_link=%s", $chal_link);
$chal = $wpdb->get_row($query);

if(!$chal) {
    wp_die("Could not find that challenge, please check your link", "Damnit Jim, I'm a doctor, not a mindreader", [
        'response' => 301
    ]);
}

$now = new DateTime("now", new DateTimeZone(get_option('timezone_string')));
$start = new DateTime($chal->start, new DateTimeZone(get_option('timezone_string')));
$end = new DateTime($chal->end, new DateTimeZone(get_option('timezone_string')));
$end->setTime(23, 59, 59);

if ($now < $start) {
    wp_die("Challenge hasn't started yet", "Where we're going we don't need roads", [
        'response' => 301
    ]);
} elseif ($now > $end) {
    wp_die("Challenge is over", "You need a time machine", [
        'response' => 301
    ]);
}
?>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>
<?php

if (is_user_logged_in()) {
    $user = wp_get_current_user();

    if (! Point_Tracker::is_user_in_challenge($chal_link, $user->ID)) {
        wp_die("You are not a participant in that challenge", "You shall not pass", array(
            'response' => 301
        ));
    } elseif (! Point_Tracker::is_participant_approved($chal_link, $user->ID)) {
        wp_die("You are in the challenge, but not approved at the moment", "I don't know you", array(
            'response' => 301
        ));
    }

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

<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-activity'); ?>' />
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
	<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-activity'); ?>' />
	<input type='hidden' id='chal-id' value='<?php print $chal->id; ?>' />
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