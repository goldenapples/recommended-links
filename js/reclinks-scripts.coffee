jQuery ($) ->
	$('form.reclinks_addlink').bind 'submit', (event) ->
		event.preventDefault
		form = $(this)
		$.ajax reclinks.ajaxUrl + '?action=add_reclink',
			type: 'post'
			data: form.serialize()
			complete: () ->
				form[0].reset()
		return false
	$('form.reclinks_vote button').bind 'click', (event) ->
		event.preventDefault
		form = $(this).parent('form')
		vote = $(this).data('vote')
		$.ajax reclinks.ajaxUrl + '?action=vote_reclink',
			type: 'post'
			data: form.serialize() + '&vote=' + vote
			complete: (r) ->
				response = $.parseJSON(r.responseText)
				if ( response.exception )
					window.location.href = reclinks.loginUrl + '&msg=reclinks-login'
				else
					form.find('.votescore').text( response.newCount );
		return false
	$('#reclink_URL').bind 'change', (event) ->
		linkUrl = $(this).val()
		$.ajax reclinks.ajaxUrl + '?action=check_reclink_title',
			type: 'post',
			data: { url: linkUrl },
			complete: (r) ->
				response = $.parseJSON(r.responseText)
				if ( response.title )
					$('#reclink_title').val response.title unless $('#reclink_title').val() is not ''
		null
	null
