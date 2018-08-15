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

$chal_link = filter_input(INPUT_GET, 'chal', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$chal = Point_Tracker::init($chal_link);

$prev = '';
$act_page = get_page_by_title("My Activity");
$part = null;

$query = "SELECT *
FROM {$wpdb->prefix}pt_activities
WHERE
    challenge_id = %d
ORDER BY `order`";
$chal->activities = $wpdb->get_results($wpdb->prepare($query, $chal->id));
$chal->name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
$chal->desc = nl2br(html_entity_decode(stripcslashes($chal->desc), ENT_QUOTES | ENT_HTML5));

if(!$chal->activities) {
    wp_die("There are no activities in this challenge");
}
?>

<div id='msg'></div>
<div id='waiting'></div>
<div id='loading'></div>
<input type='hidden' id='chal-link' value='<?php print $chal->short_link; ?>' />

<h2><?php print $chal->name; ?></h2>
<small><?php print $chal->desc; ?></small>
<br />

<?php
if (is_user_logged_in()) {
    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_participants WHERE user_id = %d", get_current_user_id());
    $part = $wpdb->get_row($query);
    if(!$part) {
        wp_die();
    }
    ?>
<a href='<?php print "{$act_page->guid}?chal={$chal_link}"; ?>' target='_blank'>View My Activity</a>&nbsp;&nbsp;
<input type='text' id='member-id' placeholder='Member ID...'
	title='Please enter your member ID'
	value='<?php print (isset($part) ? $part->member_id : null); ?>' />
<br />
<input type='text' id='user-name' placeholder='Name...'
	title='Please enter your first and last name'
	value='<?php print (isset($part) ? html_entity_decode($part->name, ENT_QUOTES | ENT_HTML5) : null); ?>' />
<br />
<input type='email' id='user-email' placeholder='Email...'
	title='Please enter your email' value='<?php print (isset($part) ? $part->email : null); ?>' />
<br />
<?php
    foreach ($chal->activities as $act) {
        $desc = esc_attr($act->desc);
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
<div class='activity tooltip-field'<?php print ($desc ? " title='{$desc}'" : ""); ?>>
	<div class='question-container'>
<?php
        } elseif (empty($prev)) {
            ?>
  <div class='activity tooltip-field'
			<?php print ($desc ? " title='{$desc}'" : ""); ?>>
			<div class='question-container'>
<?php
        }

        print "<input type='hidden' class='id' value='{$act->id}' />
<input type='hidden' class='type' value='{$act->type}' />";

        $query = $wpdb->prepare("SELECT CONCAT(log_date,' ', log_time) as 'last-activity'
FROM {$wpdb->prefix}pt_log
WHERE
    `user_id`=%d AND
    `activity_id`=%d
ORDER BY log_date DESC
LIMIT 1", get_current_user_id(), $act->id);

        $ques = html_entity_decode($act->question, ENT_QUOTES | ENT_HTML5);
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
        print "<h3>{$ques} $pts $la</h3>";

        if ($act->type == 'radio' || $act->type == 'checkbox') {
            $labels = explode(",", $act->label);

            foreach ($labels as $label) {
                $id = str_replace(" ", "-", strtolower($label));
                $label = esc_attr($label);

                print <<<EOR
<label for='$id'>$label</label>&nbsp;&nbsp;
<input type='{$act->type}' class='value' id='$id' value='$label' /><br />
EOR;
            }
        } else {
            $min = ($act->type == 'number' && $act->min ? "min='{$act->min}'" : '');
            $max = ($act->type == 'number' && $act->max ? "max='{$act->max}'" : '');
            $val = ($act->type == 'number' && $act->min ? "value='{$act->min}'" : '');

            $max = ($act->type == 'text' && $act->max ? "maxlength='{$act->max}'" : $max);

            $inputmode = ($act->type == 'number' ? " inputmode='numeric' pattern='[0-9]*'" : null);

            print "<input type='{$act->type}' class='value' id='$id'$inputmode $min $max $val />&nbsp;&nbsp;";
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
} else {
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
        $desc = esc_attr($act->desc);
        $id = str_replace(" ", "-", strtolower($act->name));
        $ques = html_entity_decode($act->question, ENT_QUOTES | ENT_HTML5);

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
		<?php print ($desc ? " title='{$desc}'" : ""); ?>>
		<div class='question-container'>
<?php
        } elseif (empty($prev)) {
            ?>
  <div class='activity tooltip-field'
				<?php print ($desc ? " title='{$desc}'" : ""); ?>>
				<div class='question-container'>
<?php
        }

        print "<input type='hidden' class='id' value='{$act->id}' />
<input type='hidden' class='type' value='{$act->type}' />";

        if ($act->type == 'radio' || $act->type == 'checkbox') {
            print "<div>{$ques} <small>({$act->points} pts)</small></div>";

            $labels = explode(",", $act->label);

            foreach ($labels as $label) {
                $id = str_replace(" ", "-", strtolower($label));
                $label = esc_attr($label);

                print <<<EOR
<label for='$id'>$label</label>&nbsp;&nbsp;
<input type='{$act->type}' class='value' id='$id' value='$label' /><br />
EOR;
            }
        } else {
            $min = ($act->type == 'number' && $act->min ? "min='{$act->min}'" : "");
            $max = ($act->type == 'number' && $act->max ? "max='{$act->max}'" : "");
            $val = ($act->type == 'number' && $act->min ? "value='{$act->min}'" : "");

            $max = ($act->type == 'text' && $act->max ? "maxlength='{$act->max}'" : $max);

            $inputmode = ($act->type == 'number' ? " inputmode='numeric' pattern='[0-9]*'" : null);

            print "<input type='{$act->type}' class='value' id='$id'$inputmode $min $max $val />&nbsp;&nbsp;";
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

