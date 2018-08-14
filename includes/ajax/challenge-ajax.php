<?php
add_action('wp_ajax_get-challenge', 'pt_get_challenge');
add_action('wp_ajax_save-challenge', 'pt_save_challenge');
add_action('wp_ajax_delete-challenge', 'pt_delete_challenge');

/**
 * Getter function for the challenge specifics
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string of challenge data
 */
function pt_get_challenge()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'Error retrieving challenge (access denied)'
        ]);
        wp_die();
    }

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pt_challenges WHERE id = %d", $chal_id);
    $chal = $wpdb->get_row($query);

    if(!$chal) {
        print json_encode([
            'error' => 'Unable to find the challenge selected'
        ]);
        wp_die();
    }

    $start = new DateTime($chal->start);
    $end = new DateTime($chal->end);

    $chal->name = html_entity_decode($chal->name, ENT_QUOTES | ENT_HTML5);
    $chal->start = $start->format(get_option('date_format', 'm/d/Y'));
    $chal->end = $end->format(get_option('date_format', 'm/d/Y'));
    $chal->desc = html_entity_decode(stripcslashes($chal->desc), ENT_QUOTES | ENT_HTML5);
    $chal->act_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->prefix}pt_activities WHERE challenge_id=%d", $chal_id));
    $chal->part_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->prefix}pt_participants WHERE challenge_id=%d", $chal_id));

    print json_encode($chal);

    wp_die();
}

/**
 * Function to save a challenge
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the success of the save operation
 */
function pt_save_challenge()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'You are not an admin (access denied)'
        ]);
        wp_die();
    }

    $req_start_date = filter_input(INPUT_POST, 'start-date', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
    $req_end_date = filter_input(INPUT_POST, 'end-date', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

    $start_dt = new DateTime($req_start_date, new DateTimeZone(get_option('timezone_string')));
    if(!is_a($start_dt, 'DateTime')) {
        print json_encode([
            'error' => 'Not a valid start date'
        ]);
        wp_die();
    }
    $end_dt = new DateTime($req_end_date, new DateTimeZone(get_option('timezone_string')));
    if(!is_a($end_dt, 'DateTime')) {
        print json_encode([
            'error' => 'Not a valid end date'
        ]);
        wp_die();
    }
    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    $name = sanitize_text_field(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE));

    $params = [
        'name' => $name,
        'start' => $start_dt->format("Y-m-d"),
        'end' => $end_dt->format("Y-m-d"),
        'approval' => (boolean) filter_input(INPUT_POST, 'approval', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        'desc' => sanitize_textarea_field(filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE))
    ];
    if ($chal_id) {
        $res = $wpdb->update("{$wpdb->prefix}pt_challenges", $params, [
            'id' => $chal_id
        ]);

        if (!$params['approval']) {
            $wpdb->update("{$wpdb->prefix}pt_participants", [
                "approved" => 1
            ], [
                'challenge_id' => $chal_id
            ]);
        }
    } else {
        $params['short_link'] = uniqid();

        $res = $wpdb->insert("{$wpdb->prefix}pt_challenges", $params);
        if($res) {
            $chal_id = $wpdb->insert_id;
        }
    }

    $query = $wpdb->prepare("SELECT short_link FROM {$wpdb->prefix}pt_challenges WHERE id = %d", $chal_id);
    $link = isset($params['short_link']) ? $params['short_link'] : $wpdb->get_var($query);

    print json_encode($res === false ? [
        'error' => 'Update failed'
    ] : [
        'success' => 'Challenge Saved',
        'uid' => $link,
        'id' => $chal_id,
        'name' => $name
    ]);

    wp_die();
}

/**
 * Function to delete a challenge
 *
 * @global wpdb $wpdb
 *
 * @return string JSON encoded string representing the status of the deletion operation
 */
function pt_delete_challenge()
{
    global $wpdb;

    if (! current_user_can('manage_options')) {
        print json_encode([
            'error' => 'Access Denied'
        ]);
        wp_die();
    } elseif(!check_ajax_referer('pt-delete-challenge', 'security', false)) {
        print json_encode([
            'error' => 'We were unable to verify the nonce'
        ]);
        wp_die();
    }

    $chal_id = filter_input(INPUT_POST, 'chal-id', FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    $res = $wpdb->delete("{$wpdb->prefix}pt_challenges", [
        'id' => $chal_id
    ]);

	if($res) {
		$activities = $wpdb->get_results($wpdb->prepare("SELECT id FROM `{$wpdb->prefix}pt_activities` WHERE challenge_id=%d", $chal_id));

		$wpdb->delete("{$wpdb->prefix}pt_activities", [
			'challenge_id' => $chal_id
		]);
		$wpdb->delete("{$wpdb->prefix}pt_participants", [
			'challenge_id' => $chal_id
		]);

		foreach ($activities as $act) {
			$wpdb->delete("{$wpdb->prefix}pt_log", [
				"activity_id" => $act
			]);
		}
	}

	print json_encode($res ? [
        'success' => 'Successfully deleted challenge'
    ] : [
		'error' => $wpdb->last_error
	]);

    wp_die();
}

