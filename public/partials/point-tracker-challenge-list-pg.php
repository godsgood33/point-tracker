<?php
/**
 * File: point-tracker-challenge-list.php
 * Author: Ryan Prather
 * Purpose: To display the challenge list to the user
 */

global $wpdb;

$req_login = (boolean) get_option('pt-require-login', 0);
$challenge_page = get_page_by_title("Challenge");
$act_page = get_page_by_title("My Activity");
$in_chal = false;
$chal = null;

$chal_link = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

if($chal_link) {
  $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE `short_link`=%s", $chal_link);
  $chal = $wpdb->get_row($query);
}

if($req_login && !is_user_logged_in()) {
  wp_die("Web site settings required that you log in to participate in a challenge",
         "How do I know who you are?", array('response' => 301));
}
elseif(!$req_login && !is_user_logged_in() && $chal_link) {
  print "<script type='text/javascript'>window.location = '{$challenge_page->guid}?chal={$chal_link}';</script>";
}
elseif(!$req_login && !is_user_logged_in()) {
  print "<span style='color:red;'>NOTE: Access to a list of challenges you are participating in requires an account</span>";
}
elseif(is_user_logged_in() && !empty($chal_link)) {
  $query = $wpdb->prepare("SELECT COUNT(1) ".
      "FROM {$wpdb->prefix}pt_participants ".
      "WHERE challenge_id = %d AND user_id=%d AND approved=1", $chal->id, get_current_user_id());
  $in_chal = ($wpdb->get_var($query) ? true : false);
}

$now = new DateTime("now", new DateTimeZone(get_option('timezone_string')));

?>
<div id='msg'></div>
<input type='hidden' id='chal-link' value='<?php print $chal_link; ?>' />
<?php

if($in_chal) {
  print "<h3>{$chal->name}</h3>".
        "<p>".stripcslashes(nl2br($chal->desc))."</p>".
        "<a href='{$challenge_page->guid}?chal={$chal->short_link}'>Go to Challenge</a>";
}
elseif($chal_link) {
  print "<h3>{$chal->name}</h3>".
        "<p>".stripcslashes(nl2br($chal->desc))."</p>".
        "<input type='button' id='join-challenge' value='Join Challenge' />";
}
else {
?>
<form method='get' action='#'>
	Challenge ID: <input type='text' name='chal' id='chal-id' placeholder='ID...' /><br />
	<input type='submit' value='Get Challenge' />
</form>
<?php
}

$current_challenges = $wpdb->get_results($wpdb->prepare(
    "SELECT *
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id = c.id
WHERE
    cp.`user_id`=%d AND
    c.`start` <= %s AND
    c.`end` >= %s
ORDER BY c.name", get_current_user_id(), $now->format("Y-m-d"), $now->format("Y-m-d")));
$past_challenges = $wpdb->get_results($wpdb->prepare(
    "SELECT *
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id = c.id
WHERE
    cp.`user_id`=%d AND
    c.`end` < %s
ORDER BY c.`name`", get_current_user_id(), $now->format("Y-m-d")));
$upcoming_challenges = $wpdb->get_results($wpdb->prepare(
    "SELECT *
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_participants cp ON cp.challenge_id = c.id
WHERE
    cp.`user_id`=%d AND
    c.`start` > %s
ORDER BY c.`name`", get_current_user_id(), $now->format("Y-m-d")));

if(is_user_logged_in()) {
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
foreach($current_challenges as $chal) {
  $desc = nl2br(stripcslashes($chal->desc));
  $link = ($chal->approved ? "<a href='{$challenge_page->guid}?chal={$chal->short_link}'>{$chal->short_link}</a>" : "{$chal->short_link}");
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
foreach($upcoming_challenges as $chal) {
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
foreach($past_challenges as $chal) {
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
