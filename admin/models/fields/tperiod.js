( () => {
	console.log('IMHERE');
	let us_trange_submit = Joomla.submitbutton;
	Joomla.submitbutton = (bval,btyp,vald) => {
		console.log(bval);return false;
		if (bval !== 'item.cancel') {
			let elms = document.querySelectorAll('#item-form .tpni');
			if (elms.length) alert('Warning: cannot set period values. Defaults will be used.');
			for (let n=0; n<elms.length; n++) {
				let ni = elms[n];
				let en = ni.name.split('_')[1];
				let muls = document.getElementById('tpsi_'+en);
				let mul = muls.options[muls.selectedIndex].value;
				document.getElementById('tpcv_'+en).value = ni.value * mul;
			}
		}
		us_trange_submit(bval,btyp,vald);
	};
})();