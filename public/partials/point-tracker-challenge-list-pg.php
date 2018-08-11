<?php
/**
 * File: point-tracker-challenge-list-pg.php
 * Author: Ryan Prather
 * Purpose: To display the challenge list to the user
 */
global $wpdb;
$act_page = get_page_by_title("My Activity");
$chal_page = get_page_by_title("Challenge");
$chal_link = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$user = null;

$chal = Point_Tracker::init($chal_link, true);

if (is_user_logged_in()) {
    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_participants WHERE user_id = %d", get_current_user_id());
    $user = $wpdb->get_row($query);

    if($chal) {
        if (! $user || ! Point_Tracker::is_user_in_challenge($chal->id, $user->user_id)) {
            // allow user to join
            Point_Tracker::join_challenge($chal);
        } elseif (! Point_Tracker::is_participant_approved($chal->id, $user->user_id)) {
            wp_die("You are not current approved for this challenge", "NOT_APPROVED", [
                'response' => 301
            ]);
        } else {
            $desc = stripcslashes(nl2br($chal->desc));
            print <<<EOL
    <h3>{$chal->name}</h3>
    <p>{$desc}</p>
    <a href='{$chal_page->guid}?chal={$chal->short_link}'>Go to Challenge</a>
EOL;
        }
    }
} else {
    wp_die("Viewing your current challenges requires you to <a href='" . wp_login_url() . "'>login</a>", "ACCOUNT_REQUIRED", [
        'response' => 301
    ]);
}

$now = new DateTime("now", new DateTimeZone(get_option('timezone_string')));

?>
<div id='msg'></div>
<input type='hidden' id='chal-link' value='<?php print $chal_link; ?>' />
<?php
$current_challenges = $wpdb->get_results($wpdb->prepare("SELECT *
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id = c.id
WHERE
    cp.`user_id`=%d AND
    c.`start` <= %s AND
    c.`end` >= %s
ORDER BY c.name", get_current_user_id(), $now->format("Y-m-d"), $now->format("Y-m-d")));
$past_challenges = $wpdb->get_results($wpdb->prepare("SELECT *
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id = c.id
WHERE
    cp.`user_id`=%d AND
    c.`end` < %s
ORDER BY c.`name`", get_current_user_id(), $now->format("Y-m-d")));
$upcoming_challenges = $wpdb->get_results($wpdb->prepare("SELECT *
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id = c.id
WHERE
    cp.`user_id`=%d AND
    c.`start` > %s
ORDER BY c.`name`", get_current_user_id(), $now->format("Y-m-d")));

if (is_user_logged_in()) {
    ?>

<h2>Current Challenges</h2>

<table id='registered-challenges'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Link</th>
			<th>Approved</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody id='registered-challenges-body'>
<?php
    foreach ($current_challenges as $chal) {
        $desc = nl2br(stripcslashes($chal->desc));
        $link = ($chal->approved ? "<a href='{$chal_page->guid}?chal={$chal->short_link}'>{$chal->short_link}</a>" : "{$chal->short_link}");
        $approved = ($chal->approved ? "Yes" : "No");
        print <<<EOR
    <tr>
        <td>{$chal->name}</td>
        <td>$link<br />
            <a href='{$act_page->guid}?chal={$chal->short_link}'>My Activity</a>
        </td>
        <td>$approved</td>
        <td>$desc</td>
    </tr>
EOR;
    }
    ?>
	</tbody>
</table>

<h2>Upcoming Challenges</h2>

<table id='upcoming-challenges'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Link</th>
			<th>Approved</th>
			<th>Starts</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody id='upcoming-challenges-body'>
<?php
    foreach ($upcoming_challenges as $chal) {
        $starts = new DateTime($chal->start, new DateTimeZone(get_option('timezone_string')));
        $desc = nl2br(stripcslashes($chal->desc));
        $approved = ($chal->approved ? "Yes" : "No");
        print <<<EOR
    <tr>
        <td>{$chal->name}</td>
        <td>{$chal->short_link}</td>
        <td>$approved</td>
        <td>{$starts->format('M j, y')}</td>
        <td>$desc</td>
    </tr>
EOR;
    }
    ?>
	</tbody>
</table>

<h2>Past Challenges</h2>

<table id='past-challenges'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Link</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody id='past-challenges-body'>

<?php
    foreach ($past_challenges as $chal) {
        $desc = nl2br(stripcslashes($chal->desc));
        print <<<EOR
<tr>
    <td>$chal->name</td>
    <td>$chal->short_link</td>
    <td>$desc</td>
</tr>
EOR;
    }
    ?>
	</tbody>
</table>

<?php } ?>
