jQuery ($) ->
	$('form.reclinks_addlink').submit = (event) ->
		event.preventDefault
		$.ajax
			method: 'post'
			url: reclinks.ajaxUrl
			data:
				action: 'add_reclink'
				reclink_URL: $('#reclink_URL').val()
				reclink_title: $('#reclink_title').val()
				reclink_description: $('#reclink_description').val()
			complete: () ->
				alert 'Link Submitted!'
	$('#reclink_URL').change = (event) ->
		return if $('#reclink_title').val() is not ''
		$.ajax
			method: 'get'
			url: $(this).val()
			complete: () ->

