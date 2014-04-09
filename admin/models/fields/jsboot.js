//mootools version
function show_hide (val) {
	var selm = $('jfp_site_auth');
	var gelm = $('jfp_group_auth');
	switch (val) {
	case '2':
		selm.setStyle('display','block');
		gelm.setStyle('display','none');
		break;
	case '1':
		selm.setStyle('display','none');
		gelm.setStyle('display','block');
		break;
	case '0':
		selm.setStyle('display','none');
		gelm.setStyle('display','none');
		break;
	}
}