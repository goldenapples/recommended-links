jQuery ($) ->
	$('form.reclinks_addlink').bind 'submit', (event) ->
		event.preventDefault
		alert 'submitting'
		$.ajax
			type: 'post'
			url: reclinks.ajaxUrl + '?action=add_reclink'
			data: $(this).serialize()
			complete: () ->
				alert 'Link Submitted!'
		return false

