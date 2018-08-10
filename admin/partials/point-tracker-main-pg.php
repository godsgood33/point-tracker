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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<h2>Point Tracker</h2>
<div id='msg'></div>
<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-challenge'); ?>' />
<input type='button' id='save-challenge' value='Save' />
&nbsp;&nbsp;
<input type='button' id='delete-challenge' value='Delete' />
<br />
<br />
Challenge Name:
<select id='challenge'>
	<option value=''>-- Select Challenge --</option>
<?php foreach($challenges as $chal): print "<option value='{$chal->id}'>{$chal->name}</option>"; endforeach; ?>
</select>
<br />
<br />
Name:&nbsp;&nbsp;
<input type='text' id='name' class='tooltip-field'
	title='A name for this challenge' />
<br />
Start Date:&nbsp;&nbsp;
<input type='text' id='start-date' class='tooltip-field'
	title='Start date for the challenge' />
<br />
End Date:&nbsp;&nbsp;
<input type='text' id='end-date' class='tooltip-field'
	title='End date for the challenge' />
<br />
Approval Required:&nbsp;&nbsp;
<input type='checkbox' id='approval' class='tooltip-field'
	title='Do you want to approve requests to join the challenge (requires account)' />
<br />
Link:&nbsp;&nbsp;
<span id='link' class='tooltip-field'
	title='Link to the challenge. Copy/paste this when you are ready for people to start joining.'></span>
<br />
Description:
<br />
<textarea id='desc' rows='5' cols='100' class='tooltip-field'
	title='A long description for what the challenge seeks to accomplish, and what, if any, prize will be rewarded'></textarea>
<br />
<br />
Activity Count:&nbsp;&nbsp;
<span id='act-count'></span>
<br />
Participant Count:&nbsp;&nbsp;
<span id='part-count'></span>
