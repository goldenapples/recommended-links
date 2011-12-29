(function() {
  jQuery(function($) {
    $('form.reclinks_addlink').bind('submit', function(event) {
      event.preventDefault;
      alert('submitting');
      $.ajax({
        type: 'post',
        url: reclinks.ajaxUrl + '?action=add_reclink',
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
      $.ajax({
        type: 'post',
        url: reclinks.ajaxUrl + '?action=vote_reclink',
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
    return null;
  });
}).call(this);
