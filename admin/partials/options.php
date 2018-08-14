<?php

if(!current_user_can('manage_options')) {
  wp_die("You do not have permissions to do this", "You Dirty Rat!", ['response' => 301]);
}

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

if($action == 'Save Settings') {
	if(!check_ajax_referer('pt-update-options', '_wpnonce', false)) {
		print "Unable to verify permissions";
		wp_die();
	}
	
    $req_login = (boolean) filter_input(INPUT_POST, 'require-login', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    update_option('pt-require-login', ($req_login ? 1 : 0));

    print "Saved Settings<br />";
}
?>

<h2>Point Tracker Settings</h2>

<div>
	<form method='post' action='#'>
		<input type='hidden' name='_wpnonce' value='<?php print wp_create_nonce('pt-update-options'); ?>' />
		<div class='notice notice-warning'>
			<?php print __('Not requiring a login opens challenge participants to potential unauthorized activity deletions', 'point-tracker'); ?>
		</div>
		<label for='require-login'>Login Required? </label>
		<input type='checkbox' name='require-login' id='require-login' value='1' <?php print (get_option('pt-require-login', 0) ? "checked" : ''); ?> /><br />

		<input type='submit' name='action' value='Save Settings' />
	</form>
</div>