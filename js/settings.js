$(document).ready(function(){
	$('#discourse_settings_form').change(function() {
		OC.msg.startSaving('#discourse_settings_msg');
		$.ajax({
			url: OC.filePath('user_discourse', 'ajax', 'save.php'),
			type: 'POST',
			data: $('#discourse_settings_form').serialize(),
			success: function(){
				OC.msg.finishedSuccess('#discourse_settings_msg', t('settings', 'Saved'));
			},
			error: function(xhr){
				OC.msg.finishedError('#discourse_settings_msg', xhr.responseJSON);
			}
		});
	});
});
