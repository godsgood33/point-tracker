<?php

if(!current_user_can('manage_options')) {
  wp_die("You do not have permissions to do this", "You Dirty Rat!", array('response' => 301));
}

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

if($action == 'Save Settings') {
    $req_login = (boolean) filter_input(INPUT_POST, 'require-login', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $email_new = (boolean) filter_input(INPUT_POST, 'email-new', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $admin_summary = (boolean) filter_input(INPUT_POST, 'admin-summary-email', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    update_option('pt-require-login', ($req_login ? 1 : 0));
    update_option('pt-admin-summary-email', ($admin_summary ? 1 : 0));
    update_option('pt-email-new-participants', ($email_new ? 1 : 0));

    print "Saved Settings<br />";
}
?>

<h2>Point Tracker Settings</h2>

<div>
	<form method='post' action='#'>
		<div class='notice notice-warning'>
			<?php print __('Not requiring a login opens challenge participants to potential unauthorized activity deletions'); ?>
		</div>
		<label for='require-login'>Login Required? </label>
		<input type='checkbox' name='require-login' id='require-login' value='1' <?php print (get_option('pt-require-login', 0) ? "checked" : ''); ?> /><br />

		<label for='admin-summary-email'>Send Admin a Summary Email? </label>
		<input type='checkbox' name='admin-summary-email' id='admin-summary-email' value='1' <?php print (get_option('pt-admin-summary-email', 0) ? "checked" : ''); ?> /><br />

    <!--
    <label for='email-new-participant'>Email New Participant? </label>
    <input type='checkbox' name='email-new' id='email-new-participants' value='1' <?php print (get_option('pt-email-new-participants', 0) ? 'checked' : ''); ?> /><br />
    -->

		<input type='submit' name='action' value='Save Settings' />
	</form>
</div>