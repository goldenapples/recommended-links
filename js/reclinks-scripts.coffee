jQuery ($) ->
	$('form.reclinks_addlink').bind 'submit', (event) ->
		event.preventDefault
		form = $(this)
		$.ajax reclinks.ajaxUrl + '?action=add_reclink',
			type: 'post'
			data: form.serialize()
			complete: () ->
				form.find
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
		$.ajax 'http://query.yahooapis.com/v1/public/yql',
			type: 'get',
			data: {
				q: "use 'http://www.datatables.org/data/htmlstring.xml' as htmlstring; select * from htmlstring where url='#{ linkUrl }'",
				format: 'json'
				},
			dataType: 'json',
			success: (r) ->
				response = r.query.results
				unless response
					alert '404 error?!'
					return false
				title = response.result.match( /<\s*title\s*>([^<]*)<\/title>/ )[1]
				unless title
					alert 'Document has no title?!'
				$('#reclink_title').val title unless $('#reclink_title').val() is not ''
		null
	null
