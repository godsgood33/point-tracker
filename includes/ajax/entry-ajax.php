<?php
// logged in user ajax requests
add_action('wp_ajax_get-log', 'pt_get_log_table');
add_action('wp_ajax_save-entry', 'pt_participant_save_entry');
add_action('wp_ajax_delete-participant-activity', 'pt_delete_participant_activity');

// public ajax requests
add_action('wp_ajax_nopriv_save-entry', 'pt_participant_save_entry');
add_action('wp_ajax_nopriv_get-my-activity', 'pt_get_my_activity_table');
add_action('wp_ajax_nopriv_delete-participant-activity', 'pt_delete_participant_activity');

/**
 * Function to get the activity log for all participants
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded array of all participant activity
 */
function pt_get_log_table()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'Access Denied'
        ]);
        wp_die();
    }

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $chal = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE id = %d", $chal_id));
    if(!$chal) {
        print json_encode([
            'error' => 'Unable to find the selected challenge'
        ]);
        wp_die();
    }

    $query = $wpdb->prepare("CREATE TEMPORARY TABLE tmp_log SELECT
al.activity_id,al.log_date,al.log_time,al.value,ca.question,ca.points,cp.*
FROM {$wpdb->prefix}pt_log al
JOIN {$wpdb->prefix}pt_activities ca ON ca.id = al.activity_id
JOIN {$wpdb->prefix}pt_participants cp ON cp.user_id = al.user_id AND cp.challenge_id = ca.challenge_id
WHERE ca.challenge_id = %d", $chal_id);
    $wpdb->query($query);

    $query = "SELECT al.*
FROM tmp_log al
ORDER BY al.log_date,al.log_time";
    $log_res = $wpdb->get_results($query);

    $_data = [];

    if (is_array($log_res) && count($log_res)) {
        foreach ($log_res as $log) {
            $dt = new DateTime($log->log_date . " " . $log->log_time, new DateTimeZone(get_option('timezone_string')));
            $_data[] = [
                'id' => $log->member_id,
                'name' => html_entity_decode($log->name, ENT_QUOTES | ENT_HTML5),
                'activity' => html_entity_decode($log->question, ENT_QUOTES | ENT_HTML5),
                'points' => $log->points,
                'dt' => $dt->format(get_option('date_format', 'Y-m-d')),
                'answer' => html_entity_decode($log->value, ENT_QUOTES | ENT_HTML5),
                'action' => "<i class='far fa-trash-alt' title='Delete this activity so you can reinput with the correct info' data-act-id='{$log->activity_id}' data-log-date='{$dt->format("Y-m-d")}' data-user-id='{$log->user_id}'></i>"
            ];
        }
    }

    print json_encode([
        'data' => $_data,
        'columns' => [
            [
                'title' => 'ID',
                'defaultContent' => '',
                'data' => 'id'
            ],
            [
                'title' => 'Name',
                'defaultContent' => '',
                'data' => 'name'
            ],
            [
                'title' => 'Activity',
                'defaultContent' => '',
                'data' => 'activity'
            ],
            [
                'title' => 'Points',
                'defaultContent' => '',
                'data' => 'points'
            ],
            [
                'title' => 'Date',
                'defaultContent' => '',
                'data' => 'dt'
            ],
            [
                'title' => 'Answer',
                'defaultContent' => '',
                'data' => 'answer'
            ],
            [
                'title' => 'Action',
                'defaultContent' => '',
                'data' => 'action'
            ]
        ]
    ]);
    wp_die();
}

/**
 * Function to save a participants response to an activity
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the requested save operation
 */
function pt_participant_save_entry()
{
    global $wpdb;
    $wpdb->suppress_errors = true;
    $wpdb->show_errors = false;
    $altered = false;
    $now = new DateTime("now", new DateTimeZone(get_option('timezone_string')));

    $act_id = filter_input(INPUT_POST, 'act-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $log_date = filter_input(INPUT_POST, 'log-date', FILTER_VALIDATE_REGEXP, [
        'options' => [
            'regexp' => "/[\d\\]{10,12}/"
        ]
    ]);
    $member_id = filter_input(INPUT_POST, 'member-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $user_name = sanitize_text_field(filter_input(INPUT_POST, 'user-name', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));
    $user_email = strtolower(sanitize_email(filter_input(INPUT_POST, 'user-email', FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)));

    if (! $member_id) {
        print json_encode([
            'warning' => 'That is an invalid value for your member ID'
        ]);
        wp_die();
    } elseif (! $user_name) {
        print json_encode([
            'warning' => 'You must put your name on the form'
        ]);
        wp_die();
    } elseif (! $user_email) {
        print json_encode([
            'warning' => 'You must put your e-mail on the form'
        ]);
        wp_die();
    }

    if (isset($log_date)) {
        $now = new DateTime($log_date, new DateTimeZone(get_option('timezone_string')));
    }

    if (! is_a($now, 'DateTime')) {
        print json_encode([
            'error' => 'Were not able to parse the activity date'
        ]);
        wp_die();
    }

    $req_login = (boolean) get_option('pt-require-login', 0);
    $query = $wpdb->prepare("SELECT c.*
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_activities ca ON ca.challenge_id = c.id
WHERE ca.id = %d", $act_id);
    $chal = $wpdb->get_row($query);

    if(!$chal) {
        print json_encode([
            'error' => 'Unable to find that selected challenge'
        ]);
        wp_die();
    }

    $start = new DateTime($chal->start, new DateTimeZone(get_option('timezone_string')));
    $end = new DateTime($chal->end, new DateTimeZone(get_option('timezone_string')));
    $end->setTime(23, 59, 59);

    // Verify the challenge is still running
    if ($now < $start) {
        print json_encode([
            'error' => "Challenge hasn't started"
        ]);
        wp_die();
    } elseif ($now > $end) {
        print json_encode([
            'error' => "Challenge is already over"
        ]);
        wp_die();
    }

    $user_id = null;

    if ($req_login && (! is_user_logged_in() || ! Point_Tracker::is_user_in_challenge($chal->id, get_current_user_id()))) {
        print json_encode([
            'error' => 'You must be logged in to access this (access denied)'
        ]);
        wp_die();
    } // if the user is logged in, get their info
    elseif (is_user_logged_in()) {
        $user_id = get_current_user_id();
    } // if login is not required and they aren't logged in, check for the presents of an account using their email
    elseif (! $req_login && ! $user_id) {
        $user_id = email_exists($user_email);
        if (! $user_id) {
            $random_pwd = wp_generate_password();
            $user_id = wp_create_user($user_email, $random_pwd, $user_email);
        }
    }

    if (! $user_id) {
        print json_encode([
            'error' => 'Unable to add you to the challenge'
        ]);
        wp_die();
    }

    $query = $wpdb->prepare("SELECT *
FROM {$wpdb->prefix}pt_participants
WHERE
    user_id = %d AND
    challenge_id = %d", $user_id, $chal->id);
    $part = $wpdb->get_row($query);
    $res = true;

    if (! $part) {
        $res = $wpdb->insert("{$wpdb->prefix}pt_participants", [
            'challenge_id' => $chal->id,
            'user_id' => $user_id,
            'approved' => 1,
            'date_joined' => $now->format("Y-m-d"),
            'date_approved' => $now->format("Y-m-d"),
            'name' => $user_name,
            'email' => $user_email,
            'member_id' => $member_id
        ]);
    }

    // Make sure a user was added to the challenge
    if (! $res) {
        print json_encode([
            'error' => 'Unable to add you to the challenge'
        ]);
        wp_die();
    }

    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_activities WHERE id = %d", $act_id);
    $act = $wpdb->get_row($query);

    if($act->type == 'checkbox') {
        $value = implode(',', filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY));
    } else {
        $value = sanitize_text_field(filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));
    }

    $params = [
        'user_id' => $user_id,
        'activity_id' => $act_id,
        'log_date' => $now->format("Y-m-d"),
        'log_time' => $now->format("H:i:s"),
        'value' => $value
    ];
    $amt = $act->points;

    // validate the activity by type
    switch ($act->type) {
        case 'checkbox':
            if (! strlen($value)) {
                print json_encode([
                    'warning' => 'Must select at least one option'
                ]);
                wp_die();
            }
            break;
        case 'number':
            if (empty($value) || ! is_numeric($value)) {
                print json_encode([
                    'warning' => 'Value must be numeric'
                ]);
                wp_die();
            } elseif ($act->min && $value < $act->min) {
                print json_encode([
                    'warning' => 'Value must be at least ' . $act->min
                ]);
                wp_die();
            } elseif ($act->max && $value > $act->max) {
                print json_encode([
                    'warning' => 'Value must be less than or equal to ' . $act->max
                ]);
                wp_die();
            } elseif ($value <= 0) {
                print json_encode([
                    'warning' => 'Value must be a positive integer'
                ]);
                wp_die();
            }
            $amt = ((int) $value) * ((int) $act->points);
            break;
        case 'radio':
            if (! strlen($value)) {
                print json_encode([
                    'warning' => 'Must select one option'
                ]);
                wp_die();
            }
            break;
        case 'text':
            if (empty($value)) {
                print json_encode([
                    'warning' => 'Cannot save an empty entry'
                ]);
                wp_die();
            } elseif ($act->min && strlen($value) < $act->min) {
                print json_encode([
                    'warning' => 'Text must be at least ' . $act->min . ' characters'
                ]);
                wp_die();
            } elseif ($act->max && strlen($value) > $act->max) {
                print json_encode([
                    'warning' => 'Text must be less than ' . $act->max . ' characters'
                ]);
                wp_die();
            }
    }

    // check to see if they have reached the maximum points allowed for that activity during the challenge
    if ($act->chal_max) {
        $wpdb->query($wpdb->prepare("SET @challenge_id=%d", $chal->id));
        $wpdb->query($wpdb->prepare("SET @activity_id=%d", $act->id));
        $query = $wpdb->prepare("SELECT COALESCE(SUM(total_points),0)
FROM {$wpdb->prefix}point_totals
WHERE user_id = %d", $user_id);
        $total_points = (int) $wpdb->get_var($query);

        // check to see if their current points + current value exceeds the maximum allowed
        if (($total_points + $amt) > $act->chal_max) {
            // check to see if the the maximum is still larger than what they have so we can add the difference from what they just entered
            if ($act->chal_max > $total_points) {
                $params['value'] = $act->chal_max - $total_points;
                $altered = true;
            } else {
                print json_encode([
                    'error' => 'You have reached the maximum points allowed for this activity for the duration of the challenge'
                ]);
                wp_die();
            }
        }
    }

    $res = $wpdb->insert("{$wpdb->prefix}pt_log", $params);

    print json_encode($res && $altered ? [
        'warning' => "You have reached the maximum points allowed for this activity ({$act->chal_max}) so your points were altered"
    ] : ($res ? [
        'success' => 'Activity was added'
    ] : [
        'error' => 'You have already recorded this activity for today'
    ]));

    wp_die();
}

/**
 * Function to get the user's activity
 *
 * @global wpdb $wpdb
 *
 * @return string
 */
function pt_get_my_activity_table()
{
    global $wpdb;
    $_data = [];
    $tp = 0;
    $member_id = filter_input(INPUT_POST, 'member-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $email = strtolower(sanitize_email(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL, FILTER_NULL_ON_FAILURE)));
    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $query = $wpdb->prepare("SELECT user_id
FROM {$wpdb->prefix}pt_participants
WHERE
    challenge_id = %d AND
    member_id = %d AND
    email = %s", $chal_id, $member_id, $email);
    $uid = $wpdb->get_var($query);

    if (! $uid) {
        print json_encode([
            'error' => 'Failed to retrieve user id'
        ]);
    } else {
        $wpdb->query($wpdb->prepare("SET @challenge_id=%d", $chal_id));
        $tp = $wpdb->get_var($wpdb->prepare("SELECT SUM(total_points) FROM {$wpdb->prefix}leader_board WHERE user_id = %d", $uid));

        $query = $wpdb->prepare("SELECT
    ca.*,al.*
FROM {$wpdb->prefix}pt_challenges c
JOIN {$wpdb->prefix}pt_activities ca ON ca.`challenge_id` = c.id
JOIN {$wpdb->prefix}pt_log al ON al.`activity_id` = ca.id
WHERE
    al.`user_id` = %d AND
    c.id = %d
ORDER BY
    al.log_date,al.log_time", $uid, $chal_id);

        $my_act = $wpdb->get_results($query);

        foreach ($my_act as $act) {
            $dt = new DateTime("{$act->log_date} {$act->log_time}", new DateTimeZone(get_option('timezone_string')));
            $_data[] = [
                'name' => html_entity_decode($act->question, ENT_QUOTES | ENT_HTML5),
                'points' => $act->points,
                'date' => $dt->format(get_option('date_format', 'Y-m-d')),
                'time' => $dt->format(get_option('time_format', 'H:i:s')),
                'answer' => html_entity_decode($act->value, ENT_QUOTES | ENT_HTML5),
                'action' => "<i class='far fa-trash-alt' title='Delete this activity so you can input the correct info' data-act-id='{$act->id}' data-log-date='{$act->log_date}' data-user-id='{$act->user_id}'></i>"
            ];
        }

        print json_encode([
            'total_points' => $tp,
            'columns' => [
                [
                    'title' => 'Name',
                    'defaultContent' => '',
                    'data' => 'name'
                ],
                [
                    'title' => 'Points',
                    'defaultContent' => 0,
                    'data' => 'points'
                ],
                [
                    'title' => 'Date',
                    'defaultContent' => '',
                    'data' => 'date'
                ],
                [
                    'title' => 'Time',
                    'defaultContent' => '',
                    'data' => 'time'
                ],
                [
                    'title' => 'Answer',
                    'defaultContent' => '',
                    'data' => 'answer'
                ],
                [
                    'title' => 'Action',
                    'defaultContent' => '',
                    'data' => 'action'
                ]
            ],
            'data' => $_data
        ]);
    }

    wp_die();
}

/**
 * Function to delete the participants activity
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the operation
 */
function pt_delete_participant_activity()
{
    global $wpdb;

    if (! check_ajax_referer('pt-delete-entry', 'security', false)) {
        print json_encode([
            'error' => 'We were unable to verify the nonce'
        ]);
        wp_die();
    }

    $user_id = filter_input(INPUT_POST, 'user-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $act_id = filter_input(INPUT_POST, 'act-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $log_date = filter_input(INPUT_POST, 'log-date', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

    $dt = new DateTime($log_date, new DateTimeZone(get_option('timezone_string')));
    if(!is_a($dt, 'DateTime')) {
        print json_encode([
            'error' => 'Failed to determine the log date'
        ]);
        wp_die();
    }

    $res = $wpdb->delete("{$wpdb->prefix}pt_log", [
        'user_id' => $user_id,
        'activity_id' => $act_id,
        'log_date' => $dt->format("Y-m-d")
    ]);

    print json_encode($res ? [
        'success' => 'Activity was removed'
    ] : [
        'error' => "There was an error removing that activity, please contact the site admin " . get_option('admin-email', null)
    ]);

    wp_die();
}

