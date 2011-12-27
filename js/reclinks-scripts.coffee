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
	$('form.reclinks_vote button').bind 'click', (event) ->
		event.preventDefault
		form = $(this).parent('form')
		vote = $(this).data('vote')
		$.ajax
			type: 'post'
			url: reclinks.ajaxUrl + '?action=vote_reclink'
			data: form.serialize() + '&vote=' + vote
			complete: (r) ->
				response = $.parseJSON(r.responseText)
				form.next('.votescore').text( response.newCount );
		return false
	null
