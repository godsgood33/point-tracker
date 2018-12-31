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

if(!is_admin()) {
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

$user = wp_get_current_user();

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
        $name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
        $desc = nl2br(html_entity_decode(stripcslashes($chal->desc), ENT_QUOTES | ENT_HTML5));
        $link = ($chal->approved ? "<a href='{$chal_page->guid}?chal={$chal->short_link}'>{$chal->short_link}</a>" : "{$chal->short_link}");
        $approved = ($chal->approved ? "Yes" : "No");
        print <<<EOR
<tr>
    <td>{$name}</td>
    <td>{$link}<br />
        <a href='{$act_page->guid}?chal={$chal->short_link}'>My Activity</a>
    </td>
    <td>{$approved}</td>
    <td>{$desc}</td>
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
        $name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
        $desc = nl2br(html_entity_decode(stripcslashes($chal->desc), ENT_QUOTES | ENT_HTML5));
        $starts = new DateTime($chal->start, new DateTimeZone(get_option('timezone_string')));
        $approved = ($chal->approved ? "Yes" : "No");
        print <<<EOR
<tr>
    <td>{$name}</td>
    <td>{$chal->short_link}</td>
    <td>{$approved}</td>
    <td>{$starts->format(get_option('date_format', 'Y-m-d'))}</td>
    <td>{$desc}</td>
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
        $name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
        $desc = nl2br(html_entity_decode(stripcslashes($chal->desc), ENT_QUOTES | ENT_HTML5));
        print <<<EOR
<tr>
    <td>{$name}</td>
    <td>{$chal->short_link}</td>
    <td>{$desc}</td>
</tr>
EOR;
}
?>
	</tbody>
</table>

<?php
}

?>

<div id="dialog-form" title="Add new leader">
    <p class="validateTips">All form fields are required.</p>

    <form>
        <fieldset>
            <input type="text" id="member-id" placeholder="Member ID..."
                inputmode='numeric' pattern='[0-9]*'
                class="text ui-widget-content ui-corner-all" />
            <input type="text" id="name" placeholder="Name..." value='<?php print $user->display_name; ?>'
                class="text ui-widget-content ui-corner-all" />
            <input type='email' id='email' placeholder='Email...' value='<?php print $user->user_email; ?>'
                class='text ui-widget-content ui-corner-all' />

            <!-- Allow form submission with keyboard without duplicating the dialog button -->
            <input type="submit" tabindex="-1"
                style="position: absolute; top: -1000px">
        </fieldset>
    </form>
</div>

<?php } ?>
