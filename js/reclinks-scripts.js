(function() {
  jQuery(function($) {
    return $('form.reclinks_addlink').bind('submit', function(event) {
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
  });
}).call(this);
