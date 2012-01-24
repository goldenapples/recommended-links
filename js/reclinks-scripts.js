(function() {
  jQuery(function($) {
    $('form.reclinks_addlink').bind('submit', function(event) {
      var form;
      event.preventDefault;
      form = $(this).fadeOut();
      $.ajax(reclinks.ajaxUrl + '?action=add_reclink', {
        type: 'post',
        data: form.serialize(),
        complete: function() {
          form.find;
          form[0].reset();
          return form.prepend("<div class='message'><strong>" + reclinks.messages_linkSubmitted + "</strong></div>").fadeIn();
        }
      });
      return false;
    });
    $('form.reclinks_vote button').bind('click', function(event) {
      var button, form, vote;
      event.preventDefault;
      button = $(this);
      form = $(this).parent('form');
      form.find('button').removeClass('current-vote');
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
            button.addClass('current-vote');
            return form.find('.votescore').text(response.newCount);
          }
        }
      });
      return false;
    });
    $('#reclink_URL').bind('change', function(event) {
      var linkUrl;
      linkUrl = $(this).val();
      $.ajax('http://query.yahooapis.com/v1/public/yql', {
        type: 'get',
        data: {
          q: "select * from html where url='" + linkUrl + "' and xpath='/html/head/title|/html/head/link[@rel=\"icon\"]|/html/head/link[@rel=\"shortcut icon\"]'",
          format: 'json'
        },
        dataType: 'json',
        success: function(r) {
          var response, title;
          response = r.query.results;
          if (!response) {
            alert(reclinks.messages_error404);
            return false;
          }
          title = response.title;
          if (!title) {
            alert(reclinks.messages_errorNoTitle);
            return false;
          }
          if ($('#reclink_title').val() !== !'') {
            return $('#reclink_title').val(title);
          }
        }
      });
      return null;
    });
    return null;
  });
}).call(this);
