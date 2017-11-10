$('#optiform').submit(function(e){
	e.preventDefault();
	$('#send').attr('disabled', true);
	$('#send').html('<span class="glyphicon glyphicon-cog spinner"></span>');
	ourform = $(this);
	$.post(ourform.attr('action'), ourform.serialize(), function(data){
		$('#console').append(data+'<div class="divider"></div>');
		$("#console").scrollTop($("#console")[0].scrollHeight);
		$('#send').html($('#send').data('text'));
		$('#send').attr('disabled', false);
	});
	return false;
});