<?php
/**
 * File: point-tracker-log-pg.php
 * Author: Ryan Prather
 * Purpose: To display to the admin the log of participant activity
 */

global $wpdb;

if(!current_user_can('manage_options')) {
  wp_die('You do not have permissions to do this', "You Dirty Rat!", array('response' => 301));
}

$query = "SELECT * FROM {$wpdb->prefix}pt_challenges";
$challenges = $wpdb->get_results($query) or [];
?>

<h2>Participant Log</h2>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>

Challenge Name:
<select id='participant-log'>
	<option value=''>-- Select Challenge --</option>
<?php foreach($challenges as $chal): print "<option value='{$chal->id}'>{$chal->name}</option>"; endforeach; ?>
</select>
<br />

<!-- <a href='javascript:void(0);' id='add-activity-link'>Add Activity</a><br /> -->
<div id='add-participant-activity'>
  <input type='hidden' id='activity-type' />
  Activity: <select id='participant-activity'>
    <option value=''>-- Select Activity --</option>
  </select><br />

  <input type='date' id='log-date' placeholder='Activity Date...' />&nbsp;&nbsp;
  <input type='time' id='log-time' placeholder='Activity Time...' /><br />

  <div id='activity-answer'></div>

  <input type='button' id='save-participant-activity' value='Save' />
</div>

<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-entry'); ?>' />
<table id='participant-log-table'></table>
