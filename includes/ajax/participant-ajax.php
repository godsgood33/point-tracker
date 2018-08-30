<?php
add_action('wp_ajax_get-participants', 'pt_get_participant_table');
add_action('wp_ajax_approve-participant', 'pt_approve_participant');
add_action('wp_ajax_remove-participant', 'pt_remove_participant');
add_action('wp_ajax_add-participant', 'pt_add_participant');
add_action('wp_ajax_join-challenge', 'pt_join_challenge');
add_action('wp_ajax_clear-participants', 'pt_clear_participants');

/**
 * Function to get all the challenge participants
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded array of stdClass objects of all challenge participants
 */
function pt_get_participant_table()
{
    global $wpdb;
    $_data = [];

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $chal = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE id = %d", $chal_id));

    if (! $chal) {
        print json_encode([
            'error' => 'Unable to find the selected challenge'
        ]);
        wp_die();
    }

    $query = $wpdb->prepare("CREATE TEMPORARY TABLE tmp_log
SELECT IF(ca.type = 'number',(ca.points * al.value),ca.points) AS 'total_points',cp.*
FROM {$wpdb->prefix}pt_participants cp
LEFT JOIN {$wpdb->prefix}pt_log al ON al.user_id = cp.user_id
LEFT JOIN {$wpdb->prefix}pt_activities ca on ca.id = al.activity_id AND ca.challenge_id = cp.challenge_id
WHERE cp.challenge_id = %d
GROUP BY cp.user_id, al.activity_id, al.log_date", $chal_id);
    $wpdb->query($query);

    $query = "SELECT SUM(al.total_points) AS 'total',al.*
FROM tmp_log al
GROUP BY al.user_id";
    $participants = $wpdb->get_results($query);

    foreach ($participants as $part) {
        $_data[] = [
            'approved' => "<input type='checkbox' " . (! $chal->approval ? "disabled" : "") . " class='approve' " . ((boolean) $part->approved ? " checked" : "") . " data-user-id='{$part->user_id}' />",
            'memberid' => $part->member_id,
            'name' => html_entity_decode($part->name, ENT_QUOTES | ENT_HTML5),
            'email' => sanitize_email($part->email),
            'totalPoints' => $part->total,
            'action' => "<i class='far fa-trash-alt' title='Remove this participant from the activity' data-user-id='{$part->user_id}'></i>"
        ];
    }

    $columns = [
        [
            'title' => 'Approved',
            'defaultContent' => "",
            'data' => 'approved'
        ],
        [
            'title' => 'Member ID',
            'defaultContent' => '',
            'data' => 'memberid'
        ],
        [
            'title' => 'Name',
            'defaultContent' => '',
            'data' => 'name'
        ],
        [
            'title' => 'Email',
            'defaultContent' => '',
            'data' => 'email'
        ],
        [
            'title' => 'Total Points',
            'defaultContent' => 0,
            'data' => 'totalPoints'
        ],
        [
            'title' => 'Action',
            'defaultContent' => '',
            'data' => 'action'
        ]
    ];

    print json_encode([
        'columns' => $columns,
        'data' => $_data
    ]);
    wp_die();
}

/**
 * Function to approve a participant
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the requested approval operation
 */
function pt_approve_participant()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'You are not the coordinator for this challenge (access denied)'
        ]);
        wp_die();
    }

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $user_id = filter_input(INPUT_POST, 'user-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $res = $wpdb->update("{$wpdb->prefix}pt_participants", [
        'approved' => 1
    ], [
        'challenge_id' => $chal_id,
        'user_id' => $user_id
    ]);

    if ($res) {
        $email = $wpdb->get_var($wpdb->prepare("SELECT email FROM {$wpdb->prefix}pt_participants WHERE user_id = %d AND challenge_id = %d", $user_id, $chal_id));
        if($email) {
            wp_mail($email, 'Approved for Team Challenge', PT_USER_APPROVED);
        }

        print json_encode([
            'success' => 'Participant has been approved for the challenge'
        ]);
    } else {
        print json_encode([
            'error' => $wpdb->last_error
        ]);
    }

    wp_die();
}

/**
 * Function to remove a participant from a challenge
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the requested removal operation
 */
function pt_remove_participant()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'You are not the coordinator of this challenge (access denied)'
        ]);
        wp_die();
    } elseif (! check_ajax_referer('pt-delete-participant', 'security', false)) {
        print json_encode([
            'error' => 'We were unable to verify the nonce'
        ]);
        wp_die();
    }

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $user_id = filter_input(INPUT_POST, 'user-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $email = $wpdb->get_var($wpdb->prepare("SELECT email FROM {$wpdb->prefix}pt_participants WHERE user_id = %d AND challenge_id = %d", $user_id, $chal_id));

    $query = $wpdb->prepare("DELETE al.*
FROM {$wpdb->prefix}pt_log al
JOIN {$wpdb->prefix}pt_participants cp ON cp.user_id = al.user_id
WHERE
    cp.challenge_id = %d AND
    cp.user_id = %d", $chal_id, $user_id);
    $wpdb->query($query);

    $res = $wpdb->delete("{$wpdb->prefix}pt_participants", [
        'challenge_id' => $chal_id,
        'user_id' => $user_id
    ]);

    if ($res) {
        if($email) {
            wp_mail($email, 'Removed from Team Challenge', PT_USER_DENIED);
        }

        print json_encode([
            'success' => 'User was removed from the challenge'
        ]);
    } else {
        print json_encode([
            'error' => $wpdb->last_error
        ]);
    }

    wp_die();
}

/**
 * Function to manually add a participant
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the requested operation
 */
function pt_add_participant()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'You are not the coordinator of this challenge (access denied)'
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

    $member_id = filter_input(INPUT_POST, 'member-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $name = sanitize_text_field(filter_input(INPUT_POST, 'user-name', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));
    $email = strtolower(sanitize_email(filter_input(INPUT_POST, 'user-email', FILTER_SANITIZE_EMAIL, FILTER_NULL_ON_FAILURE)));
    $now = new DateTime("now", new DateTimeZone(get_option("timezone_string")));

    if (! $member_id) {
        print json_encode([
            'error' => 'Member ID must be numeric'
        ]);
        wp_die();
    } elseif (! $name) {
        print json_encode([
            'error' => "Must specify the user's name"
        ]);
        wp_die();
    } elseif (! $email) {
        print json_encode([
            'error' => "Must specify the user's e-mail"
        ]);
        wp_die();
    }

    if ($uid = email_exists($email)) {
        $res = $wpdb->insert("{$wpdb->prefix}pt_participants", [
            'challenge_id' => $chal_id,
            'user_id' => $uid,
            'name' => $name,
            'email' => $email,
            'member_id' => $member_id,
            'approved' => 1,
            'date_joined' => $now->format("Y-m-d"),
            'date_approved' => $now->format("Y-m-d")
        ]);
    } else {
        // generate a random password and create an account
        $random_pwd = wp_generate_password();
        $uid = wp_create_user($email, $random_pwd, $email);

        $res = $wpdb->insert("{$wpdb->prefix}pt_participants", [
            'challenge_id' => $chal_id,
            'user_id' => $uid,
            'name' => $name,
            'email' => $email,
            'member_id' => $member_id,
            'approved' => 1,
            'date_joined' => $now->format("Y-m-d"),
            'date_approved' => $now->format("Y-m-d")
        ]);
    }

    print json_encode($res ? [
        'success' => 'Successfully added participant',
        'user_id' => $uid,
        'name' => $name,
        'email' => $email
    ] : [
        'error' => $wpdb->last_error
    ]);

    wp_mail("{$name} <{$email}>", "Added to challenge", str_replace([
        "{name}",
        "{desc}"
    ], [
        html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5),
        html_entity_decode($chal->desc, ENT_QUOTES | ENT_HTML5)
    ], PT_USER_ADDED));

    wp_die();
}

/**
 * Function to allow a participant to join a challenge
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the requested join operation
 */
function pt_join_challenge()
{
    global $wpdb;

    $chal_link = filter_input(INPUT_POST, 'chal-link', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
    $member_id = filter_input(INPUT_POST, 'member-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE short_link = %s", $chal_link);
    $chal = $wpdb->get_row($query);

    if(!$chal) {
        print json_encode([
            'error' => 'Unable to find the selected challenge'
        ]);
        wp_die();
    }

    $name = sanitize_text_field(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));
    $email = strtolower(sanitize_email(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)));

    $now = new DateTime("now", new DateTimeZone(get_option("timezone_string")));

    $res = $wpdb->insert("{$wpdb->prefix}pt_participants", [
        'challenge_id' => $chal->id,
        'user_id' => get_current_user_id(),
        'approved' => ($chal->approval ? '0' : '1'),
        'date_joined' => $now->format("Y-m-d"),
        'date_approved' => ($chal->approval ? null : $now->format("Y-m-d")),
        'member_id' => $member_id,
        'name' => $name,
        'email' => $email
    ]);

    $chal_name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);

    if (get_option('admin_email', null)) {
        wp_mail(get_option('admin_email'), "Participant joined {$chal_name}", str_replace([
            "{name}",
            "{chal}"
        ], [
            html_entity_decode($name, ENT_QUOTES | ENT_HTML5),
            $chal_name
        ], PT_NEW_PARTICIPANT));
    }

    print json_encode($res ? [
        'success' => ($chal->approval ? "Requested to join" : "Joined challenge"),
        'redirect' => !$chal->approval
    ] : [
        'error' => 'Unknown error'
    ]);

    wp_die();
}

/**
 * Function to remove all participants and log from a challenge
 *
 * @global wpdb $wpdb
 */
function pt_clear_participants()
{
    global $wpdb;
    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $query = $wpdb->prepare("DELETE l.* FROM {$wpdb->prefix}pt_log l JOIN {$wpdb->prefix}pt_participants p ON p.user_id = l.user_id WHERE p.challenge_id = %d", $chal_id);
    $res = $wpdb->query($query);

    if($res) {
        $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}pt_participants WHERE challenge_id = %d", $chal_id);
        $res = $wpdb->query($query);
    }

    print json_encode($res ? [
        'success' => "Deleted participants from challenge"
    ] : [
        'error' => $wpdb->last_error
    ]);
    wp_die();
}
