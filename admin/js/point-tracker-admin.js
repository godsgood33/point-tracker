jQuery(function($) {
	/**
	 * Function to get the challenge details
	 */
	function get_challenge() {
		// check that a challenge was selected from the dropdown
		if (!$("#challenge").val()) {
			reset_challenge_form();
			return;
		}

		$.ajax(ajaxurl, {
			data : {
				action : 'get-challenge',
				'chal-id' : $('#challenge').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				$('#name').val(data.name);
				$('#start-date').val(data.start);
				$('#end-date').val(data.end);
				$('#desc').text(data.desc);

				$('#act-count').text(data.act_count);
				$('#part-count').text(data.part_count);

				$('#approval').prop('checked', stringToBoolean(data.approval));
				
				if(data.winner) {
					$('#winner').html("Winner is " + data.winner);
					$('#winner').show();
				} else {
					$('#winner').html('');
					$('#winner').hide();
				}

				if ($('#approval').is(":checked")) {
					$('#link').html(
							'<a href="/index.php/challenge-list/?chal='
									+ data.short_link + '" target="_blank">'
									+ data.short_link + '</a>');
				} else {
					$('#link').html(
							'<a href="/index.php/challenge/?chal='
									+ data.short_link + '" target="_blank">'
									+ data.short_link + '</a>');
				}
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to save a challenge
	 */
	function save_challenge() {
		$.ajax(ajaxurl, {
			data : {
				action : 'save-challenge',
				'chal-id' : $('#challenge').val(),
				name : $('#name').val(),
				'start-date' : $('#start-date').val(),
				'end-date' : $('#end-date').val(),
				'desc' : $('#desc').val(),
				'approval' : ($('#approval').is(":checked") ? '1' : '0')
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				if (!$('#challenge').val()) {
					$('#challenge').append(
							"<option value='" + data.id + "' selected>"
									+ data.name + "</option>");
				}
				$('#link').html(
						'<a href="/index.php/challenge-list/?chal=' + data.uid
								+ '" target="_blank">' + data.uid + '</a>');
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to delete a challenge
	 */
	function delete_challenge() {
		if (!$('#challenge').val().length) {
			return;
		}

		if (!confirm("Are you sure you want to delete this challenge?")) {
			return;
		}

		$.ajax(ajaxurl, {
			data : {
				'action' : 'delete-challenge',
				'chal-id' : $('#challenge').val(),
				'security' : $('#_wpnonce').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				$('#challenge option:selected').remove();

				reset_challenge_form();
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}
	
	/**
	 * Function to remove the winner from a challenge
	 */
	function remove_winner() {
		if(!$('#challenge').val()) {
			return;
		}
		
		$.ajax(ajaxurl, {
			data : {
				action : 'remove-winner',
				'chal-id' : $('#challenge').val()
			},
			success : function(data) {
				if(!check_for_error(data)) {
					return;
				}
				
				$('#winner').html('');
				$('#winner').hide();
			},
			error : function(xhr, status, error) {
				console.log(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to reset all form elements on the challenge page
	 */
	function reset_challenge_form() {
		$('#challenge,#name,#start-date,#end-date').val('');
		$('#approval').prop('checked', false);
		$('#act-count,#part-count').html(0);
		$('#link,#winner').html('');
		$('#desc').text('');
		
		$('#winner').hide();
	}

	/**
	 * Challenge page events
	 */
	$('#challenge').change(get_challenge);
	$('#save-challenge').click(save_challenge);
	$('#delete-challenge').click(delete_challenge);
	$('#remove-winner').click(remove_winner);

	/**
	 * Function to retrieve the challenge activities
	 */
	function get_challenge_activities() {
		if (!$('#challenge_activities').val().length) {
			return;
		}

		$.ajax(ajaxurl, {
			data : {
				'action' : 'get-activities',
				'chal-id' : $('#challenge_activities').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				if ($.fn.DataTable.isDataTable('#activity-table')) {
					table1.destroy();
					$('#activity-table').empty();
				}

				table1 = $('#activity-table').DataTable({
					data : data.data.slice(0),
					columns : data.columns.slice(0),
					paging : false,
					buttons : [ 'copyHtml5', 'csv', 'excel', 'pdf', 'print' ],
					dom : 'Bfrtip'
				});

				$('.tooltip-field').tooltip({
					show : {
						effect : 'slideDown',
						delay : 100
					},
					hide : {
						effect : 'slideUp',
						delay : 250
					}
				});

				if (data.group_msg) {
					$("#group-msg").show();
				} else {
					$('#group-msg').hide();
				}

				reset_activity_form();
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to save activity in a challenge
	 */
	function save_activity() {
		$('#msg').removeClass('err-msg');
		var name = $('#act-name').val().toLowerCase().replace(/[^a-z0-9]/g, '');

		if (!validate_activity()) {
			return;
		}
		
		$.ajax(ajaxurl, {
			data : {
				action : 'save-activity',
				'chal-id' : $('#challenge_activities').val(),
				'act-id' : $('#act-id').val(),
				name : name,
				points : $('#act-pts').val(),
				type : $('#act-type').val(),
				question : $('#act-ques').val(),
				label : $('#act-labels').val(),
				min : $('#act-min').val(),
				max : $('#act-max').val(),
				'chal-max' : $('#act-chal-max').val(),
				desc : $('#act-desc').val(),
				order : $('#act-order').val(),
				hidden : stringToBoolean($('#act-hidden').is(":checked")),
				group : $('#act-group').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				var act_name = data.name;
				if ($('#act-hidden').is(":checked")) {
					act_name = "<i>" + data.name + "*</i>";
				}

				var t = $('#activity-table').DataTable();
				if (!$('#act-id').val()) {
					t.row.add({
						order : $('#act-order').val(),
						type : $('#act-type option:selected').text(),
						name : act_name,
						points : $('#act-pts').val(),
						chal_max : ($('#act-chal-max').val() ? $('#act-chal-max').val() : 0),
						question : data.question,
						desc : data.desc,
						group : data.group,
						extras : ($('#act-type').val() === 'checkbox' || $('#act-type').val() === 'radio' ? data.label : ($('#act-min').val() ? $('#act-min').val() : 0)
									+ "/" + ($('#act-max').val() ? $('#act-max').val() : 0)),
						action : "<i class='fas fa-edit' data-id='"
								+ data.id
								+ "'></i>&nbsp;&nbsp;<i class='far fa-trash-alt' data-id='"
								+ data.id
								+ "'></i>"
					}).draw(false);
				} else {
					var tmp = t.row($('#t-row').val()).data();
					tmp.order = $('#act-order').val();
					tmp.type = $('#act-type option:selected')
							.text();
					tmp.name = act_name;
					tmp.points = $('#act-pts').val();
					tmp.chal_max = ($('#act-chal-max').val() ? $(
							'#act-chal-max').val()
							: 0);
					tmp.question = data.question;
					tmp.desc = data.desc;
					tmp.group = data.group;
					tmp.extras = ($('#act-type').val() === 'checkbox' || $('#act-type').val() === 'radio' ? data.label : ($('#act-min').val() ? $('#act-min').val() : 0) 
							+ "/" + ($('#act-max').val() ? $('#act-max').val() : 0));

					t.row($('#t-row').val()).invalidate(tmp)
							.draw();
				}

				if (data.group_msg) {
					$('#group-msg').show();
				} else {
					$('#group-msg').hide();
				}

				reset_activity_form();
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to make the appropriate fields visible
	 */
	function change_act_type() {
		$('#act-labels,#act-min,#act-max').hide();
		if ($('#act-type').val() == 'checkbox'
				|| $('#act-type').val() == 'radio') {
			$('#act-labels').show();
		} else if ($('#act-type').val() == 'text'
				|| $('#act-type').val() == 'number') {
			$('#act-min,#act-max').show();
		}
	}

	/**
	 * Function to edit activity
	 */
	function edit_activity() {
		$.ajax(ajaxurl, {
			data : {
				action : 'get-activity-details',
				'act-id' : $(this).data('id'),
				'chal-id' : $('#challenge_activities').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				$('#t-row').val(data.order - 1);
				$('#act-id').val(data.id);
				$('#act-type').val(data.type);
				$('#act-name').val(data.name);
				$('#act-pts').val(data.points);
				$('#act-chal-max').val(data.chal_max);
				$('#act-ques').val(data.question);
				$('#act-desc').val(data.desc);
				$('#act-group').val(data.group);
				$('#act-hidden').prop('checked',
						(stringToBoolean(data.hidden) ? true : false));
				$('#act-order').val(data.order);
				$('#act-labels').val(data.label);
				$('#act-min').val(data.min);
				$('#act-max').val(data.max);

				change_act_type();
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to validate activity entry
	 */
	function validate_activity() {
		var ret = true;
		$('#msg span').remove();
		$('#msg').removeClass('err-msg');

		if (!$('#act-type').val()) {
			$('#msg').append("<div>You need to select an activity type</div>");
			ret = false;
		}

		if (!$('#act-name').val()) {
			$('#msg').append('<div>Please enter a name for the activity</div>');
			ret = false;
		}

		if (!$('#act-pts').val() && !$('#act-hidden').is(":checked")) {
			$('#msg').append(
					'<div>Please enter a point value for this activity</div>');
			ret = false;
		}

		if (!$('#act-ques').val()) {
			$('#msg').append(
					'<div>Please enter a question to ask the user</div>');
			ret = false;
		}

		if (!$('#act-desc').val()) {
			$('#msg')
					.append(
							'<div>Please enter a long description for the question</div>');
			ret = false;
		}

		if (!$('#act-order').val()) {
			$('#msg')
					.append(
							'<div>Please enter a numeric order for the question to appear</div>');
			ret = false;
		}

		return ret;
	}

	/**
	 * Function to reset all form elements on the activities page
	 */
	function reset_activity_form() {
		$(
				'#act-type,#act-name,#act-ques,#act-desc,#act-labels,#act-id,#act-group')
				.val('')
		$('#act-pts,#act-chal-max,#act-min,#act-max').val(0);
		$('#act-labels,#act-min,#act-max').hide();
		$('#act-pts,#act-chal-max').prop('disabled', false);
		$('#act-hidden').prop('checked', false);

		$('.fa-trash-alt').off('click');
		$('.fa-edit').off('click');

		$('.fa-trash-alt').click(
				function() {
					var button = $(this);
					$.ajax(ajaxurl, {
						data : {
							'action' : 'delete-activity',
							'act-id' : $(this).data('id'),
							'chal-id' : $('#challenge_activities').val(),
							'security' : $('#_wpnonce').val()
						},
						beforeSend : beforeAjaxSend,
						complete : ajaxComplete,
						success : function(data) {
							if (!check_for_error(data)) {
								return;
							}
							table1.row($(button).closest('tr').index())
									.remove().draw();
							$('#act-order').val(table1.rows().count() + 1);
						},
						error : function(xhr, status, error) {
							console.error(error);
						},
						dataType : 'json',
						method : 'post'
					});
				});
		$('.fa-edit').click(edit_activity);
		$('#act-order').val(table1.rows().count() + 1);
	}

	/**
	 * Activity page events
	 */
	$('#save-activity').click(save_activity);
	$('#challenge_activities').change(get_challenge_activities);
	$('#act-type').change(change_act_type);
	$('#act-hidden').click(function() {
		$('#act-pts,#act-chal-max').prop('disabled', false);
		if ($(this).is(":checked")) {
			$('#act-pts,#act-chal-max').val('0');
			$('#act-pts,#act-chal-max').prop('disabled', true);
		}
	});

	/**
	 * Function to get the list of participants in a challenge
	 */
	function get_participants() {
		if (!$('#challenge_participants').val()) {
			return;
		}

		$.ajax(ajaxurl, {
			data : {
				'action' : 'get-participants',
				'chal-id' : $('#challenge_participants').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				if ($.fn.DataTable.isDataTable('#participant-table')) {
					table1.destroy();
					$('#participant-table').empty();
				}

				table1 = $('#participant-table').DataTable({
					data : data.data.slice(0),
					columns : data.columns.slice(0),
					order : [ [ 4, 'desc' ] ],
					buttons : [ 'copyHtml5', 'csv', 'excel', 'pdf', 'print' ],
					dom : 'Bfrtip'
				});

				$('.approve').click(approve_participant);
				$('.fa-trash-alt').click(remove_participant);
				$('.fa-trophy').click(mark_winner);
				
				if($.isNumeric(data.winner)) {
					$(table1.row(data.winner).nodes()[0]).addClass('winner');
				}
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to approve a participant in a challenge
	 */
	function approve_participant() {
		var uid = $(this).data('user-id');

		$.ajax(ajaxurl, {
			data : {
				'action' : 'approve-participant',
				'chal-id' : $('#challenge_participants').val(),
				'user-id' : uid
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				$(this).prop('checked', true);
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to remove a participant from a challenge
	 */
	function remove_participant() {
		if (!$('#challenge_participants').val()) {
			return;
		}

		var uid = $(this).data('user-id');

		$.ajax(ajaxurl, {
			data : {
				'action' : 'remove-participant',
				'chal-id' : $('#challenge_participants').val(),
				'user-id' : uid,
				'security' : $('#_wpnonce').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				table1.row().remove().draw();
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	function clear_participants() {
		$.ajax(ajaxurl, {
			data : {
				action : 'clear-participants',
				'chal-id' : $('#challenge_participants').val(),
				'security' : $('#_wpnonce').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				if ($.fn.DataTable.isDataTable('#participant-table')) {
					table1.destroy();
					$('#participant-table').empty();
				}
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Function to manually add a participant to a challenge
	 */
	function add_participant() {
		if (!$('#challenge_participants').val()) {
			return;
		}
		var numeric = /^\d+$/;
		var ret = true;

		$('#msg div').remove();

		if (!$('#member-id').val() || !$('#member-id').val().match(numeric)) {
			$('#msg').append('<div>Member ID must be numeric</div>');
			ret = false;
		}

		if (!$('#user-name').val()) {
			$('#msg').append('<div>Must add users name</div>');
			ret = false;
		}

		if (!$('#user-email').val()) {
			$('#msg').append('<div>Must add the users email</div>');
			ret = false;
		}

		if (!ret) {
			$('#msg').show(300);
			$('#msg').addClass('err-msg');

			setTimeout(function() {
				$('#msg').hide(300);
			}, 3000);

			return ret;
		}

		$.ajax(ajaxurl, {
			data : {
				'action' : 'add-participant',
				'chal-id' : $('#challenge_participants').val(),
				'member-id' : $('#member-id').val(),
				'user-name' : $('#user-name').val(),
				'user-email' : $('#user-email').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				table1.row.add({
					approved : "<input type='checkbox' class='approved' checked />",
					memberid : $('#member-id')
							.val(),
					name : data.name,
					email : data.email,
					totalPoints : 0,
					action : "<i class='far fa-trash-alt' title='Remove this participant from the activity' data-user-id='"
							+ data.user_id
							+ "'></i>" + "&nbsp;&nbsp" +
							"<i class='fas fa-trophy' title='Mark participant as challenge winner' data-user-id='"
							+ data.user_id
							+ "'></i>"
				}).draw(false);

				$('.fa-trash-alt').off('click');
				$('.fa-trash-alt').on('click',
						remove_participant);
				$('.fa-trophy').off('click');
				$('.fa-trophy').on('click', mark_winner);
				$('#admin-add-participant').hide(300);
			},
			error : function(xhr, status, error) {
				console.error(data.error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Method to mark a participant as the challenge winner
	 */
	function mark_winner() {
		if (!$('#challenge_participants').val()) {
			return;
		}
		var user_id = $(this).data('user-id');
		button = $(this);
		
		$.ajax(ajaxurl, {
			data : {
				action : 'mark-winner',
				'chal-id' : $("#challenge_participants").val(),
				'user-id' : user_id
			},
			success : function(data) {
				if(!check_for_error(data)) {
					return;
				}
				
				$(button).closest('tr').addClass('winner');
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Participant page events
	 */
	$('#challenge_participants').change(get_participants);
	$('#add-challenge-participant').click(function() {
		$('#admin-add-participant').toggle(300);
	});
	$('#add-participant').click(add_participant);
	$('#clear-participants').click(clear_participants);

	/**
	 * Function to retrieve all the participants activity
	 */
	function get_log() {
		if (!$('#participant-log').val()) {
			return;
		}
		$
				.ajax(
						ajaxurl,
						{
							data : {
								action : 'get-log',
								'chal-id' : $('#participant-log').val()
							},
							beforeSend : beforeAjaxSend,
							complete : ajaxComplete,
							success : function(data) {
								if (!check_for_error(data)) {
									return;
								}

								if ($.fn.DataTable
										.isDataTable('#participant-log-table')) {
									table1.destroy();
									$('#participant-log-table').empty();
								}

								table1 = $('#participant-log-table')
										.DataTable(
												{
													data : data.data.slice(0),
													columns : data.columns
															.slice(0),
													ordering : false,
													buttons : [ 'copyHtml5',
															'csv', 'excel',
															'pdf', 'print' ],
													dom : 'Bfrtip',
													initComplete : function() {
														this
																.api()
																.columns()
																.every(
																		function() {
																			var column = this;
																			var header = $(
																					column
																							.header())
																					.text()
																					.slice(
																							0);

																			var select = $(
																					"<select><option value=''>"
																							+ header
																							+ "</option></select>")
																					.appendTo(
																							$(
																									column
																											.header())
																									.empty())
																					.on(
																							'change',
																							function() {
																								var val = $.fn.dataTable.util
																										.escapeRegex($(
																												this)
																												.val());
																								column
																										.search(
																												val ? '^'
																														+ val
																														+ '$'
																														: '',
																												true,
																												false)
																										.draw();
																							});

																			column
																					.data()
																					.unique()
																					.sort()
																					.each(
																							function(
																									d,
																									j) {
																								select
																										.append("<option value='"
																												+ d
																												+ "'>"
																												+ d
																												+ "</option>");
																							});
																		});
													}
												});

								$('#participant-log-table tbody').on('click',
										'i', delete_activity);
							},
							error : function(xhr, status, error) {
								console.error(error);
							},
							dataType : 'json',
							method : 'post'
						});
	}

	/**
	 * Function to delete a specific activity
	 */
	function delete_activity() {
		var button = $(this);
		$.ajax(ajaxurl, {
			data : {
				'action' : 'delete-participant-activity',
				'act-id' : $(this).data('act-id'),
				'user-id' : $(this).data('user-id'),
				'log-date' : $(this).data('log-date'),
				'security' : $('#_wpnonce').val()
			},
			beforeSend : beforeAjaxSend,
			complete : ajaxComplete,
			success : function(data) {
				if (!check_for_error(data)) {
					return;
				}

				table1.row().remove().draw();
			},
			error : function(xhr, status, error) {
				console.error(error);
			},
			dataType : 'json',
			method : 'post'
		});
	}

	/**
	 * Entry log page events
	 */
	$('#participant-log').change(get_log);

	/**
	 * Function to convert strings to boolean
	 * 
	 * @param string
	 *            string
	 * 
	 * @return boolean
	 */
	function stringToBoolean(string) {
		if (string == undefined) {
			return false;
		}

		if (typeof string === "boolean") {
			return string;
		} else if (typeof string !== "string") {
			console.error("unknown type: " + typeof string);
			return false;
		}

		switch (string.toLowerCase().trim()) {
		case "true":
		case "yes":
		case "1":
			return true;
		case "false":
		case "no":
		case "0":
		case null:
			return false;
		default:
			return Boolean(string);
		}
	}

	/**
	 * Function to check for errors returned from the AJAX request
	 * 
	 * @return boolean Returns true if everything is okay, false otherwise
	 */
	function check_for_error(data) {
		$('#msg div').remove();
		$('#msg').removeClass('err-msg,warn-msg');
		var err = false;

		if (data == '0') {
			$('#msg').html("<div>There was an error</div>");
			$('#msg').addClass('err-msg');
			err = true;
		}
		if (data.error) {
			$('#msg').html('<div>' + data.error + '</div>');
			$('#msg').addClass('err-msg');
			err = true;
		}
		if (data.warning) {
			$('#msg').html('<div>' + data.warning + '</div>');
			$('#msg').addClass('warn-msg');
			err = true;
		}
		if (data.success) {
			$('#msg').html('<div>' + data.success + '</div>');
		}

		if ($('#msg').html()) {
			$('#msg').show(300);
		}

		if (err) {
			setTimeout(function() {
				$('#msg').hide(300);
				$('#msg').html("");
			}, 3000);
		} else {
			setTimeout(function() {
				$('#msg').hide(300);
				$('#msg').html("");
			}, 1500);
		}

		return !err;
	}

	/**
	 * Method to retrieve the data for the admin dashboard widget
	 */
	function get_widget_data() {
		var type = $('#pt-widget-type').val();
		$('#pt-widget-results').html('');

		if (!$('#pt-widget-challenge').val()) {
			return;
		} else if (type != 'challenge' && type != 'participants'
				&& type != 'log' && type != 'activities') {
			alert('Please select a valid report type to query');
			return;
		}

		$
				.ajax(
						ajaxurl,
						{
							data : {
								action : 'pt-get-widget-data',
								'chal-id' : $('#pt-widget-challenge').val(),
								'report-type' : type
							},
							beforeSend : beforeAjaxSend,
							complete : ajaxComplete,
							success : function(data) {
								if (type == 'challenge') {
									$('#pt-widget-results')
											.html(
													"<p class='close' style='margin-top:10px;'><b>Start</b>: "
															+ data.start
															+ "</p>"
															+ "<p class='close'><b>End</b>: "
															+ data.end
															+ "</p>"
															+ "<p class='close'><b>Participant Count</b>: "
															+ data.p_count
															+ "</p>"
															+ "<p class='close'><b>Total Points</b>: "
															+ data.total_points
															+ "</p>"
															+ "<p class='close'><b>Point Leader</b>: "
															+ data.leader
															+ " ("
															+ data.leaders_points
															+ ")</p>");
								} else if (type == 'activities') {
									$('#pt-widget-results')
											.html(
													"<table width='100%'><thead><tr><th>Group</th><th>Name</th><th>Points</th><th>Total</th></tr></thead><tbody></tbody></table>")
									for ( var x in data) {
										$('#pt-widget-results tbody').append(
												"<tr>" + "<td>" + data[x].group
														+ "</td>" + "<td>"
														+ data[x].name
														+ "</td>" + "<td>"
														+ data[x].points
														+ "</td>" + "<td>"
														+ data[x].pt + "</td>"
														+ "</tr>");
									}
								} else if (type == 'participants') {
									$('#pt-widget-results')
											.html(
													"<table width='100%'><thead><tr><th>Name</th><th>Email</th><th>Approved</th><th>Points</th></tr></thead><tbody></tbody></table>");
									for ( var x in data) {
										$('#pt-widget-results tbody')
												.append(
														"<tr>"
																+ "<td>"
																+ data[x].name
																+ "</td>"
																+ "<td><a href='mailto:"
																+ data[x].email
																+ "'>"
																+ data[x].email
																+ "</a></td>"
																+ "<td>"
																+ (data[x].approved ? "Yes"
																		: "No")
																+ "</td>"
																+ "<td>"
																+ data[x].pt
																+ "</td>"
																+ "</tr>");
									}
								} else if (type == 'log') {
								}
							},
							error : function(xhr, status, error) {
								console.error(error);
							},
							dataType : 'json',
							method : 'post'
						});
	}

	/**
	 * Function to call before sending an AJAX request
	 */
	function beforeAjaxSend() {
		$('#loading,#waiting').show();
		$('#waiting').animate({
			'opacity' : '0.5'
		}, 300, 'linear');
	}

	/**
	 * Function to call after completing an AJAX request
	 */
	function ajaxComplete() {
		$('#loading,#waiting').hide();
		$('#waiting').animate({
			'opacity' : '0'
		}, 300, 'linear');
	}

	var opts = {
		lines : 25, // The number of lines to draw
		length : 25, // The length of each line
		width : 5, // The line thickness
		radius : 50, // The radius of the inner circle
		scale : 1, // Scales overall size of the spinner
		corners : 1, // Corner roundness (0..1)
		color : '#000', // #rgb or #rrggbb or array of colors
		opacity : 0.25, // Opacity of the lines
		rotate : 0, // The rotation offset
		direction : 1, // 1: clockwise, -1: counterclockwise
		speed : .5, // Rounds per second
		trail : 60, // Afterglow percentage
		fps : 20, // Frames per second when using setTimeout() as a fallback
					// for CSS
		zIndex : 2e9, // The z-index (defaults to 2000000000)
		className : 'spin-thingy', // The CSS class to assign to the spinner
		top : '50%', // Top position relative to parent
		left : '50%', // Left position relative to parent
		shadow : false, // Whether to render a shadow
		hwaccel : false, // Whether to use hardware acceleration
		position : 'absolute', // Element positioning
	};
	var target, spinner, table1, table2;
	$('#start-date').datepicker({
		dateFormat : my_object.date_format,
		onSelect : function(date) {
			var dt2 = $('#end-date');
			var minDate = $(this).datepicker('getDate');
			// sets dt2 maxDate to the last day of 30 days window
			dt2.datepicker('option', 'minDate', minDate);
			// $(this).datepicker('option', 'minDate', minDate);
		}
	});
	$('#end-date').datepicker({
		dateFormat : my_object.date_format
	});

	$('#act-group').autocomplete({
		source : function(req, res) {
			if (!$('#challenge_activities').val()) {
				return false;
			}
			$.ajax(ajaxurl, {
				data : {
					action : 'ac-group',
					keyword : $('#act-group').val(),
					'chal-id' : $("#challenge_activities").val()
				},
				method : 'post',
				success : function(data) {
					res(JSON.parse(data));
				},
				error : function(errorThrown) {
					console.error(errorThrown);
				}
			})
		},
		minLength : 2,
		select : function(e, ui) {
			console.log("selected: " + ui.item.value + " aka " + ui.item.id);
		}
	});

	if ($('#loading').length) {
		target = document.getElementById('loading');
		spinner = new Spinner(opts).spin(target);
	}

	$('.tooltip-field').tooltip({
		show : {
			effect : 'slideDown',
			delay : 100
		},
		hide : {
			effect : 'slideUp',
			delay : 250
		}
	});

	$('#pt-widget-challenge,#pt-widget-type').change(get_widget_data);
});
