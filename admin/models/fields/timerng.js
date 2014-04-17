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

window.addEvent("domready",function() {
	var us_trange_submit = Joomla.submitbutton;
	Joomla.submitbutton = function (bval,btyp) {
		var elms = $$('#item-form .tpni');
		if (!elms.length) alert('Warning: cannot set period values. Defaults will be used.');
		for (var n=0; n<elms.length; n++) {
			var ni = $(elms[n]);
			var en = ni.get('name').split('_')[1];
			var muls = $('tpsi_'+en);
			var mul = muls.options[muls.selectedIndex].value;
			$('tpcv_'+en).set('value',ni.get('value')*mul);
			}
		us_trange_submit(bval,btyp);
		};
});