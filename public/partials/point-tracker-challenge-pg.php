<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://essentialscentsabilities.com
 * @since      1.0.0
 *
 * @package    Point_Tracker
 * @subpackage Point_Tracker/public/partials
 */
global $wpdb;

$now = new DateTime("now", new DateTimeZone(get_option('timezone_string')));
$prev = '';

$req_login = (boolean) get_option('pt-require-login', 0);
$act_page = get_page_by_title("My Activity");
$chal_link = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

if (! $chal_link) {
    wp_die("You must select a challenge to participate in", "Damnit Jim, I'm a doctor, not a mindreader", [
        'response' => 301
    ]);
}

$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE `short_link`=%s", $chal_link);
$chal = $wpdb->get_row($query);

if (! $chal) {
    wp_die("Could not find the challenge requested", "I don't know what you mean", [
        'response' => 301
    ]);
}

$query = "SELECT *
FROM {$wpdb->prefix}pt_activities
WHERE
    challenge_id = %d
ORDER BY `order`";
$chal->activities = $wpdb->get_results($wpdb->prepare($query, $chal->id));

$start = new DateTime($chal->start, new DateTimeZone(get_option('timezone_string')));
$end = new DateTime($chal->end, new DateTimeZone(get_option('timezone_string')));
$end->setTime(23, 59, 59);

if ($now < $start) {
    wp_die("Challenge hasn't started yet", "Where we're going, we don't need roads", [
        'response' => 301
    ]);
} elseif ($now > $end) {
    wp_die("Challenge is already over", "You built a time machine out of a Delorean", [
        'response' => 301
    ]);
}

if(is_user_logged_in()) {
    if (! Point_Tracker::is_user_in_challenge($chal->id, get_current_user_id())) {
        wp_die("You are not a participant of this challenge", "You shall not pass!", [
            'response' => 301
        ]);
    } elseif (! Point_Tracker::is_participant_approved($chal->id, get_current_user_id())) {
        wp_die("You have not been approved to access this challenge yet", "You shall not pass!", [
            'response' => 301
        ]);
    }

    $query = "SELECT * FROM {$wpdb->prefix}pt_participants WHERE user_id = %d AND challenge_id = %d";
    $part = $wpdb->get_row($wpdb->prepare($query, get_current_user_id(), $chal->id));
} elseif ($req_login) {
    wp_die("Settings require a login and you are not logged in", "You shall not pass!", [
        'response' => 301
    ]);
}
?>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>
<input type='hidden' id='chal-id' value='<?php print $chal->id; ?>' />
<input type='hidden' id='chal-link' value='<?php print $chal->short_link; ?>' />
<h2><?php print $chal->name; ?></h2>
<small><?php print $chal->desc; ?></small>
<br />

<?php
if (is_user_logged_in()) {
    ?>
<a href='<?php print "{$act_page->guid}?chal={$chal_link}"; ?>' target='_blank'>View My Activity</a>&nbsp;&nbsp;
<input type='text' id='member-id' placeholder='Member ID...'
	title='Please enter your member ID'
	value='<?php print (isset($part) ? $part->member_id : null); ?>' />
<br />
<input type='text' id='user-name' placeholder='Name...'
	title='Please enter your first and last name'
	value='<?php print (isset($part) ? $part->name : null); ?>' />
<br />
<input type='email' id='user-email' placeholder='Email...'
	title='Please enter your email' value='<?php print (isset($part) ? $part->email : null); ?>' />
<br />
<?php
    foreach ($chal->activities as $act) {
        $id = str_replace(" ", "-", strtolower($act->name));

        if ($prev && $prev != $id) {
            ?>
</div>
<!-- closing tag for .question-container -->
<div class='save-container'>
	<input type='button' class='save' value='Save' />
</div>
</div>
<!-- closing tag for .activity -->
<div class='activity tooltip-field'<?php print ($act->desc ? " title='{$act->desc}'" : ""); ?>>
	<div class='question-container'>
<?php
        } elseif (empty($prev)) {
            ?>
  <div class='activity tooltip-field'
			<?php print ($act->desc ? " title='{$act->desc}'" : ""); ?>>
			<div class='question-container'>
<?php
        }

        print "<input type='hidden' class='id' value='{$act->id}' />" . "<input type='hidden' class='type' value='{$act->type}' />";

        $query = $wpdb->prepare("SELECT CONCAT(log_date,' ', log_time) as 'last-activity'
FROM `{$wpdb->prefix}pt_log`
WHERE
    `user_id`=%d AND
    `activity_id`=%d
ORDER BY log_date DESC
LIMIT 1", get_current_user_id(), $act->id);

        $la = null;
        if ($last_activity = $wpdb->get_var($query)) {
            $last_activity = new DateTime($last_activity);
            $la = "&nbsp;&nbsp;({$last_activity->format(get_option("date_format"))})";
        }

        $pts = null;
        if($act->chal_max) {
            $pts = "<small title='Activity Point Value / Max Allowed'>($act->points / $act->chal_max)</small>";
        } else {
            $pts = "<small title='Activity Point Value'>($act->points pts)</small>";
        }
        print "<h3>{$act->question} $pts $la</h3>";

        if ($act->type == 'radio' || $act->type == 'checkbox') {
            $labels = explode(",", $act->label);

            foreach ($labels as $label) {
                $id = str_replace(" ", "-", strtolower($label));

                print <<<EOR

EOR;
                print "<label for='$id'>$label</label>&nbsp;&nbsp;";
                print "<input type='{$act->type}' class='value' id='$id' value='$label' /><br />";
            }
        } else {
            $min = ($act->type == 'number' && $act->min ? "min='{$act->min}'" : '');
            $max = ($act->type == 'number' && $act->max ? "max='{$act->max}'" : '');
            $val = ($act->type == 'number' && $act->min ? "value='{$act->min}'" : '');

            $max = ($act->type == 'text' && $act->max ? "maxlength='{$act->max}'" : $max);

            print "<input type='{$act->type}' class='value' id='$id' $min $max $val />";
        }

        $prev = $id;
    }
    ?>
  </div>
			<!-- closing tag for .question-container -->
			<div class='save-container'>
				<input type='button' class='save' value='Save' />
			</div>
<?php
} elseif (! $req_login) {
    print "<a href='{$act_page->guid}?chal={$chal_link}' target='_blank'>View My Activity</a>&nbsp;&nbsp;";
    ?>
  <input type='text' id='member-id' placeholder='Member ID...'
				title='Please enter your member ID' /><br />
  <input type='text' id='user-name' placeholder='Name...'
				title='Please enter your first and last name' /><br />
  <input type='email' id='user-email' placeholder='Email...'
				title='Please enter your email' /><br />
<?php
    foreach ($chal->activities as $act) {
        $id = str_replace(" ", "-", strtolower($act->name));

        if ($prev && $prev != $id) {
            ?>
    </div>
		<!-- closing tag for .question-container -->
		<div class='save-container'>
			<input type='button' class='save' value='Save' />
		</div>
	</div>
	<!-- closing tag for .activity -->
	<div class='activity tooltip-field'
		<?php print ($act->desc ? " title='{$act->desc}'" : ""); ?>>
		<div class='question-container'>
<?php
        } elseif (empty($prev)) {
            ?>
  <div class='activity tooltip-field'
				<?php print ($act->desc ? " title='{$act->desc}'" : ""); ?>>
				<div class='question-container'>
<?php
        }

        print "<input type='hidden' class='id' value='{$act->id}' />" . "<input type='hidden' class='type' value='{$act->type}' />";

        if ($act->type == 'radio' || $act->type == 'checkbox') {
            print "<div>{$act->question} <small>({$act->points} pts)</small></div>";

            $labels = explode(",", $act->label);

            foreach ($labels as $label) {
                $id = str_replace(" ", "-", strtolower($label));

                print "<label for='{$act->type}-$id'>$label</label>&nbsp;&nbsp;";
                print "<input type='{$act->type}' class='value' id='{$act->type}-$id' name='{$act->name}' value='$label' /><br />";
            }
        } else {
            $min = ($act->type == 'number' && $act->min ? "min='{$act->min}'" : "");
            $max = ($act->type == 'number' && $act->max ? "max='{$act->max}'" : "");
            $val = ($act->type == 'number' && $act->min ? "value='{$act->min}'" : "");

            $max = ($act->type == 'text' && $act->max ? "maxlength='{$act->max}'" : $max);

            print "<label for='$id'>{$act->question} <small>({$act->points} pts)</small></label><br />";
            print "<input type='{$act->type}' class='value' id='$id' $min $max $val />&nbsp;&nbsp;";
        }

        $prev = $id;
    }
    ?>
  </div>
				<!-- closing tag for .question-container -->
				<div class='save-container'>
					<input type='button' class='save' value='Save' />
				</div>
<?php
}

