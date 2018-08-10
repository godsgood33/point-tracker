<?php
/**
 * File: point-tracker-activity-pg.php
 * Author: Ryan Prather
 * Purpose: To display the admin activity editor page
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
<h2>Activity Tracker</h2>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>

<input type='hidden' id='_wpnonce' value='<?php print wp_create_nonce('pt-delete-activity'); ?>' />

Challenge Name:
<select id='challenge_activities'>
	<option value=''>-- Select Challenge --</option>
<?php foreach($challenges as $chal): print "<option value='{$chal->id}'>{$chal->name}</option>"; endforeach; ?>
</select>
<br />
<input type='button' id='save-activity' value='Save' />

<div id='activity'>
	<input type='hidden' id='t-row' />
	<input type='hidden' id='act-id' />
	<div class='onethird'>
    	<label for='act-type'>Type:</label>
    	<select id='act-type' class='act-type tooltip-field' title='What type of activity is this?'>
    		<option value=''>-- Type --</option>
    		<option value='checkbox'>Checkbox</option>
    		<option value='number'>Number</option>
    		<option value='radio'>Radio</option>
    		<option value='text'>Text</option>
    	</select><br />

    	<label for='name'>Name:</label>
    	<input type='text' class='act-name tooltip-field' id='act-name' max='10' placeholder='Name...' title='Short name for the activity (max 10 characters)' /><br />

    	<label for='pts'>Points:</label>
    	<input type='text' class='act-pts tooltip-field' id='act-pts' value='0' title='Point value for each amount of the entry' /><br />

    	<label for='chal-max'>Max Allowed:</label>
    	<input type='text' class='act-chal-max tooltip-field' id='act-chal-max' value='0' title='Numeric value of the maximum amount points allowed during the whole challenge' /><br />
	</div>

	<div class='onethird'>
		<label for='question'>Question:</label>
		<input type='text' class='act-ques tooltip-field' id='act-ques' placeholder='Question...' title='What question do you want to ask the participant' /><br />

		<label for='desc'>Description:</label>
		<input type='text' class='act-desc tooltip-field' id='act-desc' placeholder='Desc...' title='Long description explaining any limitations, restrictions, or allowances for this activity' /><br />

		<label for='order'>Order:</label>
		<input type='text' class='act-order tooltip-field' id='act-order' value='0' placeholder='Order...' title='What order do you want this displayed on the page' /><br />
	</div>

	<div class='onethird'>
		<label for='labels'>Labels:</label>
		<input type='text' class='act-label tooltip-field' id='act-labels' value='' placeholder='Label...' title='Comma delimited list of possible options for checkboxes and radio buttons' /><br />

        <label for='min'>Min:</label>
        <input type='text' class='act-min tooltip-field' id='act-min' value='0' placeholder='Min...' title='Numeric value of what the minimum entry amount is (for text activities this is the required entry length)' /><br />

        <label for='max'>Max:</label>
        <input type='text' class='act-max tooltip-field' id='act-max' value='0' placeholder='Max...' title='Numeric value of the maximum amount allowed/day (for text activities this is the max allowed text length)' />
	</div>
</div>

<table id='activity-table'></table>
