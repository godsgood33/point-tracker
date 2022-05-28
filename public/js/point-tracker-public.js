jQuery(function ($) {
  /**
   * Function for user to join a challenge
   */
  function join_challenge() {
    var numeric = /^\d+$/;
    var good = true;
    if (!$('#member-id').val().match(numeric)) {
      good = check_for_error({
        error: 'Invalid member ID'
      });
    }
    if (good && !$('#name').val()) {
      good = check_for_error({
        error: 'Invalid name'
      });
    }
    if (good && !$('#email').val()) {
      good = check_for_error({
        error: 'Invalid email'
      });
    }

    if (good) {
      $.ajax(ajax_object.ajax_url, {
        data: {
          'action': 'join-challenge',
          'chal-link': $('#chal-link').val(),
          'member-id': $('#member-id').val(),
          'name': $('#name').val(),
          'email': $('#email').val()
        },
        beforeSend: beforeAjaxSend,
        complete: ajaxComplete,
        success: function (data) {
          if (!check_for_error(data)) {
            return;
          }

          alert(data.success);
          if (data.redirect) {
            location.href = ajax_object.chal_page + '/?chal=' + id;
          }
        },
        error: function (xhr, status, error) {
          console.error(error);
        },
        dataType: 'json',
        method: 'post'
      });
    }
    dialog.dialog("close");
  }

  /**
   * Function to get the participants activity
   */
  function get_my_activity() {
    $('#msg').html("");
    var numeric = /^\d+$/;
    if (!$('#member-id').val() || !$('#member-id').val().match(numeric)) {
      $('#msg').append("<div>Please enter a valid member ID</div>");
      $('#msg').slideToggle(300);
      $('#msg').addClass('warn-msg');
      setTimeout(function () {
        $('#msg').slideToggle(300);
      }, 5000);

      return false;
    } else if (!$('#email').val()) {
      $('#msg').append("<div>Please enter your email</div>");
      $('#msg').slideToggle(300);
      $('#msg').addClass('warn-msg');
      setTimeout(function () {
        $('#msg').slideToggle(300);
      }, 5000);
      return false;
    }

    $.ajax(ajax_object.ajax_url, {
      data: {
        action: 'get-my-activity',
        'member-id': $('#member-id').val(),
        'email': $('#email').val(),
        'chal-id': $('#chal-id').val()
      },
      beforeSend: beforeAjaxSend,
      complete: ajaxComplete,
      success: function (data) {
        if (!check_for_error(data)) {
          return;
        }

        $('#total-points').html(data.total_points);

        if ($.fn.DataTable.isDataTable('#my-activity-table')) {
          table.destroy();
          $('#my-activity-table').empty();
        }

        table = $('#my-activity-table').DataTable({
          data: data.data.slice(0),
          columns: data.columns.slice(0),
          paging: false,
          buttons: ['copyHtml5', 'csv', 'excel', 'pdf', 'print'],
          dom: 'Bfrtip',
          searching: false
        });

        $('.fa-trash-alt').click(delete_activity);
      },
      error: function (xhr, status, error) {
        console.error(error);
      },
      method: 'post',
      dataType: 'json'
    })
  }

  /**
   * Function to save a participants activity
   */
  function save_activity() {
    var start = $(this).parent().parent();
    var act_type = $(start).find('.type').val();
    var act_value = '';

    if (!validate_entry(start))
      return;

    if (act_type == 'checkbox') {
      var length = $(start).find("input[type='checkbox']:checked").length;

      if (length > 1) {
        act_value = [];
        for (var x = 0; x < length; x++) {
          act_value.push($(start).find("input[type='checkbox']:checked").eq(x).val());
        }
      } else {
        act_value = $(start).find("input[type='checkbox']:checked").val();
      }
    } else if (act_type == 'radio') {
      act_value = $(start).find("input[type='radio']:checked").val();
    } else if (act_type == 'long-text') {
      act_value = $(start).find('textarea').val();
    } else {
      act_value = $(start).find('input.value').val();
    }

    $.ajax(ajax_object.ajax_url, {
      data: {
        action: 'save-entry',
        'chal-link': $('#chal-link').val(),
        'act-id': $(start).find('.id').val(),
        type: act_type,
        value: act_value,
        'member-id': $('#member-id').val(),
        'user-name': $('#user-name').val(),
        'user-email': $('#user-email').val()
      },
      beforeSend: beforeAjaxSend,
      complete: ajaxComplete,
      success: function (data) {
        $('#msg').empty();
        $('#msg').removeClass('err-msg,warn-msg');
        var duration = 1500;

        if (data.error) {
          $('#msg').html('<p>' + data.error + '</p>');
          $('#msg').addClass('err-msg');

          duration = 5000;
        } else if (data.warning) {
          $('#msg').html('<p>' + data.warning + '</p>');
          $('#msg').addClass('warn-msg');

          duration = 3000;
        } else {
          $('#msg').html('<p>Activity saved</p>');
        }

        $('#msg').css('top', $(start).position().top + 5);
        $('#msg').height($(start).height() + 2);

        $('#msg').slideToggle(300);
        setTimeout(function () {
          $('#msg').slideToggle(300);
        }, duration);
      },
      error: function (xhr, status, error) {
        console.error(error);
      },
      dataType: 'json',
      method: 'post'
    });
  }

  /**
   * Function to delete a specific activity
   */
  function delete_activity() {
    var button = $(this);
    $.ajax(ajax_object.ajax_url, {
      data: {
        'action': 'delete-participant-activity',
        'act-id': $(this).data('act-id'),
        'user-id': $(this).data('user-id'),
        'log-date': $(this).data('log-date'),
        'security': $('#_wpnonce').val()
      },
      beforeSend: beforeAjaxSend,
      complete: ajaxComplete,
      success: function (data) {
        if (!check_for_error(data)) {
          return;
        }

        table.row($(button).closest('tr').index()).remove().draw();
      },
      error: function (xhr, status, error) {
        console.error(error);
      },
      dataType: 'json',
      method: 'post'
    });
  }

  /**
   * Function to open printer friendly hidden page
   */
  function printer_friendly() {

  }

  /**
   * Function to validate participant activity entry
   */
  function validate_entry(act) {
    var numeric = /^\d+$/;
    var ret = true;
    var start = $(act);
    var type = $(act).find('input.type').val();

    $('#msg').empty();
    $('#msg').removeClass('err-msg');
    $('#msg').removeClass('warn-msg');
    $('#msg').css('top', $(start).position().top + 5);
    $('#msg').height($(start).height() + 2);

    if (!$('#member-id').val() || !$('#member-id').val().match(numeric)) {
      $('#msg').append('<p>That is an invalid member ID</p>');
      ret = false;
    }

    if (!$('#user-name').val()) {
      $('#msg').append('<p>You need to put your name in the form</p>');
      ret = false;
    }

    if (!$('#user-email').val()) {
      $('#msg').append('<p>You need to enter your e-mail address</p>');
      ret = false;
    }

    if (type == 'checkbox' || type == 'radio') {
      if (!$(act).find('input[type=' + type + ']:checked').length) {
        $('#msg').append('<p>You must select one of the options</p>');
        ret = false;
      }
    } else if (type == 'text') {
      if (!$(act).find('input[type="text"]').val().length) {
        $('#msg').append('<p>Invalid entry for this activity</p>');
        ret = false;
      }
    } else if (type == 'number') {
      var field = $(act).find('input[type="number"]');
      var num = $(field).val();
      var min = ($(field).attr('min') ? $(field).attr('min') : 0);
      var max = ($(field).attr('max') ? $(field).attr('max') : 0);
      if (!num.match(numeric)) {
        $('#msg').append('<p>Invalid entry for this activity</p>');
        ret = false;
      } else if (parseInt(num) < 1) {
        $('#msg').append('<p>Invalid entry for this activity, positive numbers only</p>');
        ret = false;
      } else if (min > 0 && parseInt(num) < min) {
        $('#msg').append(
          '<p>Invalid entry for this activity, number must be greater than or equal to ' + min
          + '</p>');
        ret = false;
      } else if (max > 0 && parseInt(num) > max) {
        $('#msg').append(
          '<p>Invalid entry for this activity, number must be lesser than or equal to ' + max
          + '</p>');
        ret = false;
      }
    }

    if (!ret) {
      $('#msg').slideToggle(300);
      $('#msg').addClass('warn-msg');
      setTimeout(function () {
        $('#msg').slideToggle(300);
        $('#msg').empty();
      }, 5000);
    }

    return ret;
  }

  /**
   * Function to check for errors returned from the AJAX request
   *
   * @return boolean Returns true if everything is okay, false otherwise
   */
  function check_for_error(data) {
    $('#msg').empty();
    $('#msg').removeClass('err-msg,warn-msg');
    var err = false;

    if (data == '0') {
      $('#msg').html("<p>There was an error</p>");
      $('#msg').addClass('err-msg');
      err = true;
    } else if (data.error) {
      $('#msg').html('<p>' + data.error + '</p>');
      $('#msg').addClass('err-msg');
      err = true;
    } else if (data.warning) {
      $('#msg').html('<p>' + data.warning + '</p>');
      $('#msg').addClass('warn-msg');
      err = true;
    } else if (data.success) {
      $('#msg').html('<p>' + data.success + '</p>');
    }

    if ($('#msg').html()) {
      $('#msg').show(300);
    }

    if (err) {
      setTimeout(function () {
        $('#msg').hide(300);
        $('#msg').empty();
      }, 3000);
    } else {
      setTimeout(function () {
        $('#msg').hide(300);
        $('#msg').empty();
      }, 1500);
    }

    return !err;
  }

  /**
   * Function to call before sending the AJAX request
   */
  function beforeAjaxSend() {
    $('#loading,#waiting').show();
    $('#waiting').animate({
      'opacity': '0.5'
    }, 300, 'linear');
  }

  /**
   * Function to call at the completion of an AJAX request
   */
  function ajaxComplete() {
    $('#loading,#waiting').hide();
    $('#waiting').animate({
      'opacity': '0'
    }, 300, 'linear');
  }

  var opts = {
    lines: 25, // The number of lines to draw
    length: 25, // The length of each line
    width: 5, // The line thickness
    radius: 50, // The radius of the inner circle
    scale: 1, // Scales overall size of the spinner
    corners: 1, // Corner roundness (0..1)
    color: '#000', // #rgb or #rrggbb or array of colors
    opacity: 0.25, // Opacity of the lines
    rotate: 0, // The rotation offset
    direction: 1, // 1: clockwise, -1: counterclockwise
    speed: .5, // Rounds per second
    trail: 60, // Afterglow percentage
    fps: 20, // Frames per second when using setTimeout() as a fallback
    // for CSS
    zIndex: 2e9, // The z-index (defaults to 2000000000)
    className: 'spinner', // The CSS class to assign to the spinner
    top: '50%', // Top position relative to parent
    left: '50%', // Left position relative to parent
    shadow: false, // Whether to render a shadow
    hwaccel: false, // Whether to use hardware acceleration
    position: 'absolute' // Element positioning
  };
  var target, spinner, table;

  $('.text-max').keyup(function () {
    $('#text-len-' + $(this).attr('id')).text($(this).val().length);
  });

  $('#registered-challenges,#upcoming-challenges,#past-challenges').DataTable({
    paging: false,
    searching: false
  });
  $('.tooltip-field').tooltip({
    show: {
      effect: 'slideDown',
      delay: 100
    },
    hide: {
      effect: 'slideUp',
      delay: 250
    }
  });

  dialog = $("#dialog-form").dialog({
    autoOpen: false,
    height: 500,
    width: 350,
    modal: true,
    buttons: {
      "Join Challenge": join_challenge,
      Cancel: function () {
        dialog.dialog("close");
      }
    },
    close: function () {
      form[0].reset();
      // allFields.removeClass( "ui-state-error" );
    }
  });

  form = dialog.find("form").on("submit", function (event) {
    event.preventDefault();
    join_challenge();
  });
  $("#join-challenge").button().on("click", function () {
    dialog.dialog("open");
  });

  $('.save').click(save_activity);
  $('#get-activity').click(get_my_activity);
  $('#msg').hide();
  if ($('#loading')) {
    target = document.getElementById('loading');
    spinner = new Spinner(opts).spin(target);
  }
  if ($('#my-activity-body tr').length) {
    table = $('#my-activity-table').DataTable({
      paging: false,
      buttons: ['copyHtml5', 'csv', 'excel', 'pdf', 'print'],
      dom: 'Bfrtip',
      searching: false,
      stripClasses: ['odd-row', 'even-row'],
      order: [[0, 'asc'], [2, 'asc']]
    });
    $('.fa-trash-alt').click(delete_activity);
  }

});
