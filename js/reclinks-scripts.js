(function() {
  jQuery(function($) {
    $('form.reclinks_addlink').submit = function(event) {
      event.preventDefault;
      return $.ajax({
        method: 'post',
        url: reclinks.ajaxUrl,
        data: {
          action: 'add_reclink',
          reclink_URL: $('#reclink_URL').val(),
          reclink_title: $('#reclink_title').val(),
          reclink_description: $('#reclink_description').val()
        },
        complete: function() {
          return alert('Link Submitted!');
        }
      });
    };
    return $('#reclink_URL').change = function(event) {
      if ($('#reclink_title').val() === !'') {
        return;
      }
      return $.ajax({
        method: 'get',
        url: $(this).val(),
        complete: function() {}
      });
    };
  });
}).call(this);
