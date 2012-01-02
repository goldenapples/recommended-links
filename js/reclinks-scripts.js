(function() {
  jQuery(function($) {
    $('form.reclinks_addlink').bind('submit', function(event) {
      event.preventDefault;
      alert('submitting');
      $.ajax(reclinks.ajaxUrl + '?action=add_reclink', {
        type: 'post',
        data: $(this).serialize(),
        complete: function() {
          return alert('Link Submitted!');
        }
      });
      return false;
    });
    $('form.reclinks_vote button').bind('click', function(event) {
      var form, vote;
      event.preventDefault;
      form = $(this).parent('form');
      vote = $(this).data('vote');
      $.ajax(reclinks.ajaxUrl + '?action=vote_reclink', {
        type: 'post',
        data: form.serialize() + '&vote=' + vote,
        complete: function(r) {
          var response;
          response = $.parseJSON(r.responseText);
          if (response.exception) {
            return window.location.href = reclinks.loginUrl + '&msg=reclinks-login';
          } else {
            return form.find('.votescore').text(response.newCount);
          }
        }
      });
      return false;
    });
    $('#reclink_URL').bind('change', function(event) {
      var linkUrl;
      linkUrl = $(this).val();
      $.ajax(reclinks.ajaxUrl + '?action=check_reclink_title', {
        type: 'post',
        data: {
          url: linkUrl
        },
        complete: function(r) {
          var response;
          response = $.parseJSON(r.responseText);
          if (response.title) {
            return $('#reclink_title').val(response.title);
          }
        }
      });
      return null;
    });
    return null;
  });
}).call(this);
