<?php
add_action('wp_ajax_get-activities', 'pt_get_activity_table');
add_action('wp_ajax_get-activity-details', 'pt_get_activity_details');
add_action('wp_ajax_save-activity', 'pt_save_activity');
add_action('wp_ajax_delete-activity', 'pt_delete_activity');
add_action('wp_ajax_ac-group', 'pt_ac_group');

/**
 * Getter function to get all the activities for a particular challenge
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string that is an array of activity objects
 */
function pt_get_activity_table()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'Access Denied'
        ]);
        wp_die();
    }

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_activities WHERE challenge_id = %d ORDER BY `order`", $chal_id);
    $res = $wpdb->get_results($query);
    $_data = [];
    foreach ($res as $row) {
        if($row->hidden) {
            $name = "<i title='Hidden activity, not used for point counts'>" . html_entity_decode($row->name, ENT_QUOTES | ENT_HTML5) . "*</i>";
        }
        else {
            $name = html_entity_decode($row->name, ENT_QUOTES | ENT_HTML5);
        }
        $_data[] = [
            'order' => $row->order,
            'type' => ucfirst($row->type),
            'name' => $name,
            'points' => $row->points,
            'chal_max' => $row->chal_max,
            'question' => html_entity_decode($row->question, ENT_QUOTES | ENT_HTML5),
            'desc' => html_entity_decode($row->desc, ENT_QUOTES | ENT_HTML5),
            'extras' => ($row->label ? html_entity_decode($row->label, ENT_QUOTES | ENT_HTML5) : "{$row->min}/{$row->max}"),
            'action' => "<i class='fas fa-edit' data-id='{$row->id}'></i>&nbsp;&nbsp;<i class='far fa-trash-alt' data-id='{$row->id}'></i>",
            'hidden' => (boolean) $row->hidden,
            'group' => html_entity_decode($row->group, ENT_QUOTES | ENT_HTML5)
        ];
    }

    $tac = count($_data);

    $query = $wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->prefix}pt_activities WHERE challenge_id = %d AND (`group` IS NOT NULL AND `group` != '')", $chal_id);
    $tagc = $wpdb->get_var($query);

    $group_msg = false;
    if($tagc > 0 && $tac != $tagc) {
        $group_msg = true;
    }

    $ret = [
        'group_msg' => $group_msg,
        'data' => $_data,
        'columns' => [
            [
                'title' => 'Order',
                'defaultContent' => 0,
                'data' => 'order'
            ],
            [
                'title' => 'Type',
                'defaultContent' => '',
                'data' => 'type'
            ],
            [
                'title' => 'Name',
                'defaultContent' => '',
                'data' => 'name'
            ],
            [
                'title' => 'Group',
                'defaultContent' => '',
                'data' => 'group'
            ],
            [
                'title' => 'Point Value',
                'defaultContent' => 0,
                'data' => 'points'
            ],
            [
                'title' => 'Max Allowed',
                'defaultContent' => 0,
                'data' => 'chal_max'
            ],
            [
                'title' => 'Question',
                'defaultContent' => '',
                'data' => 'question'
            ],
            [
                'title' => 'Description',
                'defaultContent' => '',
                'data' => 'desc'
            ],
            [
                'title' => 'Extras',
                'defaultContent' => '',
                'data' => 'extras'
            ],
            [
                'title' => 'Action',
                'defaultContent' => "",
                'data' => 'action'
            ]
        ]
    ];

    print json_encode($ret);
    wp_die();
}

/**
 * Function get activity details
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the details of the activity
 */
function pt_get_activity_details()
{
    global $wpdb;
    $act_id = filter_input(INPUT_POST, 'act-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $query = "SELECT * FROM {$wpdb->prefix}pt_activities WHERE id = %d AND challenge_id = %d";
    $act = $wpdb->get_row($wpdb->prepare($query, $act_id, $chal_id));

    if(!$act) {
        print json_encode([
            'error' => 'Unable to find the selected activity'
        ]);
        wp_die();
    }

    $act->name = html_entity_decode($act->name, ENT_QUOTES | ENT_HTML5);
    $act->desc = html_entity_decode($act->desc, ENT_QUOTES | ENT_HTML5);
    $act->question = html_entity_decode($act->question, ENT_QUOTES | ENT_HTML5);
    $act->label = html_entity_decode($act->label, ENT_QUOTES | ENT_HTML5);
    $act->group = html_entity_decode($act->group, ENT_QUOTES | ENT_HTML5);

    print json_encode($act);
    wp_die();
}

/**
 * Function to save the activities
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the save operation
 */
function pt_save_activity()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'Error saving activity (access denied)'
        ]);
        wp_die();
    }

    $name = preg_replace("/[^a-z0-9]/", "", strtolower(
        filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE)
        ));
    $act_id = filter_input(INPUT_POST, 'act-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $pts = filter_input(INPUT_POST, 'points', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $type = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_REGEXP, [
        'options' => [
            'regexp' => "/checkbox|number|radio|text/"
        ]
    ]);
    $ques = sanitize_text_field(filter_input(INPUT_POST, 'question', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));
    $desc = sanitize_text_field(filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));
    $order = filter_input(INPUT_POST, 'order', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $hidden = (boolean) filter_input(INPUT_POST, 'hidden', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $group = sanitize_text_field(filter_input(INPUT_POST, 'group', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));

    $params = [
        'name' => $name,
        'points' => $pts,
        'challenge_id' => $chal_id,
        'type' => $type,
        'question' => $ques,
        'desc' => $desc,
        'order' => $order,
        'hidden' => $hidden ? 1 : 0,
        'group' => $group
    ];

    if (! pt_validate_activity($params)) {
        print json_encode([
            'error' => $params['error']
        ]);
        wp_die();
    }

    $query = $wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->prefix}pt_activities WHERE name = %s AND challenge_id = %d AND id != %d", $name, $chal_id, $act_id);
    $count = $wpdb->get_var($query);
    if($count) {
        print json_encode([
            'error' => 'Invalid name value (cannot duplicate a name in this challenge)'
        ]);
        wp_die();
    }

    if ($act_id) {
        $id = $act_id;
        if ($wpdb->update("{$wpdb->prefix}pt_activities", $params, [
            'id' => $act_id
        ]) === false) {
            print json_encode([
                'error' => 'Error saving activity'
            ]);
            wp_die();
        }
    } else {
        if (($id = $wpdb->insert("{$wpdb->prefix}pt_activities", $params)) === false) {
            print json_encode([
                'error' => 'Error adding activity'
            ]);
            wp_die();
        }
    }

    $query = $wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->prefix}pt_activities WHERE challenge_id = %d", $chal_id);
    $tac = $wpdb->get_var($query);

    $query = $wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->prefix}pt_activities WHERE challenge_id = %d AND (`group` IS NOT NULL AND `group` != '')", $chal_id);
    $tagc = $wpdb->get_var($query);

    $group_msg = false;
    if($tagc > 0 && $tac != $tagc) {
        $group_msg = true;
    }

    print json_encode([
        'id' => $id,
        'success' => 'Successfully saved the activity',
        'name' => $name,
        'desc' => $desc,
        'question' => $ques,
        'label' => $params['label'],
        'group' => $group,
        'group_msg' => $group_msg
    ]);

    wp_die();
}

/**
 * Function to delete an activity
 *
 * @global wpdb $wpdb
 *
 * @return string
 */
function pt_delete_activity()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'Access Denied'
        ]);
        wp_die();
    }

    if(!check_ajax_referer('pt-delete-activity', 'security', false)) {
        print json_encode([
            'error' => 'We were unable to verify the nonce'
        ]);
        wp_die();
    }

    $act_id = filter_input(INPUT_POST, 'act-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $res = $wpdb->delete("{$wpdb->prefix}pt_activities", [
        'id' => $act_id
    ]);

    $wpdb->delete("{$wpdb->prefix}pt_log", [
        'activity_id' => $act_id
    ]);

    print json_encode($res !== false ? [
        'success' => "Successfully delete the activity"
    ] : [
        'error' => $wpdb->last_error
    ]);

    wp_die();
}

/**
 * Function to validate activity entry form
 *
 * @param array $act
 *            stdClass object that is the activity being evaluated
 *
 * @return boolean true if all required values are present and within range otherwise false
 */
function pt_validate_activity(&$act)
{
    $ret = true;

    $act['error'] = null;

    if (! $act['type'] || ! in_array($act['type'], [
        'checkbox',
        'radio',
        'number',
        'text',
        'long-text'
    ])) {
        $act['error'] .= 'Invalid activity type selected<br />';
        $ret = false;
    }

    if (! $act['name'] || strlen($act['name']) > 10) {
        $act['error'] .= 'Invalid name for activity<br />';
        $ret = false;
    }

    if (! $act['hidden'] && (! $act['points'] || ! is_numeric($act['points']))) {
        $act['error'] .= 'Invalid value for activity points<br />';
        $ret = false;
    }

    if (! $act['question']) {
        $act['error'] .= 'Invalid question for activity<br />';
        $ret = false;
    }

    if (in_array($act['type'], [
        'checkbox',
        'radio'
    ])) {
        $label = filter_input(INPUT_POST, 'label', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
        if (! $label) {
            $act['error'] .= 'Invalid label for answer options<br />';
            $ret = false;
        } else {
            $act['label'] = sanitize_text_field($label);
        }
    } elseif (in_array($act['type'], [
        'text',
        'number'
    ])) {
        $min = filter_input(INPUT_POST, 'min', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $max = filter_input(INPUT_POST, 'max', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if (! is_numeric($min)) {
            $act['error'] .= 'Invalid value for activity min<br />';
            $ret = false;
        } else {
            $act['min'] = $min;
        }

        if (! is_numeric($max)) {
            $act['error'] .= 'Invalid value for activity max<br />';
            $ret = false;
        } else {
            $act['max'] = $max;
        }
    }

    $chal_max = filter_input(INPUT_POST, 'chal-max', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $act['chal_max'] = ($chal_max && !$act['hidden'] ? $chal_max : 0);

    if ($ret) {
        unset($act['error']);
    }

    return $ret;
}

/**
 * Method to perform an autocomplete when typing and selecting activity groups
 *
 * @global wpdb $wpdb
 *
 * @return string
 */
function pt_ac_group() {
    global $wpdb;

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $term = "%" . $wpdb->esc_like(stripslashes(filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE))) . "%";

    if(!$chal_id) {
        print json_encode([
            'error' => 'Please select a challenge to search'
        ]);
        wp_die();
    }

    $groups = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT(`group`) AS 'act_group'
FROM {$wpdb->prefix}pt_activities
WHERE challenge_id = %d AND `group` LIKE %s", $chal_id, $term));

    $ret = [];
    foreach($groups as $g) {
        $ret[] = $g->act_group;
    }

    print json_encode($ret);
    wp_die();
}
