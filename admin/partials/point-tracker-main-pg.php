<?php

/**
 * File: point-tracker-main-pg.php
 * Author: Ryan Prather
 * Purpose: To display the main admin page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://essentialscentsabilities.com
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/admin/partials
 */
global $wpdb;

if (! current_user_can('manage_options')) {
    wp_die("You do not have permissions to do this", "You Dirty Rat!", array(
        'response' => 301
    ));
}

$query = "SELECT * FROM {$wpdb->prefix}pt_challenges";
$challenges = $wpdb->get_results($query) or [];

?>

<div id='waiting'></div>
<div id='loading'></div>

<h2>Point Tracker</h2>
<div id='msg'></div>
<input type='hidden' id='_wpnonce'
	value='<?php print wp_create_nonce('pt-delete-challenge'); ?>' />
<input type='button' id='save-challenge' value='Save' />
&nbsp;&nbsp;
<input type='button' id='delete-challenge' value='Delete' />
<br />
<br />
Challenge Name:
<select id='challenge'>
	<option value=''>-- Select Challenge --</option>
<?php
foreach($challenges as $chal) {
    $name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
    print "<option value='{$chal->id}'>{$name}</option>";
}
?>
</select>
<br />
<br />
<div>
	<input type='text' id='name' class='tooltip-field'
		placeholder='Name...' title='A name for this challenge' />
</div>
<div>
	<input type='text' id='start-date' placeholder='Start Date...'
		class='tooltip-field' title='Start date for the challenge' />
</div>
<div>
	<input type='text' id='end-date' placeholder='End Date...'
		class='tooltip-field' title='End date for the challenge' />
</div>
<div class='tooltip-field'
        title='Do you want to approve requests to join the challenge (requires account)'>
	<input type='checkbox' id='approval' />
    <label for='approval'>Approval Required?</label>
</div>
<div>
	Link:&nbsp;&nbsp; <span id='link' class='tooltip-field'
		title='Link to the challenge. Copy/paste this when you are ready for people to start joining.'></span>
</div>
<div>
	Description: <br />
	<textarea id='desc' rows='5' cols='100' class='tooltip-field'
		title='A long description for what the challenge seeks to accomplish, and what, if any, prize will be rewarded'></textarea>
</div>

<br />
Activity Count:&nbsp;&nbsp;
<span id='act-count'></span>
<br />
Participant Count:&nbsp;&nbsp;
<span id='part-count'></span>
