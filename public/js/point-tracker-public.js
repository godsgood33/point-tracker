jQuery(function($)
{
  /**
   * Function for user to join a challenge
   */
  function join_challenge()
  {
    var member_id = prompt("Please enter your member ID", "Member ID");
    if(!member_id.match(/^[\d]+$/)) {
      alert("Invalid member ID");
      return;
    }
    
    $.ajax(ajax_object.ajax_url, {
      data : {
        'action' : 'join-challenge',
        'chal-id' : $('#chal-link').val(),
        'member-id' : member_id
      },
      beforeSend : beforeAjaxSend,
      complete : ajaxComplete,
      success : function(data)
      {
        if (data.error) {
          console.error(data.error);
        } else {
          alert(data.success);
          location.href = ajax_object.chal_page + '/?chal=' + id;
        }
      },
      error : function(xhr, status, error)
      {
        console.error(error);
      },
      dataType : 'json',
      method : 'post'
    });
  }

  /**
   * Function to get the participants activity
   */
  function get_my_activity()
  {
    $('#msg').html("");
    var numeric = /^\d+$/;
    if (!$('#member-id').val() || !$('#member-id').val().match(numeric)) {
      $('#msg').append("<div>Please enter a valid member ID</div>");
      $('#msg').slideToggle(300);
      $('#msg').addClass('warn-msg');
      setTimeout(function()
      {
        $('#msg').slideToggle(300);
      }, 5000);

      return false;
    } else if (!$('#email').val()) {
      $('#msg').append("<div>Please enter your email</div>");
      $('#msg').slideToggle(300);
      $('#msg').addClass('warn-msg');
      setTimeout(function()
      {
        $('#msg').slideToggle(300);
      }, 5000);
      return false;
    }

    $.ajax(ajax_object.ajax_url, {
      data : {
        action : 'get-my-activity',
        'member-id' : $('#member-id').val(),
        'email' : $('#email').val(),
        'chal-id' : $('#chal-id').val()
      },
      beforeSend : beforeAjaxSend,
      complete : ajaxComplete,
      success : function(data)
      {
        if (!check_for_error(data)) {
          return;
        }

        $('#total-points').html(data.total_points);

        if ($.fn.DataTable.isDataTable('#my-activity-table')) {
          table.destroy();
          $('#my-activity-table').empty();
        }

        table = $('#my-activity-table').DataTable({
          data : data.data.slice(0),
          columns : data.columns.slice(0),
          paging : false,
          buttons : [ 'copyHtml5', 'csv', 'excel', 'pdf', 'print' ],
          dom : 'Bfrtip',
          searching : false
        });

        $('.fa-trash-alt').click(delete_activity);
      },
      error : function(xhr, status, error)
      {
        console.error(error);
      },
      method : 'post',
      dataType : 'json'
    })
  }

  /**
   * Function to save a participants activity
   */
  function save_activity()
  {
    var start = $(this).parent().parent();
    var type = $(start).find('.type').val();
    var value = '';

    if (!validate_entry(start))
      return;

    if (type == 'checkbox') {
      var length = $(start).find("input[type='checkbox']:checked").length;

      if (length > 1) {
        value = [];
        for (var x = 0; x < length; x++) {
          value.push($(start).find("input[type='checkbox']:checked").eq(x).val());
        }
      } else {
        value = $(start).find("input[type='checkbox']:checked").val();
      }
    } else if (type == 'radio') {
      value = $(start).find("input[type='radio']:checked").val();
    } else {
      value = $(start).find('input.value').val();
    }

    $.ajax(ajax_object.ajax_url, {
      data : {
        'action' : 'save-entry',
        'act-id' : $(start).find('.id').val(),
        'type' : type,
        'value' : value,
        'member-id' : $('#member-id').val(),
        'user-name' : $('#user-name').val(),
        'user-email' : $('#user-email').val()
      },
      beforeSend : beforeAjaxSend,
      complete : ajaxComplete,
      success : function(data)
      {
        $('#msg').html('');
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
        setTimeout(function()
        {
          $('#msg').slideToggle(300);
        }, duration);
      },
      error : function(xhr, status, error)
      {
        console.error(error);
      },
      dataType : 'json',
      method : 'post'
    });
  }

  /**
   * Function to delete a specific activity
   */
  function delete_activity()
  {
    var button = $(this);
    $.ajax(ajax_object.ajax_url, {
      data : {
        'action' : 'delete-participant-activity',
        'act-id' : $(this).data('act-id'),
        'user-id' : $(this).data('user-id'),
        'log-date' : $(this).data('log-date'),
        'security' : $('#_wpnonce').val()
      },
      beforeSend : beforeAjaxSend,
      complete : ajaxComplete,
      success : function(data)
      {
        if (!check_for_error(data)) {
          return;
        }

        table.row().remove().draw();
      },
      error : function(xhr, status, error)
      {
        console.error(error);
      },
      dataType : 'json',
      method : 'post'
    });
  }

  /**
   * Function to validate participant activity entry
   */
  function validate_entry(act)
  {
    var numeric = /^\d+$/;
    var ret = true;
    var type = $(act).find('input.type').val();

    $('#msg div').remove();
    $('#msg').removeClass('err-msg');
    $('#msg').removeClass('warn-msg');

    if (!$('#member-id').val() || !$('#member-id').val().match(numeric)) {
      $('#msg').append('<div>That is an invalid member ID</div>');
      ret = false;
    }

    if (!$('#user-name').val()) {
      $('#msg').append('<div>You need to put your name in the form</div>');
      ret = false;
    }

    if (!$('#user-email').val()) {
      $('#msg').append('<div>You need to enter your e-mail address</div>');
      ret = false;
    }

    if (type == 'checkbox' || type == 'radio') {
      if (!$(act).find('input[type=' + type + ']:checked').length) {
        $('#msg').append('<div>You must select one of the options</div>');
        ret = false;
      }
    } else if (type == 'text') {
      if (!$(act).find('input[type="text"]').val().length) {
        $('#msg').append('<div>Invalid entry for this activity</div>');
        ret = false;
      }
    } else if (type == 'number') {
      var num = $(act).find('input[type="number"]').val();
      if (!num.match(numeric)) {
        $('#msg').append('<div>Invalid entry for this activity</div>');
        ret = false;
      } else if (parseInt(num) < 1) {
        $('#msg').append('<div>Invalid entry for this activity, positive numbers only</div>');
        ret = false;
      }
    }

    if (!ret) {
      $('#msg').slideToggle(300);
      $('#msg').addClass('warn-msg');
      setTimeout(function()
      {
        $('#msg').slideToggle(300);
      }, 5000);
    }

    return ret;
  }

  /**
   * Function to check for errors returned from the AJAX request
   * 
   * @return boolean Returns true if everything is okay, false otherwise
   */
  function check_for_error(data)
  {
    $('#msg div').remove();
    $('#msg').removeClass('err-msg,warn-msg');
    var err = false;

    if (data == '0') {
      $('#msg').html("<div>There was an error</div>");
      $('#msg').addClass('err-msg');
      err = true;
    } else if (data.error) {
      $('#msg').html('<div>' + data.error + '</div>');
      $('#msg').addClass('err-msg');
      err = true;
    } else if (data.warning) {
      $('#msg').html('<div>' + data.warning + '</div>');
      $('#msg').addClass('warn-msg');
      err = true;
    } else if (data.success) {
      $('#msg').html('<div>' + data.success + '</div>');
    }

    if ($('#msg').html()) {
      $('#msg').show(300);
    }

    if (err) {
      setTimeout(function()
      {
        $('#msg').hide(300);
        $('#msg').html("");
      }, 3000);
    } else {
      setTimeout(function()
      {
        $('#msg').hide(300);
        $('#msg').html("");
      }, 1500);
    }

    return !err;
  }

  /**
   * Function to call before sending the AJAX request
   */
  function beforeAjaxSend()
  {
    $('#loading,#waiting').show();
    $('#waiting').animate({
      'opacity' : '0.5'
    }, 300, 'linear');
  }

  /**
   * Function to call at the completion of an AJAX request
   */
  function ajaxComplete()
  {
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
    className : 'spinner', // The CSS class to assign to the spinner
    top : '50%', // Top position relative to parent
    left : '50%', // Left position relative to parent
    shadow : false, // Whether to render a shadow
    hwaccel : false, // Whether to use hardware acceleration
    position : 'absolute' // Element positioning
  };
  var target, spinner, table;

  $('#registered-challenges,#upcoming-challenges,#past-challenges').DataTable({
    paging : false,
    searching : false
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
  $('#join-challenge').click(join_challenge);
  $('.save').click(save_activity);
  $('#get-activity').click(get_my_activity);
  $('#msg').hide();
  if ($('#loading')) {
    target = document.getElementById('loading');
    spinner = new Spinner(opts).spin(target);
  }
  if($('#my-activity-body tr').length) {
    table = $('#my-activity-table').DataTable({
      paging : false,
      buttons : ['copyHtml5', 'csv', 'excel', 'pdf', 'print'],
      dom : 'Bfrtip',
      searching : false,
      stripClasses : ['odd-row', 'even-row']
    });
    $('.fa-trash-alt').click(delete_activity);
  }

});
