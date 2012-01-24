jQuery ($) ->
	$('form.reclinks_addlink').bind 'submit', (event) ->
		event.preventDefault
		form = $(this).fadeOut()
		$.ajax reclinks.ajaxUrl + '?action=add_reclink',
			type: 'post'
			data: form.serialize()
			complete: () ->
				form.find
				form[0].reset()
				form.prepend("<div class='message'><strong>#{ reclinks.messages_linkSubmitted }</strong></div>").fadeIn()
		return false
	$('form.reclinks_vote button').bind 'click', (event) ->
		event.preventDefault
		button = $(this)
		form = $(this).parent('form')
		form.find('button').removeClass 'current-vote'
		vote = $(this).data('vote')
		$.ajax reclinks.ajaxUrl + '?action=vote_reclink',
			type: 'post'
			data: form.serialize() + '&vote=' + vote
			complete: (r) ->
				response = $.parseJSON(r.responseText)
				if ( response.exception )
					window.location.href = reclinks.loginUrl + '&msg=reclinks-login'
				else
					button.addClass 'current-vote'
					form.find('.votescore').text( response.newCount );
		return false
	$('#reclink_URL').bind 'change', (event) ->
		linkUrl = $(this).val()
		if linkUrl is '' return false
		$.ajax 'http://query.yahooapis.com/v1/public/yql',
			type: 'get',
			data: {
				q: "select * from html where url='#{ linkUrl }' and xpath='/html/head/title'",
				format: 'json'
				},
			dataType: 'json',
			success: (r) ->
				response = r.query.results
				unless response
					alert reclinks.messages_error404
					return false
				title = response.title
				unless title
					alert reclinks.messages_errorNoTitle
					return false
				$('#reclink_title').val title unless $('#reclink_title').val() is not ''
		null
	null
