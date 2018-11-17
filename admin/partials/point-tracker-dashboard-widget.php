<?php
if(!current_user_can('manage_options')) {
    wp_die("You are not allowed to access this page");
}

global $wpdb;

$challenges = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pt_challenges");

?>

<select id='pt-widget-type'>
    <option value='challenge'>Challenge</option>
    <option value='activities'>Activities</option>
    <option value='participants'>Participants</option>
    <!-- <option value='log'>Log</option> -->
</select>

<select id='pt-widget-challenge'>
    <option value=''>-- Select Challenge --</option>
    <?php foreach($challenges as $c); print "<option value='{$c->id}'>{$c->name}</option>"; ?>
</select>

<div id='pt-widget-results'></div>
