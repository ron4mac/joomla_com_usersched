// jQuery version
jQuery(document).ready(function() {
	var us_trange_submit = Joomla.submitbutton;
	Joomla.submitbutton = function (bval,btyp) {
		if (bval !== 'item.cancel') {
			var elms = document.querySelectorAll('#item-form .tpni');
			if (!elms.length) alert('Warning: cannot set period values. Defaults will be used.');
			for (var n=0; n<elms.length; n++) {
				var ni = elms[n];
				var en = ni.name.split('_')[1];
				var muls = document.getElementById('tpsi_'+en);
				var mul = muls.options[muls.selectedIndex].value;
				document.getElementById('tpcv_'+en).value = ni.value * mul;
			}
		}
		us_trange_submit(bval,btyp);
		};
});