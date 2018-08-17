<?php
/**
 * File: point-tracker-participant-pg.php
 * Author: Ryan Prather
 * Purpose: To display to the admins the participant list
 */
global $wpdb;

if (! current_user_can('manage_options')) {
    wp_die('You do not have permissions to do this', "You Dirty Rat!", array(
        'response' => 301
    ));
}

$query = "SELECT * FROM {$wpdb->prefix}pt_challenges";
$challenges = $wpdb->get_results($query) or [];

?>

<h2>Participant List</h2>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>

<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-participant'); ?>' />

Challenge Name:
<select id='challenge_participants'>
	<option value=''>-- Select Challenge --</option>
<?php
foreach($challenges as $chal) {
    $name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
    print "<option value='{$chal->id}'>{$name}</option>";
}
?>
</select>
<br />

<a href='javascript:void(0);' id='add-challenge-participant'>Add Participant</a>

<div id='admin-add-participant'>
	<input type='text' id='member-id' placeholder='Member ID...' inputmode='numeric' pattern='[0-9]*' /><br />
	<input type='text' id='user-name' placeholder='Name...' /><br />
	<input type='email' id='user-email' placeholder='Email...' /><br />
	<input type='button' id='add-participant' value='Add Participant' />
</div>

<input type='button' id='clear-participants' value='Clear Participants' />

<table id='participant-table' class='display'></table>
