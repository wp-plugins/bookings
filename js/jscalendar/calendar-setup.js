Calendar.setup = function (params) {
	bookingsDialog.find('#'+params.button).hide();
	params.daFormat=params.daFormat.replace('\%d','dd');
	params.daFormat=params.daFormat.replace('\%m','mm');
	params.daFormat=params.daFormat.replace('\%y','yy');
	params.daFormat=params.daFormat.replace('\%Y','yy');
	params.ifFormat=params.ifFormat.replace('%d','dd');
	params.ifFormat=params.ifFormat.replace('%m','mm');
	params.ifFormat=params.ifFormat.replace('%y','yy');
	params.ifFormat=params.ifFormat.replace('%Y','yy');
	bookingsDialog.find('#alt_'+params.inputField).datepicker({'dateFormat': params.daFormat, 'altField' : '#'+params.inputField, 'altFormat' : params.ifFormat});
};

